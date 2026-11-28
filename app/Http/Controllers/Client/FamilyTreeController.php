<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\FamilyTree;
use App\Rules\NoProfanity;
use App\Services\FamilyTreeService;
use App\Services\PlanAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class FamilyTreeController extends Controller
{
    public function __construct(
        protected FamilyTreeService $familyTreeService,
        protected PlanAccessService $planAccess
    ) {}

    /**
     * Trang Landing SEO Gia Phả Online (/gia-pha) — Từ khóa: gia phả online, tạo gia phả, mẫu gia phả.
     */
    public function index(Request $request): View
    {
        $templates = $this->familyTreeService->getTemplates();
        $user = $request->user();
        $canCreateRealLink = $this->familyTreeService->canCreateRealLink($user);
        $currentPlanLabel = $this->planAccess->getTierLabel($this->planAccess->getEffectivePlanSlug($user));

        return view('client.pages.family-tree.index', compact(
            'templates',
            'canCreateRealLink',
            'currentPlanLabel'
        ));
    }

    /**
     * Xem thử mẫu phả đồ tương tác (/gia-pha/mau/{slug}).
     */
    public function templatePreview(string $slug): View
    {
        $template = $this->familyTreeService->getTemplate($slug);
        if (! $template) {
            abort(404);
        }

        $treeData = [
            'id' => null,
            'slug' => $template['slug'],
            'title' => $template['sample_title'],
            'family_surname' => $template['sample_surname'],
            'ancestral_hall' => $template['sample_hall'],
            'template_style' => $template['slug'],
            'layout_mode' => 'vertical',
            'intro_chronicle' => $template['sample_chronicle'],
            'clan_rules' => $template['sample_rules'],
            'members' => $template['sample_members'],
        ];

        $stats = $this->familyTreeService->computeStats($treeData['members']);

        return view('client.pages.family-tree.show', [
            'treeData' => $treeData,
            'template' => $template,
            'stats' => $stats,
            'isTemplatePreview' => true,
            'familyTreeModel' => null,
            'isLocked' => false,
        ]);
    }

    /**
     * Studio Tạo & Vẽ Cây Gia Phả Trực Quan Miễn Phí (/gia-pha/tao-thu).
     */
    public function studio(Request $request): View
    {
        $templates = $this->familyTreeService->getTemplates();
        $requestedTemplate = (string) $request->query('mau', 'hoang-kim-tu-duong');
        $selectedTemplate = $templates[$requestedTemplate] ?? $templates['hoang-kim-tu-duong'];

        $user = $request->user();
        $canCreateRealLink = $this->familyTreeService->canCreateRealLink($user);
        $currentPlanSlug = $this->planAccess->getEffectivePlanSlug($user);
        $currentPlanLabel = $this->planAccess->getTierLabel($currentPlanSlug);

        $editingTree = null;
        $treeSlug = trim((string) $request->query('tree', ''));
        if ($treeSlug !== '' && $user && ! $this->planAccess->isGuestUser($user)) {
            $found = FamilyTree::where('slug', $treeSlug)->first();
            if ($found && ($found->user_id === $user->id || $user->isAdmin())) {
                $editingTree = $found;
            }
        }

        $initialTreeData = $editingTree ? [
            'id' => $editingTree->id,
            'slug' => $editingTree->slug,
            'title' => $editingTree->title,
            'family_surname' => $editingTree->family_surname ?? '',
            'ancestral_hall' => $editingTree->ancestral_hall ?? '',
            'template_style' => $editingTree->template_style,
            'layout_mode' => $editingTree->layout_mode,
            'intro_chronicle' => $editingTree->intro_chronicle ?? '',
            'clan_rules' => $editingTree->clan_rules ?? '',
            'visibility' => $editingTree->visibility,
            'members' => is_array($editingTree->members_json) ? $editingTree->members_json : [],
        ] : [
            'id' => null,
            'slug' => '',
            'title' => $selectedTemplate['sample_title'],
            'family_surname' => $selectedTemplate['sample_surname'],
            'ancestral_hall' => $selectedTemplate['sample_hall'],
            'template_style' => $selectedTemplate['slug'],
            'layout_mode' => 'vertical',
            'intro_chronicle' => $selectedTemplate['sample_chronicle'],
            'clan_rules' => $selectedTemplate['sample_rules'],
            'visibility' => 'public',
            'members' => $selectedTemplate['sample_members'],
        ];

        return view('client.pages.family-tree.studio', compact(
            'templates',
            'selectedTemplate',
            'initialTreeData',
            'editingTree',
            'canCreateRealLink',
            'currentPlanSlug',
            'currentPlanLabel'
        ));
    }

    /**
     * Tạo Link Thật / Lưu Gia Phả Online lên hệ thống (Yêu cầu đăng nhập gói Thành Viên Premium trở lên).
     */
    public function publish(Request $request): JsonResponse
    {
        // Chặn sớm Payload Bomb trước khi parse/validate JSON lớn
        $contentLength = (int) $request->header('Content-Length', 0);
        $rawBodySize = strlen((string) $request->getContent());
        if ($contentLength > FamilyTreeService::MAX_REQUEST_BYTES || $rawBodySize > FamilyTreeService::MAX_REQUEST_BYTES) {
            return response()->json([
                'success' => false,
                'message' => 'Dung lượng dữ liệu gia phả vượt quá giới hạn cho phép (tối đa 2.5MB). Vui lòng giảm bớt ảnh chân dung hoặc kích thước dữ liệu.',
            ], 413);
        }

        $user = $request->user();
        if (! $this->familyTreeService->canCreateRealLink($user)) {
            $studioRedirect = route('family_tree.studio', [], false);

            return response()->json([
                'success' => false,
                'code' => 'PREMIUM_PLAN_REQUIRED',
                'message' => 'Bạn có thể tạo & xuất ảnh gia phả hoàn toàn miễn phí ngay tại đây. Để tạo link chia sẻ thật và lưu trữ đám mây, vui lòng đăng nhập tài khoản Mây Hẹn (tự động nhận miễn phí gói Thành Viên Premium).',
                'login_url' => route('login', ['redirect' => $studioRedirect]),
                'register_url' => route('register', ['redirect' => $studioRedirect]),
            ], 403);
        }

        $validated = $request->validate([
            'tree_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'min:3', 'max:180', new NoProfanity],
            'family_surname' => ['nullable', 'string', 'max:80', new NoProfanity],
            'ancestral_hall' => ['nullable', 'string', 'max:180', new NoProfanity],
            'template_style' => ['required', 'string', 'in:hoang-kim-tu-duong,thanh-ngoc-gia-phong,tram-mac-co-dien,toi-gian-hien-dai'],
            'layout_mode' => ['nullable', 'string', 'in:vertical,horizontal,chronicle'],
            'intro_chronicle' => ['nullable', 'string', 'max:6000', new NoProfanity],
            'clan_rules' => ['nullable', 'string', 'max:6000', new NoProfanity],
            'visibility' => ['nullable', 'string', 'in:public,password'],
            'access_password' => ['nullable', 'string', 'min:4', 'max:40'],
            'members' => ['required', 'array', 'min:1', 'max:'.FamilyTreeService::MAX_MEMBERS],
        ]);

        $members = $this->familyTreeService->sanitizeMembers($validated['members']);
        if ($members === []) {
            return response()->json([
                'success' => false,
                'message' => 'Cây gia phả cần có ít nhất 1 thành viên hợp lệ.',
            ], 422);
        }

        $encodedMembers = json_encode($members, JSON_UNESCAPED_UNICODE);
        if ($encodedMembers === false || strlen($encodedMembers) > FamilyTreeService::MAX_MEMBERS_JSON_BYTES) {
            return response()->json([
                'success' => false,
                'message' => 'Tổng dung lượng danh sách thành viên vượt giới hạn 2MB. Vui lòng giảm số lượng ảnh chân dung để cây tải nhanh hơn.',
            ], 422);
        }

        $effectivePlan = $this->planAccess->getEffectivePlanSlug($user);
        $treeId = isset($validated['tree_id']) ? (int) $validated['tree_id'] : null;

        // Khóa tuần tự theo user_id + DB::transaction để chống Race Condition tạo vượt hạn mức gói
        $lock = Cache::lock('family_tree_publish_user_'.$user->id, 10);

        $outcome = $lock->block(5, function () use ($user, $validated, $members, $effectivePlan, $treeId): array {
            return DB::transaction(function () use ($user, $validated, $members, $effectivePlan, $treeId): array {
                if ($treeId) {
                    $tree = FamilyTree::whereKey($treeId)->lockForUpdate()->first();
                    if (! $tree || ($tree->user_id !== $user->id && ! $user->isAdmin())) {
                        return [
                            'status' => 403,
                            'body' => [
                                'success' => false,
                                'message' => 'Bạn không có quyền chỉnh sửa gia phả này.',
                            ],
                        ];
                    }
                } else {
                    $maxTrees = $this->familyTreeService->getMaxTreesForUser($user);
                    $currentCount = FamilyTree::where('user_id', $user->id)->lockForUpdate()->count();
                    if (! $user->isAdmin() && $currentCount >= $maxTrees) {
                        return [
                            'status' => 403,
                            'body' => [
                                'success' => false,
                                'code' => 'PLAN_QUOTA_EXCEEDED',
                                'message' => "Gói {$this->planAccess->getTierLabel($effectivePlan)} hiện tại cho phép lưu tối đa {$maxTrees} gia phả link thật. Vui lòng xóa bớt bản cũ hoặc nâng cấp gói Plus/Pro để mở rộng.",
                                'pricing_url' => route('pricing'),
                            ],
                        ];
                    }

                    $tree = new FamilyTree;
                    $tree->user_id = $user->id;
                    $tree->slug = $this->familyTreeService->generateUniqueSlug($validated['title']);
                    $tree->plan_slug = $effectivePlan;
                    $tree->expires_at = $this->planAccess->calculateInvitationExpiresAt($effectivePlan);
                }

                $visibility = $validated['visibility'] ?? 'public';
                $tree->title = $validated['title'];
                $tree->family_surname = $validated['family_surname'] ?? '';
                $tree->ancestral_hall = $validated['ancestral_hall'] ?? '';
                $tree->template_style = $validated['template_style'];
                $tree->layout_mode = $validated['layout_mode'] ?? 'vertical';
                $tree->intro_chronicle = $validated['intro_chronicle'] ?? '';
                $tree->clan_rules = $validated['clan_rules'] ?? '';
                $tree->members_json = $members;
                $tree->visibility = $visibility;
                $tree->is_published = true;

                if ($visibility === 'password' && ! empty($validated['access_password'])) {
                    $tree->access_password = Hash::make($validated['access_password']);
                } elseif ($visibility === 'public') {
                    $tree->access_password = null;
                }

                $tree->save();

                return [
                    'status' => 200,
                    'body' => [
                        'success' => true,
                        'message' => 'Đã xuất bản link Gia Phả Online thành công!',
                        'tree_id' => $tree->id,
                        'slug' => $tree->slug,
                        'public_url' => route('family_tree.show', $tree->slug),
                        'studio_url' => route('family_tree.studio', ['tree' => $tree->slug]),
                        'expires_at_label' => $tree->expires_at ? $tree->expires_at->format('d/m/Y') : 'Vĩnh viễn',
                    ],
                ];
            });
        });

        return response()->json($outcome['body'], $outcome['status']);
    }

    /**
     * Danh sách Gia Phả đã tạo link thật của tài khoản (/gia-pha/cua-toi).
     */
    public function myTrees(Request $request): View
    {
        $user = $request->user();
        $trees = FamilyTree::where('user_id', $user->id)
            ->latest('updated_at')
            ->get();

        $currentPlanSlug = $this->planAccess->getEffectivePlanSlug($user);
        $currentPlanLabel = $this->planAccess->getTierLabel($currentPlanSlug);
        $maxTrees = $this->familyTreeService->getMaxTreesForUser($user);

        return view('client.pages.family-tree.my-trees', compact(
            'trees',
            'currentPlanSlug',
            'currentPlanLabel',
            'maxTrees'
        ));
    }

    /**
     * Xóa gia phả của chính chủ.
     */
    public function destroy(Request $request, string $slug): RedirectResponse
    {
        $user = $request->user();
        $tree = FamilyTree::where('slug', $slug)->firstOrFail();

        if ($tree->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        $tree->delete();

        return redirect()->route('family_tree.my_trees')
            ->with('success', 'Đã xóa cây gia phả khỏi danh sách lưu trữ.');
    }

    /**
     * Trang xem Link Thật Gia Phả Online (/gia-pha/{slug}) — Luôn gắn noindex bảo vệ dữ liệu dòng họ.
     */
    public function show(Request $request, string $slug): View
    {
        $tree = FamilyTree::where('slug', $slug)->where('is_published', true)->first();
        if (! $tree) {
            abort(404);
        }

        if ($tree->isExpired()) {
            return view('client.pages.family-tree.expired', ['tree' => $tree]);
        }

        $user = $request->user();
        $isOwnerOrAdmin = $user && ($user->id === $tree->user_id || $user->isAdmin());
        $sessionKey = 'family_tree_unlocked_'.$tree->id;
        $isLocked = $tree->visibility === 'password'
            && ! empty($tree->access_password)
            && ! $isOwnerOrAdmin
            && ! $request->session()->get($sessionKey, false);

        if (! $isLocked) {
            // Khử trùng lượt xem theo IP trong 10 phút để chống spam F5 ghi DB liên tục
            $viewDedupKey = 'ft_view:'.$tree->id.':'.sha1((string) $request->ip());
            if (Cache::add($viewDedupKey, 1, now()->addMinutes(10))) {
                $tree->increment('view_count');
            }
        }

        $templates = $this->familyTreeService->getTemplates();
        $template = $templates[$tree->template_style] ?? $templates['hoang-kim-tu-duong'];
        $members = (! $isLocked && is_array($tree->members_json)) ? $tree->members_json : [];

        $treeData = [
            'id' => $tree->id,
            'slug' => $tree->slug,
            'title' => $tree->title,
            'family_surname' => $tree->family_surname ?? '',
            'ancestral_hall' => $tree->ancestral_hall ?? '',
            'template_style' => $tree->template_style,
            'layout_mode' => $tree->layout_mode,
            'intro_chronicle' => $isLocked ? '' : ($tree->intro_chronicle ?? ''),
            'clan_rules' => $isLocked ? '' : ($tree->clan_rules ?? ''),
            'members' => $members,
        ];

        $stats = $this->familyTreeService->computeStats($members);

        return view('client.pages.family-tree.show', [
            'treeData' => $treeData,
            'template' => $template,
            'stats' => $stats,
            'isTemplatePreview' => false,
            'familyTreeModel' => $tree,
            'isLocked' => $isLocked,
        ]);
    }

    /**
     * Mở khóa gia phả đặt mật khẩu (có chống brute-force theo slug + IP).
     */
    public function unlock(Request $request, string $slug): RedirectResponse
    {
        $tree = FamilyTree::where('slug', $slug)->where('is_published', true)->firstOrFail();

        $rateKey = 'ft_unlock:'.$tree->id.':'.sha1((string) $request->ip());
        if (RateLimiter::tooManyAttempts($rateKey, 8)) {
            $seconds = RateLimiter::availableIn($rateKey);

            return back()->withErrors([
                'password' => "Bạn đã thử sai quá nhiều lần. Vui lòng chờ {$seconds} giây rồi thử lại.",
            ]);
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'max:40'],
        ]);

        if (! empty($tree->access_password) && Hash::check($validated['password'], $tree->access_password)) {
            RateLimiter::clear($rateKey);
            $request->session()->put('family_tree_unlocked_'.$tree->id, true);

            return redirect()->route('family_tree.show', $slug);
        }

        RateLimiter::hit($rateKey, 300);

        return back()->withErrors(['password' => 'Mật khẩu gia phả chưa chính xác. Vui lòng thử lại.']);
    }
}
