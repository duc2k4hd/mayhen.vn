<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class LastMinuteLogService
{
    /**
     * Danh mục phát sinh tiếng Việt.
     *
     * @var array<string, string>
     */
    public const CATEGORIES = [
        'seating' => 'Chỗ Ngồi & Bàn Tiệc',
        'dietary' => 'Ẩm Thực & Món Ăn',
        'concierge' => 'Lễ Tân & Đón Tiếp',
        'stage' => 'Sân Khấu, Âm Thanh & MC',
        'logistics' => 'Hậu Cần & Đạo Cụ',
        'other' => 'Phát Sinh Khác',
    ];

    /**
     * Mức độ ưu tiên.
     *
     * @var array<string, string>
     */
    public const PRIORITIES = [
        'urgent' => 'Khẩn Cấp (Xử lý ngay)',
        'high' => 'Ưu Tiên Cao',
        'normal' => 'Bình Thường',
    ];

    /**
     * Trạng thái xử lý.
     *
     * @var array<string, string>
     */
    public const STATUSES = [
        'pending' => 'Chờ Xử Lý',
        'in_progress' => 'Đang Thực Hiện',
        'resolved' => 'Đã Hoàn Thành',
        'cancelled' => 'Đã Hủy Bỏ',
    ];

    /**
     * Lấy danh sách nhật ký phát sinh có lọc.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLogs(Invitation $invitation, array $filters = []): array
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $logs = $customConfig['last_minute_logs'] ?? [];
        if (! is_array($logs)) {
            $logs = [];
        }

        // Lọc theo danh mục
        if (! empty($filters['category'])) {
            $logs = array_filter($logs, fn ($l) => ($l['category'] ?? '') === $filters['category']);
        }

        // Lọc theo trạng thái
        if (! empty($filters['status'])) {
            $logs = array_filter($logs, fn ($l) => ($l['status'] ?? '') === $filters['status']);
        }

        // Lọc theo mức độ ưu tiên
        if (! empty($filters['priority'])) {
            $logs = array_filter($logs, fn ($l) => ($l['priority'] ?? '') === $filters['priority']);
        }

        // Tìm kiếm theo từ khóa
        if (! empty($filters['search'])) {
            $kw = mb_strtolower(trim($filters['search']));
            $logs = array_filter($logs, function ($l) use ($kw) {
                return str_contains(mb_strtolower($l['title'] ?? ''), $kw)
                    || str_contains(mb_strtolower($l['assignee_name'] ?? ''), $kw)
                    || str_contains(mb_strtolower($l['related_table'] ?? ''), $kw)
                    || str_contains(mb_strtolower($l['related_guest_name'] ?? ''), $kw);
            });
        }

        // Sắp xếp: urgent lên đầu, sau đó theo thời gian mới nhất
        usort($logs, function ($a, $b) {
            $pOrder = ['urgent' => 3, 'high' => 2, 'normal' => 1];
            $pa = $pOrder[$a['priority'] ?? 'normal'] ?? 1;
            $pb = $pOrder[$b['priority'] ?? 'normal'] ?? 1;

            if ($pa !== $pb) {
                return $pb <=> $pa;
            }

            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        return array_values($logs);
    }

    /**
     * Lấy số liệu thống kê chỉ số phát sinh.
     *
     * @return array{
     *     total_logs: int,
     *     pending_count: int,
     *     in_progress_count: int,
     *     resolved_count: int,
     *     urgent_count: int,
     *     total_cost_impact: int
     * }
     */
    public function getStats(Invitation $invitation): array
    {
        $allLogs = $this->getLogs($invitation);

        $pending = 0;
        $inProgress = 0;
        $resolved = 0;
        $urgent = 0;
        $totalCost = 0;

        foreach ($allLogs as $log) {
            $st = $log['status'] ?? 'pending';
            if ($st === 'pending') {
                $pending++;
            } elseif ($st === 'in_progress') {
                $inProgress++;
            } elseif ($st === 'resolved') {
                $resolved++;
            }

            if (($log['priority'] ?? '') === 'urgent' && $st !== 'resolved') {
                $urgent++;
            }

            if (! empty($log['cost_impact'])) {
                $totalCost += (int) $log['cost_impact'];
            }
        }

        return [
            'total_logs' => count($allLogs),
            'pending_count' => $pending,
            'in_progress_count' => $inProgress,
            'resolved_count' => $resolved,
            'urgent_count' => $urgent,
            'total_cost_impact' => $totalCost,
        ];
    }

    /**
     * Thêm mới một bản ghi phát sinh giờ chót.
     */
    public function createLog(Invitation $invitation, array $data, ?User $user = null): array
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $logs = $customConfig['last_minute_logs'] ?? [];
        if (! is_array($logs)) {
            $logs = [];
        }

        $now = now();
        $newLog = [
            'id' => 'log_'.Str::random(12),
            'title' => trim((string) ($data['title'] ?? 'Phát sinh mới')),
            'category' => $data['category'] ?? 'other',
            'priority' => $data['priority'] ?? 'normal',
            'status' => 'pending',
            'assignee_name' => trim((string) ($data['assignee_name'] ?? '')),
            'related_table' => trim((string) ($data['related_table'] ?? '')),
            'related_guest_name' => trim((string) ($data['related_guest_name'] ?? '')),
            'cost_impact' => max(0, (int) ($data['cost_impact'] ?? 0)),
            'note' => trim((string) ($data['note'] ?? '')),
            'created_at' => $now->toIso8601String(),
            'created_by' => $user?->name ?? 'Cặp đôi',
            'resolved_at' => null,
        ];

        array_unshift($logs, $newLog);
        $customConfig['last_minute_logs'] = array_slice($logs, 0, 100);
        $invitation->custom_config = $customConfig;
        $invitation->save();

        AuditLogger::log(
            'LAST_MINUTE_LOG_CREATED',
            'Invitation',
            $invitation->id,
            "Ghi nhận phát sinh giờ chót: '{$newLog['title']}' [Ưu tiên: {$newLog['priority']}]"
        );

        return $newLog;
    }

    /**
     * Cập nhật thông tin bản ghi phát sinh.
     */
    public function updateLog(Invitation $invitation, string $logId, array $data, ?User $user = null): ?array
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $logs = $customConfig['last_minute_logs'] ?? [];
        if (! is_array($logs)) {
            return null;
        }

        $updatedLog = null;
        foreach ($logs as &$log) {
            if (($log['id'] ?? '') === $logId) {
                if (isset($data['title'])) {
                    $log['title'] = trim((string) $data['title']);
                }
                if (isset($data['category'])) {
                    $log['category'] = $data['category'];
                }
                if (isset($data['priority'])) {
                    $log['priority'] = $data['priority'];
                }
                if (isset($data['assignee_name'])) {
                    $log['assignee_name'] = trim((string) $data['assignee_name']);
                }
                if (isset($data['related_table'])) {
                    $log['related_table'] = trim((string) $data['related_table']);
                }
                if (isset($data['related_guest_name'])) {
                    $log['related_guest_name'] = trim((string) $data['related_guest_name']);
                }
                if (isset($data['cost_impact'])) {
                    $log['cost_impact'] = max(0, (int) $data['cost_impact']);
                }
                if (isset($data['note'])) {
                    $log['note'] = trim((string) $data['note']);
                }

                $updatedLog = $log;
                break;
            }
        }
        unset($log);

        if ($updatedLog) {
            $customConfig['last_minute_logs'] = $logs;
            $invitation->custom_config = $customConfig;
            $invitation->save();

            AuditLogger::log(
                'LAST_MINUTE_LOG_UPDATED',
                'Invitation',
                $invitation->id,
                "Cập nhật phát sinh giờ chót '{$updatedLog['title']}'"
            );
        }

        return $updatedLog;
    }

    /**
     * Chuyển trạng thái xử lý của một phát sinh (1-chạm).
     */
    public function toggleStatus(Invitation $invitation, string $logId, string $newStatus, ?User $user = null): ?array
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $logs = $customConfig['last_minute_logs'] ?? [];
        if (! is_array($logs)) {
            return null;
        }

        $target = null;
        foreach ($logs as &$log) {
            if (($log['id'] ?? '') === $logId) {
                $log['status'] = $newStatus;
                if ($newStatus === 'resolved') {
                    $log['resolved_at'] = now()->toIso8601String();
                } else {
                    $log['resolved_at'] = null;
                }
                $target = $log;
                break;
            }
        }
        unset($log);

        if ($target) {
            $customConfig['last_minute_logs'] = $logs;
            $invitation->custom_config = $customConfig;
            $invitation->save();

            AuditLogger::log(
                'LAST_MINUTE_LOG_STATUS_CHANGED',
                'Invitation',
                $invitation->id,
                "Đổi trạng thái phát sinh '{$target['title']}' sang '{$newStatus}'"
            );
        }

        return $target;
    }

    /**
     * Xóa một bản ghi phát sinh.
     */
    public function deleteLog(Invitation $invitation, string $logId, ?User $user = null): bool
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $logs = $customConfig['last_minute_logs'] ?? [];
        if (! is_array($logs)) {
            return false;
        }

        $initialCount = count($logs);
        $logs = array_values(array_filter($logs, fn ($l) => ($l['id'] ?? '') !== $logId));

        if (count($logs) < $initialCount) {
            $customConfig['last_minute_logs'] = $logs;
            $invitation->custom_config = $customConfig;
            $invitation->save();

            AuditLogger::log(
                'LAST_MINUTE_LOG_DELETED',
                'Invitation',
                $invitation->id,
                "Đã xóa một bản ghi phát sinh giờ chót #{$logId}"
            );

            return true;
        }

        return false;
    }

    /**
     * Xuất danh sách nhật ký phát sinh sang file CSV chuẩn UTF-8 BOM kháng công thức injection.
     */
    public function exportCsv(Invitation $invitation): string
    {
        $logs = $this->getLogs($invitation);

        $output = "\xEF\xBB\xBF"; // UTF-8 BOM cho Excel
        $output .= "ID,Tiêu Đề Phát Sinh,Danh Mục,Mức Độ Ưu Tiên,Trạng Thái,Người Phụ Trách,Bàn Liên Quan,Khách Liên Quan,Chi Phí Phát Sinh (VNĐ),Ghi Chú,Thời Gian Ghi Nhận,Thời Gian Hoàn Thành\n";

        foreach ($logs as $log) {
            $cat = self::CATEGORIES[$log['category'] ?? ''] ?? $log['category'];
            $prio = self::PRIORITIES[$log['priority'] ?? ''] ?? $log['priority'];
            $st = self::STATUSES[$log['status'] ?? ''] ?? $log['status'];
            $cost = number_format((int) ($log['cost_impact'] ?? 0));
            $created = ! empty($log['created_at']) ? Carbon::parse($log['created_at'])->format('d/m/Y H:i') : '';
            $resolved = ! empty($log['resolved_at']) ? Carbon::parse($log['resolved_at'])->format('d/m/Y H:i') : '';

            $row = [
                CsvSanitizerService::sanitize((string) ($log['id'] ?? '')),
                CsvSanitizerService::sanitize((string) ($log['title'] ?? '')),
                CsvSanitizerService::sanitize((string) $cat),
                CsvSanitizerService::sanitize((string) $prio),
                CsvSanitizerService::sanitize((string) $st),
                CsvSanitizerService::sanitize((string) ($log['assignee_name'] ?? '')),
                CsvSanitizerService::sanitize((string) ($log['related_table'] ?? '')),
                CsvSanitizerService::sanitize((string) ($log['related_guest_name'] ?? '')),
                $cost,
                CsvSanitizerService::sanitize((string) ($log['note'] ?? '')),
                $created,
                $resolved,
            ];

            $output .= implode(',', array_map(fn ($f) => '"'.str_replace('"', '""', $f).'"', $row))."\n";
        }

        return $output;
    }
}
