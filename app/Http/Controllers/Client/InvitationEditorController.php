<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\WeddingInvitationCreatedMail;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Rules\NoProfanity;
use App\Services\AiStoryWriterService;
use App\Services\AuditLogger;
use App\Services\CloudflareR2StorageService;
use App\Services\InvitationTemplateCatalog;
use App\Services\InvitationTemplateService;
use App\Services\PublicInvitationPresenter;
use App\Services\StudioDecorationLibraryService;
use App\Services\WeddingAnalyticsService;
use App\Services\WeddingConsistencyGuardianService;
use App\Services\WeddingMusicService;
use App\Services\WeddingStorageService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationEditorController extends Controller
{
    /**
     * Xóa sạch cache kho mẫu thông qua seam InvitationTemplateCatalog.
     */
    public static function clearMemoization(): void
    {
        InvitationTemplateCatalog::invalidate();
    }

    /**
     * Lấy toàn bộ danh sách mẫu thiệp đang hoạt động qua InvitationTemplateCatalog.
     */
    public static function getAllActiveTemplates(): Collection
    {
        return InvitationTemplateCatalog::allActive();
    }

    /**
     * Lấy mẫu thiệp mặc định (ưu tiên gói Free) qua InvitationTemplateCatalog.
     */
    public static function getDefaultTemplate(?Collection $allTemplates = null): ?InvitationTemplate
    {
        if ($allTemplates !== null) {
            return $allTemplates->first(fn ($t) => ($t->tier === 'free')) ?? $allTemplates->first();
        }

        return InvitationTemplateCatalog::defaultFree();
    }

    /**
     * Lấy một mẫu thiệp theo slug qua InvitationTemplateCatalog.
     */
    public static function getTemplateBySlug(string $slug, ?Collection $allTemplates = null): ?InvitationTemplate
    {
        if ($allTemplates !== null) {
            return $allTemplates->firstWhere('slug', $slug);
        }

        return InvitationTemplateCatalog::bySlug($slug);
    }

    public function __construct(
        protected InvitationTemplateService $templateService,
        protected WeddingMusicService $musicService,
        protected WeddingAnalyticsService $analyticsService,
        protected CloudflareR2StorageService $r2Storage
    ) {}

    /**
     * Show the quick creation & live editor studio.
     * Tối ưu hóa cực hạn cho hàng triệu người dùng:
     * - Zero Database Query cho khách vãng lai và truy cập mẫu thiệp (99.9% traffic).
     * - In-Memory Static RAM Memoization trong worker PHP.
     * - Application Cache tầng 2 (Redis / File).
     * - Edge CDN Caching qua ETag & HTTP 304 Not Modified.
     */
    public function create(Request $request): Response|View|RedirectResponse
    {
        $selectedTemplateSlug = (string) $request->query('template', '');
        $editSlug = (string) ($request->query('edit') ?: $request->query('invitation') ?: '');
        $editId = $request->query('invitation_id') ? (int) $request->query('invitation_id') : null;
        $editingInvitation = null;

        if ($editSlug !== '' || $editId) {
            $invQuery = Invitation::with(['wedding', 'template']);
            $inv = $editSlug !== ''
                ? $invQuery->where('slug', $editSlug)->first()
                : $invQuery->find($editId);
            if ($inv && ($inv->isOwner() || (auth()->check() && auth()->user()->isAdmin()))) {
                $editingInvitation = $inv;
                if ($selectedTemplateSlug === '' && $inv->template) {
                    $selectedTemplateSlug = $inv->template->slug;
                }
            }
        }

        // mayhen-curated là chức năng tạo thiệp tự động AI (/tao-thiep-tu-dong), không phải mẫu thiệp tĩnh
        if ($selectedTemplateSlug === 'mayhen-curated' && $editingInvitation === null) {
            return redirect()->route('curated.create');
        }

        // Lấy toàn bộ active templates từ Static RAM hoặc Cache (0 DB queries)
        $allTemplates = self::getAllActiveTemplates();

        // Tìm mẫu thiệp được chọn từ Collection
        if ($selectedTemplateSlug === '') {
            $template = self::getDefaultTemplate($allTemplates);
            if (! $template) {
                abort(404, 'Hiện chưa có mẫu thiệp cưới nào được kích hoạt.');
            }
        } else {
            $template = self::getTemplateBySlug($selectedTemplateSlug, $allTemplates);
            if (! $template && $selectedTemplateSlug === 'mayhen-curated' && $editingInvitation !== null) {
                $template = InvitationTemplate::where('slug', 'mayhen-curated')->first();
            }
            if (! $template) {
                abort(404, "Không tìm thấy mẫu thiệp cưới '{$selectedTemplateSlug}'.");
            }
        }

        // Mẫu Premium, Plus & Pro bắt buộc phải đăng nhập mới được tạo/chỉnh sửa
        if ($template->tier !== 'free' && ! auth()->check()) {
            return redirect()->route('login', ['redirect' => $request->fullUrl()])
                ->with('warning', "Mẫu thiệp \"{$template->name}\" thuộc gói {$template->tier_label}. Vui lòng đăng nhập để bắt đầu tạo thiệp.");
        }

        $schema = $this->templateService->getSchema($template->slug, $template->current_version ?? 'v1', $template);
        $isCuratedEditing = false;
        if ($editingInvitation) {
            $editCfg = is_array($editingInvitation->custom_config)
                ? $editingInvitation->custom_config
                : (json_decode((string) $editingInvitation->custom_config, true) ?? []);
            $isCuratedEditing = ! empty($editCfg['is_curated_seven']) || ! empty($editCfg['is_curated']) || $template->slug === 'mayhen-curated';
        } elseif ($template->slug === 'mayhen-curated' || $request->boolean('curated_seven')) {
            $isCuratedEditing = true;
        }

        if ($isCuratedEditing && ! empty($schema['sections']) && is_array($schema['sections'])) {
            $curatedAllowed = ['hero', 'couple', 'story', 'parents', 'groom_family', 'bride_family', 'countdown_calendar', 'countdown', 'events', 'wedding_event', 'timeline', 'album', 'gallery'];
            $schema['sections'] = array_values(array_filter($schema['sections'], function ($sec) use ($curatedAllowed) {
                return in_array($sec['id'] ?? '', $curatedAllowed, true);
            }));
        }

        $banks = config('banks.supported', []);
        $v3Catalog = StudioDecorationLibraryService::frontendCatalog();

        // Nếu user đã đăng nhập hoặc đang chỉnh sửa thiệp cá nhân: trả về view động riêng biệt (không cache qua CDN)
        if (auth()->check() || $editingInvitation !== null) {
            return view('client.pages.editor', [
                'selectedTemplate' => $template,
                'allTemplates' => $allTemplates,
                'schema' => $schema,
                'banks' => $banks,
                'editingInvitation' => $editingInvitation,
                'isCuratedEditing' => $isCuratedEditing,
                'v3Catalog' => $v3Catalog,
            ]);
        }

        // Khách vãng lai: Tối ưu Edge CDN Caching qua ETag & 304 Not Modified
        $etag = '"mayhen_editor_v3_'.md5(
            $template->slug.'_'.
            ($template->updated_at ?? '2026-01-01').'_'.
            $allTemplates->count().'_'.
            PlatformSetting::getBrandName()
        ).'"';

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'public, max-age=1800, stale-while-revalidate=86400');
        }

        return response()
            ->view('client.pages.editor', [
                'selectedTemplate' => $template,
                'allTemplates' => $allTemplates,
                'schema' => $schema,
                'banks' => $banks,
                'editingInvitation' => $editingInvitation,
                'isCuratedEditing' => $isCuratedEditing,
                'v3Catalog' => $v3Catalog,
            ])
            ->header('Cache-Control', 'public, max-age=1800, stale-while-revalidate=86400')
            ->header('ETag', $etag);
    }

    /**
     * Save draft invitation from editor.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate(array_merge(
            ['wedding' => 'nullable|array'],
            $this->weddingInputRules('wedding.'),
            $this->weddingInputRules(''),
        ));
        $templateId = (int) $request->input('template_id');
        $template = InvitationTemplate::where('is_active', true)->findOrFail($templateId);

        $existingIdForAuth = $request->input('invitation_id') ? (int) $request->input('invitation_id') : null;
        $existingForAuth = $existingIdForAuth ? Invitation::with('wedding')->find($existingIdForAuth) : null;
        $isSessionOwnerSavingSameTemplate = $existingForAuth !== null
            && $existingForAuth->isOwner()
            && (int) $existingForAuth->template_id === (int) $template->id;

        if ($template->tier !== 'free' && ! auth()->check() && ! $isSessionOwnerSavingSameTemplate) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mẫu thiệp thuộc gói nâng cao, vui lòng đăng nhập để sử dụng.',
                ], 403);
            }

            return redirect()->route('login', ['redirect' => route('invitation.create', ['template' => $template->slug])]);
        }

        // Chuẩn hóa input: hỗ trợ cả dạng phẳng và dạng lồng nhau
        $rawWedding = $request->input('wedding', []);
        if (empty($rawWedding) && $request->has('groom_name')) {
            $rawWedding = array_intersect_key($request->all(), array_flip([
                'groom_name', 'bride_name', 'groom_parents', 'bride_parents',
                'wedding_date', 'venue_name', 'venue_address', 'story',
                'cover_photo_url', 'music_url', 'bank_name',
                'bank_account_number', 'bank_account_name', 'extra_data',
            ]));
        }

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
        if (! empty($extraData['party_venue'])) {
            $extraData['party_venue'] = mb_substr(trim(strip_tags((string) $extraData['party_venue'])), 0, 150);
        }
        if (! empty($extraData['party_time'])) {
            $extraData['party_time'] = mb_substr(trim(strip_tags((string) $extraData['party_time'])), 0, 30);
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
            'invitation_id' => 'nullable|integer|min:1',
            'revision_id' => 'required_with:invitation_id|nullable|integer|min:0',
        ]);

        $existing = null;
        $invitationId = $request->input('invitation_id') ? (int) $request->input('invitation_id') : null;
        if ($invitationId) {
            $existing = Invitation::with('wedding')->findOrFail($invitationId);
            abort_unless($existing->isOwner(), 404);

            $existingConfig = is_array($existing->custom_config)
                ? $existing->custom_config
                : (json_decode((string) $existing->custom_config, true) ?? []);

            if (! empty($existingConfig['is_locked'])) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Thiệp cưới tạm thời bị khóa bởi Quản trị viên, không thể lưu hoặc xuất bản thay đổi.',
                    ], 403);
                }
                abort(403, 'Thiệp cưới tạm thời bị khóa bởi Quản trị viên, không thể lưu hoặc xuất bản thay đổi.');
            }
        }

        // Tìm hoặc tạo guest user
        $user = auth()->user() ?? User::firstOrCreate(
            ['email' => 'guest@mayhen.vn'],
            [
                'name' => 'Khách Trải Nghiệm',
                'password' => bcrypt(Str::random(16)),
                'role' => 'customer',
            ]
        );

        // Tiếp nhận và chuẩn hóa custom_config (bố cục, bảng màu, tem sáp)
        $rawConfig = $request->input('invitation.custom_config', $request->input('custom_config', []));
        if (is_string($rawConfig)) {
            $decoded = json_decode($rawConfig, true);
            $customConfig = is_array($decoded) ? $decoded : [];
        } else {
            $customConfig = is_array($rawConfig) ? $rawConfig : [];
        }

        // Kiểm tra và phát hiện thay đổi thông tin tài khoản ngân hàng mừng cưới (Section 35 Master PRD V3)
        if (! empty($existing) && $existing->wedding) {
            $oldWeddingExtra = is_array($existing->wedding->extra_data) ? $existing->wedding->extra_data : (json_decode((string) $existing->wedding->extra_data, true) ?? []);
            $oldGroomBank = (string) ($oldWeddingExtra['groom_bank_account'] ?? ($existing->wedding->bank_account_number ?? ''));
            $oldGroomBankName = (string) ($oldWeddingExtra['groom_bank_name'] ?? ($existing->wedding->bank_name ?? ''));
            $oldBrideBank = (string) ($oldWeddingExtra['bride_bank_account'] ?? '');
            $oldBrideBankName = (string) ($oldWeddingExtra['bride_bank_name'] ?? '');

            $newWeddingExtra = (array) ($rawWedding['extra_data'] ?? []);
            $newGroomBank = (string) ($newWeddingExtra['groom_bank_account'] ?? ($rawWedding['bank_account_number'] ?? ''));
            $newGroomBankName = (string) ($newWeddingExtra['groom_bank_name'] ?? ($rawWedding['bank_name'] ?? ''));
            $newBrideBank = (string) ($newWeddingExtra['bride_bank_account'] ?? '');
            $newBrideBankName = (string) ($newWeddingExtra['bride_bank_name'] ?? '');

            $isBankChanged = ($oldGroomBank !== $newGroomBank) || ($oldGroomBankName !== $newGroomBankName) || ($oldBrideBank !== $newBrideBank) || ($oldBrideBankName !== $newBrideBankName);

            if ($isBankChanged) {
                AuditLogger::log(
                    'BANK_ACCOUNT_CHANGED',
                    'Wedding',
                    $existing->wedding->id,
                    "Tài khoản mừng cưới của thiệp [{$existing->slug}] đã được cập nhật.",
                    [
                        'invitation_id' => $existing->id,
                        'invitation_slug' => $existing->slug,
                        'old_accounts' => [
                            'groom' => $oldGroomBank ? "{$oldGroomBankName} - {$oldGroomBank}" : null,
                            'bride' => $oldBrideBank ? "{$oldBrideBankName} - {$oldBrideBank}" : null,
                        ],
                        'new_accounts' => [
                            'groom' => $newGroomBank ? "{$newGroomBankName} - {$newGroomBank}" : null,
                            'bride' => $newBrideBank ? "{$newBrideBankName} - {$newBrideBank}" : null,
                        ],
                        'user_id' => $user->id,
                        'ip' => $request->ip(),
                    ]
                );

                $customConfig['last_bank_updated_at'] = now()->toIso8601String();
            }
        }

        $invitation = $this->templateService->saveInvitation($user, $template->id, [
            'wedding' => $rawWedding,
            'invitation' => [
                'custom_config' => $customConfig,
            ],
        ], $invitationId, $invitationId ? (int) $request->input('revision_id') : null);

        // Gửi email chúc mừng tạo thiệp cưới thành công (đưa vào Queue và đánh dấu flag chống gửi lặp)
        $latestConfig = is_array($invitation->custom_config) ? $invitation->custom_config : [];
        if (empty($latestConfig['created_mail_sent_at']) && ! empty($user->email)) {
            $latestConfig['created_mail_sent_at'] = now()->toIso8601String();
            $invitation->forceFill(['custom_config' => $latestConfig])->save();

            try {
                Mail::to($user->email)->queue(new WeddingInvitationCreatedMail($invitation));
            } catch (\Throwable $e) {
                Log::warning('Không thể đưa email chúc mừng tạo thiệp cưới vào hàng đợi: '.$e->getMessage(), [
                    'invitation_id' => $invitation->id,
                    'user_id' => $user->id,
                ]);
            }
        }

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
    public function show(string $slug): View|RedirectResponse
    {
        $outcome = app(PublicInvitationPresenter::class)->resolve($slug, request());

        if ($outcome['type'] === 'redirect') {
            return redirect()->route($outcome['redirect_route'], $outcome['redirect_params']);
        }

        return view($outcome['view'], $outcome['data']);
    }

    /**
     * Upload an asset image and save into public storage (storage/app/public/weddings/photos/).
     */
    public function uploadAsset(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,webp,gif,heic|max:20480',
            'template_slug' => 'nullable|string|max:120',
            'invitation_id' => 'nullable|integer|min:1',
        ]);

        $file = $request->file('file');

        // Xác minh MIME hình ảnh thật bằng finfo/getimagesize chống giả mạo đuôi tệp
        $realPath = $file->getRealPath();
        $mimeType = $file->getMimeType() ?: '';
        if ($mimeType !== 'image/heic' && $mimeType !== 'image/heif') {
            $imgInfo = $realPath ? @getimagesize($realPath) : false;
            if ($imgInfo === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tệp tải lên không phải là hình ảnh hợp lệ.',
                ], 422);
            }
        }

        // Kiểm tra Quyền Sở Hữu, Khóa Thiệp & Hạn Mức Dung Lượng Lưu Trữ (Section 26 Master PRD V3)
        if ($invitationId = $request->input('invitation_id')) {
            $invitation = Invitation::with('wedding')->findOrFail((int) $invitationId);
            $isAuthorized = $invitation->isOwner() || (auth()->check() && auth()->user()->isAdmin());
            if (! $isAuthorized) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền tải tệp vào thiệp cưới này.',
                ], 403);
            }

            if ($invitation->isLocked()) {
                return response()->json([
                    'success' => false,
                    'locked' => true,
                    'message' => 'Thiệp cưới đang bị khóa bởi Quản trị viên, không thể tải thêm tệp.',
                ], 403);
            }

            if ($invitation->wedding) {
                $storageService = app(WeddingStorageService::class);
                if (! $storageService->canUpload($invitation->wedding, $invitation, $file->getSize())) {
                    $usage = $storageService->getStorageUsage($invitation->wedding, $invitation);

                    return response()->json([
                        'success' => false,
                        'message' => "Dung lượng lưu trữ của thiệp cưới đã vượt quá giới hạn gói ({$usage['limit_mb']} MB). Bạn đã dùng {$usage['used_mb']} MB. Vui lòng nâng cấp gói hoặc dọn bớt ảnh trong Kho Tệp.",
                        'quota_exceeded' => true,
                    ], 422);
                }
            }
        } else {
            // Giới hạn tổng dung lượng tải tạm theo phiên VÀ theo IP cho khách chưa đăng nhập (chống xóa cookie spam R2)
            $isGuestUnauthenticated = ! auth()->check();
            $sessionLimitBytes = $isGuestUnauthenticated ? (15 * 1024 * 1024) : (40 * 1024 * 1024);
            $guestBytes = (int) $request->session()->get('studio_guest_uploaded_bytes', 0);
            if (($guestBytes + $file->getSize()) > $sessionLimitBytes) {
                $limitMb = (int) ($sessionLimitBytes / (1024 * 1024));

                return response()->json([
                    'success' => false,
                    'message' => "Phiên soạn thảo tạm đã đạt giới hạn {$limitMb} MB ảnh tải lên. Vui lòng đăng nhập và lưu thiệp để tiếp tục.",
                    'quota_exceeded' => true,
                ], 422);
            }

            if ($isGuestUnauthenticated) {
                $ipCacheKey = 'studio_guest_upload_bytes_ip_'.sha1((string) $request->ip());
                $ipBytes = (int) Cache::get($ipCacheKey, 0);
                if (($ipBytes + $file->getSize()) > (15 * 1024 * 1024)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bạn đã đạt giới hạn tải ảnh cho khách trải nghiệm (15 MB/giờ). Vui lòng đăng nhập để tiếp tục tải ảnh.',
                        'quota_exceeded' => true,
                    ], 429);
                }
                Cache::put($ipCacheKey, $ipBytes + $file->getSize(), now()->addHour());
            }

            $request->session()->put('studio_guest_uploaded_bytes', $guestBytes + $file->getSize());
        }

        $allowedExtensions = ['jpeg', 'png', 'jpg', 'webp', 'gif', 'heic'];
        $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        if (! in_array($ext, $allowedExtensions, true)) {
            $ext = 'jpg';
        }
        $filename = Str::uuid()->toString().'.'.$ext;

        // Lưu trữ ưu tiên Cloudflare R2 Object Storage (tự động fallback local nếu có sự cố)
        $uploadResult = $this->r2Storage->upload($file, 'weddings/photos', $filename);

        return response()->json([
            'success' => true,
            'file_name' => $uploadResult['file_name'],
            'file_url' => $uploadResult['url'],
            'relative_path' => $uploadResult['relative_url'],
            'storage_path' => $uploadResult['storage_path'],
            'disk' => $uploadResult['disk'],
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
     * Gợi ý Lời ngỏ và Câu chuyện tình yêu bằng Trợ lý AI (TASK-V1-04 & FLOW-14).
     */
    public function generateAiStory(Request $request, AiStoryWriterService $aiService): JsonResponse
    {
        $validated = $request->validate([
            'groom_name' => ['nullable', 'string', 'max:50', new NoProfanity],
            'bride_name' => ['nullable', 'string', 'max:50', new NoProfanity],
            'keywords' => ['nullable', 'string', 'max:200', new NoProfanity],
            'how_we_met' => ['nullable', 'string', 'max:200', new NoProfanity],
            'tone' => ['nullable', 'string', 'in:romantic,minimal,cheerful'],
        ]);

        $groom = $validated['groom_name'] ?? 'Chú Rể';
        $bride = $validated['bride_name'] ?? 'Cô Dâu';
        $keywords = $validated['keywords'] ?? null;
        $tone = $validated['tone'] ?? 'romantic';
        $howWeMet = $validated['how_we_met'] ?? null;

        $data = $aiService->generate($groom, $bride, $keywords, $tone, $howWeMet);

        return response()->json($data);
    }

    /**
     * Bác Sĩ Đồng Bộ Thông Tin Trọng Yếu (Wedding Consistency Guardian - TASK-VAL-02).
     */
    public function consistencyCheck(int $invitationId, WeddingConsistencyGuardianService $guardian): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        abort_unless($invitation->isOwner(), 403, 'Bạn không có quyền kiểm tra thiệp cưới này.');

        $config = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode((string) $invitation->custom_config, true) ?? []);
        if (! empty($config['is_locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        $result = $guardian->inspect($invitation->wedding, $invitation);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * API Phân Tích Khác Biệt & Báo Cáo Tác Động Khi Sửa Ngày Giờ Thiệp Đã Gửi (TASK-VAL-09).
     */
    public function publishImpact(Request $request, int $invitationId, WeddingConsistencyGuardianService $guardian): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        abort_unless($invitation->isOwner(), 403, 'Bạn không có quyền thực hiện thao tác này.');

        $config = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode((string) $invitation->custom_config, true) ?? []);
        if (! empty($config['is_locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        $newData = $request->input('wedding', []);
        $result = $guardian->evaluatePublishImpact($invitation->wedding, is_array($newData) ? $newData : []);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * API Bảng Đánh Giá Độ Sẵn Sàng Ngày Cưới (Wedding Readiness Checklist - TASK-VAL-12).
     */
    public function readinessCheck(int $invitationId, WeddingConsistencyGuardianService $guardian): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        abort_unless($invitation->isOwner(), 403, 'Bạn không có quyền kiểm tra thiệp cưới này.');

        $config = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode((string) $invitation->custom_config, true) ?? []);
        if (! empty($config['is_locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        $result = $guardian->calculateReadinessChecklist($invitation->wedding, $invitation);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * API Bộ Phát Hiện Xung Đột Lịch Trình Lễ Cưới & Tiệc Cưới Hai Nhà (TASK-VAL-14).
     */
    public function collisionCheck(int $invitationId, WeddingConsistencyGuardianService $guardian): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        abort_unless($invitation->isOwner(), 403, 'Bạn không có quyền kiểm tra thiệp cưới này.');

        $config = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode((string) $invitation->custom_config, true) ?? []);
        if (! empty($config['is_locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        $result = $guardian->detectCeremonyCollisions($invitation->wedding, $invitation);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * API Autosave định kỳ từ Studio V2 kèm Optimistic Concurrency Control.
     */
    public function autosave(Request $request): JsonResponse
    {
        $request->validate(array_merge([
            'invitation_id' => 'required|integer|exists:invitations,id',
            'revision_id' => 'required|integer|min:0',
            'draft_config' => 'nullable|array',
            'wedding_delta' => 'nullable|array',
        ], $this->weddingInputRules('wedding_delta.')));

        $invitation = Invitation::with('wedding')->findOrFail((int) $request->input('invitation_id'));

        // Kiểm tra quyền sở hữu thiệp
        if (! $invitation->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền chỉnh sửa thiệp cưới này.',
            ], 403);
        }

        $invitationConfig = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode((string) $invitation->custom_config, true) ?? []);
        if (! empty($invitationConfig['is_locked'])) {
            return response()->json([
                'success' => false,
                'conflict' => true,
                'locked' => true,
                'message' => 'Thiệp cưới đang bị khóa bởi Quản trị viên, không thể tự động lưu nháp.',
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

    /** @return array<string, string> */
    private function weddingInputRules(string $prefix): array
    {
        $rules = [
            'groom_name' => 'sometimes|required|string|max:255',
            'bride_name' => 'sometimes|required|string|max:255',
            'wedding_date' => 'sometimes|required|date_format:Y-m-d',
            'groom_parents' => 'nullable|string|max:1000',
            'bride_parents' => 'nullable|string|max:1000',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'nullable|string|max:3000',
            'story' => 'nullable|string|max:10000',
            'cover_photo_url' => 'nullable|string',
            'music_url' => 'nullable|string|max:500',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:100',
            'extra_data' => 'nullable|array',
            'extra_data.gallery' => 'nullable|array|max:20',
            'extra_data.gallery.*' => 'nullable|string',
            'extra_data.timeline' => 'nullable|array|max:10',
            'extra_data.timeline.*' => 'nullable|array',
            'extra_data.timeline.*.title' => 'nullable|string|max:255',
            'extra_data.timeline.*.time' => 'nullable|string|max:100',
        ];
        foreach ([
            'groom_father', 'groom_mother', 'bride_father', 'bride_mother',
            'event_party_address', 'party_venue', 'party_time', 'event_party_time',
            'groom_bank_account', 'bride_bank_account',
        ] as $field) {
            $rules['extra_data.'.$field] = 'nullable|string';
        }

        $prefixed = [];
        foreach ($rules as $field => $rule) {
            $prefixed[$prefix.$field] = $rule;
        }

        return $prefixed;
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

    /**
     * Mở khóa thiệp cưới được bảo vệ bằng mật mã.
     */
    public function unlockPassword(Request $request, string $slug): RedirectResponse
    {
        $invitation = Invitation::where('slug', $slug)->firstOrFail();
        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode($invitation->custom_config, true) ?? []);

        $expectedPassword = $config['password'] ?? null;

        $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Vui lòng nhập mật mã bảo mật.',
        ]);

        $inputPassword = trim((string) $request->input('password'));
        $isValid = false;

        if ($expectedPassword) {
            $expectedStr = trim((string) $expectedPassword);
            if (str_starts_with($expectedStr, '$2y$') || str_starts_with($expectedStr, '$2a$')) {
                $isValid = Hash::check($inputPassword, $expectedStr);
            } else {
                $isValid = hash_equals($expectedStr, $inputPassword);
            }
        }

        if ($isValid) {
            session()->put("unlocked_invitation_{$invitation->id}", true);

            return redirect()->route('invitation.show', $slug);
        }

        return back()->with('password_error', 'Mật mã mở thiệp không chính xác, vui lòng thử lại.');
    }
}
