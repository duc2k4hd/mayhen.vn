<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Models\Wedding;
use App\Rules\NoProfanity;
use App\Services\AuditLogger;
use App\Services\CuratedInvitationService;
use App\Services\InvitationTemplateService;
use App\Services\PlanAccessService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CuratedInvitationController extends Controller
{
    public function __construct(
        protected CuratedInvitationService $curatedService,
        protected InvitationTemplateService $templateService,
        protected PlanAccessService $planAccess
    ) {}

    /**
     * Kiểm tra quyền sử dụng tính năng Tạo Thiệp Tự Động (yêu cầu đăng nhập + gói Plus/Pro, Admin miễn trừ).
     */
    protected function ensureCuratedPlusAccess(Request $request): ?JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'code' => 'UNAUTHENTICATED',
                'message' => 'Vui lòng đăng nhập tài khoản để sử dụng tính năng Tạo Thiệp Tự Động.',
                'login_url' => route('login', ['redirect' => $request->fullUrl()]),
            ], 401);
        }

        if (! $this->planAccess->canAccessCuratedStudio($user)) {
            return response()->json([
                'success' => false,
                'code' => 'PLUS_PLAN_REQUIRED',
                'message' => 'Tính năng Tạo Thiệp Tự Động AI Curated dành riêng cho thành viên Gói Plus (99.000đ) trở lên. Vui lòng nâng cấp để mở khóa.',
                'upgrade_url' => route('checkout.show', 'plus'),
            ], 403);
        }

        return null;
    }

    /**
     * Màn hình Wizard khởi tạo thiệp tự phối thông minh (Mục 1, 2, 3).
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $styles = $this->curatedService->getStyles();
        $palettes = $this->curatedService->getPalettes();
        $hasPlusAccess = $this->planAccess->canAccessCuratedStudio($user);
        $currentPlanSlug = $this->planAccess->getEffectivePlanSlug($user);

        return view('client.pages.curated.wizard', [
            'styles' => $styles,
            'palettes' => $palettes,
            'hasPlusAccess' => $hasPlusAccess,
            'currentPlanSlug' => $currentPlanSlug,
            'currentPlanLabel' => $this->planAccess->getTierLabel($currentPlanSlug),
        ]);
    }

    /**
     * Khởi tạo thiệp tự phối: AI viết nội dung + Mây Hẹn tự phối layout từ components chuẩn (Mục 1, 4, 7, 10).
     */
    public function generate(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $this->planAccess->canAccessCuratedStudio($user)) {
            if ($request->expectsJson()) {
                return $this->ensureCuratedPlusAccess($request);
            }

            return redirect()
                ->route('checkout.show', 'plus')
                ->with('warning', 'Tính năng Tạo Thiệp Tự Động AI Curated dành riêng cho Gói Plus (99.000đ) trở lên. Vui lòng nâng cấp để bắt đầu.');
        }

        $request->validate([
            'groom_name' => ['required', 'string', 'max:50', new NoProfanity],
            'bride_name' => ['required', 'string', 'max:50', new NoProfanity],
            'groom_father' => ['nullable', 'string', 'max:50', new NoProfanity],
            'groom_mother' => ['nullable', 'string', 'max:50', new NoProfanity],
            'bride_father' => ['nullable', 'string', 'max:50', new NoProfanity],
            'bride_mother' => ['nullable', 'string', 'max:50', new NoProfanity],
            'style' => ['required', 'string', Rule::in(array_keys($this->curatedService->getStyles()))],
            'palette' => ['required', 'string', Rule::in(array_keys($this->curatedService->getPalettes()))],
            'wedding_date' => ['nullable', 'date'],
            'party_time' => ['nullable', 'string', 'max:10'],
            'ceremony_type' => ['nullable', 'string', 'max:50', new NoProfanity],
            'venue_name' => ['nullable', 'string', 'max:100', new NoProfanity],
            'venue_address' => ['nullable', 'string', 'max:255', new NoProfanity],
            'layout_mode' => ['nullable', 'string', Rule::in(['curated_7', 'full_10'])],
        ], [
            'style.in' => 'Phong cách lựa chọn phải thuộc danh mục chuẩn của '.PlatformSetting::getBrandName().'.',
            'palette.in' => 'Bảng màu lựa chọn phải thuộc danh mục chuẩn của '.PlatformSetting::getBrandName().'.',
        ]);

        $style = (string) $request->input('style');
        $palette = (string) $request->input('palette');
        $isCuratedSevenMode = $request->input('layout_mode', 'curated_7') !== 'full_10';

        // 1. AI sinh nội dung cảm xúc chọn lọc (Mục 7)
        $aiContent = $this->curatedService->generateCuratedContent([
            'groom_name' => $request->input('groom_name'),
            'bride_name' => $request->input('bride_name'),
        ], $style);

        // 2. Mây Hẹn tự phối ngẫu nhiên layout từ các component 7 section đã kiểm duyệt (Mục 4, 5)
        $curatedLayout = $this->curatedService->assembleCuratedLayout($style, $palette);

        // 3. Chuẩn bị dữ liệu Wedding
        $groomName = trim((string) $request->input('groom_name'));
        $brideName = trim((string) $request->input('bride_name'));
        $rawWeddingDate = $request->input('wedding_date');
        $hasCustomWeddingDate = ! empty($rawWeddingDate);
        $weddingDate = $hasCustomWeddingDate ? $rawWeddingDate : now()->addMonths(2)->format('Y-m-d');
        $partyTime = trim((string) $request->input('party_time', '')) ?: '18:00';
        $ceremonyType = trim((string) $request->input('ceremony_type', '')) ?: ($aiContent['ceremony_type'] ?? 'Lễ Thành Hôn');

        $rawVenueName = $request->input('venue_name');
        $rawVenueAddress = $request->input('venue_address');
        $hasVenueInfo = ! empty($rawVenueName) || ! empty($rawVenueAddress);
        $venueName = $rawVenueName ? trim((string) $rawVenueName) : null;
        $venueAddress = $rawVenueAddress ? trim((string) $rawVenueAddress) : null;

        $extraData = [
            'groom_father' => trim((string) $request->input('groom_father', '')) ?: 'Ông Nguyễn Văn Hùng',
            'groom_mother' => trim((string) $request->input('groom_mother', '')) ?: 'Bà Lê Thị Thu',
            'groom_parents_address' => 'Tư gia Nhà Trai',
            'bride_father' => trim((string) $request->input('bride_father', '')) ?: 'Ông Trần Quốc Bảo',
            'bride_mother' => trim((string) $request->input('bride_mother', '')) ?: 'Bà Phạm Thị Mai',
            'bride_parents_address' => 'Tư gia Nhà Gái',
            'couple_quote' => $aiContent['couple_quote'],
            'story_title' => $aiContent['story_title'],
            'story_subtitle' => $aiContent['story_subtitle'],
            'story_content' => $aiContent['story_content'],
            'ceremony_type' => $ceremonyType,
            'party_time' => $partyTime,
            'party_venue' => $venueName,
            'event_party_address' => $venueAddress,
            'has_custom_wedding_date' => $hasCustomWeddingDate,
            'has_venue_info' => $hasVenueInfo,
            'ceremony_time' => '08:00',
            'ceremony_groom_time' => '10:00',
            'wedding_lunar_date' => 'Nhằm ngày lành tháng tốt năm Bính Ngọ',
            'timeline' => [
                ['time' => '17:30', 'title' => 'Đón tiếp quan khách'],
                ['time' => $partyTime, 'title' => 'Cử hành hôn lễ'],
                ['time' => '18:30', 'title' => 'Khai tiệc mừng'],
                ['time' => '20:00', 'title' => 'Chụp hình & Tiễn khách'],
            ],
            'gallery' => [
                'gallery-1.webp',
                'gallery-2.webp',
                'gallery-3.webp',
                'gallery-4.webp',
                'gallery-5.webp',
                'gallery-6.webp',
                'gallery-7.webp',
                'gallery-8.webp',
                'gallery-9.webp',
                'gallery-10.webp',
            ],
        ];

        // 4. Tìm kiếm template gốc Native tương ứng với style & giới hạn đúng tier của gói cước người dùng
        $allowedTiers = $this->planAccess->getAllowedTemplateTiers($user);
        $template = $this->curatedService->resolveNativeTemplate($style, $palette, $allowedTiers);

        // 5. Thực hiện tạo trong Transaction (Bắt buộc user đã đăng nhập & đạt gói Plus/Pro)
        $presentationToken = Str::random(32);

        $invitation = DB::transaction(function () use ($user, $template, $groomName, $brideName, $weddingDate, $venueName, $venueAddress, $extraData, $curatedLayout, $palette, $style, $presentationToken, $isCuratedSevenMode) {
            $wedding = Wedding::create([
                'user_id' => $user->id,
                'groom_name' => $groomName,
                'bride_name' => $brideName,
                'wedding_date' => $weddingDate,
                'venue_name' => $venueName,
                'venue_address' => $venueAddress,
                'cover_photo_url' => null,
                'extra_data' => $extraData,
            ]);

            $slug = Str::slug($groomName.'-va-'.$brideName.'-'.Str::lower(Str::random(6)));

            return Invitation::create([
                'wedding_id' => $wedding->id,
                'template_id' => $template->id,
                'slug' => $slug,
                'title' => 'Thiệp Cưới '.$groomName.' & '.$brideName,
                'version_type' => 'general',
                'template_version' => 'v1',
                'custom_config' => [
                    'curated_layout' => $curatedLayout,
                    'color_theme' => $palette,
                    'is_curated' => true,
                    'is_curated_seven' => $isCuratedSevenMode,
                    'source_template_slug' => $template->slug,
                    'style' => $style,
                    'presentation_token' => $presentationToken,
                    'first_presented_at' => null,
                ],
                'is_published' => true,
                'published_at' => now(),
                'revision_id' => 1,
            ]);
        });

        // Lưu slug vào session để nhận diện chủ nhân
        $ownedSlugs = (array) $request->session()->get('owned_invitation_slugs', []);
        if (! in_array($invitation->slug, $ownedSlugs, true)) {
            $ownedSlugs[] = $invitation->slug;
            $request->session()->put('owned_invitation_slugs', $ownedSlugs);
        }

        AuditLogger::log(
            'generate_curated_invitation',
            'invitation',
            $invitation->id,
            "Tự động tạo thiệp cưới tự phối thông minh phong cách [{$style}], bảng màu [{$palette}]",
            [
                'slug' => $invitation->slug,
                'style' => $style,
                'palette' => $palette,
                'sections_count' => 7,
            ]
        );

        $previewUrl = route('curated.preview', ['slug' => $invitation->slug, 'pt' => $presentationToken]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã tạo thiệp cưới tự phối thành công!',
                'slug' => $invitation->slug,
                'preview_url' => $previewUrl,
                'layout' => $curatedLayout,
            ]);
        }

        return redirect($previewUrl);
    }

    /**
     * Màn hình Live Crafting Preview: Dựng thiệp từ trên xuống dưới, chi tiết từ trái sang phải (Mục 8, 9).
     */
    public function preview(Request $request, string $slug): View|RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $this->planAccess->canAccessCuratedStudio($user)) {
            return redirect()
                ->route('checkout.show', 'plus')
                ->with('warning', 'Tính năng Tạo Thiệp Tự Động AI Curated yêu cầu Gói Plus (99.000đ) trở lên.');
        }

        $invitation = Invitation::with(['wedding', 'template'])->where('slug', $slug)->firstOrFail();
        $this->authorizeInvitationOwner($invitation);

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        // One-time presentation check: chỉ phát animation lần đầu tiên thực sự khi có token hợp lệ
        $requestToken = (string) $request->query('pt', '');
        $storedToken = (string) ($config['presentation_token'] ?? '');
        $firstPresentedAt = $config['first_presented_at'] ?? null;

        $shouldAnimate = false;
        if (! empty($requestToken) && ! empty($storedToken) && hash_equals($storedToken, $requestToken) && empty($firstPresentedAt)) {
            $shouldAnimate = true;
            // Tiêu thụ token một lần duy nhất để reload/back/forward hoặc copy link không bị replay
            $config['first_presented_at'] = now()->toIso8601String();
            $config['presentation_token'] = null;
            $invitation->custom_config = $config;
            $invitation->saveQuietly();
        }

        if ($request->boolean('crafting')) {
            $shouldAnimate = true;
        }

        $curatedLayout = $config['curated_layout'] ?? [];

        // Tương thích ngược: map dresscode cũ sang album
        if (isset($curatedLayout['sections']['dresscode']) && ! isset($curatedLayout['sections']['album'])) {
            $curatedLayout['sections']['album'] = $curatedLayout['sections']['dresscode'];
            unset($curatedLayout['sections']['dresscode']);
        }

        $style = $curatedLayout['style'] ?? 'romantic';
        $palette = $curatedLayout['palette'] ?? ($config['color_theme'] ?? 'ruby');

        $styles = $this->curatedService->getStyles();
        $palettes = $this->curatedService->getPalettes();
        $sectionDefinitions = $this->curatedService->getSectionDefinitions();
        $allowedTiers = $this->planAccess->getAllowedTemplateTiers($user, $invitation);
        $selectableTemplates = $this->curatedService->getSelectableTemplates($style, $allowedTiers);
        $isCuratedSeven = (bool) ($config['is_curated_seven'] ?? true);

        return view('client.pages.curated.preview', [
            'invitation' => $invitation,
            'wedding' => $invitation->wedding,
            'template' => $invitation->template,
            'curatedLayout' => $curatedLayout,
            'currentStyle' => $style,
            'currentPalette' => $palette,
            'styles' => $styles,
            'palettes' => $palettes,
            'sectionDefinitions' => $sectionDefinitions,
            'selectableTemplates' => $selectableTemplates,
            'isCuratedSeven' => $isCuratedSeven,
            'shouldAnimate' => $shouldAnimate,
        ]);
    }

    /**
     * Chuyển đổi sang mẫu gốc khác trong cùng phong cách - BẢO TOÀN MÀU SẮC & PHONG CÁCH (Master Prompt V3).
     */
    public function remix(Request $request, int $invitationId): JsonResponse
    {
        if ($denied = $this->ensureCuratedPlusAccess($request)) {
            return $denied;
        }

        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        if ($invitation->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang bị khóa bởi Quản trị viên, không thể chỉnh sửa.',
            ], 403);
        }

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $style = (string) ($config['style'] ?? ($config['curated_layout']['style'] ?? 'romantic'));
        $palette = (string) ($config['color_theme'] ?? ($config['curated_layout']['palette'] ?? 'ruby'));
        $currentSlug = $invitation->template->slug ?? ($config['source_template_slug'] ?? 'giua-muon-ngan-vi-sao');

        // Chọn mẫu gốc khác trong cùng phong cách & đúng phạm vi gói cước của người dùng
        $allowedTiers = $this->planAccess->getAllowedTemplateTiers($request->user(), $invitation);
        $newTemplate = $this->curatedService->resolveAlternativeTemplate($style, $currentSlug, $allowedTiers);

        // Bảo toàn dữ liệu 7 section & trạng thái hiện tại
        $curatedLayout = $this->curatedService->assembleCuratedLayout($style, $palette);

        $config['curated_layout'] = $curatedLayout;
        $config['source_template_slug'] = $newTemplate->slug;
        $config['style'] = $style;
        $config['color_theme'] = $palette;

        $invitation->template_id = $newTemplate->id;
        $invitation->custom_config = $config;
        $invitation->revision_id = (int) $invitation->revision_id + 1;
        $invitation->save();
        $invitation->setRelation('template', $newTemplate);

        return response()->json([
            'success' => true,
            'message' => 'Đã chuyển sang mẫu tuyển chọn ['.$newTemplate->name.'] thành công!',
            'template_name' => $newTemplate->name,
            'template_slug' => $newTemplate->slug,
            'layout' => $curatedLayout,
            'revision_id' => $invitation->revision_id,
        ]);
    }

    /**
     * Đổi riêng biến thể của một section cụ thể (Mục 9).
     */
    public function switchVariant(Request $request): JsonResponse
    {
        if ($denied = $this->ensureCuratedPlusAccess($request)) {
            return $denied;
        }

        $request->validate([
            'invitation_id' => 'required|integer|exists:invitations,id',
            'section' => 'required|string',
            'direction' => 'nullable|string|in:next,prev',
        ]);

        $invitation = Invitation::with('wedding')->findOrFail((int) $request->input('invitation_id'));
        $this->authorizeInvitationOwner($invitation);

        if ($invitation->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang bị khóa bởi Quản trị viên, không thể chỉnh sửa.',
            ], 403);
        }

        $sectionKey = (string) $request->input('section');
        $direction = (string) $request->input('direction', 'next');

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $curatedLayout = $config['curated_layout'] ?? [];

        $newLayout = $this->curatedService->switchSectionVariant($curatedLayout, $sectionKey, $direction);

        $config['curated_layout'] = $newLayout;
        $invitation->update([
            'custom_config' => $config,
            'revision_id' => (int) $invitation->revision_id + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Đã đổi biến thể mục [{$sectionKey}] thành công!",
            'layout' => $newLayout,
            'revision_id' => $invitation->revision_id,
        ]);
    }

    /**
     * Tùy biến nhanh trực tiếp từ màn hình Curated Preview (Đổi mẫu, đổi bảng màu, đổi phong cách, bật/tắt 7↔10 mục, sửa nội dung).
     */
    public function customize(Request $request, int $invitationId): JsonResponse
    {
        if ($denied = $this->ensureCuratedPlusAccess($request)) {
            return $denied;
        }

        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        if ($invitation->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang bị khóa bởi Quản trị viên, không thể chỉnh sửa.',
            ], 403);
        }

        $validated = $request->validate([
            'template_slug' => ['nullable', 'string', 'max:120'],
            'style' => ['nullable', 'string', Rule::in(array_keys($this->curatedService->getStyles()))],
            'palette' => ['nullable', 'string', Rule::in(array_keys($this->curatedService->getPalettes()))],
            'is_curated_seven' => ['nullable', 'boolean'],
            'groom_name' => ['nullable', 'string', 'max:50', new NoProfanity],
            'bride_name' => ['nullable', 'string', 'max:50', new NoProfanity],
            'groom_father' => ['nullable', 'string', 'max:60', new NoProfanity],
            'groom_mother' => ['nullable', 'string', 'max:60', new NoProfanity],
            'bride_father' => ['nullable', 'string', 'max:60', new NoProfanity],
            'bride_mother' => ['nullable', 'string', 'max:60', new NoProfanity],
            'wedding_date' => ['nullable', 'date'],
            'party_time' => ['nullable', 'string', 'max:20'],
            'ceremony_type' => ['nullable', 'string', 'max:50', new NoProfanity],
            'venue_name' => ['nullable', 'string', 'max:120', new NoProfanity],
            'venue_address' => ['nullable', 'string', 'max:255', new NoProfanity],
            'story_title' => ['nullable', 'string', 'max:100', new NoProfanity],
            'couple_quote' => ['nullable', 'string', 'max:400', new NoProfanity],
            'story_content' => ['nullable', 'string', 'max:1200', new NoProfanity],
        ]);

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $wedding = $invitation->wedding;
        $extra = is_array($wedding->extra_data) ? $wedding->extra_data : [];

        // 1. Đổi mẫu nguồn trực tiếp nếu người dùng chọn (kiểm tra quyền tier nghiêm ngặt)
        if (! empty($validated['template_slug'])) {
            $selectedTemplate = InvitationTemplate::where('slug', $validated['template_slug'])
                ->where('is_active', true)
                ->where('slug', '!=', 'mayhen-curated')
                ->first();

            if ($selectedTemplate) {
                if (! $this->planAccess->canUseTemplateTier($request->user(), $selectedTemplate->tier, $invitation)) {
                    $requiredTier = $this->planAccess->normalizeTier($selectedTemplate->tier);

                    return response()->json([
                        'success' => false,
                        'code' => 'TEMPLATE_TIER_LOCKED',
                        'message' => "Mẫu \"{$selectedTemplate->name}\" thuộc {$this->planAccess->getTierLabel($requiredTier)}. Vui lòng nâng cấp gói để sử dụng mẫu này.",
                        'upgrade_url' => route('checkout.show', $requiredTier === 'pro' ? 'pro' : 'plus'),
                    ], 403);
                }

                $invitation->template_id = $selectedTemplate->id;
                $config['source_template_slug'] = $selectedTemplate->slug;
                $invitation->setRelation('template', $selectedTemplate);
            }
        }

        // 2. Đổi phong cách & bảng màu
        $style = (string) ($validated['style'] ?? ($config['style'] ?? ($config['curated_layout']['style'] ?? 'romantic')));
        $palette = (string) ($validated['palette'] ?? ($config['color_theme'] ?? ($config['curated_layout']['palette'] ?? 'ruby')));

        $config['style'] = $style;
        $config['color_theme'] = $palette;
        if (! isset($config['curated_layout']) || ! is_array($config['curated_layout'])) {
            $config['curated_layout'] = $this->curatedService->assembleCuratedLayout($style, $palette);
        } else {
            $config['curated_layout']['style'] = $style;
            $config['curated_layout']['palette'] = $palette;
        }

        // 3. Bật/tắt chế độ 7 mục tinh gọn ↔ 10 mục đầy đủ
        if (array_key_exists('is_curated_seven', $validated) && $validated['is_curated_seven'] !== null) {
            $config['is_curated_seven'] = (bool) $validated['is_curated_seven'];
        }

        // 4. Cập nhật thông tin cưới & lời ngỏ
        if (! empty($validated['groom_name'])) {
            $wedding->groom_name = trim((string) $validated['groom_name']);
        }
        if (! empty($validated['bride_name'])) {
            $wedding->bride_name = trim((string) $validated['bride_name']);
        }
        if (array_key_exists('wedding_date', $validated) && ! empty($validated['wedding_date'])) {
            $wedding->wedding_date = $validated['wedding_date'];
            $extra['has_custom_wedding_date'] = true;
        }
        if (array_key_exists('venue_name', $validated)) {
            $wedding->venue_name = trim((string) ($validated['venue_name'] ?? '')) ?: null;
            $extra['party_venue'] = $wedding->venue_name;
            $extra['has_venue_info'] = ! empty($wedding->venue_name) || ! empty($wedding->venue_address);
        }
        if (array_key_exists('venue_address', $validated)) {
            $wedding->venue_address = trim((string) ($validated['venue_address'] ?? '')) ?: null;
            $extra['event_party_address'] = $wedding->venue_address;
            $extra['has_venue_info'] = ! empty($wedding->venue_name) || ! empty($wedding->venue_address);
        }

        $extraMap = [
            'groom_father' => 'groom_father',
            'groom_mother' => 'groom_mother',
            'bride_father' => 'bride_father',
            'bride_mother' => 'bride_mother',
            'party_time' => 'party_time',
            'ceremony_type' => 'ceremony_type',
            'story_title' => 'story_title',
            'couple_quote' => 'couple_quote',
            'story_content' => 'story_content',
        ];
        foreach ($extraMap as $inputKey => $extraKey) {
            if (array_key_exists($inputKey, $validated) && $validated[$inputKey] !== null) {
                $extra[$extraKey] = trim((string) $validated[$inputKey]);
            }
        }

        $wedding->extra_data = $extra;
        $wedding->save();

        $invitation->title = 'Thiệp Cưới '.$wedding->groom_name.' & '.$wedding->bride_name;
        $invitation->custom_config = $config;
        $invitation->revision_id = (int) $invitation->revision_id + 1;
        $invitation->save();

        $styles = $this->curatedService->getStyles();
        $palettes = $this->curatedService->getPalettes();

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật thiết kế thiệp cưới!',
            'template_name' => $invitation->template->name ?? '',
            'template_slug' => $invitation->template->slug ?? '',
            'style' => $style,
            'style_name' => $styles[$style]['name'] ?? $style,
            'palette' => $palette,
            'palette_short_name' => $palettes[$palette]['short_name'] ?? $palette,
            'palette_primary' => $palettes[$palette]['primary'] ?? '#85182A',
            'is_curated_seven' => (bool) ($config['is_curated_seven'] ?? true),
            'groom_name' => $wedding->groom_name,
            'bride_name' => $wedding->bride_name,
            'revision_id' => $invitation->revision_id,
        ]);
    }

    /**
     * AI viết lại lời ngỏ, tiêu đề và trích dẫn tình yêu theo sắc thái phong cách (1 chạm).
     */
    public function regenerateCopy(Request $request, int $invitationId): JsonResponse
    {
        if ($denied = $this->ensureCuratedPlusAccess($request)) {
            return $denied;
        }

        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        if ($invitation->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang bị khóa bởi Quản trị viên, không thể chỉnh sửa.',
            ], 403);
        }

        $validated = $request->validate([
            'style' => ['nullable', 'string', Rule::in(array_keys($this->curatedService->getStyles()))],
        ]);

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $style = (string) ($validated['style'] ?? ($config['style'] ?? 'romantic'));
        $wedding = $invitation->wedding;
        $extra = is_array($wedding->extra_data) ? $wedding->extra_data : [];

        $newCopy = $this->curatedService->regenerateCuratedContent(
            [
                'groom_name' => $wedding->groom_name,
                'bride_name' => $wedding->bride_name,
            ],
            $style,
            (string) ($extra['couple_quote'] ?? '')
        );

        $extra['couple_quote'] = $newCopy['couple_quote'];
        $extra['story_title'] = $newCopy['story_title'];
        $extra['story_subtitle'] = $newCopy['story_subtitle'];
        $extra['story_content'] = $newCopy['story_content'];

        $wedding->extra_data = $extra;
        $wedding->save();

        $invitation->revision_id = (int) $invitation->revision_id + 1;
        $invitation->save();

        return response()->json([
            'success' => true,
            'message' => 'AI đã biên soạn lời ngỏ mới dành riêng cho hai bạn!',
            'copy' => [
                'story_title' => $newCopy['story_title'],
                'story_subtitle' => $newCopy['story_subtitle'],
                'couple_quote' => $newCopy['couple_quote'],
                'story_content' => $newCopy['story_content'],
                'content_source' => $newCopy['content_source'],
            ],
            'revision_id' => $invitation->revision_id,
        ]);
    }

    /**
     * Kiểm tra quyền sở hữu thiệp cưới.
     */
    protected function authorizeInvitationOwner(Invitation $invitation): void
    {
        if ($invitation->isOwner()) {
            return;
        }

        $sessionSlugs = (array) request()->session()->get('owned_invitation_slugs', []);
        if (in_array($invitation->slug, $sessionSlugs, true)) {
            return;
        }

        abort(404);
    }
}
