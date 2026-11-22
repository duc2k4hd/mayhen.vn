<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Wedding;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Deep module owning guest roster CSV I/O:
 * BOM/delimiter sniffing, header auto-mapping, phone normalization,
 * transactional skip/update deduplication, and sanitized UTF-8 BOM Excel export.
 */
class GuestRosterCsvService
{
    /**
     * Import guest rows from raw CSV text into a wedding roster inside a transaction.
     *
     * @param  array{
     *     duplicate_mode?: string,
     *     default_salutation?: string,
     *     default_group?: string
     * }  $options
     * @return array{created: int, updated: int, skipped: int, message: string}
     */
    public function import(Wedding $wedding, string $rawCsv, array $options = []): array
    {
        if (trim($rawCsv) === '') {
            throw new InvalidArgumentException('Tệp CSV tải lên bị rỗng.');
        }

        $content = str_starts_with($rawCsv, "\xEF\xBB\xBF") ? substr($rawCsv, 3) : $rawCsv;
        $trimmedContent = trim($content);
        if ($trimmedContent === '') {
            throw new InvalidArgumentException('Tệp CSV không chứa dòng dữ liệu nào.');
        }

        $duplicateMode = ($options['duplicate_mode'] ?? 'skip') === 'update' ? 'update' : 'skip';
        $defaultSalutation = strip_tags(trim((string) ($options['default_salutation'] ?? ''))) ?: 'Thân mời';
        $defaultGroup = strip_tags(trim((string) ($options['default_group'] ?? ''))) ?: 'Bạn Bè';

        $delimiter = $this->detectDelimiter($trimmedContent);
        $parsedRows = $this->parseRows($trimmedContent, $delimiter);

        if ($parsedRows === []) {
            throw new InvalidArgumentException('Không thể đọc dữ liệu từ tệp CSV.');
        }

        [$colMap, $startIndex] = $this->detectColumnMap($parsedRows[0]);

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use (
            $parsedRows, $startIndex, $wedding, $colMap,
            $defaultSalutation, $defaultGroup, $duplicateMode,
            &$createdCount, &$updatedCount, &$skippedCount
        ): void {
            $totalRows = count($parsedRows);
            for ($i = $startIndex; $i < $totalRows; $i++) {
                $row = $parsedRows[$i];
                $nameCol = $colMap['name'];
                $name = isset($row[$nameCol]) ? strip_tags(trim((string) $row[$nameCol])) : '';
                if ($name === '') {
                    continue;
                }

                if (ContentModerationService::containsProfanity($name)) {
                    $skippedCount++;

                    continue;
                }

                $salutation = ($colMap['salutation'] !== null && isset($row[$colMap['salutation']]) && trim((string) $row[$colMap['salutation']]) !== '')
                    ? strip_tags(trim((string) $row[$colMap['salutation']]))
                    : $defaultSalutation;

                $group = ($colMap['group'] !== null && isset($row[$colMap['group']]) && trim((string) $row[$colMap['group']]) !== '')
                    ? strip_tags(trim((string) $row[$colMap['group']]))
                    : $defaultGroup;

                $attendees = 2;
                if ($colMap['attendees'] !== null && isset($row[$colMap['attendees']])) {
                    $val = preg_replace('/[^0-9]/', '', trim((string) $row[$colMap['attendees']]));
                    if ($val !== '' && is_numeric($val)) {
                        $attendees = min(50, max(1, (int) $val));
                    }
                }

                $phone = null;
                if ($colMap['phone'] !== null && isset($row[$colMap['phone']])) {
                    $cleanedPhone = preg_replace('/[^0-9+]/', '', trim((string) $row[$colMap['phone']]));
                    if (str_starts_with($cleanedPhone, '+84')) {
                        $cleanedPhone = '0'.substr($cleanedPhone, 3);
                    }
                    if ($cleanedPhone !== '') {
                        $phone = Str::limit($cleanedPhone, 20, '');
                    }
                }

                $table = ($colMap['table'] !== null && isset($row[$colMap['table']])) ? strip_tags(trim((string) $row[$colMap['table']])) : null;
                $tableClean = $table ? Str::limit($table, 100, '') : null;
                $tableId = null;
                if (! empty($tableClean)) {
                    $matchedTable = $wedding->tables()->where('name', $tableClean)->first();
                    $tableId = $matchedTable?->id;
                }

                $notes = ($colMap['notes'] !== null && isset($row[$colMap['notes']])) ? strip_tags(trim((string) $row[$colMap['notes']])) : null;
                $notesClean = $notes ? Str::limit($notes, 255, '') : null;

                $existingGuest = null;
                if ($phone) {
                    $existingGuest = Guest::where('wedding_id', $wedding->id)->where('phone', $phone)->first();
                }
                if (! $existingGuest) {
                    $existingGuest = Guest::where('wedding_id', $wedding->id)
                        ->where('name', $name)
                        ->where('group_name', $group)
                        ->first();
                }

                if ($existingGuest) {
                    if ($duplicateMode === 'skip') {
                        $skippedCount++;

                        continue;
                    }

                    $existingGuest->update([
                        'name' => Str::limit($name, 190, ''),
                        'salutation' => Str::limit($salutation, 50, ''),
                        'group_name' => Str::limit($group, 50, ''),
                        'expected_attendees' => $attendees,
                        'table_name' => $tableClean ?: $existingGuest->table_name,
                        'table_id' => $tableId ?: $existingGuest->table_id,
                        'check_in_note' => $notesClean ?: $existingGuest->check_in_note,
                    ]);
                    $updatedCount++;

                    continue;
                }

                Guest::create([
                    'wedding_id' => $wedding->id,
                    'name' => Str::limit($name, 190, ''),
                    'salutation' => Str::limit($salutation, 50, ''),
                    'group_name' => Str::limit($group, 50, ''),
                    'table_name' => $tableClean,
                    'table_id' => $tableId,
                    'phone' => $phone,
                    'expected_attendees' => $attendees,
                    'guest_token' => Str::random(32),
                    'check_in_note' => $notesClean,
                ]);
                $createdCount++;
            }
        });

