<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;

/**
 * Đọc đuôi tệp log JSONL theo từng khối ngược từ cuối file (bounded memory tail reader).
 * Tránh dùng file() nạp toàn bộ file hàng chục MB vào RAM gây HTTP 500 / Cloudflare 520.
 */
class LogTailReader
{
    /**
     * Đọc tối đa `$limit` bản ghi JSON mới nhất từ cuối tệp `$path`, trong giới hạn bộ nhớ `$maxBytes`.
     *
     * @param  callable(array<string, mixed>): bool|null  $matcher
     * @return array<int, array<string, mixed>>
     */
    public static function readJsonLines(
        string $path,
        int $limit = 100,
        ?callable $matcher = null,
        int $maxBytes = 4194304,
        int $chunkSize = 65536
    ): array {
        if ($limit <= 0 || ! File::exists($path)) {
            return [];
        }

        clearstatcache(true, $path);
        $fileSize = @filesize($path);
        if (! is_int($fileSize) || $fileSize <= 0) {
            return [];
        }

        $fp = @fopen($path, 'rb');
        if (! $fp) {
            return [];
        }

        $results = [];
        $pos = $fileSize;
        $bytesRead = 0;
        $remainder = '';

        try {
            while ($pos > 0 && $bytesRead < $maxBytes && count($results) < $limit) {
                $readSize = min($chunkSize, $pos);
                $pos -= $readSize;
                $bytesRead += $readSize;

                fseek($fp, $pos, SEEK_SET);
                $chunk = fread($fp, $readSize);
                if ($chunk === false || $chunk === '') {
                    break;
                }

                $combined = str_replace("\r\n", "\n", $chunk.$remainder);
                $lines = explode("\n", $combined);

                // Dòng đầu tiên của khối hiện tại có thể bị cắt ngang giữa ranh giới block nếu chưa chạm đầu file
                $remainder = $pos > 0 ? (string) array_shift($lines) : '';

                for ($i = count($lines) - 1; $i >= 0; $i--) {
                    $line = trim($lines[$i]);
                    if ($line === '') {
                        continue;
                    }

                    $decoded = json_decode($line, true);
                    if (! is_array($decoded)) {
                        continue;
                    }

                    if ($matcher !== null && ! $matcher($decoded)) {
                        continue;
                    }

                    $results[] = $decoded;

                    if (count($results) >= $limit) {
                        break;
                    }
                }
            }
        } finally {
            fclose($fp);
        }

        return $results;
    }
}
