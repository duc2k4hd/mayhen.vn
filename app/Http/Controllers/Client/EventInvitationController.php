<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\EventInvitation;
use App\Models\EventInvitationResponse;
use App\Rules\NoProfanity;
use App\Services\EventCategorySeoProfileService;
use App\Services\EventInvitationService;
use App\Services\PlanAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EventInvitationController extends Controller
{
    public function __construct(
        protected EventInvitationService $eventService,
        protected EventCategorySeoProfileService $categorySeoProfileService,
        protected PlanAccessService $planAccess
    ) {}

    /**
     * Trang Chủ Thiệp Sự Kiện, Lễ Hội & Thiệp Chúc Mừng (/thiep-su-kien) — Chuẩn SEO Google.
     */
    public function index(Request $request): View
    {
        $categories = $this->eventService->getCategories();
        $selectedCategorySlug = $request->query('category');
        $filters = [
            'mode' => $request->query('mode'),
            'envelope_color' => $request->query('envelope_color'),
            'card_border' => $request->query('card_border'),
            'q' => $request->query('q'),
        ];

        $templates = $this->eventService->getTemplatesByCategory(
            is_string($selectedCategorySlug) ? $selectedCategorySlug : null,
            $filters
        );

        $palettes = $this->eventService->getPalettes();
        $envelopeColors = $this->eventService->getEnvelopeColors();
        $envelopeLiners = $this->eventService->getEnvelopeLiners();
        $cardBorders = $this->eventService->getCardBorders();

        $user = $request->user();
        $hasProPlan = $this->planAccess->canPublishEventInvitation($user);
        $currentPlanLabel = $this->planAccess->getTierLabel($this->planAccess->getEffectivePlanSlug($user));

        return view('client.pages.events.index', compact(
            'categories',
            'templates',
            'palettes',
            'envelopeColors',
            'envelopeLiners',
            'cardBorders',
            'selectedCategorySlug',
            'filters',
            'hasProPlan',
            'currentPlanLabel'
        ));
    }

    /**
     * Trang Danh Mục SEO Chuyên Sâu Theo Dịp (/thiep-su-kien/{categorySlug}).
     */
    public function category(Request $request, string $categorySlug): View
    {
        $category = $this->eventService->getCategory($categorySlug);
        if (! $category) {
            abort(404);
        }

        $categories = $this->eventService->getCategories();
        $filters = [
            'mode' => $request->query('mode'),
            'envelope_color' => $request->query('envelope_color'),
            'card_border' => $request->query('card_border'),
            'q' => $request->query('q'),
        ];
        $templates = $this->eventService->getTemplatesByCategory($categorySlug, $filters);
        $totalCategoryTemplatesCount = count($this->eventService->getTemplatesByCategory($categorySlug));
        $seoProfile = $this->categorySeoProfileService->getProfile($categorySlug);
        $relatedCategories = [];
        foreach (($seoProfile['related_slugs'] ?? []) as $relatedSlug) {
            if (isset($categories[$relatedSlug])) {
                $relatedCategories[] = $categories[$relatedSlug];
            }
        }

        $palettes = $this->eventService->getPalettes();
        $envelopeColors = $this->eventService->getEnvelopeColors();
        $envelopeLiners = $this->eventService->getEnvelopeLiners();
        $cardBorders = $this->eventService->getCardBorders();

        $user = $request->user();
        $hasProPlan = $this->planAccess->canPublishEventInvitation($user);
        $currentPlanLabel = $this->planAccess->getTierLabel($this->planAccess->getEffectivePlanSlug($user));

        return view('client.pages.events.category', compact(
            'category',
            'categories',
            'templates',
            'totalCategoryTemplatesCount',
            'seoProfile',
            'relatedCategories',
            'palettes',
            'envelopeColors',
            'envelopeLiners',
            'cardBorders',
            'filters',
            'hasProPlan',
            'currentPlanLabel'
        ));
    }

    /**
     * Trang Xem Thử Mẫu Thiệp Sự Kiện & Phong Bì 3D (/thiep-su-kien/xem-thu/{templateSlug}).
     */
    public function preview(Request $request, string $templateSlug): View
    {
        $template = $this->eventService->getTemplate($templateSlug);
        if (! $template) {
            abort(404);
        }

        $category = $this->eventService->getCategory($template['category_slug']);
        $palettes = $this->eventService->getPalettes();
        $envelopeColors = $this->eventService->getEnvelopeColors();
        $envelopeLiners = $this->eventService->getEnvelopeLiners();
        $cardBorders = $this->eventService->getCardBorders();
        $backdrops = $this->eventService->getBackdrops();
        $artworkMotifs = $this->eventService->getArtworkMotifs();

        $requestedPalette = (string) $request->query('palette', $template['default_palette']);
        $activePalette = $palettes[$requestedPalette] ?? $palettes[$template['default_palette']];

        $defaults = $template['defaults'];
        $demoInvitation = new EventInvitation([
            'template_slug' => $template['slug'],
            'category_slug' => $template['category_slug'],
            'slug' => 'demo-'.$template['slug'],
            'title' => $defaults['title'],
            'event_badge' => $defaults['event_badge'],
            'host_name' => $defaults['host_name'],
            'sub_host_name' => $defaults['sub_host_name'],
            'invitation_message' => $defaults['invitation_message'],
            'event_date' => $defaults['event_date'],
            'welcome_time' => $defaults['welcome_time'],
            'party_time' => $defaults['party_time'],
            'lunar_note' => $defaults['lunar_note'],
            'venue_name' => $defaults['venue_name'],
            'venue_address' => $defaults['venue_address'],
            'map_url' => $defaults['map_url'],
            'dresscode_note' => $defaults['dresscode_note'],
            'color_palette' => $activePalette['id'],
            'enable_rsvp' => $defaults['enable_rsvp'],
            'enable_gift_qr' => $defaults['enable_gift_qr'],
            'bank_name' => $defaults['bank_name'],
            'bank_account_number' => $defaults['bank_account_number'],
            'bank_account_holder' => $defaults['bank_account_holder'],
            'gift_note' => $defaults['gift_note'],
            'custom_data' => [
                'schedule_items' => $defaults['schedule_items'],
                'envelope_color' => $defaults['envelope_color'],
                'envelope_liner' => $defaults['envelope_liner'],
                'card_border' => $defaults['card_border'],
                'artwork_motif' => $defaults['artwork_motif'],
                'backdrop' => $defaults['backdrop'],
                'font_style' => $defaults['font_style'],
                'show_datetime_venue' => $defaults['show_datetime_venue'],
                'rsvp_question' => $defaults['rsvp_question'],
                'custom_photo_data' => '',
            ],
            'status' => 'draft',
        ]);

        $isDemoPreview = true;
        $isEmbed = $request->boolean('embed');
        $guestName = trim((string) $request->query('guest', 'Quý khách & Gia đình'));
        $responses = collect();

        return view('client.pages.events.card', compact(
            'template',
            'category',
            'palettes',
            'envelopeColors',
            'envelopeLiners',
            'cardBorders',
            'backdrops',
            'activePalette',
            'demoInvitation',
            'isDemoPreview',
            'isEmbed',
            'guestName',
            'responses'
        ));
    }

    /**
     * Studio Tạo Thử & Chỉnh Sửa Nâng Cao 5 Tab (/thiep-su-kien/tao-thu/{templateSlug}).
     * Mở 100% cho mọi khách truy cập vào test miễn phí; khi bấm Xuất Bản sẽ kiểm tra Gói Pro.
     */
    public function studio(Request $request, string $templateSlug): View
    {
        $template = $this->eventService->getTemplate($templateSlug);
        if (! $template) {
            abort(404);
        }

        $category = $this->eventService->getCategory($template['category_slug']);
        $categories = $this->eventService->getCategories();
        $allTemplates = $this->eventService->getTemplates();
        $palettes = $this->eventService->getPalettes();
        $envelopeColors = $this->eventService->getEnvelopeColors();
        $envelopeLiners = $this->eventService->getEnvelopeLiners();
        $cardBorders = $this->eventService->getCardBorders();
        $backdrops = $this->eventService->getBackdrops();
        $artworkMotifs = $this->eventService->getArtworkMotifs();

        $draft = null;
        $draftId = (int) $request->query('draft', 0);
        if ($draftId > 0) {
            $candidate = EventInvitation::find($draftId);
            if ($candidate && $this->canManageDraft($request, $candidate)) {
                $draft = $candidate;
            }
        }

        $defaults = $template['defaults'];
        $draftCustom = is_array($draft?->custom_data) ? $draft->custom_data : [];

        $state = [
            'id' => $draft?->id,
            'slug' => $draft?->slug,
            'status' => $draft?->status ?? 'draft',
            'template_slug' => $template['slug'],
            'category_slug' => $template['category_slug'],
            'title' => $draft?->title ?? $defaults['title'],
            'event_badge' => $draft?->event_badge ?? $defaults['event_badge'],
            'host_name' => $draft?->host_name ?? $defaults['host_name'],
            'sub_host_name' => $draft?->sub_host_name ?? $defaults['sub_host_name'],
            'invitation_message' => $draft?->invitation_message ?? $defaults['invitation_message'],
            'event_date' => $draft?->event_date ? $draft->event_date->format('Y-m-d') : $defaults['event_date'],
            'welcome_time' => $draft?->welcome_time ?? $defaults['welcome_time'],
            'party_time' => $draft?->party_time ?? $defaults['party_time'],
            'lunar_note' => $draft?->lunar_note ?? $defaults['lunar_note'],
            'venue_name' => $draft?->venue_name ?? $defaults['venue_name'],
            'venue_address' => $draft?->venue_address ?? $defaults['venue_address'],
            'map_url' => $draft?->map_url ?? $defaults['map_url'],
            'dresscode_note' => $draft?->dresscode_note ?? $defaults['dresscode_note'],
            'color_palette' => $draft?->color_palette ?? $template['default_palette'],
            'enable_rsvp' => $draft ? (bool) $draft->enable_rsvp : (bool) $defaults['enable_rsvp'],
            'enable_gift_qr' => $draft ? (bool) $draft->enable_gift_qr : (bool) $defaults['enable_gift_qr'],
            'bank_name' => $draft?->bank_name ?? $defaults['bank_name'],
            'bank_account_number' => $draft?->bank_account_number ?? $defaults['bank_account_number'],
            'bank_account_holder' => $draft?->bank_account_holder ?? $defaults['bank_account_holder'],
            'gift_note' => $draft?->gift_note ?? $defaults['gift_note'],
            'schedule_items' => $draftCustom['schedule_items'] ?? $defaults['schedule_items'],
            'envelope_color' => $draftCustom['envelope_color'] ?? $defaults['envelope_color'],
            'envelope_liner' => $draftCustom['envelope_liner'] ?? $defaults['envelope_liner'],
            'card_border' => $draftCustom['card_border'] ?? $defaults['card_border'],
            'artwork_motif' => $draftCustom['artwork_motif'] ?? $defaults['artwork_motif'],
            'backdrop' => $draftCustom['backdrop'] ?? $defaults['backdrop'],
            'font_style' => $draftCustom['font_style'] ?? $defaults['font_style'],
            'show_datetime_venue' => array_key_exists('show_datetime_venue', $draftCustom) ? (bool) $draftCustom['show_datetime_venue'] : (bool) $defaults['show_datetime_venue'],
            'rsvp_question' => $draftCustom['rsvp_question'] ?? $defaults['rsvp_question'],
            'custom_photo_data' => $draftCustom['custom_photo_data'] ?? '',
        ];

        $user = $request->user();
        $hasProPlan = $this->planAccess->canPublishEventInvitation($user);
        $currentPlanLabel = $this->planAccess->getTierLabel($this->planAccess->getEffectivePlanSlug($user));

        return view('client.pages.events.studio', compact(
            'template',
            'category',
            'categories',
            'allTemplates',
            'palettes',
            'envelopeColors',
            'envelopeLiners',
            'cardBorders',
            'backdrops',
            'artworkMotifs',
            'state',
            'draft',
            'hasProPlan',
            'currentPlanLabel'
        ));
    }

    /**
     * Lưu bản nháp Thiệp Sự Kiện (Ai cũng lưu nháp được để không mất dữ liệu vừa test).
     */
    public function saveDraft(Request $request): JsonResponse
    {
        $validated = $this->validateEventPayload($request);

        $existing = null;
        if (! empty($validated['draft_id'])) {
            $candidate = EventInvitation::find((int) $validated['draft_id']);
            if ($candidate && $this->canManageDraft($request, $candidate)) {
                $existing = $candidate;
            }
        }

        $invitation = $this->eventService->saveDraft($request->user(), $validated, $existing);
        $this->rememberOwnedEventDraft($request, $invitation->id);

        return response()->json([
            'success' => true,
            'draft_id' => $invitation->id,
            'slug' => $invitation->slug,
            'status' => $invitation->status,
            'message' => 'Đã lưu bản nháp thiệp sự kiện thành công!',
        ]);
    }

    /**
     * Xuất bản chính thức Thiệp Sự Kiện -> /loi-moi/{slug}.
     * CỔNG KHÓA ĐẶC QUYỀN GÓI PRO.
     */
    public function publish(Request $request): JsonResponse
    {
        $validated = $this->validateEventPayload($request);

        $existing = null;
        if (! empty($validated['draft_id'])) {
            $candidate = EventInvitation::find((int) $validated['draft_id']);
            if ($candidate && $this->canManageDraft($request, $candidate)) {
                $existing = $candidate;
            }
        }

        $invitation = $this->eventService->saveDraft($request->user(), $validated, $existing);
        $this->rememberOwnedEventDraft($request, $invitation->id);

        $user = $request->user();
        $studioDraftUrl = route('event_invitations.studio', [
            'templateSlug' => $invitation->template_slug,
            'draft' => $invitation->id,
        ]);

        if (! $user) {
            return response()->json([
                'success' => false,
                'code' => 'UNAUTHENTICATED',
                'draft_id' => $invitation->id,
                'message' => 'Bản thiết kế thiệp của bạn đã được lưu nháp! Vui lòng đăng nhập tài khoản Gói Pro VIP để xuất bản và lấy link gửi khách mời.',
                'login_url' => route('login', ['redirect' => $studioDraftUrl]),
                'upgrade_url' => route('checkout.show', 'pro'),
            ], 401);
        }

        if (! $this->planAccess->canPublishEventInvitation($user)) {
            $currentTierLabel = $this->planAccess->getTierLabel($this->planAccess->getEffectivePlanSlug($user));

            return response()->json([
                'success' => false,
                'code' => 'PRO_PLAN_REQUIRED',
                'draft_id' => $invitation->id,
                'current_plan' => $currentTierLabel,
                'message' => "Bản thiết kế của bạn đã được lưu nháp an toàn! Tài khoản của bạn hiện ở mức {$currentTierLabel}. Đặc quyền Xuất bản Thiệp Sự Kiện Gia Đình & Mùa Lễ Hội thuộc về Thành Viên Gói Pro VIP (199.000đ).",
                'upgrade_url' => route('checkout.show', 'pro'),
                'pricing_url' => route('pricing'),
            ], 403);
        }

        $published = $this->eventService->publishForProUser($user, $invitation);

        return response()->json([
            'success' => true,
            'draft_id' => $published->id,
            'slug' => $published->slug,
            'status' => $published->status,
            'public_url' => route('event_invitations.public_show', $published->slug),
            'message' => 'Đã xuất bản thiệp mời sự kiện thành công! Bạn có thể sao chép link gửi Zalo cho khách mời ngay.',
        ]);
    }

    /**
     * Hiển thị Thiệp Sự Kiện Công Khai Dành Cho Khách Mời (/loi-moi/{slug}).
     */
    public function publicShow(Request $request, string $slug): View
    {
        $demoInvitation = EventInvitation::where('slug', $slug)->firstOrFail();

        if (! $demoInvitation->isPublished()) {
            $user = Auth::user();
            $isOwnerOrAdmin = ($user && ($user->isAdmin() || $demoInvitation->user_id === $user->id))
                || $this->canManageDraft($request, $demoInvitation);

            if (! $isOwnerOrAdmin || ! $this->planAccess->canPublishEventInvitation($demoInvitation->user ?? $user)) {
                abort(404);
            }
        }

        $sessionKey = 'viewed_event_inv_'.$demoInvitation->id;
        if (! $request->session()->has($sessionKey)) {
            $demoInvitation->increment('view_count');
            $request->session()->put($sessionKey, true);
        }

        $template = $this->eventService->getTemplate($demoInvitation->template_slug)
            ?? $this->eventService->getTemplate('day-thang-hoang-gia-nhi');
        $category = $this->eventService->getCategory($demoInvitation->category_slug);
        $palettes = $this->eventService->getPalettes();
        $envelopeColors = $this->eventService->getEnvelopeColors();
        $envelopeLiners = $this->eventService->getEnvelopeLiners();
        $cardBorders = $this->eventService->getCardBorders();
        $backdrops = $this->eventService->getBackdrops();
        $artworkMotifs = $this->eventService->getArtworkMotifs();
        $activePalette = $palettes[$demoInvitation->color_palette] ?? $palettes[$template['default_palette']];

        $isDemoPreview = false;
        $isEmbed = $request->boolean('embed');
        $guestName = trim((string) $request->query('guest', 'Quý khách & Gia đình'));
        $responses = $demoInvitation->responses()->latest('id')->take(30)->get();

        return view('client.pages.events.card', compact(
            'template',
            'category',
            'palettes',
            'envelopeColors',
            'envelopeLiners',
            'cardBorders',
            'backdrops',
            'activePalette',
            'demoInvitation',
            'isDemoPreview',
            'isEmbed',
            'guestName',
            'responses'
        ));
    }

    /**
     * Khách mời gửi Xác nhận tham dự (RSVP) & Lời chúc trên trang /loi-moi/{slug}.
     */
    public function submitRsvp(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $invitation = EventInvitation::published()->where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'guest_name' => ['required', 'string', 'max:120', new NoProfanity],
            'guest_phone' => ['nullable', 'string', 'max:40'],
            'attendance_status' => ['required', 'in:attending,declined,maybe'],
            'guests_count' => ['nullable', 'integer', 'min:1', 'max:20'],
            'children_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'custom_answer' => ['nullable', 'string', 'max:250', new NoProfanity],
            'wish_message' => ['nullable', 'string', 'max:800', new NoProfanity],
        ]);

        $wishText = isset($validated['wish_message']) ? trim((string) $validated['wish_message']) : '';
        $customAnswer = isset($validated['custom_answer']) ? trim((string) $validated['custom_answer']) : '';
        if ($customAnswer !== '') {
            $wishText = $wishText !== '' ? ($wishText.' (Ghi chú: '.$customAnswer.')') : ('Ghi chú: '.$customAnswer);
        }

        $response = EventInvitationResponse::create([
            'event_invitation_id' => $invitation->id,
            'guest_name' => trim($validated['guest_name']),
            'guest_phone' => isset($validated['guest_phone']) ? trim((string) $validated['guest_phone']) : null,
            'attendance_status' => $validated['attendance_status'],
            'guests_count' => $validated['attendance_status'] === 'declined' ? 0 : (int) ($validated['guests_count'] ?? 1),
            'children_count' => $validated['attendance_status'] === 'declined' ? 0 : (int) ($validated['children_count'] ?? 0),
            'wish_message' => $wishText ?: null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cảm ơn bạn đã gửi xác nhận tham dự và lời chúc tốt đẹp!',
                'response' => [
                    'guest_name' => $response->guest_name,
                    'attendance_status' => $response->attendance_status,
                    'guests_count' => $response->guests_count,
                    'children_count' => $response->children_count,
                    'wish_message' => $response->wish_message,
                ],
            ]);
        }

        return back()->with('success', 'Cảm ơn bạn đã gửi xác nhận tham dự và lời chúc tốt đẹp!');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateEventPayload(Request $request): array
    {
        return $request->validate([
            'draft_id' => ['nullable', 'integer'],
            'template_slug' => ['required', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:255', new NoProfanity],
            'event_badge' => ['nullable', 'string', 'max:120', new NoProfanity],
            'host_name' => ['required', 'string', 'max:150', new NoProfanity],
            'sub_host_name' => ['nullable', 'string', 'max:150', new NoProfanity],
            'invitation_message' => ['nullable', 'string', 'max:1500', new NoProfanity],
            'event_date' => ['nullable', 'date'],
            'welcome_time' => ['nullable', 'string', 'max:20'],
            'party_time' => ['nullable', 'string', 'max:20'],
            'lunar_note' => ['nullable', 'string', 'max:180', new NoProfanity],
            'venue_name' => ['nullable', 'string', 'max:180', new NoProfanity],
            'venue_address' => ['nullable', 'string', 'max:255', new NoProfanity],
            'map_url' => ['nullable', 'string', 'max:500'],
            'dresscode_note' => ['nullable', 'string', 'max:180', new NoProfanity],
            'color_palette' => ['nullable', 'string', 'max:50'],
            'envelope_color' => ['nullable', 'string', 'max:50'],
            'envelope_liner' => ['nullable', 'string', 'max:50'],
            'card_border' => ['nullable', 'string', 'max:50'],
            'artwork_motif' => ['nullable', 'string', 'max:60'],
            'backdrop' => ['nullable', 'string', 'max:50'],
            'font_style' => ['nullable', 'string', 'max:30'],
            'show_datetime_venue' => ['nullable', 'boolean'],
            'enable_rsvp' => ['nullable', 'boolean'],
            'enable_gift_qr' => ['nullable', 'boolean'],
            'rsvp_question' => ['nullable', 'string', 'max:255', new NoProfanity],
            'custom_photo_data' => ['nullable', 'string', 'max:700000'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:60'],
            'bank_account_holder' => ['nullable', 'string', 'max:120'],
            'gift_note' => ['nullable', 'string', 'max:255', new NoProfanity],
            'schedule_items' => ['nullable', 'array', 'max:5'],
            'schedule_items.*.time' => ['nullable', 'string', 'max:30'],
            'schedule_items.*.title' => ['nullable', 'string', 'max:180', new NoProfanity],
        ]);
    }

    protected function rememberOwnedEventDraft(Request $request, int $id): void
    {
        $owned = (array) $request->session()->get('owned_event_invitation_ids', []);
        if (! in_array($id, $owned, true)) {
            $owned[] = $id;
            $request->session()->put('owned_event_invitation_ids', $owned);
        }
    }

    protected function canManageDraft(Request $request, EventInvitation $invitation): bool
    {
        $user = $request->user();
        if ($user && ($user->isAdmin() || ($invitation->user_id !== null && $invitation->user_id === $user->id))) {
            return true;
        }

        $owned = (array) $request->session()->get('owned_event_invitation_ids', []);

        return in_array($invitation->id, $owned, true);
    }
}
