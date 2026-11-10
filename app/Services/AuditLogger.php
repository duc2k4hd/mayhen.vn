<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class AuditLogger
{
    /**
     * Path to audit log file.
     */
    protected static string $logPath = 'logs/admin_audit.jsonl';

    /**
     * Keys that must always be masked in audit logs.
     */
    protected const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'secret',
        'token',
        'api_key',
        'webhook_secret',
        'card_number',
        'cvv',
        'bank_account',
    ];

    /**
     * Redact sensitive fields recursively.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function sanitizeDetails(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $normalizedKey = strtolower((string) $key);
            $isSensitive = false;
            foreach (self::SENSITIVE_KEYS as $sensitive) {
                if (str_contains($normalizedKey, $sensitive)) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $sanitized[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitizeDetails($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Record an administrative action into the audit log.
     *
     * @param  array<string, mixed>  $details
     */
    public static function log(string $action, string $targetType, int|string $targetId, string $description, array $details = []): void
    {
        try {
            $user = Auth::user();
            $path = storage_path(self::$logPath);
            $dir = dirname($path);

            if (! File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            $entry = json_encode([
                'timestamp' => now()->toIso8601String(),
                'admin_id' => $user?->id,
                'admin_name' => $user?->name ?? 'System',
                'admin_email' => $user?->email ?? 'system@mayhen.vn',
                'action' => $action,
                'target_type' => $targetType,
                'target_id' => (string) $targetId,
                'description' => $description,
                'details' => self::sanitizeDetails($details),
                'ip' => request()->ip(),
            ], JSON_UNESCAPED_UNICODE);

            File::append($path, $entry."\n");
        } catch (\Throwable) {
            // Fail silently to never interrupt admin operations
        }
    }

    /**
     * Retrieve recent audit logs with optional filtering.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getRecentLogs(int $limit = 100, ?string $filterAction = null, ?string $search = null): array
    {
        $path = storage_path(self::$logPath);
        if (! File::exists($path)) {
            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (! is_array($lines)) {
            return [];
        }

        $lines = array_reverse($lines);
        $logs = [];

        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if (! is_array($data)) {
                continue;
            }

            // Filter action
            if ($filterAction && ($data['action'] ?? '') !== $filterAction) {
                continue;
            }

            // Search text
            if ($search) {
                $searchLower = mb_strtolower($search);
                $found = str_contains(mb_strtolower($data['admin_name'] ?? ''), $searchLower)
                    || str_contains(mb_strtolower($data['description'] ?? ''), $searchLower)
                    || str_contains(mb_strtolower($data['target_id'] ?? ''), $searchLower);

                if (! $found) {
                    continue;
                }
            }

            $logs[] = $data;

            if (count($logs) >= $limit) {
                break;
            }
        }

        return $logs;
    }
}
