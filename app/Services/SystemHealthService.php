<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class SystemHealthService
{
    /**
     * Lấy 4 Thẻ KPI Sức Khỏe Toàn Diện.
     */
    public function getHealthKpis(): array
    {
        $health = $this->getHealthSummary();

        $overallStatus = $health['overall_status'];
        $statusText = match ($overallStatus) {
            'healthy' => 'Hoạt Động 100%',
            'warning' => 'Cần Chú Ý',
            default => 'Sự Cố Nghiêm Trọng',
        };

        // Đếm lỗi trong file log hôm nay
        $errorsToday = $this->countErrorsToday();

        // Bộ nhớ PHP
        $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 1);
        $memoryPeak = round(memory_get_peak_usage(true) / 1024 / 1024, 1);

        // Dung lượng đĩa
        $diskFreeGb = $health['storage']['free_gb'];

        return [
            'service_status' => $statusText,
            'overall_status' => $overallStatus,
            'errors_today_count' => $errorsToday,
            'memory_usage_mb' => $memoryUsage,
            'memory_peak_mb' => $memoryPeak,
            'disk_free_gb' => $diskFreeGb,
            'disk_used_percent' => $health['storage']['used_percent'],
        ];
    }

    /**
     * Kiểm tra trạng thái các phân hệ dịch vụ hạ tầng.
     */
    public function getHealthSummary(): array
    {
        $hasError = false;
        $hasWarning = false;

        // 1. Kiểm tra Cơ sở dữ liệu
        $dbStatus = 'healthy';
        $dbLatency = 0;
        $dbDriver = config('database.default');
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $dbLatency = round((microtime(true) - $start) * 1000, 2);
        } catch (Throwable $e) {
            $dbStatus = 'critical';
            $hasError = true;
        }

        // 2. Kiểm tra Cache
        $cacheStatus = 'healthy';
        $cacheLatency = 0;
        $cacheDriver = config('cache.default');
        try {
            $start = microtime(true);
            $testKey = 'system_health_check_'.time();
            Cache::put($testKey, 'ok', 5);
            $val = Cache::get($testKey);
            Cache::forget($testKey);
            $cacheLatency = round((microtime(true) - $start) * 1000, 2);
            if ($val !== 'ok') {
                $cacheStatus = 'warning';
                $hasWarning = true;
            }
        } catch (Throwable $e) {
            $cacheStatus = 'critical';
            $hasError = true;
        }

        // 3. Kiểm tra Bộ nhớ lưu trữ (Storage & Disk)
        $storagePath = storage_path();
        $isWritable = is_writable($storagePath);
        $freeBytes = @disk_free_space($storagePath) ?: 0;
        $totalBytes = @disk_total_space($storagePath) ?: 1;
        $usedBytes = $totalBytes - $freeBytes;
        $usedPercent = round(($usedBytes / $totalBytes) * 100, 1);

        $storageStatus = 'healthy';
        if (! $isWritable) {
            $storageStatus = 'critical';
            $hasError = true;
        } elseif ($usedPercent > 90) {
            $storageStatus = 'critical';
            $hasError = true;
        } elseif ($usedPercent > 80) {
            $storageStatus = 'warning';
            $hasWarning = true;
        }

        // Kích thước tệp log
        $logPath = storage_path('logs/laravel.log');
        $logSizeBytes = File::exists($logPath) ? File::size($logPath) : 0;
        $logSizeMb = round($logSizeBytes / 1024 / 1024, 2);

        // 4. Hàng đợi (Queue)
        $queueDriver = config('queue.default');
        $failedJobsCount = DB::table('failed_jobs')->count();
        $pendingJobsCount = DB::table('jobs')->count();
        $queueStatus = 'healthy';
        if ($failedJobsCount > 5) {
            $queueStatus = 'warning';
            $hasWarning = true;
        }

        // 5. Tổng kết
        $overallStatus = 'healthy';
        if ($hasError) {
            $overallStatus = 'critical';
        } elseif ($hasWarning) {
            $overallStatus = 'warning';
        }

        return [
            'overall_status' => $overallStatus,
            'database' => [
                'status' => $dbStatus,
                'driver' => $dbDriver,
                'latency_ms' => $dbLatency,
            ],
            'cache' => [
                'status' => $cacheStatus,
                'driver' => $cacheDriver,
                'latency_ms' => $cacheLatency,
            ],
            'storage' => [
                'status' => $storageStatus,
                'is_writable' => $isWritable,
                'free_gb' => round($freeBytes / 1024 / 1024 / 1024, 2),
                'total_gb' => round($totalBytes / 1024 / 1024 / 1024, 2),
                'used_percent' => $usedPercent,
                'log_size_mb' => $logSizeMb,
            ],
            'queue' => [
                'status' => $queueStatus,
                'driver' => $queueDriver,
                'pending_jobs' => $pendingJobsCount,
                'failed_jobs' => $failedJobsCount,
            ],
            'environment' => [
                'app_env' => config('app.env'),
                'debug_mode' => config('app.debug'),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'timezone' => config('app.timezone'),
                'opcache_enabled' => function_exists('opcache_get_status') && ! empty(opcache_get_status()),
            ],
        ];
    }

    /**
     * Đọc và bóc tách các dòng log lỗi từ file `storage/logs/laravel.log` (Tail Reader).
     */
    public function parseLaravelLogs(int $limit = 50, ?string $level = null, ?string $search = null): array
    {
        $logPath = storage_path('logs/laravel.log');
        if (! File::exists($logPath)) {
            return [];
        }

        clearstatcache(true, $logPath);
        $fileSize = @filesize($logPath);
        if (! $fileSize) {
            return [];
        }

        // Đọc tối đa 2MB cuối file để tránh tràn bộ nhớ khi log lớn
        $readBytes = min($fileSize, 2 * 1024 * 1024);

        $fp = fopen($logPath, 'r');
        if (! $fp) {
            return [];
        }

        if ($readBytes < $fileSize) {
            fseek($fp, -$readBytes, SEEK_END);
        } else {
            rewind($fp);
        }

        $content = fread($fp, $readBytes);
        fclose($fp);

        if (empty($content)) {
            return [];
        }

        // Chuẩn hóa dòng về \n
        $content = str_replace("\r\n", "\n", $content);

        // Tách các bản ghi log dựa trên mốc thời gian bắt đầu [YYYY-MM-DD HH:MM:SS]
        $entries = preg_split('/(?:\n|^)(?=\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\])/', $content, -1, PREG_SPLIT_NO_EMPTY);

        $parsedLogs = [];
        $entries = array_reverse($entries); // Đưa log mới nhất lên đầu

        foreach ($entries as $entry) {
            $entry = trim($entry);
            if (empty($entry)) {
                continue;
            }

            if (! preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] ([a-zA-Z0-9_-]+)\.([A-Z]+): (.*)$/s', $entry, $match)) {
                continue;
            }

            $timestamp = $match[1];
            $env = $match[2];
            $logLevel = strtoupper($match[3]);
            $body = trim($match[4]);

            // Lọc theo level nếu có
            if ($level && $logLevel !== strtoupper($level)) {
                continue;
            }

            // Lọc theo từ khóa tìm kiếm nếu có
            if ($search && ! str_contains(mb_strtolower($body), mb_strtolower($search))) {
                continue;
            }

            // Tách dòng đầu tiên làm message, các dòng sau là stack trace
            $lines = explode("\n", $body, 2);
            $message = trim($lines[0]);
            $stackTrace = isset($lines[1]) ? trim($lines[1]) : '';

            $parsedLogs[] = [
                'timestamp' => $timestamp,
                'environment' => $env,
                'level' => $logLevel,
                'message' => $message,
                'stack_trace' => $stackTrace,
                'badge_color' => match ($logLevel) {
                    'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY' => 'danger',
                    'WARNING' => 'warning',
                    'NOTICE', 'INFO' => 'info',
                    default => 'muted',
                },
            ];

            if (count($parsedLogs) >= $limit) {
                break;
            }
        }

        return $parsedLogs;
    }

    /**
     * Đếm số lượng log cấp độ ERROR / CRITICAL trong 24 giờ qua.
     */
    protected function countErrorsToday(): int
    {
        $logPath = storage_path('logs/laravel.log');
        if (! File::exists($logPath)) {
            return 0;
        }

        clearstatcache(true, $logPath);
        $fileSize = @filesize($logPath);
        if (! $fileSize) {
            return 0;
        }

        $todayStr = Carbon::now()->format('Y-m-d');
        $fp = fopen($logPath, 'r');
        if (! $fp) {
            return 0;
        }

        $count = 0;
        $maxBytes = min($fileSize, 1024 * 1024); // Đọc tối đa 1MB cuối
        if ($maxBytes < $fileSize) {
            fseek($fp, -$maxBytes, SEEK_END);
        } else {
            rewind($fp);
        }

        while (($line = fgets($fp)) !== false) {
            if (str_starts_with($line, "[{$todayStr}") && (str_contains($line, '.ERROR:') || str_contains($line, '.CRITICAL:') || str_contains($line, '.ALERT:'))) {
                $count++;
            }
        }

        fclose($fp);

        return $count;
    }

    /**
     * Làm sạch tệp log hệ thống `storage/logs/laravel.log`.
     */
    public function clearLaravelLog(?User $admin = null): bool
    {
        $logPath = storage_path('logs/laravel.log');

        if (File::exists($logPath)) {
            File::put($logPath, '');
        }

        AuditLogger::log(
            'SYSTEM_LOG_CLEARED',
            'system_log',
            0,
            "Quản trị viên đã làm sạch tệp nhật ký lỗi hệ thống 'storage/logs/laravel.log'.",
            ['cleared_by' => $admin?->name]
        );

        return true;
    }

    /**
     * Tải tệp log gốc.
     */
    public function downloadLog(): BinaryFileResponse
    {
        $logPath = storage_path('logs/laravel.log');

        if (! File::exists($logPath)) {
            File::put($logPath, '');
        }

        return response()->download(
            $logPath,
            'mayhen_laravel_'.now()->format('Ymd_His').'.log',
            ['Content-Type' => 'text/plain']
        );
    }
}
