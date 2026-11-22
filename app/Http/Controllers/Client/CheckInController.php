<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Services\EmergencyOfflinePackService;
use App\Services\ReceptionDeskService;
use App\Services\ReceptionDryRunService;
use App\Services\StaffShiftHandoverService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CheckInController extends Controller
{
    /**
     * Hiển thị Cổng Tiếp Tân Điểm Danh & Quét Mã QR Ngày Cưới.
     */
    public function showPortal(Request $request, ?string $slug = null): View|RedirectResponse
    {
        if (! $slug) {
            $customDomain = $request->attributes->get('custom_wedding_domain');
            if ($customDomain && $customDomain->invitation) {
                $slug = $customDomain->invitation->slug;
            }
        }
        abort_unless($slug, 404, 'Không tìm thấy thông tin đám cưới.');

        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return view('client.pages.invitation-locked', [
                'invitation' => $invitation,
                'lockReason' => $config['lock_reason'] ?? 'Thiệp cưới tạm thời bị khóa do yêu cầu từ Quản trị viên.',
            ]);
        }

        // Kiểm tra quyền: Chủ tiệc hoặc Tiếp tân đã nhập đúng PIN
        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return view('client.pages.checkin-pin', [
                'invitation' => $invitation,
                'wedding' => $wedding,
            ]);
        }

        $isDryRun = $request->boolean('dry_run') || (bool) $request->session()->get('is_dry_run_'.$invitation->id, false);
        if ($request->boolean('dry_run')) {
            $request->session()->put('is_dry_run_'.$invitation->id, true);
            $isDryRun = true;
        }

        $currentShift = $request->session()->get("reception_shift_{$invitation->id}");

        if ($isDryRun) {
            $dryRunService = app(ReceptionDryRunService::class);
            $dryRunState = $dryRunService->getDryRunState($invitation);

            return view('client.pages.checkin', [
                'invitation' => $invitation,
                'wedding' => $wedding,
                'totalExpected' => $dryRunState['stats']['total_expected'],
                'checkedInCount' => $dryRunState['stats']['checked_in_count'],
                'checkedInGuestsCount' => $dryRunState['stats']['checked_in_guests'],
                'totalGuestsCount' => $dryRunState['stats']['total_guests'],
                'recentCheckIns' => collect($dryRunState['guests'])->whereNotNull('checked_in_at')->values(),
                'tables' => $dryRunState['tables'],
                'isDryRun' => true,
                'dryRunGuests' => $dryRunState['guests'],
                'currentShift' => $currentShift,
            ]);
        }

        // Thống kê nhanh đón tiếp
        $allGuests = $wedding->guests()->with('response')->get();
        $totalExpected = (int) $allGuests->sum(function (Guest $g) {
            return $g->actualAttendeesCount();
        });
        $checkedInCount = (int) $allGuests->whereNotNull('checked_in_at')->sum(function (Guest $g) {
            return $g->checked_in_attendees ?: $g->actualAttendeesCount();
        });
        $checkedInGuestsCount = $allGuests->whereNotNull('checked_in_at')->count();
        $totalGuestsCount = $allGuests->count();

        // 10 khách mới check-in gần nhất
        $recentCheckIns = $wedding->guests()
            ->whereNotNull('checked_in_at')
            ->orderByDesc('checked_in_at')
            ->limit(10)
            ->get();

        // Danh sách bàn tiệc để chọn nhanh (kết hợp từ Seating Planner và Guest table_name)
        $plannerTables = $wedding->tables()->pluck('name')->filter()->toArray();
        $guestTables = $wedding->guests()
            ->whereNotNull('table_name')
            ->distinct()
            ->pluck('table_name')
            ->toArray();
        $tables = array_values(array_unique(array_merge($plannerTables, $guestTables)));

        return view('client.pages.checkin', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'totalExpected' => $totalExpected,
            'checkedInCount' => $checkedInCount,
            'checkedInGuestsCount' => $checkedInGuestsCount,
            'totalGuestsCount' => $totalGuestsCount,
            'recentCheckIns' => $recentCheckIns,
            'tables' => $tables,
            'isDryRun' => false,
            'dryRunGuests' => [],
            'currentShift' => $currentShift,
        ]);
    }

    /**
     * Xác thực mã PIN tiếp tân.
     */
    public function verifyPin(Request $request, string $slug): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();

        $request->validate([
            'pin' => ['required', 'string', 'max:20'],
        ], [
            'pin.required' => 'Vui lòng nhập mã PIN tiếp tân.',
        ]);

        $inputPin = trim((string) $request->input('pin'));
        $correctPin = $this->getReceptionistPin($invitation);

        if (! hash_equals($correctPin, $inputPin)) {
            // Kiểm tra mã PIN ca trực tiếp tân (TASK-VAL-23)
            $shift = app(StaffShiftHandoverService::class)->authenticateShiftPin($invitation, $inputPin);
            if ($shift) {
                $request->session()->put("receptionist_verified_{$invitation->id}", true);
                $request->session()->put("reception_shift_{$invitation->id}", $shift);

                return redirect()->route('checkin.portal', $slug)
                    ->with('success', 'Đã đăng nhập ca trực: '.$shift['name'].' ('.$shift['staff_name'].')');
            }

            return back()->with('error', 'Mã PIN tiếp tân không chính xác. Vui lòng hỏi cô dâu chú rể.');
        }

        $request->session()->put("receptionist_verified_{$invitation->id}", true);
        $request->session()->forget("reception_shift_{$invitation->id}");

        return redirect()->route('checkin.portal', $slug)
            ->with('success', 'Đã mở khóa Cổng Tiếp Tân Điểm Danh thành công!');
    }

    /**
     * Resolve and authorize an invitation for JSON reception desk endpoints.
     *
     * @return array{invitation: Invitation, is_dry_run: bool}|JsonResponse
     */
    private function resolveAuthorizedDesk(Request $request, string $slug, string $unauthorizedMessage = 'Unauthorized'): array|JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();

        if ($invitation->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        if (! $this->isAuthorizedReceptionist($request, $invitation, $invitation->wedding)) {
            return response()->json([
                'success' => false,
                'message' => $unauthorizedMessage,
            ], 403);
        }

        return [
            'invitation' => $invitation,
            'is_dry_run' => (bool) $request->session()->get('is_dry_run_'.$invitation->id, false),
        ];
    }

    /**
     * Xử lý quét mã QR điểm danh (Gọi bằng AJAX từ Camera hoặc nhập Token).
     */
    public function processScan(Request $request, string $slug): JsonResponse
    {
        $ctx = $this->resolveAuthorizedDesk(
            $request,
            $slug,
            'Phiên làm việc tiếp tân đã hết hạn. Vui lòng tải lại trang và nhập lại mã PIN.'
        );
        if ($ctx instanceof JsonResponse) {
            return $ctx;
        }

        $validated = $request->validate([
            'token' => ['nullable', 'string', 'max:500'],
            'guest_id' => ['nullable', 'integer'],
            'attendees' => ['nullable', 'integer', 'min:1', 'max:50'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $currentShift = $request->session()->get("reception_shift_{$ctx['invitation']->id}");
        $staffName = is_array($currentShift) ? ($currentShift['staff_name'] ?? null) : null;

        $result = app(ReceptionDeskService::class)->scan(
            $ctx['invitation'],
            $validated,
            $ctx['is_dry_run'],
            is_string($staffName) ? $staffName : null
        );

        return response()->json($result['payload'], $result['status']);
    }

    /**
     * Tìm kiếm nhanh khách mời khi tiếp tân gõ tên hoặc SĐT.
     */
    public function quickSearch(Request $request, string $slug): JsonResponse
    {
        $ctx = $this->resolveAuthorizedDesk($request, $slug);
        if ($ctx instanceof JsonResponse) {
            return $ctx;
        }

        $payload = app(ReceptionDeskService::class)->quickSearch(
            $ctx['invitation'],
            (string) $request->query('q', ''),
            $ctx['is_dry_run']
        );

        return response()->json($payload);
    }

    /**
     * Hủy điểm danh cho khách (trường hợp tiếp tân bấm nhầm).
     */
    public function undo(Request $request, string $slug, int $guestId): JsonResponse
    {
        $ctx = $this->resolveAuthorizedDesk($request, $slug);
        if ($ctx instanceof JsonResponse) {
            return $ctx;
        }

        $result = app(ReceptionDeskService::class)->undo(
            $ctx['invitation'],
            $guestId,
            $ctx['is_dry_run']
        );

        return response()->json($result['payload'], $result['status']);
    }

    /**
     * Cập nhật nhanh vị trí bàn tiệc cho khách.
     */
    public function updateTable(Request $request, string $slug, int $guestId): JsonResponse
    {
        $ctx = $this->resolveAuthorizedDesk($request, $slug);
        if ($ctx instanceof JsonResponse) {
            return $ctx;
        }

        $validated = $request->validate([
            'table_name' => ['required', 'string', 'max:100'],
        ]);

        $payload = app(ReceptionDeskService::class)->assignTable(
            $ctx['invitation'],
            $guestId,
            (string) $validated['table_name'],
            $ctx['is_dry_run']
        );

        return response()->json($payload);
    }

    /**
     * Bàn Xử Lý Ngoại Lệ: Thêm khách phát sinh (Walk-in) tại cửa tiệc (TASK-VAL-21).
     */
    public function walkInGuest(Request $request, string $slug): JsonResponse
    {
        $ctx = $this->resolveAuthorizedDesk($request, $slug);
        if ($ctx instanceof JsonResponse) {
            return $ctx;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'salutation' => ['nullable', 'string', 'max:50'],
            'group_name' => ['nullable', 'string', 'max:100'],
            'table_name' => ['nullable', 'string', 'max:100'],
            'attendees' => ['required', 'integer', 'min:1', 'max:20'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Vui lòng nhập tên khách phát sinh.',
            'attendees.required' => 'Vui lòng chọn số lượng khách đi cùng.',
        ]);

        $result = app(ReceptionDeskService::class)->walkIn(
            $ctx['invitation'],
            $validated,
            $ctx['is_dry_run']
        );

        return response()->json($result['payload'], $result['status']);
    }

    /**
     * Bàn Xử Lý Ngoại Lệ: Điều chỉnh sĩ số thực tế khách đi kèm (+1/+2) (TASK-VAL-21).
     */
    public function adjustAttendees(Request $request, string $slug, int $guestId): JsonResponse
    {
        $ctx = $this->resolveAuthorizedDesk($request, $slug);
        if ($ctx instanceof JsonResponse) {
            return $ctx;
        }

        $validated = $request->validate([
            'attendees' => ['required', 'integer', 'min:1', 'max:50'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $result = app(ReceptionDeskService::class)->adjustAttendees(
            $ctx['invitation'],
            $guestId,
            (int) $validated['attendees'],
            isset($validated['note']) ? (string) $validated['note'] : null,
            $ctx['is_dry_run']
        );

        return response()->json($result['payload'], $result['status']);
    }

    /**
     * Bàn Xử Lý Ngoại Lệ: Chuyển khách báo bận (declined) sang tham dự (TASK-VAL-21).
     */
    public function convertDeclined(Request $request, string $slug, int $guestId): JsonResponse
    {
        $ctx = $this->resolveAuthorizedDesk($request, $slug);
        if ($ctx instanceof JsonResponse) {
            return $ctx;
        }

        $validated = $request->validate([
            'table_name' => ['nullable', 'string', 'max:100'],
            'attendees' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $result = app(ReceptionDeskService::class)->convertDeclined(
            $ctx['invitation'],
            $guestId,
            isset($validated['table_name']) ? (string) $validated['table_name'] : null,
            isset($validated['attendees']) ? (int) $validated['attendees'] : null,
            $ctx['is_dry_run']
        );

        return response()->json($result['payload'], $result['status']);
    }

    /**
     * Bật chế độ diễn tập đón tiếp (TASK-VAL-20).
     */
    public function enableDryRun(Request $request, string $slug): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            abort(403, 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.');
        }

        $request->session()->put('is_dry_run_'.$invitation->id, true);

        return redirect()->route('checkin.portal', ['slug' => $slug, 'dry_run' => 1])
            ->with('success', 'Đã chuyển sang Chế Độ Diễn Tập Lễ Tân. Dữ liệu thử nghiệm hoàn toàn cách ly.');
    }

    /**
     * Tắt chế độ diễn tập đón tiếp.
     */
    public function disableDryRun(Request $request, string $slug): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            abort(403, 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.');
        }

        $request->session()->forget('is_dry_run_'.$invitation->id);
        app(ReceptionDryRunService::class)->resetDryRun($invitation);

        return redirect()->route('checkin.portal', ['slug' => $slug])
            ->with('success', 'Đã quay trở lại Cổng Tiếp Tân Đón Tiếp Thực Tế.');
    }

    /**
     * Làm mới dữ liệu diễn tập đón tiếp.
     */
    public function resetDryRun(Request $request, string $slug): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            abort(403, 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.');
        }

        app(ReceptionDryRunService::class)->resetDryRun($invitation);

        return redirect()->route('checkin.portal', ['slug' => $slug, 'dry_run' => 1])
            ->with('success', 'Đã làm mới toàn bộ 5 kịch bản khách mẫu diễn tập.');
    }

    /**
     * Khách quét mã QR bằng camera thường của điện thoại: Mở trang Concierge đón tiếp VIP.
     */
    public function directScan(Request $request, string $guestToken): View|RedirectResponse
    {
        return $this->guestConcierge($request, $guestToken);
    }

    /**
     * Trang Hành Trình Khách Mời Cá Nhân Hóa (One-Page Guest Concierge - TASK-VAL-13).
     * Trợ lý số 1 trang cho khách mời: Lời chào cá nhân, bàn tiệc, QR Check-in,
     * thời gian, chỉ đường Google Maps, hướng dẫn bãi đỗ xe, lưu lịch Calendar và VietQR mừng cưới.
     */
    public function guestConcierge(Request $request, string $guestToken): View
    {
        $guest = Guest::with(['wedding.invitations.template'])->where('guest_token', $guestToken)->first();

        if (! $guest) {
            $isRevoked = Guest::whereJsonContains('revoked_tokens', $guestToken)->exists();
            if ($isRevoked) {
                abort(410, 'Liên kết thiệp mời này đã được thu hồi và cấp mã mới. Quý khách vui lòng liên hệ cô dâu chú rể để nhận liên kết mới nhất.');
            }
            abort(404, 'Không tìm thấy thông tin khách mời.');
        }

        $wedding = $guest->wedding;
        $invitation = $wedding->invitations()->where('is_published', true)->first()
            ?? $wedding->invitations()->first();

        if (! $invitation) {
            abort(404, 'Không tìm thấy thiệp cưới.');
        }

        $extra = is_array($wedding->extra_data) ? $wedding->extra_data : (json_decode((string) $wedding->extra_data, true) ?? []);
        $config = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return view('client.pages.invitation-locked', [
                'invitation' => $invitation,
                'lockReason' => $config['lock_reason'] ?? 'Thiệp cưới tạm thời bị khóa do yêu cầu từ Quản trị viên.',
            ]);
        }

        $isOwner = $invitation->isOwner() || (auth()->check() && auth()->user()->isAdmin());
        if (! $isOwner) {
            if (! $invitation->is_published) {
                abort(404, 'Thiệp cưới chưa được xuất bản công khai.');
            }
            if ($invitation->isExpired()) {
                abort(410, 'Thiệp cưới đã hết thời hạn lưu trữ trực tuyến.');
            }
        }

        // 1. Lời nhắn riêng cho nhóm khách (Persona message - hỗ trợ chuẩn groups.{group_name} và fallback legacy)
        $personaMessage = null;
        $personaCfg = is_array($config['persona_messages'] ?? null) ? $config['persona_messages'] : [];
        if (($personaCfg['enabled'] ?? true) && $personaCfg !== []) {
            $grpKey = ! empty($guest->group_name) ? trim((string) $guest->group_name) : 'default';
            if (isset($personaCfg['groups']) && is_array($personaCfg['groups'])) {
                $personaMessage = $personaCfg['groups'][$grpKey] ?? ($personaCfg['groups']['default'] ?? null);
            } elseif (! empty($guest->group_name) && ! empty($personaCfg[$guest->group_name])) {
                $personaMessage = is_array($personaCfg[$guest->group_name])
                    ? $personaCfg[$guest->group_name]
                    : ['content' => (string) $personaCfg[$guest->group_name]];
            }
        }

        // 2. Hướng dẫn bãi đỗ xe & lối vào sảnh tiệc
        $parkingGuide = is_array($config['parking_guide'] ?? null) ? $config['parking_guide'] : [];

        // 3. Phản hồi xác nhận tham dự (RSVP) hiện tại
        $existingResponse = GuestResponse::where('wedding_id', $wedding->id)->where('guest_id', $guest->id)->first();

        // 4. Link Google Calendar 1 chạm
        $googleCalendarUrl = $this->buildGoogleCalendarUrl($wedding, $extra, $guest);

        return view('client.pages.guest-concierge', [
            'guest' => $guest,
            'wedding' => $wedding,
            'invitation' => $invitation,
            'extra' => $extra,
            'config' => $config,
            'personaMessage' => $personaMessage,
            'parkingGuide' => $parkingGuide,
            'existingResponse' => $existingResponse,
            'googleCalendarUrl' => $googleCalendarUrl,
        ]);
    }

    /**
     * Tải file iCalendar (.ics) cho khách thêm vào Apple Calendar / Outlook 1 chạm.
     */
    public function downloadIcs(Request $request, string $guestToken): Response
    {
        $guest = Guest::with(['wedding.invitations'])->where('guest_token', $guestToken)->firstOrFail();
        $wedding = $guest->wedding;

        $invitation = $wedding->invitations()->where('is_published', true)->first()
            ?? $wedding->invitations()->first();
        if (! $invitation) {
            abort(404, 'Không tìm thấy thiệp cưới.');
        }

        $config = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode((string) $invitation->custom_config, true) ?? []);
        if (! empty($config['is_locked'])) {
            abort(403, 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.');
        }

        $isOwner = $invitation->isOwner() || (auth()->check() && auth()->user()->isAdmin());
        if (! $isOwner) {
            if (! $invitation->is_published) {
                abort(404, 'Thiệp cưới chưa được xuất bản công khai.');
            }
            if ($invitation->isExpired()) {
                abort(410, 'Thiệp cưới đã hết thời hạn lưu trữ trực tuyến.');
            }
        }

        $extra = is_array($wedding->extra_data) ? $wedding->extra_data : (json_decode((string) $wedding->extra_data, true) ?? []);
        $icsContent = ReceptionDeskService::buildIcsContent($wedding, $extra, $guest);
        $filename = 'thiep-cuoi-'.Str::slug($wedding->groom_name.'-'.$wedding->bride_name).'.ics';

        return response($icsContent, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Tạo đường dẫn thêm vào Google Calendar 1 chạm.
     *
     * @param  array<string, mixed>  $extra
     */
    private function buildGoogleCalendarUrl(Wedding $wedding, array $extra, Guest $guest): string
    {
        return ReceptionDeskService::buildGoogleCalendarUrl($wedding, $extra, $guest);
    }

    /**
     * Kiểm tra quyền của ban tiếp tân hoặc cặp đôi.
     */
    private function isAuthorizedReceptionist(Request $request, Invitation $invitation, Wedding $wedding): bool
    {
        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return false;
        }

        // 1. Cặp đôi đã đăng nhập
        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return true;
        }

        // 2. Cặp đôi có cookie/session sở hữu thiệp
        $ownedSlugs = (array) $request->session()->get('owned_invitation_slugs', []);
        if (in_array($invitation->slug, $ownedSlugs, true)) {
            return true;
        }

        // 3. Tiếp tân đã nhập đúng PIN trong phiên làm việc
        return $request->session()->get("receptionist_verified_{$invitation->id}") === true;
    }

    /**
     * Lấy mã PIN tiếp tân (cấu hình hoặc sinh tự động).
     */
    private function getReceptionistPin(Invitation $invitation): string
    {
        return $invitation->ensureReceptionistPin();
    }

    /**
     * Xác thực quyền chủ sở hữu thiệp cưới của cặp đôi (IDOR Defense).
     */
    private function authorizeOwner(Invitation $invitation): void
    {
        $wedding = $invitation->wedding;
        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return;
        }

        if (auth()->check() && auth()->user()->partner && (int) $wedding->partner_id === (int) auth()->user()->partner->id) {
            return;
        }

        if (auth()->check() && auth()->user()->isAdmin()) {
            return;
        }

        $ownedSlugs = (array) request()->session()->get('owned_invitation_slugs', []);
        if (in_array($invitation->slug, $ownedSlugs, true)) {
            return;
        }

        abort(404);
    }

    /**
     * API Lấy biên bản tóm tắt ca trực hiện tại để bàn giao (TASK-VAL-23).
     */
    public function getHandoverSummary(Request $request, string $slug, StaffShiftHandoverService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return response()->json([
                'success' => false,
                'message' => 'Phiên làm việc tiếp tân đã hết hạn.',
            ], 403);
        }

        $currentShift = $request->session()->get("reception_shift_{$invitation->id}");
        $shiftId = $request->input('shift_id') ?: ($currentShift['id'] ?? 'current_shift');

        $summary = $service->generateHandoverSummary($invitation, $shiftId);

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'data' => $summary,
        ]);
    }

    /**
     * API Lưu biên bản bàn giao ca trực (TASK-VAL-23).
     */
    public function submitShiftHandover(Request $request, string $slug, StaffShiftHandoverService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return response()->json([
                'success' => false,
                'message' => 'Phiên làm việc tiếp tân đã hết hạn.',
            ], 403);
        }

        $validated = $request->validate([
            'handed_to_staff' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
            'shift_id' => ['nullable', 'string', 'max:50'],
        ]);

        $currentShift = $request->session()->get("reception_shift_{$invitation->id}");
        $shiftId = $validated['shift_id'] ?? ($currentShift['id'] ?? 'shift_default');

        $summaryData = $service->generateHandoverSummary($invitation, $shiftId)['summary'] ?? [];

        $record = $service->recordHandover($invitation, $shiftId, [
            'handed_to_staff' => $validated['handed_to_staff'],
            'notes' => $validated['notes'] ?? '',
            'summary' => $summaryData,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu biên bản bàn giao ca trực thành công!',
            'handover' => $record,
            'data' => $record,
        ]);
    }

    /**
     * API Cặp đôi xem danh sách ca trực lễ tân (TASK-VAL-23).
     */
    public function getShifts(int $invitationId, StaffShiftHandoverService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeOwner($invitation);

        $shifts = $service->getShifts($invitation);

        return response()->json([
            'success' => true,
            'shifts' => $shifts,
            'data' => $shifts,
        ]);
    }

    /**
     * API Cặp đôi tạo hoặc sửa ca trực lễ tân (TASK-VAL-23).
     */
    public function saveShift(Request $request, int $invitationId, StaffShiftHandoverService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeOwner($invitation);

        $validated = $request->validate([
            'id' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'],
            'staff_name' => ['required', 'string', 'max:100'],
            'staff_phone' => ['nullable', 'string', 'max:20'],
            'pin' => ['required', 'string', 'min:4', 'max:8'],
            'start_time' => ['nullable', 'string', 'max:10'],
            'end_time' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $savedShift = $service->saveShift($invitation, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu thông tin ca trực lễ tân thành công!',
            'shift' => $savedShift,
            'data' => $savedShift,
        ]);
    }

    /**
     * API Cặp đôi xóa ca trực lễ tân (TASK-VAL-23).
     */
    public function deleteShift(int $invitationId, string $shiftId, StaffShiftHandoverService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeOwner($invitation);

        $deleted = $service->deleteShift($invitation, $shiftId);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Đã xóa ca trực lễ tân.' : 'Không tìm thấy ca trực cần xóa.',
        ]);
    }

    /**
     * API Đồng bộ dữ liệu check-in ngoại tuyến từ lễ tân lên máy chủ (IDEA-C07 & TASK-VAL-11).
     */
    public function syncOffline(Request $request, string $slug, EmergencyOfflinePackService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return response()->json([
                'success' => false,
                'message' => 'Phiên trực lễ tân đã hết hạn hoặc chưa xác thực mã PIN.',
            ], 403);
        }

        $validated = $request->validate([
            'checkins' => ['required', 'array'],
        ]);

        $result = $service->syncOfflineCheckins($invitation, $validated['checkins']);

        return response()->json($result);
    }
}
