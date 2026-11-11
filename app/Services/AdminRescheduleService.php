<?php

namespace App\Services;

use App\Models\GuestResponse;
use App\Models\Wedding;
use App\Models\WeddingRescheduleLog;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AdminRescheduleService
{
    /**
     * Lấy danh sách nhật ký dời ngày cưới và thống kê 4 KPI chỉ số.
     */
    public function getRescheduleOverview(array $filters = []): array
    {
        // 1. Thống kê KPI
        $totalReschedules = WeddingRescheduleLog::count();
        $totalAffectedGuests = (int) WeddingRescheduleLog::sum('affected_guests_count');
        $totalNotifiedGuests = (int) WeddingRescheduleLog::sum('notified_guests_count');
        $notificationRate = $totalAffectedGuests > 0 ? round(($totalNotifiedGuests / $totalAffectedGuests) * 100, 1) : 0;
        $reconfirmRequestsCount = WeddingRescheduleLog::where('rsvp_action', 'request_reconfirm')->count();

        $stats = [
            'total_reschedules' => $totalReschedules,
            'total_affected_guests' => $totalAffectedGuests,
            'total_notified_guests' => $totalNotifiedGuests,
            'notification_rate' => $notificationRate,
            'reconfirm_requests_count' => $reconfirmRequestsCount,
        ];

        // 2. Query danh sách log
        $query = WeddingRescheduleLog::with(['wedding.user', 'invitation'])->latest();

        // Lọc từ khóa
        if (! empty($filters['q'])) {
            $keyword = trim($filters['q']);
            $query->where(function ($q) use ($keyword) {
                $q->where('reason', 'like', "%{$keyword}%")
                    ->orWhere('old_venue', 'like', "%{$keyword}%")
                    ->orWhere('new_venue', 'like', "%{$keyword}%")
                    ->orWhereHas('wedding', function ($wq) use ($keyword) {
                        $wq->where('title', 'like', "%{$keyword}%")
                            ->orWhere('groom_name', 'like', "%{$keyword}%")
                            ->orWhere('bride_name', 'like', "%{$keyword}%");
                    });
            });
        }

        // Lọc theo tiệc cưới
        if (! empty($filters['wedding_id'])) {
            $query->where('wedding_id', $filters['wedding_id']);
        }

        // Lọc theo hành động RSVP
        if (! empty($filters['rsvp_action']) && $filters['rsvp_action'] !== 'all') {
            $query->where('rsvp_action', $filters['rsvp_action']);
        }

        $logs = $query->paginate(15)->withQueryString();

        return [
            'stats' => $stats,
            'logs' => $logs,
        ];
    }

    /**
     * Thực hiện đổi ngày cưới / địa điểm và ghi nhận nhật ký dời lịch.
     */
    public function rescheduleWedding(Wedding $wedding, array $data, string $operator = 'Admin'): WeddingRescheduleLog
    {
        return DB::transaction(function () use ($wedding, $data) {
            $oldDate = $wedding->wedding_date;
            $oldVenue = $wedding->venue_name;

            $newDate = Carbon::parse($data['new_date']);
            $newVenue = isset($data['new_venue']) ? trim($data['new_venue']) : $oldVenue;
            $reason = isset($data['reason']) ? trim($data['reason']) : 'Yêu cầu điều chỉnh từ gia đình';
            $rsvpAction = $data['rsvp_action'] ?? 'keep_existing';

            // 1. Cập nhật ngày cưới & địa điểm cho Wedding
            $wedding->wedding_date = $newDate;
            if (isset($data['new_venue'])) {
                $wedding->venue_name = $newVenue;
            }
            if (isset($data['new_venue_address'])) {
                $wedding->venue_address = trim($data['new_venue_address']);
            }
            $wedding->save();

            // 2. Tính số lượng khách mời bị ảnh hưởng (khách trong danh bạ)
            $affectedGuestsCount = $wedding->guests()->count();

            // 3. Xử lý chính sách RSVP
            if ($rsvpAction === 'request_reconfirm') {
                // Đánh dấu cần xác nhận lại cho các phản hồi RSVP hiện hữu
                GuestResponse::whereHas('guest', function ($q) use ($wedding) {
                    $q->where('wedding_id', $wedding->id);
                })->update([
                    'needs_reconfirmation' => true,
                    'reconfirmed_at' => null,
                ]);
            }

            // 4. Tạo bản ghi WeddingRescheduleLog
            $invitation = $wedding->invitation;

            return WeddingRescheduleLog::create([
                'wedding_id' => $wedding->id,
                'invitation_id' => $invitation?->id,
                'old_date' => $oldDate,
                'new_date' => $newDate,
                'old_venue' => $oldVenue,
                'new_venue' => $newVenue,
                'reason' => $reason,
                'rsvp_action' => $rsvpAction,
                'affected_guests_count' => $affectedGuestsCount,
                'notified_guests_count' => 0,
            ]);
        });
    }

    /**
     * Gửi / Ghi nhận thông báo đính chính ngày cưới tới khách mời bị ảnh hưởng.
     */
    public function broadcastNotification(WeddingRescheduleLog $log, ?int $notifiedCount = null, string $operator = 'Admin'): WeddingRescheduleLog
    {
        $count = $notifiedCount ?? $log->affected_guests_count;

        $log->notified_guests_count = min($count, $log->affected_guests_count);
        $log->save();

        return $log;
    }

    /**
     * Xuất tệp nhật ký dời lịch cưới định dạng CSV UTF-8 BOM.
     */
    public function exportRescheduleLogsCsv(array $filters = []): Response
    {
        $query = WeddingRescheduleLog::with(['wedding.user'])->latest();

        if (! empty($filters['q'])) {
            $keyword = trim($filters['q']);
            $query->where(function ($q) use ($keyword) {
                $q->where('reason', 'like', "%{$keyword}%")
                    ->orWhere('old_venue', 'like', "%{$keyword}%")
                    ->orWhere('new_venue', 'like', "%{$keyword}%")
                    ->orWhereHas('wedding', function ($wq) use ($keyword) {
                        $wq->where('title', 'like', "%{$keyword}%")
                            ->orWhere('groom_name', 'like', "%{$keyword}%")
                            ->orWhere('bride_name', 'like', "%{$keyword}%");
                    });
            });
        }

        if (! empty($filters['rsvp_action']) && $filters['rsvp_action'] !== 'all') {
            $query->where('rsvp_action', $filters['rsvp_action']);
        }

        $logs = $query->get();

        $headers = [
            'ID Nhật Ký',
            'Tiệc Cưới',
            'Cô Dâu & Chú Rể',
            'Chủ Tài Khoản',
            'Ngày Cưới Cũ',
            'Ngày Cưới Mới',
            'Địa Điểm Cũ',
            'Địa Điểm Mới',
            'Lý Do Điều Chỉnh',
            'Chính Sách RSVP',
            'Số Khách Ảnh Hưởng',
            'Số Khách Đã Nhận Tin',
            'Tỷ Lệ Nhận Tin (%)',
            'Thời Gian Thực Hiện',
        ];

        $handle = fopen('php://temp', 'r+');
        // Thêm UTF-8 BOM
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $headers);

        foreach ($logs as $item) {
            $rate = $item->affected_guests_count > 0 ? round(($item->notified_guests_count / $item->affected_guests_count) * 100, 1) : 0;
            $rsvpLabel = $item->rsvp_action === 'request_reconfirm' ? 'Yêu cầu xác nhận lại' : 'Giữ nguyên phản hồi cũ';

            fputcsv($handle, [
                $item->id,
                $item->wedding?->title ?: '—',
                ($item->wedding?->groom_name.' & '.$item->wedding?->bride_name),
                $item->wedding?->user?->name ?: '—',
                $item->old_date ? $item->old_date->format('d/m/Y') : '—',
                $item->new_date ? $item->new_date->format('d/m/Y') : '—',
                $item->old_venue ?: '—',
                $item->new_venue ?: '—',
                $item->reason ?: '—',
                $rsvpLabel,
                $item->affected_guests_count,
                $item->notified_guests_count,
                $rate.'%',
                $item->created_at ? $item->created_at->format('d/m/Y H:i:s') : '—',
            ]);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        $fileName = 'mayhen_wedding_reschedule_logs_'.date('Ymd_His').'.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
