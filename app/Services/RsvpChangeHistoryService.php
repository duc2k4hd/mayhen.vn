<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Wedding;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RsvpChangeHistoryService
{
    /**
     * Thống kê toàn diện mức độ biến động RSVP & tác động sĩ số cỗ cưới.
     *
     * @return array{
     *     total_logs: int,
     *     switched_to_attending: int,
     *     switched_to_declined: int,
     *     headcount_increased: int,
     *     headcount_decreased: int,
     *     net_headcount_impact: int,
     *     recent_24h: int,
     *     recent_7d: int,
     *     by_guest_online: int,
     *     by_manual_host: int,
     *     dietary_changed: int
     * }
     */
    public function getVolatilitySummary(Wedding $wedding): array
    {
        $logs = $wedding->guestResponseLogs()->get();

        $switchedToAttending = 0;
        $switchedToDeclined = 0;
        $headcountIncreased = 0;
        $headcountDecreased = 0;
        $netHeadcountImpact = 0;
        $recent24h = 0;
        $recent7d = 0;
        $byGuestOnline = 0;
        $byManualHost = 0;
        $dietaryChanged = 0;

        $now = Carbon::now();
        $cutoff24h = $now->copy()->subHours(24);
        $cutoff7d = $now->copy()->subDays(7);

        foreach ($logs as $log) {
            $prevStatus = $log->previous_status;
            $newStatus = $log->new_status;

            // Đổi trạng thái tham dự
            if ($prevStatus !== 'attending' && $newStatus === 'attending') {
                $switchedToAttending++;
            } elseif ($prevStatus === 'attending' && $newStatus !== 'attending') {
                $switchedToDeclined++;
            }

            // Biến động sĩ số (Adults + Children)
            $prevTotal = (int) $log->previous_adults + (int) $log->previous_children;
            $newTotal = (int) $log->new_adults + (int) $log->new_children;

            if ($newStatus !== 'attending') {
                $effectiveNewTotal = 0;
            } else {
                $effectiveNewTotal = $newTotal;
            }

            if ($prevStatus !== 'attending') {
                $effectivePrevTotal = 0;
            } else {
                $effectivePrevTotal = $prevTotal;
            }

            $diff = $effectiveNewTotal - $effectivePrevTotal;
            $netHeadcountImpact += $diff;

            if ($diff > 0) {
                $headcountIncreased++;
            } elseif ($diff < 0) {
                $headcountDecreased++;
            }

            // Mốc thời gian
            if ($log->created_at && $log->created_at->greaterThanOrEqualTo($cutoff24h)) {
                $recent24h++;
            }
            if ($log->created_at && $log->created_at->greaterThanOrEqualTo($cutoff7d)) {
                $recent7d++;
            }

            // Nguồn cập nhật
            if ($log->source === 'manual_host') {
                $byManualHost++;
            } else {
                $byGuestOnline++;
            }

            // Thay đổi khẩu vị / ăn kiêng
            if ($log->previous_dietary !== $log->new_dietary && (! empty($log->previous_dietary) || ! empty($log->new_dietary))) {
                $dietaryChanged++;
            }
        }

        return [
            'total_logs' => $logs->count(),
            'switched_to_attending' => $switchedToAttending,
            'switched_to_declined' => $switchedToDeclined,
            'headcount_increased' => $headcountIncreased,
            'headcount_decreased' => $headcountDecreased,
            'net_headcount_impact' => $netHeadcountImpact,
            'recent_24h' => $recent24h,
            'recent_7d' => $recent7d,
            'by_guest_online' => $byGuestOnline,
            'by_manual_host' => $byManualHost,
            'dietary_changed' => $dietaryChanged,
        ];
    }

    /**
     * Truy vấn danh sách nhật ký biến động có lọc đa tiêu chí.
     */
    public function getFilteredLogs(Wedding $wedding, array $filters = []): Collection
    {
        $query = $wedding->guestResponseLogs()
            ->with(['guest', 'guestResponse'])
            ->latest();

        // Lọc theo từ khóa (tên khách, ghi chú)
        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->whereHas('guest', function (Builder $gq) use ($search) {
                    $gq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('group_name', 'like', "%{$search}%");
                })->orWhere('new_wishes', 'like', "%{$search}%")
                    ->orWhere('new_dietary', 'like', "%{$search}%");
            });
        }

        // Lọc theo nguồn thay đổi
        if (! empty($filters['source']) && in_array($filters['source'], ['guest_online', 'manual_host'], true)) {
            $query->where('source', $filters['source']);
        }

        // Lọc theo loại biến động
        if (! empty($filters['type'])) {
            switch ($filters['type']) {
                case 'switched_declined':
                    $query->where('previous_status', 'attending')
                        ->where('new_status', '!=', 'attending');
                    break;
                case 'switched_attending':
                    $query->where('previous_status', '!=', 'attending')
                        ->where('new_status', 'attending');
                    break;
                case 'headcount_changed':
                    $query->where(function (Builder $q) {
                        $q->whereColumn('previous_adults', '!=', 'new_adults')
                            ->orWhereColumn('previous_children', '!=', 'new_children');
                    });
                    break;
                case 'dietary_changed':
                    $query->whereColumn('previous_dietary', '!=', 'new_dietary');
                    break;
            }
        }

        // Lọc theo khoảng thời gian
        if (! empty($filters['time_range'])) {
            $now = Carbon::now();
            switch ($filters['time_range']) {
                case '24h':
                    $query->where('created_at', '>=', $now->subHours(24));
                    break;
                case '7d':
                    $query->where('created_at', '>=', $now->subDays(7));
                    break;
                case '30d':
                    $query->where('created_at', '>=', $now->subDays(30));
                    break;
            }
        }

        return $query->get();
    }

    /**
     * Dựng chuỗi dòng thời gian tương tác đầy đủ của 1 khách mời (Customer Journey Timeline).
     *
     * @return array<int, array{
     *     title: string,
     *     description: string,
     *     timestamp: string,
     *     timestamp_formatted: string,
     *     badge_type: string,
     *     actor: string,
     *     details: array<string, mixed>
     * }>
     */
    public function getGuestTimeline(Guest $guest): array
    {
        $timeline = [];

        // Mốc 1: Khởi tạo thông tin trong danh bạ
        if ($guest->created_at) {
            $timeline[] = [
                'title' => 'Khởi tạo thông tin thiệp mời',
                'description' => "Được thêm vào danh bạ khách mời • Nhóm: {$guest->group_name}".($guest->table_name ? " • Bàn: {$guest->table_name}" : ''),
                'timestamp' => $guest->created_at->toIso8601String(),
                'timestamp_formatted' => $guest->created_at->format('H:i d/m/Y'),
                'badge_type' => 'info',
                'actor' => 'Cô dâu & Chú rể',
                'details' => [
                    'group_name' => $guest->group_name,
                    'table_name' => $guest->table_name,
                    'expected_attendees' => $guest->expected_attendees,
                    'max_attendees' => $guest->max_attendees,
                ],
            ];
        }

        // Mốc 2: Gửi thiệp mời
        if ($guest->is_sent && $guest->sent_at) {
            $timeline[] = [
                'title' => 'Đã gửi thiệp mời',
                'description' => 'Thiệp mời cá nhân hóa đã được gửi đi cho quan khách.',
                'timestamp' => $guest->sent_at->toIso8601String(),
                'timestamp_formatted' => $guest->sent_at->format('H:i d/m/Y'),
                'badge_type' => 'neutral',
                'actor' => 'Hệ thống gửi thiệp',
                'details' => [],
            ];
        }

        // Mốc 3: Lần đầu mở xem thiệp
        if ($guest->opened_at) {
            $deviceInfo = $guest->last_device ? " (Thiết bị: {$guest->last_device})" : '';
            $timeline[] = [
                'title' => 'Khách mở xem thiệp lần đầu',
                'description' => "Quan khách đã truy cập vào đường link thiệp cưới{$deviceInfo} • Đã xem {$guest->open_count} lần.",
                'timestamp' => $guest->opened_at->toIso8601String(),
                'timestamp_formatted' => $guest->opened_at->format('H:i d/m/Y'),
                'badge_type' => 'neutral',
                'actor' => 'Khách mời',
                'details' => [
                    'open_count' => $guest->open_count,
                    'last_device' => $guest->last_device,
                ],
            ];
        }

        // Mốc 4: Các lần thay đổi phản hồi RSVP
        $logs = $guest->responseLogs()->with('guestResponse')->oldest()->get();
        foreach ($logs as $index => $log) {
            $actorLabel = $log->source === 'manual_host' ? 'Cô dâu / Chú rể cập nhật' : 'Khách mời tự cập nhật';
            $statusLabel = $this->getStatusLabel($log->new_status);
            $prevStatusLabel = $this->getStatusLabel($log->previous_status);

            $diffDesc = "Trạng thái: {$prevStatusLabel} ➔ {$statusLabel}";
            $prevTotal = (int) $log->previous_adults + (int) $log->previous_children;
            $newTotal = (int) $log->new_adults + (int) $log->new_children;

            if ($prevTotal !== $newTotal) {
                $diffDesc .= " • Sĩ số: {$prevTotal} người ➔ {$newTotal} người";
            }
            if ($log->new_dietary && $log->new_dietary !== $log->previous_dietary) {
                $diffDesc .= " • Yêu cầu món ăn: {$log->new_dietary}";
            }
            if ($log->new_wishes) {
                $diffDesc .= " • Lời nhắn: \"{$log->new_wishes}\"";
            }

            $badgeType = match ($log->new_status) {
                'attending' => 'success',
                'declined' => 'danger',
                default => 'warning',
            };

            $timeline[] = [
                'title' => ($index === 0 && ! $guest->response?->needs_reconfirmation) ? 'Cập nhật xác nhận tham dự (RSVP)' : 'Điều chỉnh phản hồi RSVP',
                'description' => $diffDesc,
                'timestamp' => $log->created_at ? $log->created_at->toIso8601String() : now()->toIso8601String(),
                'timestamp_formatted' => $log->created_at ? $log->created_at->format('H:i d/m/Y') : '',
                'badge_type' => $badgeType,
                'actor' => $actorLabel,
                'details' => [
                    'previous_status' => $log->previous_status,
                    'new_status' => $log->new_status,
                    'previous_adults' => $log->previous_adults,
                    'new_adults' => $log->new_adults,
                    'previous_children' => $log->previous_children,
                    'new_children' => $log->new_children,
                    'dietary' => $log->new_dietary,
                    'wishes' => $log->new_wishes,
                    'ip_address' => $log->ip_address,
                ],
            ];
        }

        // Mốc 5: Điểm danh check-in tại sảnh cưới
        if ($guest->checked_in_at) {
            $attendeesCount = $guest->checked_in_attendees ?: $guest->expected_attendees;
            $timeline[] = [
                'title' => 'Đã điểm danh đón tiếp tại sảnh tiệc',
                'description' => "Đón tiếp thành công {$attendeesCount} người an tọa tại {$guest->table_name}.".($guest->check_in_note ? " Ghi chú: {$guest->check_in_note}" : ''),
                'timestamp' => $guest->checked_in_at->toIso8601String(),
                'timestamp_formatted' => $guest->checked_in_at->format('H:i d/m/Y'),
                'badge_type' => 'success',
                'actor' => 'Lễ tân sảnh tiệc',
                'details' => [
                    'checked_in_attendees' => $guest->checked_in_attendees,
                    'check_in_note' => $guest->check_in_note,
                ],
            ];
        }

        // Sắp xếp timeline theo thứ tự thời gian mới nhất lên đầu
        usort($timeline, fn ($a, $b) => strcmp($b['timestamp'], $a['timestamp']));

        return $timeline;
    }

    /**
     * Xuất danh sách nhật ký biến động RSVP ra file CSV (chuẩn UTF-8 BOM, an toàn Formula Injection).
     */
    public function exportLogsToCsv(Wedding $wedding, array $filters = []): StreamedResponse
    {
        $logs = $this->getFilteredLogs($wedding, $filters);
        $filename = 'nhat-ky-bien-dong-rsvp-'.($wedding->groom_name ?: 'chu-re').'-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // UTF-8 BOM để Excel hiển thị đúng dấu tiếng Việt
            fwrite($handle, "\xEF\xBB\xBF");

            // Header columns
            fputcsv($handle, CsvSanitizerService::row([
                'Mã Log',
                'Thời Gian',
                'Tên Khách Mời',
                'Số Điện Thoại',
                'Phân Nhóm',
                'Bàn Tiệc',
                'Trạng Thái Cũ',
                'Trạng Thái Mới',
                'Người Lớn (Cũ)',
                'Người Lớn (Mới)',
                'Trẻ Em (Cũ)',
                'Trẻ Em (Mới)',
                'Chênh Lệch Sĩ Số',
                'Thực Đơn / Ăn Kiêng',
                'Lời Nhắn / Ghi Chú',
                'Nguồn Thay Đổi',
                'Địa Chỉ IP',
            ]));

            foreach ($logs as $log) {
                $guest = $log->guest;
                $prevStatus = $this->getStatusLabel($log->previous_status);
                $newStatus = $this->getStatusLabel($log->new_status);

                $prevTotal = ($log->previous_status === 'attending') ? ((int) $log->previous_adults + (int) $log->previous_children) : 0;
                $newTotal = ($log->new_status === 'attending') ? ((int) $log->new_adults + (int) $log->new_children) : 0;
                $diff = $newTotal - $prevTotal;
                $diffText = $diff > 0 ? "+{$diff}" : (string) $diff;

                $sourceText = $log->source === 'manual_host' ? 'Cô dâu / Chú rể cập nhật' : 'Khách mời tự cập nhật qua link';

                fputcsv($handle, CsvSanitizerService::row([
                    $log->id,
                    $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '',
                    $guest?->name ?: 'Khách chưa định danh',
                    $guest?->phone ?: '',
                    $guest?->group_name ?: '',
                    $guest?->table_name ?: '',
                    $prevStatus,
                    $newStatus,
                    $log->previous_adults ?? 0,
                    $log->new_adults ?? 0,
                    $log->previous_children ?? 0,
                    $log->new_children ?? 0,
                    $diffText,
                    $log->new_dietary ?: '',
                    $log->new_wishes ?: '',
                    $sourceText,
                    $log->ip_address ?: '',
                ]));
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Nhãn trạng thái tiếng Việt thân thiện.
     */
    public function getStatusLabel(?string $status): string
    {
        return match ($status) {
            'attending' => 'Có tham dự',
            'declined' => 'Không thể tham dự',
            'tentative' => 'Chưa chắc chắn',
            default => 'Chưa phản hồi',
        };
    }
}
