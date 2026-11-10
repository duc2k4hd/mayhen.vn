<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Wedding;
use App\Services\AuditLogger;
use App\Services\CsvSanitizerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuestController extends Controller
{
    /**
     * Display centralized Guest CRM & Wedding Day Check-in Operations (Wave 05).
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $weddingId = $request->input('wedding_id');

        $query = Guest::query()->with([
            'wedding:id,user_id,groom_name,bride_name,wedding_date',
            'wedding.invitations:id,wedding_id,slug,is_published',
            'table:id,name,capacity,zone',
            'response',
        ]);

        // 1. Text Search: Guest name, phone, group, table, couple names
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('group_name', 'like', "%{$search}%")
                    ->orWhere('table_name', 'like', "%{$search}%")
                    ->orWhereHas('wedding', function ($wq) use ($search) {
                        $wq->where('groom_name', 'like', "%{$search}%")
                            ->orWhere('bride_name', 'like', "%{$search}%");
                    });
            });
        }

        // 2. Status Filter
        if ($status === 'checked_in') {
            $query->whereNotNull('checked_in_at');
        } elseif ($status === 'not_checked_in') {
            $query->whereNull('checked_in_at');
        } elseif ($status === 'assigned') {
            $query->where(function ($q) {
                $q->whereNotNull('table_id')->orWhereNotNull('table_name');
            });
        } elseif ($status === 'unassigned') {
            $query->whereNull('table_id')->whereNull('table_name');
        }

        // 3. Wedding Filter
        if (! empty($weddingId)) {
            $query->where('wedding_id', (int) $weddingId);
        }

        $guests = $query->latest('id')->paginate(15)->withQueryString();

        // Platform KPIs
        $totalGuests = Guest::count();
        $checkedInCount = Guest::whereNotNull('checked_in_at')->count();
        $checkInRate = $totalGuests > 0 ? (int) round(($checkedInCount / $totalGuests) * 100) : 0;
        $assignedCount = Guest::where(function ($q) {
            $q->whereNotNull('table_id')->orWhereNotNull('table_name');
        })->count();
        $unassignedCount = max(0, $totalGuests - $assignedCount);
        $totalRsvps = GuestResponse::count();
        $attendingRsvps = GuestResponse::where('attending_status', 'attending')->count();

        $allWeddings = Wedding::query()
            ->select('id', 'groom_name', 'bride_name', 'wedding_date')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('admin.pages.guests.index', [
            'guests' => $guests,
            'currentSearch' => $search,
            'currentStatus' => $status,
            'currentWeddingId' => $weddingId,
            'totalGuests' => $totalGuests,
            'checkedInCount' => $checkedInCount,
            'checkInRate' => $checkInRate,
            'assignedCount' => $assignedCount,
            'unassignedCount' => $unassignedCount,
            'totalRsvps' => $totalRsvps,
            'attendingRsvps' => $attendingRsvps,
            'allWeddings' => $allWeddings,
        ]);
    }

    /**
     * Toggle check-in state for a guest on wedding day.
     */
    public function toggleCheckIn(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $guest = Guest::with('wedding')->findOrFail($id);
        $isCheckedIn = $guest->isCheckedIn();

        if ($isCheckedIn) {
            $guest->update([
                'checked_in_at' => null,
                'checked_in_attendees' => 0,
            ]);
            $msg = "Đã hủy điểm danh cho quan khách \"{$guest->name}\".";
            $action = 'cancel_check_in';
        } else {
            $attendees = $guest->actualAttendeesCount();
            $guest->update([
                'checked_in_at' => now(),
                'checked_in_attendees' => $attendees,
            ]);
            $msg = "Đã điểm danh đón tiếp thành công quan khách \"{$guest->name}\" ({$attendees} người).";
            $action = 'check_in';
        }

        AuditLogger::log(
            $action,
            'Guest',
            $guest->id,
            $msg,
            [
                'wedding_id' => $guest->wedding_id,
                'guest_name' => $guest->name,
                'is_checked_in' => ! $isCheckedIn,
            ]
        );

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_checked_in' => ! $isCheckedIn,
                'checked_in_at' => $guest->fresh()->checked_in_at?->format('d/m/Y H:i'),
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Export guests list as UTF-8 BOM CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $weddingId = $request->input('wedding_id');

        $query = Guest::query()->with([
            'wedding:id,user_id,groom_name,bride_name,wedding_date',
            'table:id,name,zone',
            'response',
        ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('group_name', 'like', "%{$search}%")
                    ->orWhere('table_name', 'like', "%{$search}%")
                    ->orWhereHas('wedding', function ($wq) use ($search) {
                        $wq->where('groom_name', 'like', "%{$search}%")
                            ->orWhere('bride_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status === 'checked_in') {
            $query->whereNotNull('checked_in_at');
        } elseif ($status === 'not_checked_in') {
            $query->whereNull('checked_in_at');
        } elseif ($status === 'assigned') {
            $query->where(function ($q) {
                $q->whereNotNull('table_id')->orWhereNotNull('table_name');
            });
        } elseif ($status === 'unassigned') {
            $query->whereNull('table_id')->whereNull('table_name');
        }

        if (! empty($weddingId)) {
            $query->where('wedding_id', (int) $weddingId);
        }

        $guests = $query->latest('id')->get();
        $filename = 'danh-sach-quan-khach-mayhen-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($guests) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'STT',
                'Mã Khách',
                'Họ và Tên',
                'Số Điện Thoại',
                'Nhóm Quan Khách',
                'Đám Cưới Cặp Đôi',
                'Bàn Tiệc Phân Bổ',
                'Xác Nhận RSVP',
                'Sĩ Số Tham Dự',
                'Trạng Thái Check-in',
                'Thời Gian Check-in',
            ]);

            $index = 1;
            foreach ($guests as $g) {
                $rsvpLabel = match ($g->response?->attending_status) {
                    'attending' => 'Có tham dự',
                    'declined' => 'Không tham dự',
                    'uncertain' => 'Chưa chắc chắn',
                    default => 'Chưa phản hồi',
                };

                $tableName = $g->table?->name ?? $g->table_name ?? 'Chưa xếp bàn';
                $checkInStatus = $g->isCheckedIn() ? 'Đã đón tiếp' : 'Chưa đến';
                $checkInTime = $g->checked_in_at ? $g->checked_in_at->format('d/m/Y H:i:s') : '—';
                $coupleName = $g->wedding ? "{$g->wedding->groom_name} & {$g->wedding->bride_name}" : 'N/A';

                fputcsv($handle, CsvSanitizerService::row([
                    $index++,
                    $g->id,
                    $g->name,
                    $g->phone ?? 'Chưa có',
                    $g->group_name ?? 'Chung',
                    $coupleName,
                    $tableName,
                    $rsvpLabel,
                    $g->actualAttendeesCount(),
                    $checkInStatus,
                    $checkInTime,
                ]));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
