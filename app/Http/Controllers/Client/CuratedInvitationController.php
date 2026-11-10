<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use App\Models\Wedding;
use App\Rules\NoProfanity;
use App\Services\AuditLogger;
use App\Services\CuratedInvitationService;
use App\Services\InvitationTemplateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CuratedInvitationController extends Controller
{
    public function __construct(
        protected CuratedInvitationService $curatedService,
        protected InvitationTemplateService $templateService
    ) {}

    /**
     * Màn hình Wizard khởi tạo thiệp tự phối thông minh (Mục 1, 2, 3).
     */
    public function create(): View
    {
        $styles = $this->curatedService->getStyles();
        $palettes = $this->curatedService->getPalettes();

        return view('client.pages.curated.wizard', [
            'styles' => $styles,
            'palettes' => $palettes,
        ]);
    }

    /**
     * Khởi tạo thiệp tự phối: AI viết nội dung + Mây Hẹn tự phối layout từ components chuẩn (Mục 1, 4, 7, 10).
     */
    public function generate(Request $request): JsonResponse|RedirectResponse
    {
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
            'venue_name' => ['nullable', 'string', 'max:100', new NoProfanity],
            'venue_address' => ['nullable', 'string', 'max:255', new NoProfanity],
        ], [
            'style.in' => 'Phong cách lựa chọn phải thuộc danh mục chuẩn của Mây Hẹn.',
            'palette.in' => 'Bảng màu lựa chọn phải thuộc danh mục chuẩn của Mây Hẹn.',
        ]);

        $style = (string) $request->input('style');
        $palette = (string) $request->input('palette');

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

        $rawVenueName = $request->input('venue_name');
        $rawVenueAddress = $request->input('venue_address');
        $hasVenueInfo = ! empty($rawVenueName) || ! empty($rawVenueAddress);
        $venueName = $rawVenueName ? trim((string) $rawVenueName) : null;
        $venueAddress = $rawVenueAddress ? trim((string) $rawVenueAddress) : null;

        $extraData = [
            'groom_father' => $request->input('groom_father', 'Ông Nguyễn Văn Hùng'),
            'groom_mother' => $request->input('groom_mother', 'Bà Lê Thị Thu'),
            'groom_parents_address' => 'Tư gia Nhà Trai',
            'bride_father' => $request->input('bride_father', 'Ông Trần Quốc Bảo'),
            'bride_mother' => $request->input('bride_mother', 'Bà Phạm Thị Mai'),
            'bride_parents_address' => 'Tư gia Nhà Gái',
            'couple_quote' => $aiContent['couple_quote'],
            'story_title' => $aiContent['story_title'],
            'story_subtitle' => $aiContent['story_subtitle'],
            'story_content' => $aiContent['story_content'],
            'ceremony_type' => $aiContent['ceremony_type'],
            'party_time' => '18:00',
            'party_venue' => $venueName,
            'event_party_address' => $venueAddress,
            'has_custom_wedding_date' => $hasCustomWeddingDate,
            'has_venue_info' => $hasVenueInfo,
            'ceremony_time' => '08:00',
            'ceremony_groom_time' => '10:00',
            'wedding_lunar_date' => 'Nhằm ngày lành tháng tốt năm Bính Ngọ',
            'timeline' => [
                ['time' => '17:30', 'title' => 'Đón tiếp quan khách'],
                ['time' => '18:00', 'title' => 'Cử hành hôn lễ'],
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

        // 4. Tìm kiếm template gốc Native tương ứng với style
        $template = $this->curatedService->resolveNativeTemplate($style, $palette);

        // 5. Thực hiện tạo trong Transaction
        $user = Auth::user();
        if (! $user) {
            $user = User::firstOrCreate(
                ['email' => 'guest@mayhen.vn'],
                [
                    'name' => 'Khách Vãng Lai',
                    'password' => bcrypt(Str::random(32)),
                    'role' => 'couple',
                ]
            );
        }

        $presentationToken = Str::random(32);

        $invitation = DB::transaction(function () use ($user, $template, $groomName, $brideName, $weddingDate, $venueName, $venueAddress, $extraData, $curatedLayout, $palette, $style, $presentationToken) {
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
                    'is_curated_seven' => true,
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
    public function preview(Request $request, string $slug): View
    {
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
            'shouldAnimate' => $shouldAnimate,
        ]);
    }

    /**
     * Chuyển đổi sang mẫu gốc khác trong cùng phong cách - BẢO TOÀN MÀU SẮC & PHONG CÁCH (Master Prompt V3).
     */
    public function remix(Request $request, int $invitationId): JsonResponse
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $style = $config['style'] ?? 'romantic';
        $palette = $config['color_theme'] ?? 'ruby';
        $currentSlug = $invitation->template->slug ?? ($config['source_template_slug'] ?? 'giua-muon-ngan-vi-sao');

        // Chọn mẫu gốc khác trong cùng phong cách
        $newTemplate = $this->curatedService->resolveAlternativeTemplate($style, $currentSlug);

        // Bảo toàn dữ liệu 7 section
        $curatedLayout = $this->curatedService->assembleCuratedLayout($style, $palette);

        $config['curated_layout'] = $curatedLayout;
        $config['source_template_slug'] = $newTemplate->slug;
        $config['color_theme'] = $palette;

        $invitation->template_id = $newTemplate->id;
        $invitation->custom_config = $config;
        $invitation->revision_id = (int) $invitation->revision_id + 1;
        $invitation->save();

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
        $request->validate([
            'invitation_id' => 'required|integer|exists:invitations,id',
            'section' => 'required|string',
            'direction' => 'nullable|string|in:next,prev',
        ]);

        $invitation = Invitation::with('wedding')->findOrFail((int) $request->input('invitation_id'));
        $this->authorizeInvitationOwner($invitation);

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
