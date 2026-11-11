<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\ImportBatch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GuestBatchImportService
{
    /**
     * Đọc tệp tải lên, phát hiện tiêu đề cột và khởi tạo phiên ImportBatch
     */
    public function parseUploadedFile(UploadedFile $file, int $weddingId, ?int $userId = null): ImportBatch
    {
        $content = file_get_contents($file->getRealPath());

        // Xử lý BOM UTF-8 nếu có
        $bom = pack('H*', 'EFBBBF');
        $content = preg_replace("/^{$bom}/", '', $content);

        // Chuẩn hóa dòng ngắt
        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        if (empty($lines)) {
            throw new \InvalidArgumentException('Tệp tin rỗng hoặc không có dữ liệu hợp lệ.');
        }

        // Tự động nhận diện dấu phân tách: phẩy (,), chấm phẩy (;), tab (\t)
        $firstLine = $lines[0];
        $separator = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $separator = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $separator = "\t";
        }

        $headers = str_getcsv($lines[0], $separator);
        $headers = array_map(fn ($h) => trim((string) $h), $headers);

        $rows = [];
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '') {
                continue;
            }

            $values = str_getcsv($line, $separator);
            $rowObj = [];
            foreach ($headers as $index => $header) {
                $rowObj[$header] = isset($values[$index]) ? trim((string) $values[$index]) : '';
            }
            $rows[] = $rowObj;
        }

        $autoMapping = $this->autoSuggestMapping($headers);

        return ImportBatch::create([
            'batch_id' => (string) Str::uuid(),
            'wedding_id' => $weddingId,
            'created_by' => $userId,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => null,
            'status' => 'uploaded',
            'total_rows' => count($rows),
            'column_mapping' => $autoMapping,
            'raw_preview_data' => array_slice($rows, 0, 500),
        ]);
    }

    /**
     * Tự động gợi ý khớp cột nguồn với các trường đích của khách mời
     *
     * @param  array<string>  $headers
     * @return array<string, string>
     */
    public function autoSuggestMapping(array $headers): array
    {
        $mapping = [];

        foreach ($headers as $header) {
            $lower = mb_strtolower(trim($header), 'UTF-8');

            if (preg_match('/(họ và tên|họ tên|tên khách|tên|name|full_name)/u', $lower)) {
                $mapping[$header] = 'name';
            } elseif (preg_match('/(số điện thoại|điện thoại|sđt|phone|mobile|tel)/u', $lower)) {
                $mapping[$header] = 'phone';
            } elseif (preg_match('/(danh xưng|xưng hô|anh\/chị|salutation)/u', $lower)) {
                $mapping[$header] = 'salutation';
            } elseif (preg_match('/(nhóm|group|bên|quan hệ|phân loại)/u', $lower)) {
                $mapping[$header] = 'group_name';
            } elseif (preg_match('/(số người|số lượng|suất mời|suất|đi cùng|attendees|count)/u', $lower)) {
                $mapping[$header] = 'expected_attendees';
            } elseif (preg_match('/(bàn|bàn tiệc|table)/u', $lower)) {
                $mapping[$header] = 'table_name';
            } else {
                $mapping[$header] = 'ignore';
            }
        }

        return $mapping;
    }

    /**
     * Thẩm tra dữ liệu trước khi nhập (Dry Run) và kiểm soát định dạng số điện thoại GUE-01
     *
     * @param  array<string, string>  $columnMapping
     * @return array{summary: array<string, int>, preview_rows: array<int, array<string, mixed>>}
     */
    public function previewAndValidate(ImportBatch $batch, array $columnMapping, string $duplicateMode = 'skip'): array
    {
        $rawRows = $batch->raw_preview_data ?? [];

        $existingGuests = Guest::where('wedding_id', $batch->wedding_id)
            ->get(['name', 'phone'])
            ->map(fn ($g) => mb_strtolower($g->name, 'UTF-8').'|'.$g->phone)
            ->flip()
            ->toArray();

        $processedRows = [];
        $validCount = 0;
        $duplicateCount = 0;
        $invalidCount = 0;
        $seenInBatch = [];

        foreach ($rawRows as $index => $raw) {
            $rowNum = $index + 2; // Tính từ dòng 2 (sau header)
            $item = [
                'name' => '',
                'phone' => null,
                'salutation' => 'Bạn',
                'group_name' => 'Khách Mời',
                'expected_attendees' => 1,
                'table_name' => null,
            ];

            foreach ($columnMapping as $sourceCol => $targetField) {
                if ($targetField !== 'ignore' && isset($raw[$sourceCol])) {
                    $item[$targetField] = trim((string) $raw[$sourceCol]);
                }
            }

            // GUE-01: Chuẩn hóa số điện thoại Việt Nam chống mất số 0 đầu
            if (! empty($item['phone'])) {
                $item['phone'] = $this->formatVietnamesePhoneNumber($item['phone']);
            }

            // Validate họ tên
            $errors = [];
            if (empty($item['name'])) {
                $errors[] = 'Họ tên khách mời không được để trống.';
            }

            // Kiểm tra trùng lặp
            $identifier = mb_strtolower($item['name'], 'UTF-8').'|'.($item['phone'] ?? '');
            $isDuplicate = false;

            if (isset($existingGuests[$identifier])) {
                $isDuplicate = true;
            } elseif (isset($seenInBatch[$identifier])) {
                $isDuplicate = true;
                $errors[] = 'Trùng lặp với một dòng khác trong cùng tệp.';
            } else {
                $seenInBatch[$identifier] = true;
            }

            if (! empty($errors)) {
                $status = 'invalid';
                $invalidCount++;
            } elseif ($isDuplicate) {
                $status = 'duplicate';
                $duplicateCount++;
            } else {
                $status = 'valid';
                $validCount++;
            }

            $processedRows[] = [
                'row_number' => $rowNum,
                'data' => $item,
                'status' => $status,
                'errors' => $errors,
            ];
        }

        // Lưu cấu hình vào batch
        $batch->update([
            'column_mapping' => $columnMapping,
            'duplicate_mode' => $duplicateMode,
            'status' => 'mapped',
        ]);

        return [
            'summary' => [
                'total' => count($rawRows),
                'valid' => $validCount,
                'duplicate' => $duplicateCount,
                'invalid' => $invalidCount,
            ],
            'preview_rows' => $processedRows,
        ];
    }

    /**
     * Thực thi nhập dữ liệu vào cơ sở dữ liệu với Idempotent Retry (GUE-02)
     *
     * @return array<string, mixed>
     */
    public function executeImport(ImportBatch $batch): array
    {
        $validationResult = $this->previewAndValidate(
            $batch,
            $batch->column_mapping ?? [],
            $batch->duplicate_mode ?? 'skip'
        );

        $rows = $validationResult['preview_rows'];
        $importedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $failedRows = [];

        DB::transaction(function () use ($batch, $rows, &$importedCount, &$updatedCount, &$skippedCount, &$failedCount, &$failedRows) {
            foreach ($rows as $row) {
                if ($row['status'] === 'invalid') {
                    $failedCount++;
                    $failedRows[] = [
                        'row' => $row['row_number'],
                        'name' => $row['data']['name'] ?? '',
                        'phone' => $row['data']['phone'] ?? '',
                        'reason' => implode('; ', $row['errors']),
                    ];

                    continue;
                }

                $data = $row['data'];
                $existing = Guest::where('wedding_id', $batch->wedding_id)
                    ->where('name', $data['name'])
                    ->where(function ($q) use ($data) {
                        if (! empty($data['phone'])) {
                            $q->where('phone', $data['phone']);
                        }
                    })
                    ->first();

                // GUE-02: Idempotent - Nếu đã tồn tại
                if ($existing) {
                    if ($batch->duplicate_mode === 'update') {
                        $existing->update([
                            'salutation' => $data['salutation'] ?: $existing->salutation,
                            'group_name' => $data['group_name'] ?: $existing->group_name,
                            'expected_attendees' => (int) ($data['expected_attendees'] ?: $existing->expected_attendees),
                            'max_attendees' => (int) ($data['expected_attendees'] ?: $existing->max_attendees),
                            'table_name' => $data['table_name'] ?: $existing->table_name,
                        ]);
                        $updatedCount++;
                    } else {
                        // Mặc định skip
                        $skippedCount++;
                    }

                    continue;
                }

                // Tạo khách mời mới với token ngẫu nhiên bảo mật cao
                Guest::create([
                    'wedding_id' => $batch->wedding_id,
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'salutation' => $data['salutation'] ?: 'Bạn',
                    'group_name' => $data['group_name'] ?: 'Khách Mời',
                    'expected_attendees' => (int) ($data['expected_attendees'] ?: 1),
                    'max_attendees' => (int) ($data['expected_attendees'] ?: 1),
                    'table_name' => $data['table_name'] ?: null,
                    'guest_token' => Str::random(32),
                ]);

                $importedCount++;
            }

            $finalStatus = $failedCount > 0 ? 'partial_failed' : 'completed';

            $batch->update([
                'status' => $finalStatus,
                'imported_count' => $importedCount,
                'updated_count' => $updatedCount,
                'skipped_count' => $skippedCount,
                'failed_count' => $failedCount,
                'error_summary' => $failedRows,
            ]);
        });

        return [
            'batch_id' => $batch->batch_id,
            'status' => $batch->status,
            'imported' => $importedCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'failed' => $failedCount,
            'failed_rows' => $failedRows,
        ];
    }

    /**
     * GUE-01: Chuẩn hóa số điện thoại Việt Nam, bảo đảm luôn có số 0 ở đầu
     */
    public function formatVietnamesePhoneNumber(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        // Loại bỏ mọi ký tự không phải số hoặc dấu cộng
        $cleaned = preg_replace('/[^\d+]/', '', trim($phone));

        // Nếu bắt đầu bằng +84 hoặc 84 -> chuyển thành 0
        if (str_starts_with($cleaned, '+84')) {
            $cleaned = '0'.substr($cleaned, 3);
        } elseif (str_starts_with($cleaned, '84') && strlen($cleaned) >= 11) {
            $cleaned = '0'.substr($cleaned, 2);
        }

        // GUE-01: Trường hợp Excel làm rớt số 0 đầu khiến số chỉ còn 9 chữ số (ví dụ: 912345678)
        if (strlen($cleaned) === 9 && ! str_starts_with($cleaned, '0')) {
            $cleaned = '0'.$cleaned;
        }

        return $cleaned;
    }

    /**
     * Xuất danh sách dòng lỗi sang định dạng CSV có UTF-8 BOM
     */
    public function exportFailedRowsCsv(ImportBatch $batch): string
    {
        $failedRows = $batch->error_summary ?? [];
        $bom = "\xEF\xBB\xBF";
        $csv = $bom."Dòng,Họ Tên,Số Điện Thoại,Lý Do Lỗi\n";

        foreach ($failedRows as $item) {
            $csv .= sprintf(
                "\"%s\",\"%s\",\"%s\",\"%s\"\n",
                str_replace('"', '""', (string) ($item['row'] ?? '')),
                str_replace('"', '""', (string) ($item['name'] ?? '')),
                str_replace('"', '""', (string) ($item['phone'] ?? '')),
                str_replace('"', '""', (string) ($item['reason'] ?? ''))
            );
        }

        return $csv;
    }
}
