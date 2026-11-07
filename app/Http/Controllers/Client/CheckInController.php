<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Wedding;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    /**
     * Hiển thị Cổng Tiếp Tân Điểm Danh & Quét Mã QR Ngày Cưới.
     */
    public function showPortal(Request $request, string $slug): View|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        // Kiểm tra quyền: Chủ tiệc hoặc Tiếp tân đã nhập đúng PIN
        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return view('client.pages.checkin-pin', [
                'invitation' => $invitation,
                'wedding' => $wedding,
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

        if ($inputPin !== $correctPin) {
            return back()->with('error', 'Mã PIN tiếp tân không chính xác. Vui lòng hỏi cô dâu chú rể.');
        }

        $request->session()->put("receptionist_verified_{$invitation->id}", true);

        return redirect()->route('checkin.portal', $slug)
            ->with('success', 'Đã mở khóa Cổng Tiếp Tân Điểm Danh thành công!');
    }

    /**
     * Xử lý quét mã QR điểm danh (Gọi bằng AJAX từ Camera hoặc nhập Token).
     */
    public function processScan(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return response()->json([
                'success' => false,
                'message' => 'Phiên làm việc tiếp tân đã hết hạn. Vui lòng tải lại trang và nhập lại mã PIN.',
            ], 403);
        }

        $validated = $request->validate([
            'token' => ['nullable', 'string', 'max:64'],
            'guest_id' => ['nullable', 'integer'],
            'attendees' => ['nullable', 'integer', 'min:1', 'max:50'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $query = $wedding->guests()->with('response');

        if (! empty($validated['token'])) {
            $token = trim($validated['token']);
            // Nếu token chứa URL đầy đủ (ví dụ quét trực tiếp link), bóc tách token
            if (str_contains($token, 'token=')) {
                parse_str((string) parse_url($token, PHP_URL_QUERY), $queryParams);
                $token = $queryParams['token'] ?? $token;
            } elseif (str_contains($token, '/diem-danh/')) {
                $parts = explode('/diem-danh/', $token);
                $token = end($parts);
            }
            $query->where('guest_token', $token);
        } elseif (! empty($validated['guest_id'])) {
            $query->where('id', (int) $validated['guest_id']);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp mã QR hoặc ID khách mời.',
            ], 422);
        }

        /** @var Guest|null $guest */
        $guest = $query->first();

        if (! $guest) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin khách mời trong danh sách đám cưới này.',
            ], 404);
        }

        $alreadyCheckedIn = $guest->checked_in_at !== null;
        $previousCheckInTime = $guest->checked_in_at ? $guest->checked_in_at->format('H:i') : null;

        if (! $alreadyCheckedIn) {
            $attendeesCount = ! empty($validated['attendees'])
                ? (int) $validated['attendees']
                : $guest->actualAttendeesCount();

            $guest->update([
                'checked_in_at' => now(),
                'checked_in_attendees' => $attendeesCount,
                'check_in_note' => $validated['note'] ?? $guest->check_in_note,
            ]);
        }

        // Cập nhật lại thống kê
        $allGuests = $wedding->guests()->with('response')->get();
        $totalExpected = (int) $allGuests->sum(function (Guest $g) {
            return $g->actualAttendeesCount();
        });
        $checkedInCount = (int) $allGuests->whereNotNull('checked_in_at')->sum(function (Guest $g) {
            return $g->checked_in_attendees ?: $g->actualAttendeesCount();
        });

        return response()->json([
            'success' => true,
            'already_checked_in' => $alreadyCheckedIn,
            'previous_time' => $previousCheckInTime,
            'guest' => [
                'id' => $guest->id,
                'name' => $guest->name,
                'salutation' => $guest->salutation ?: 'Thân mời',
                'group_name' => $guest->group_name ?: 'Khách chung',
                'table_name' => $guest->table_name ?: 'Chưa xếp bàn',
                'expected_attendees' => $guest->actualAttendeesCount(),
                'checked_in_attendees' => $guest->checked_in_attendees ?: $guest->actualAttendeesCount(),
                'checked_in_at' => $guest->checked_in_at ? $guest->checked_in_at->format('H:i d/m/Y') : null,
                'dietary' => $guest->response?->dietary_requirements,
                'wishes' => $guest->response?->wishes,
            ],
            'stats' => [
                'checked_in_count' => $checkedInCount,
                'total_expected' => $totalExpected,
                'checked_in_guests' => $allGuests->whereNotNull('checked_in_at')->count(),
                'total_guests' => $allGuests->count(),
            ],
        ]);
    }

    /**
     * Tìm kiếm nhanh khách mời khi tiếp tân gõ tên hoặc SĐT.
     */
    public function quickSearch(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $keyword = trim((string) $request->query('q', ''));
        if (mb_strlen($keyword) < 1) {
            return response()->json(['guests' => []]);
        }

        $guests = $wedding->guests()
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('table_name', 'like', "%{$keyword}%")
                    ->orWhere('group_name', 'like', "%{$keyword}%");
            })
            ->limit(15)
            ->get()
            ->map(function (Guest $g) {
                return [
                    'id' => $g->id,
                    'name' => $g->name,
                    'salutation' => $g->salutation,
                    'group_name' => $g->group_name,
                    'table_name' => $g->table_name ?: 'Chưa xếp bàn',
                    'expected_attendees' => $g->expected_attendees,
                    'is_checked_in' => $g->checked_in_at !== null,
                    'checked_in_time' => $g->checked_in_at ? $g->checked_in_at->format('H:i') : null,
                ];
            });

        return response()->json(['guests' => $guests]);
    }

    /**
     * Hủy điểm danh cho khách (trường hợp tiếp tân bấm nhầm).
     */
    public function undo(Request $request, string $slug, int $guestId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $guest = $wedding->guests()->findOrFail($guestId);
        $guest->update([
            'checked_in_at' => null,
            'checked_in_attendees' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Đã hủy điểm danh cho khách {$guest->name}.",
        ]);
    }

    /**
     * Cập nhật nhanh vị trí bàn tiệc cho khách.
     */
    public function updateTable(Request $request, string $slug, int $guestId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;

        if (! $this->isAuthorizedReceptionist($request, $invitation, $wedding)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'table_name' => ['required', 'string', 'max:100'],
        ]);

        $guest = $wedding->guests()->findOrFail($guestId);
        $guest->update([
            'table_name' => trim($validated['table_name']),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Đã cập nhật bàn tiệc: {$guest->table_name}",
            'table_name' => $guest->table_name,
        ]);
    }

    /**
     * Khách quét mã QR bằng camera thường của điện thoại: Mở trang xem bàn tiệc và thẻ VIP.
     */
    public function directScan(Request $request, string $guestToken): View|RedirectResponse
    {
        $guest = Guest::with(['wedding.invitations'])->where('guest_token', $guestToken)->firstOrFail();
        $invitation = $guest->wedding->invitations()->first();

        if (! $invitation) {
            abort(404, 'Không tìm thấy thiệp cưới.');
        }

        return view('client.pages.guest-pass', [
            'guest' => $guest,
            'wedding' => $guest->wedding,
            'invitation' => $invitation,
        ]);
    }

    /**
     * Kiểm tra quyền của ban tiếp tân hoặc cặp đôi.
     */
    private function isAuthorizedReceptionist(Request $request, Invitation $invitation, Wedding $wedding): bool
    {
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
        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['checkin_pin'])) {
            return (string) $config['checkin_pin'];
        }

        // Mặc định sinh PIN 6 số cố định theo ngày cưới hoặc ID thiệp
        $wedding = $invitation->wedding;
        if ($wedding && $wedding->wedding_date) {
            return $wedding->wedding_date->format('dmy'); // ví dụ 151226
        }

        return str_pad((string) ($invitation->id * 7919 % 1000000), 6, '0', STR_PAD_LEFT);
    }
}
