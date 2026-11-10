<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Rules\NoProfanity;
use App\Services\AiStoryWriterService;
use App\Services\InvitationTemplateService;
use App\Services\WeddingConsistencyGuardianService;
use App\Services\WeddingMusicService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        $request->validate(array_merge(
            ['wedding' => 'nullable|array'],
            $this->weddingInputRules('wedding.'),
            $this->weddingInputRules(''),
        ));
        $templateId = (int) $request->input('template_id');
        $template = InvitationTemplate::findOrFail($templateId);

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

        $invitation = $this->templateService->saveInvitation($user, $template->id, [
            'wedding' => $rawWedding,
            'invitation' => [
                'custom_config' => $customConfig,
            ],
        ], $invitationId, $invitationId ? (int) $request->input('revision_id') : null);

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
        $invitation = Invitation::with(['wedding.partner', 'template'])
            ->where('slug', $slug)
            ->firstOrFail();

        $config = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode($invitation->custom_config, true) ?? []);
        if (! empty($config['is_locked'])) {
            return view('client.pages.invitation-locked', [
                'invitation' => $invitation,
                'lockReason' => $config['lock_reason'] ?? 'Thiệp cưới tạm thời bị khóa do yêu cầu từ Quản trị viên.',
            ]);
        }

        $isOwner = $invitation->isOwner();

        // 1. Kiểm tra thời hạn lưu trữ của thiệp cưới
        if ($invitation->expires_at && $invitation->expires_at->isPast() && ! $isOwner) {
            return view('client.pages.invitation-expired', [
                'invitation' => $invitation,
            ]);
        }

        // 2. Kiểm tra trạng thái xuất bản công khai
        if (! $invitation->is_published && ! $isOwner) {
            return view('client.pages.invitation-unpublished', [
                'invitation' => $invitation,
            ]);
        }

        // Kiểm tra mật mã bảo vệ riêng tư nếu có
        if (! empty($config['password']) && ! $isOwner) {
            $isUnlocked = session("unlocked_invitation_{$invitation->id}") === true;
            if (! $isUnlocked) {
                return view('client.pages.invitation-password', [
                    'invitation' => $invitation,
                    'wedding' => $invitation->wedding,
                ]);
            }
        }

        $wedding = $invitation->wedding;
        $wishes = $wedding ? $wedding->wishes()->where('is_approved', true)->take(50)->get() : collect([]);
        $wishSubmitUrl = route('invitation.wish.store', $slug);
        $rsvpRouteParameters = ['slug' => $slug];
        $query = request()->query();
        $guest = null;
        if (array_key_exists('token', $query)) {
            $guestToken = $query['token'];
            if (! is_string($guestToken) || $guestToken === '' || strlen($guestToken) > 64) {
                abort(404);
            }

            abort_unless($wedding, 404);
            $guest = $wedding->guests()->where('guest_token', $guestToken)->firstOrFail();
            $rsvpRouteParameters['token'] = $guestToken;
        }
        $rsvpSubmitUrl = route('rsvp.submit', $rsvpRouteParameters);

        $currentRsvp = null;
        $myWish = null;

        if ($wedding && $guest) {
            $currentRsvp = GuestResponse::where('wedding_id', $wedding->id)
                ->where('guest_id', $guest->id)
                ->latest()
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
                'attending_guests' => (int) ($rsvpResponses->where('attending_status', 'attending')->sum('adults_count')
                    + $rsvpResponses->where('attending_status', 'attending')->sum('children_count')),
                'declined' => $rsvpResponses->where('attending_status', 'declined')->count(),
            ];
        }

        // Tự động ghi nhận lượt mở thiệp và thiết bị truy cập nếu không phải chủ thiệp/admin
        if (! $isOwner && $guest) {
            $guest->recordOpen(request()->userAgent());
        }

        $template = $invitation->template;
        $customView = $template ? "client.invitations.{$template->slug}.template" : null;

        // Trích xuất thông điệp cá nhân hóa (Invitation Persona) nếu có
        $personaConfig = $config['persona_messages'] ?? [];
        $isPersonaEnabled = ! empty($personaConfig['enabled']);
        $personaMessage = null;

        // Chế Độ Tổng Duyệt Vai Trò Trước Khi Gửi Thiệp (Pre-Publish Rehearsal - TASK-VAL-08)
        $isRehearsal = false;
        $rehearsalGroup = null;
        $availableGroups = ['Bạn Bè', 'Gia Đình & Họ Hàng', 'Đồng Nghiệp', 'Khách VIP'];

        if ($wedding) {
            $dbGroups = $wedding->guests()->whereNotNull('group_name')->where('group_name', '!=', '')->distinct()->pluck('group_name')->all();
            $availableGroups = array_values(array_unique(array_filter(array_merge($availableGroups, $dbGroups))));
        }

        if ($isOwner && (request()->boolean('rehearsal') || request()->has('rehearsal_group'))) {
            $isRehearsal = true;
            $reqGroup = request()->query('rehearsal_group');
            if (is_string($reqGroup) && in_array(trim($reqGroup), $availableGroups, true)) {
                $rehearsalGroup = trim($reqGroup);
            }

            if ($rehearsalGroup) {
                if (! $guest) {
                    $dummyGuest = new Guest;
                    $dummyGuest->id = 0;
                    $dummyGuest->wedding_id = $wedding ? $wedding->id : 0;
                    $dummyGuest->name = "Khách Quý ({$rehearsalGroup})";
                    $dummyGuest->salutation = 'Quý khách';
                    $dummyGuest->group_name = $rehearsalGroup;
                    $dummyGuest->guest_token = 'rehearsal-demo-token';
                    $dummyGuest->expected_attendees = 2;
                    $dummyGuest->table_name = ($rehearsalGroup === 'Khách VIP') ? 'Bàn VIP 01' : 'Bàn 08';
                    $guest = $dummyGuest;
                }

                if ($isPersonaEnabled) {
                    $groupMsg = $personaConfig['groups'][$rehearsalGroup] ?? ($personaConfig['groups']['default'] ?? null);
                    if ($groupMsg && (! empty($groupMsg['content']) || ! empty($groupMsg['title']) || ! empty($groupMsg['subtitle']))) {
                        $rawTitle = $groupMsg['title'] ?? null;
                        $rawSubtitle = $groupMsg['subtitle'] ?? null;
                        $rawContent = $groupMsg['content'] ?? ($groupMsg['message'] ?? null);

                        $coupleName = ($wedding->groom_name ?? 'Chú Rể').' & '.($wedding->bride_name ?? 'Cô Dâu');
                        $replacePlaceholders = function (?string $text) use ($guest, $coupleName) {
                            if (empty($text)) {
                                return null;
                            }

                            return str_replace(
                                ['{guest_name}', '{salutation}', '{couple_name}'],
                                [$guest->name ?? 'Quý khách', $guest->salutation ?? 'Quý khách', $coupleName],
                                $text
                            );
                        };

                        $formattedContent = $replacePlaceholders($rawContent);

                        $personaMessage = [
                            'title' => $replacePlaceholders($rawTitle),
                            'subtitle' => $replacePlaceholders($rawSubtitle),
                            'content' => $formattedContent,
                            'message' => $formattedContent,
                            'group_name' => $rehearsalGroup,
                        ];
                    }
                }
            }
        }

        if (! $isRehearsal && $isPersonaEnabled && $guest) {
            $groupKey = ! empty($guest->group_name) ? trim($guest->group_name) : 'default';
            $groupMsg = $personaConfig['groups'][$groupKey] ?? ($personaConfig['groups']['default'] ?? null);

            if ($groupMsg && (! empty($groupMsg['content']) || ! empty($groupMsg['title']) || ! empty($groupMsg['subtitle']) || ! empty($groupMsg['message']))) {
                $rawTitle = $groupMsg['title'] ?? null;
                $rawSubtitle = $groupMsg['subtitle'] ?? null;
                $rawContent = $groupMsg['content'] ?? ($groupMsg['message'] ?? null);

                $salutation = ! empty($guest->salutation) ? trim($guest->salutation) : 'Quý khách';
                $coupleName = ($wedding->groom_name ?? 'Chú Rể').' & '.($wedding->bride_name ?? 'Cô Dâu');

                $replacePlaceholders = function (?string $text) use ($guest, $salutation, $coupleName) {
                    if (empty($text)) {
                        return null;
                    }

                    return str_replace(
                        ['{guest_name}', '{salutation}', '{couple_name}'],
                        [$guest->name, $salutation, $coupleName],
                        $text
                    );
                };

                $formattedContent = $replacePlaceholders($rawContent);

                $personaMessage = [
                    'title' => $replacePlaceholders($rawTitle),
                    'subtitle' => $replacePlaceholders($rawSubtitle),
                    'content' => $formattedContent,
                    'message' => $formattedContent,
                    'group_name' => $guest->group_name ?: 'Khách Quý',
                ];
            }
        }

        // Trích xuất thông tin hạn chót và chính sách RSVP (RSVP-03 Policy)
        $rsvpDeadlineStr = $config['rsvp_deadline'] ?? null;
        $isRsvpClosed = false;
        $isRsvpLate = false;
        $rsvpDeadlineDate = null;
        if ($rsvpDeadlineStr) {
            $rsvpDeadlineDate = Carbon::parse($rsvpDeadlineStr)->endOfDay();
            if (Carbon::now()->isAfter($rsvpDeadlineDate)) {
                if (! empty($config['rsvp_lock_after_deadline'])) {
                    $isRsvpClosed = true;
                } else {
                    $isRsvpLate = true;
                }
            }
        }

        $viewData = [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'template' => $template,
            'isCuratedSeven' => ! empty($config['is_curated_seven']) || ! empty($config['is_curated']),
            'guest' => $guest,
            'wishes' => $wishes,
            'wishSubmitUrl' => $wishSubmitUrl,
            'rsvpSubmitUrl' => $rsvpSubmitUrl,
            'currentRsvp' => $currentRsvp,
            'myWish' => $myWish,
            'isOwner' => $isOwner,
            'rsvpStats' => $rsvpStats,
            'rsvpResponses' => $rsvpResponses,
            'personaMessage' => $personaMessage,
            'isRehearsal' => $isRehearsal,
            'rehearsalGroup' => $rehearsalGroup,
            'availableGroups' => $availableGroups,
            'rsvpDeadline' => $rsvpDeadlineDate,
            'isRsvpClosed' => $isRsvpClosed,
            'isRsvpLate' => $isRsvpLate,
            'rsvpClosedNotice' => $config['rsvp_closed_notice'] ?? null,
        ];

        if ($customView && view()->exists($customView)) {
            return view($customView, $viewData);
        }

        return view('client.pages.public-invitation', $viewData);
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
        $allowedExtensions = ['jpeg', 'png', 'jpg', 'webp', 'gif', 'heic'];
        $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        if (! in_array($ext, $allowedExtensions, true)) {
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
