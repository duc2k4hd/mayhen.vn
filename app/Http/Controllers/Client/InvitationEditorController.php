<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\WeddingWish;
use App\Services\InvitationTemplateService;
use App\Services\WeddingMusicService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationEditorController extends Controller
{
    public function __construct(
        protected InvitationTemplateService $templateService,
        protected WeddingMusicService $musicService
    ) {}

    /**
     * Show the quick creation & live editor studio.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $selectedTemplateSlug = $request->query('template');
        $editSlug = $request->query('edit') ?: $request->query('invitation');
        $editingInvitation = null;

        if ($editSlug) {
            $inv = Invitation::with(['wedding', 'template'])->where('slug', $editSlug)->first();
            if ($inv && $inv->isOwner()) {
                $editingInvitation = $inv;
                if (! $selectedTemplateSlug && $inv->template) {
                    $selectedTemplateSlug = $inv->template->slug;
                }
            }
        }

        // Mặc định chọn mẫu free nếu không chỉ định
        if (! $selectedTemplateSlug) {
            $template = InvitationTemplate::where('is_active', true)
                ->where('default_config->tier', 'free')
                ->first()
                ?? InvitationTemplate::where('is_active', true)->first();
        } else {
            $template = InvitationTemplate::where('slug', $selectedTemplateSlug)
                ->where('is_active', true)
                ->firstOrFail();
        }

        // Mẫu Plus & Pro bắt buộc phải đăng nhập mới được tạo/chỉnh sửa
        if ($template->tier !== 'free' && ! auth()->check()) {
            return redirect()->route('login', ['redirect' => $request->fullUrl()])
                ->with('warning', "Mẫu thiệp \"{$template->name}\" thuộc gói {$template->tier_label}. Vui lòng đăng nhập để bắt đầu tạo thiệp.");
        }

        $allTemplates = InvitationTemplate::where('is_active', true)->get();
        $schema = $this->templateService->getSchema($template->slug, $template->current_version ?? 'v1');
        $banks = config('banks.supported', []);

        return view('client.pages.editor', [
            'selectedTemplate' => $template,
            'allTemplates' => $allTemplates,
            'schema' => $schema,
            'banks' => $banks,
            'editingInvitation' => $editingInvitation,
        ]);
    }

    /**
     * Save draft invitation from editor.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $templateId = (int) $request->input('template_id');
        $template = InvitationTemplate::findOrFail($templateId);

        // Chuẩn hóa input: hỗ trợ cả dạng phẳng và dạng lồng nhau
        $rawWedding = $request->input('wedding', []);
        if (empty($rawWedding) && $request->has('groom_name')) {
            $rawWedding = [
                'groom_name' => $request->input('groom_name'),
                'bride_name' => $request->input('bride_name'),
                'groom_parents' => $request->input('groom_parents'),
                'bride_parents' => $request->input('bride_parents'),
                'wedding_date' => $request->input('wedding_date'),
                'venue_name' => $request->input('venue_name'),
                'venue_address' => $request->input('venue_address'),
                'story' => $request->input('story'),
                'cover_photo_url' => $request->input('cover_photo_url'),
                'music_url' => $request->input('music_url'),
                'bank_name' => $request->input('bank_name'),
                'bank_account_number' => $request->input('bank_account_number'),
                'bank_account_name' => $request->input('bank_account_name'),
                'extra_data' => $request->input('extra_data', []),
            ];
        }

        // Khử toàn diện chuỗi URL tạm thời blob: tránh lưu vào database
        $sanitizeBlobs = function (mixed $value) use (&$sanitizeBlobs): mixed {
            if (is_array($value)) {
                return array_map($sanitizeBlobs, $value);
            }
            if (is_string($value) && str_starts_with(trim($value), 'blob:')) {
                return null;
            }

            return $value;
        };
        $rawWedding = $sanitizeBlobs($rawWedding);

        $extraData = (array) ($rawWedding['extra_data'] ?? []);

        // Validate và sanitize họ tên: tối đa 4 từ, tối đa 30 ký tự, không số/ký tự đặc biệt, chống XSS
        if (! empty($rawWedding['groom_name'])) {
            $rawWedding['groom_name'] = $this->validateAndSanitizeName($rawWedding['groom_name'], 'Họ và tên Chú Rể');
        }
        if (! empty($rawWedding['bride_name'])) {
            $rawWedding['bride_name'] = $this->validateAndSanitizeName($rawWedding['bride_name'], 'Họ và tên Cô Dâu');
        }
        if (! empty($extraData['groom_father'])) {
            $extraData['groom_father'] = $this->validateAndSanitizeName($extraData['groom_father'], 'Họ tên Cha (Nhà Trai)');
        }
        if (! empty($extraData['groom_mother'])) {
            $extraData['groom_mother'] = $this->validateAndSanitizeName($extraData['groom_mother'], 'Họ tên Mẹ (Nhà Trai)');
        }
        if (! empty($extraData['bride_father'])) {
            $extraData['bride_father'] = $this->validateAndSanitizeName($extraData['bride_father'], 'Họ tên Cha (Nhà Gái)');
        }
        if (! empty($extraData['bride_mother'])) {
            $extraData['bride_mother'] = $this->validateAndSanitizeName($extraData['bride_mother'], 'Họ tên Mẹ (Nhà Gái)');
        }

        // Tối ưu và sanitize các trường nhập khác chống hack & XSS
        if (! empty($extraData['event_party_address'])) {
            $extraData['event_party_address'] = mb_substr(trim(strip_tags((string) $extraData['event_party_address'])), 0, 255);
        }
        if (! empty($extraData['event_party_time'])) {
            $extraData['event_party_time'] = mb_substr(trim(strip_tags((string) $extraData['event_party_time'])), 0, 30);
        }
        if (! empty($extraData['groom_bank_account'])) {
            $extraData['groom_bank_account'] = preg_replace('/[^a-zA-Z0-9]/', '', (string) $extraData['groom_bank_account']);
        }
        if (! empty($extraData['bride_bank_account'])) {
            $extraData['bride_bank_account'] = preg_replace('/[^a-zA-Z0-9]/', '', (string) $extraData['bride_bank_account']);
        }
        $rawWedding['extra_data'] = $extraData;

        // Tự động ghép tên phụ mẫu Nhà Trai & Nhà Gái từ các trường cha mẹ riêng lẻ nếu có
        if (empty($rawWedding['groom_parents']) && (! empty($extraData['groom_father']) || ! empty($extraData['groom_mother']))) {
            $father = trim((string) ($extraData['groom_father'] ?? ''));
            $mother = trim((string) ($extraData['groom_mother'] ?? ''));
            if ($father !== '' && $mother !== '') {
                $rawWedding['groom_parents'] = 'ÔNG: '.$father.' & BÀ: '.$mother;
            } elseif ($father !== '') {
                $rawWedding['groom_parents'] = 'ÔNG: '.$father;
            } elseif ($mother !== '') {
                $rawWedding['groom_parents'] = 'BÀ: '.$mother;
            }
        }
        if (empty($rawWedding['bride_parents']) && (! empty($extraData['bride_father']) || ! empty($extraData['bride_mother']))) {
            $father = trim((string) ($extraData['bride_father'] ?? ''));
            $mother = trim((string) ($extraData['bride_mother'] ?? ''));
            if ($father !== '' && $mother !== '') {
                $rawWedding['bride_parents'] = 'ÔNG: '.$father.' & BÀ: '.$mother;
            } elseif ($father !== '') {
                $rawWedding['bride_parents'] = 'ÔNG: '.$father;
            } elseif ($mother !== '') {
                $rawWedding['bride_parents'] = 'BÀ: '.$mother;
            }
        }

        // Validate cơ bản
        $request->validate([
            'template_id' => 'required|exists:invitation_templates,id',
        ]);

        // Tìm hoặc tạo guest user
        $user = auth()->user() ?? User::firstOrCreate(
            ['email' => 'guest@mayhen.vn'],
            [
                'name' => 'Khách Trải Nghiệm',
                'password' => bcrypt(Str::random(16)),
                'role' => 'customer',
            ]
        );

        $invitationId = $request->input('invitation_id') ? (int) $request->input('invitation_id') : null;
        if ($invitationId) {
            $existing = Invitation::find($invitationId);
            if (! $existing || ! $existing->isOwner()) {
                $invitationId = null; // Bảo mật: nếu không phải chủ nhân thì không cho ghi đè
            }
        }

        // Tiếp nhận và chuẩn hóa custom_config (bố cục, bảng màu, tem sáp)
        $rawConfig = $request->input('invitation.custom_config', $request->input('custom_config', []));
        if (is_string($rawConfig)) {
            $decoded = json_decode($rawConfig, true);
            $customConfig = is_array($decoded) ? $decoded : [];
        } else {
            $customConfig = is_array($rawConfig) ? $rawConfig : [];
        }

        $invitation = $this->templateService->saveInvitation($user, $template->id, [
            'wedding' => $rawWedding,
            'invitation' => [
                'custom_config' => $customConfig,
            ],
        ], $invitationId);

        // Lưu slug thiệp vào session để ghi nhận người tạo là chủ nhân thiệp
        $ownedSlugs = (array) $request->session()->get('owned_invitation_slugs', []);
        if (! in_array($invitation->slug, $ownedSlugs, true)) {
            $ownedSlugs[] = $invitation->slug;
            $request->session()->put('owned_invitation_slugs', $ownedSlugs);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'slug' => $invitation->slug,
                'preview_url' => route('invitation.show', $invitation->slug),
            ]);
        }

        return redirect()->route('invitation.show', $invitation->slug);
    }

    /**
     * Show published public invitation.
     */
    public function show(string $slug): View
    {
        $invitation = Invitation::with(['wedding.wishes', 'template'])
            ->where('slug', $slug)
            ->firstOrFail();

        $wedding = $invitation->wedding;
        $wishes = $wedding ? $wedding->wishes()->where('is_approved', true)->take(50)->get() : collect([]);
        $wishSubmitUrl = route('invitation.wish.store', $slug);
        $rsvpSubmitUrl = route('rsvp.submit', $slug);

        $clientIp = request()->ip();
        $currentRsvp = null;
        $myWish = null;

        if ($wedding) {
            $currentRsvp = GuestResponse::where('wedding_id', $wedding->id)
                ->where('ip_address', $clientIp)
                ->latest()
                ->first();

            $myWish = WeddingWish::where('wedding_id', $wedding->id)
                ->where('ip_address', $clientIp)
                ->first();
        }

        $isOwner = $invitation->isOwner();
        $rsvpStats = null;
        $rsvpResponses = collect([]);

        if ($isOwner && $wedding) {
            $rsvpResponses = GuestResponse::where('wedding_id', $wedding->id)
                ->latest()
                ->get();

            $rsvpStats = [
                'total' => $rsvpResponses->count(),
                'attending' => $rsvpResponses->where('attending_status', 'attending')->count(),
                'attending_guests' => (int) $rsvpResponses->where('attending_status', 'attending')->sum('adults_count'),
                'declined' => $rsvpResponses->where('attending_status', 'declined')->count(),
            ];
        }

        $template = $invitation->template;
        $customView = $template ? "client.invitations.{$template->slug}.template" : null;

        if ($customView && view()->exists($customView)) {
            return view($customView, [
                'invitation' => $invitation,
                'wedding' => $wedding,
                'template' => $template,
                'wishes' => $wishes,
                'wishSubmitUrl' => $wishSubmitUrl,
                'rsvpSubmitUrl' => $rsvpSubmitUrl,
                'currentRsvp' => $currentRsvp,
                'myWish' => $myWish,
                'isOwner' => $isOwner,
                'rsvpStats' => $rsvpStats,
                'rsvpResponses' => $rsvpResponses,
            ]);
        }

        return view('client.pages.public-invitation', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'template' => $template,
            'wishes' => $wishes,
            'wishSubmitUrl' => $wishSubmitUrl,
            'rsvpSubmitUrl' => $rsvpSubmitUrl,
            'currentRsvp' => $currentRsvp,
            'myWish' => $myWish,
            'isOwner' => $isOwner,
            'rsvpStats' => $rsvpStats,
            'rsvpResponses' => $rsvpResponses,
        ]);
    }

    /**
     * Upload an asset image and save into public storage (storage/app/public/weddings/photos/).
     */
    public function uploadAsset(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,webp,gif,heic|max:20480',
            'template_slug' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (empty($ext) || $ext === 'bin') {
            $ext = 'jpg';
        }
        $filename = Str::uuid()->toString().'.'.$ext;

        // Lưu trữ chuẩn tại storage/app/public/weddings/photos/
        $path = $file->storeAs('weddings/photos', $filename, 'public');
        $assetUrl = asset('storage/'.$path);

        return response()->json([
            'success' => true,
            'file_name' => $filename,
            'file_url' => $assetUrl,
            'relative_path' => $assetUrl,
            'storage_path' => $path,
        ]);
    }

    /**
     * API Tìm kiếm nhạc đám cưới thông minh từ storage/app/public/client/musics/singer/
     */
    public function musics(Request $request): JsonResponse
    {
        $keyword = (string) $request->query('q', '');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));
        $forceRefresh = $request->boolean('refresh', false);

        $result = $this->musicService->search($keyword, $page, $perPage, $forceRefresh);

        return response()->json([
            'success' => true,
            'data' => $result['data'],
            'pagination' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'per_page' => $result['per_page'],
                'total_pages' => $result['total_pages'],
            ],
        ]);
    }

    /**
     * API Autosave định kỳ từ Studio V2 kèm Optimistic Concurrency Control.
     */
    public function autosave(Request $request): JsonResponse
    {
        $request->validate([
            'invitation_id' => 'required|integer|exists:invitations,id',
            'revision_id' => 'required|integer|min:0',
            'draft_config' => 'nullable|array',
            'wedding_delta' => 'nullable|array',
        ]);

        $invitation = Invitation::with('wedding')->findOrFail((int) $request->input('invitation_id'));

        // Kiểm tra quyền sở hữu thiệp
        if (! $invitation->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền chỉnh sửa thiệp cưới này.',
            ], 403);
        }

        $result = $this->templateService->saveDraftWithLock(
            $invitation,
            (int) $request->input('revision_id'),
            (array) $request->input('draft_config', []),
            (array) $request->input('wedding_delta', [])
        );

        if (! empty($result['conflict'])) {
            return response()->json($result, 409);
        }

        return response()->json($result, 200);
    }

    /**
     * Validate và sanitize họ tên con người (tối đa 4 từ, tối đa 30 ký tự, chỉ chữ cái và khoảng trắng, chống XSS).
     */
    protected function validateAndSanitizeName(?string $name, string $fieldLabel): string
    {
        if ($name === null || trim($name) === '') {
            return '';
        }

        // Chống hack: loại bỏ toàn bộ thẻ HTML / tags và chuẩn hóa khoảng trắng
        $clean = trim(preg_replace('/\s+/u', ' ', strip_tags($name)));

        // Kiểm tra độ dài tối đa 30 ký tự tính cả dấu cách
        if (mb_strlen($clean) > 30) {
            throw ValidationException::withMessages([
                $fieldLabel => "{$fieldLabel} không được vượt quá 30 ký tự (hiện tại: ".mb_strlen($clean).' ký tự).',
            ]);
        }

        // Tách từ và kiểm tra không quá 4 từ
        $words = array_values(array_filter(preg_split('/\s+/u', $clean)));
        if (count($words) > 4) {
            throw ValidationException::withMessages([
                $fieldLabel => "{$fieldLabel} chỉ được tối đa 4 từ (hiện tại: ".count($words).' từ).',
            ]);
        }

        // Kiểm tra không chứa ký tự đặc biệt hoặc số (chỉ cho phép chữ cái tiếng Việt/Latin và khoảng trắng)
        if (! preg_match('/^[\p{L}\s]+$/u', $clean)) {
            throw ValidationException::withMessages([
                $fieldLabel => "{$fieldLabel} chỉ được chứa chữ cái và khoảng trắng, không chứa số hoặc ký tự đặc biệt.",
            ]);
        }

        return $clean;
    }
}