        AuditLogger::log(
            'GUEST_CSV_IMPORTED',
            'wedding',
            $wedding->id,
            "Nhập danh sách khách mời qua tệp CSV: tạo mới {$createdCount}, cập nhật {$updatedCount}, bỏ qua {$skippedCount}.",
            ['created' => $createdCount, 'updated' => $updatedCount, 'skipped' => $skippedCount, 'mode' => $duplicateMode]
        );

        $msg = "Đã xử lý tệp CSV thành công: Thêm mới {$createdCount} khách mời";
        if ($updatedCount > 0) {
            $msg .= ", cập nhật {$updatedCount} khách";
        }
        if ($skippedCount > 0) {
            $msg .= ", bỏ qua {$skippedCount} dòng (trùng lặp hoặc lỗi)";
        }
        $msg .= '.';

        return [
            'created' => $createdCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'message' => $msg,
        ];
    }

    /**
     * Write filtered guest roster as UTF-8 BOM CSV to any output stream.
     *
     * @param  array{q?: string|null, group?: string|null, side?: string|null, table?: string|null, status?: string|null}  $filters
     * @param  resource  $handle
     */
    public function writeExportStream(Wedding $wedding, Invitation $invitation, array $filters, $handle): void
    {
        $guestsQuery = $wedding->guests()->with('response');

        if ($search = trim((string) ($filters['q'] ?? ''))) {
            $guestsQuery->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('table_name', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['group'])) {
            $guestsQuery->where('group_name', $filters['group']);
        }

        if (! empty($filters['side'])) {
            if ($filters['side'] === 'groom') {
                $guestsQuery->where('group_name', 'like', '%Nhà Trai%');
            } elseif ($filters['side'] === 'bride') {
                $guestsQuery->where('group_name', 'like', '%Nhà Gái%');
            }
        }

        if (! empty($filters['table'])) {
            $guestsQuery->where('table_name', $filters['table']);
        }

        if (! empty($filters['status'])) {
            match ($filters['status']) {
                'attending' => $guestsQuery->whereHas('response', fn ($q) => $q->where('attending_status', 'attending')),
                'declined' => $guestsQuery->whereHas('response', fn ($q) => $q->where('attending_status', 'declined')),
                'pending' => $guestsQuery->whereDoesntHave('response'),
                'checked_in' => $guestsQuery->whereNotNull('checked_in_at'),
                'not_checked_in' => $guestsQuery->whereNull('checked_in_at'),
                default => null,
            };
        }

        $guests = $guestsQuery->orderBy('group_name')->orderBy('name')->get();

        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'STT',
            'Tên Khách Mời',
            'Cách Xưng Hô',
            'Nhóm Khách',
            'Bàn Tiệc',
            'Số Điện Thoại',
            'Số Người Dự Kiến',
            'Trạng Thái RSVP',
            'Người Lớn (RSVP)',
            'Trẻ Em (RSVP)',
            'Điểm Danh Ngày Cưới',
            'Thời Điểm Điểm Danh',
            'Yêu Cầu Ăn Uống',
            'Lời Chúc / Ghi Chú',
            'Link Thiệp Cá Nhân Hóa',
        ]);

        $index = 1;
        foreach ($guests as $g) {
            $response = $g->response;
            $rsvpStatus = 'Chưa phản hồi';
            $adults = '';
            $children = '';
            $dietary = '';
            $wishes = '';

            if ($response) {
                $rsvpStatus = match ($response->attending_status) {
                    'attending' => 'Đã xác nhận tham dự',
                    'uncertain' => 'Đang cân nhắc / Chưa chắc chắn',
                    default => 'Từ chối / Không tham gia',
                };
                $adults = $response->adults_count;
                $children = $response->children_count;
                $dietary = $response->dietary_requirements ?? '';
                $wishes = $response->wishes ?? '';
            }

            $checkInStatus = $g->checked_in_at ? 'Đã có mặt' : 'Chưa điểm danh';
            $checkInTime = $g->checked_in_at ? $g->checked_in_at->format('H:i d/m/Y') : '';
            $personalUrl = route('invitation.show', ['slug' => $invitation->slug, 'token' => $g->guest_token]);

            fputcsv($handle, CsvSanitizerService::row([
                $index++,
                $g->name,
                $g->salutation,
                $g->group_name,
                $g->table_name ?? 'Chưa xếp bàn',
                $g->phone ?? '',
                $g->expected_attendees,
                $rsvpStatus,
                $adults,
                $children,
                $checkInStatus,
                $checkInTime,
                $dietary,
                $wishes,
                $personalUrl,
            ]));
        }
    }

    /**
     * Export filtered guest roster directly to a CSV string (ideal for testing).
     *
     * @param  array{q?: string|null, group?: string|null, side?: string|null, table?: string|null, status?: string|null}  $filters
     */
    public function exportToString(Wedding $wedding, Invitation $invitation, array $filters = []): string
    {
        $stream = fopen('php://temp', 'r+');
        $this->writeExportStream($wedding, $invitation, $filters, $stream);
        rewind($stream);
        $csv = (string) stream_get_contents($stream);
        fclose($stream);

        return $csv;
    }

    protected function detectDelimiter(string $trimmedContent): string
    {
        $firstLineBreak = strcspn($trimmedContent, "\r\n");
        $firstLine = substr($trimmedContent, 0, $firstLineBreak);
        $commaCount = substr_count($firstLine, ',');
        $semicolonCount = substr_count($firstLine, ';');
        $tabCount = substr_count($firstLine, "\t");

        if ($semicolonCount > $commaCount && $semicolonCount > $tabCount) {
            return ';';
        }
        if ($tabCount > $commaCount && $tabCount > $semicolonCount) {
            return "\t";
        }

        return ',';
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    protected function parseRows(string $trimmedContent, string $delimiter): array
    {
        $parsedRows = [];
        $csvStream = fopen('php://temp', 'r+');
        fwrite($csvStream, $trimmedContent);
        rewind($csvStream);
        while (($parsed = fgetcsv($csvStream, 0, $delimiter)) !== false) {
            if ($parsed === [null] || empty(array_filter($parsed, fn ($v): bool => trim((string) $v) !== ''))) {
                continue;
            }
            $parsedRows[] = $parsed;
        }
        fclose($csvStream);

        return $parsedRows;
    }

    /**
     * @param  array<int, string|null>  $headerRow
     * @return array{0: array{name: int, salutation: int|null, group: int|null, attendees: int|null, phone: int|null, table: int|null, notes: int|null}, 1: int}
     */
    protected function detectColumnMap(array $headerRow): array
    {
        $headerRowLower = array_map(fn ($col): string => mb_strtolower(trim((string) $col), 'UTF-8'), $headerRow);

        $map = [
            'name' => null,
            'salutation' => null,
            'group' => null,
            'attendees' => null,
            'phone' => null,
            'table' => null,
            'notes' => null,
        ];

        $hasHeader = false;
        foreach ($headerRowLower as $idx => $colName) {
            $normalized = Str::slug($colName, ' ');
            if ($map['table'] === null && preg_match('/\b(ban tiec|so ban|vi tri ban|ban|table)\b/i', $normalized)) {
                $map['table'] = $idx;
                $hasHeader = true;
            } elseif ($map['salutation'] === null && preg_match('/\b(danh xung|xung ho|salutation)\b/i', $normalized)) {
                $map['salutation'] = $idx;
                $hasHeader = true;
            } elseif ($map['group'] === null && preg_match('/\b(nhom khach|phan nhom|nhom|group|phan loai)\b/i', $normalized)) {
                $map['group'] = $idx;
                $hasHeader = true;
            } elseif ($map['attendees'] === null && preg_match('/\b(so luong|so nguoi|suat moi|so suat|headcount|attendees)\b/i', $normalized)) {
                $map['attendees'] = $idx;
                $hasHeader = true;
            } elseif ($map['phone'] === null && preg_match('/\b(dien thoai|sdt|phone|mobile|lien he)\b/i', $normalized)) {
                $map['phone'] = $idx;
                $hasHeader = true;
            } elseif ($map['notes'] === null && preg_match('/\b(ghi chu|note|notes|luu y)\b/i', $normalized)) {
                $map['notes'] = $idx;
                $hasHeader = true;
            } elseif ($map['name'] === null && preg_match('/\b(ho va ten|ho ten|ten khach|khach moi|ten|name|full name|guest)\b/i', $normalized)) {
                $map['name'] = $idx;
                $hasHeader = true;
            }
        }

        if ($hasHeader && $map['name'] !== null) {
            return [$map, 1];
        }

        return [
            [
                'name' => 0,
                'salutation' => 1,
                'group' => 2,
                'attendees' => 3,
                'phone' => 4,
                'table' => 5,
                'notes' => 6,
            ],
            0,
        ];
    }
}
