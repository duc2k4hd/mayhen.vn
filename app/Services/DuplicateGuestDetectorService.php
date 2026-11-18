<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestDelivery;
use App\Models\GuestPhoto;
use App\Models\GuestResponse;
use App\Models\GuestResponseLog;
use App\Models\Wedding;
use App\Models\WeddingTable;
use App\Models\WeddingWish;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DuplicateGuestDetectorService
{
    /**
     * Danh sách các tiền tố danh xưng thông dụng tiếng Việt cần loại bỏ khi so khớp tên.
     *
     * @var array<int, string>
     */
    protected const SALUTATIONS_TO_STRIP = [
        'anh', 'chị', 'chi', 'em', 'bạn', 'ban', 'bác', 'bac',
        'chú', 'chu', 'cô', 'co', 'dì', 'di', 'cụ', 'cu',
        'ông', 'ong', 'bà', 'ba', 'thầy', 'thay', 'cô giáo',
    ];

    /**
     * Quét và phát hiện các cặp khách mời nghi vấn trùng lặp.
     *
     * @return array{
     *     kpis: array{
     *         total_guests: int,
     *         duplicate_pairs_count: int,
     *         critical_count: int,
     *         potential_saved_invitations: int,
     *         clean_rate: float
     *     },
     *     pairs: array<int, array{
     *         pair_id: string,
     *         match_type: string,
     *         confidence: int,
     *         severity: string,
     *         reasons: array<int, string>,
     *         guest_a: array<string, mixed>,
     *         guest_b: array<string, mixed>
     *     }>
     * }
     */
    public function scan(Wedding $wedding): array
    {
        $guests = $wedding->guests()->orderBy('id')->get();
        $totalGuests = $guests->count();

        if ($totalGuests < 2) {
            return [
                'kpis' => [
                    'total_guests' => $totalGuests,
                    'duplicate_pairs_count' => 0,
                    'critical_count' => 0,
                    'potential_saved_invitations' => 0,
                    'clean_rate' => 100.0,
                ],
                'pairs' => [],
            ];
        }

        $pairs = [];
        $duplicateGuestIds = [];
        $criticalCount = 0;

        // Chuẩn bị mảng chuẩn hóa để so sánh nhanh
        $normalizedGuests = [];
        foreach ($guests as $g) {
            $cleanPhone = preg_replace('/[^0-9]/', '', (string) $g->phone);
            $normName = $this->normalizeVietnameseName($g->name);
            $normalizedGuests[$g->id] = [
                'model' => $g,
                'phone' => (strlen($cleanPhone) >= 9) ? $cleanPhone : null,
                'norm_name' => $normName,
                'group' => mb_strtolower(trim((string) $g->group_name)),
            ];
        }

        $guestIds = array_keys($normalizedGuests);
        $count = count($guestIds);

        for ($i = 0; $i < $count - 1; $i++) {
            $idA = $guestIds[$i];
            $itemA = $normalizedGuests[$idA];

            for ($j = $i + 1; $j < $count; $j++) {
                $idB = $guestIds[$j];
                $itemB = $normalizedGuests[$idB];

                $isDuplicate = false;
                $matchType = 'none';
                $confidence = 0;
                $severity = 'info';
                $reasons = [];

                // 1. So khớp chính xác Số Điện Thoại
                if (! empty($itemA['phone']) && ! empty($itemB['phone']) && $itemA['phone'] === $itemB['phone']) {
                    $isDuplicate = true;
                    $matchType = 'phone_match';
                    $confidence = 100;
                    $severity = 'critical';
                    $reasons[] = 'Trùng chính xác số điện thoại liên lạc: '.$itemA['model']->phone;
                    $criticalCount++;
                }

                // 2. So khớp Tên chuẩn hóa (bỏ dấu và bỏ danh xưng)
                if (! $isDuplicate && ! empty($itemA['norm_name']) && ! empty($itemB['norm_name'])) {
                    if ($itemA['norm_name'] === $itemB['norm_name']) {
                        $isDuplicate = true;
                        $matchType = 'exact_name';
                        $confidence = 90;
                        $severity = 'warning';
                        $reasons[] = 'Trùng khớp họ và tên: "'.$itemA['model']->name.'" và "'.$itemB['model']->name.'"';

                        if (! empty($itemA['group']) && ! empty($itemB['group']) && $itemA['group'] === $itemB['group']) {
                            $confidence = 95;
                            $reasons[] = 'Nằm chung phân nhóm: '.$itemA['model']->group_name;
                        }
                    } else {
                        // 3. So khớp tương đồng Levenshtein / similar_text (độ tương đồng >= 82%)
                        similar_text($itemA['norm_name'], $itemB['norm_name'], $similarityPercent);
                        if ($similarityPercent >= 82 && mb_strlen($itemA['norm_name']) >= 5 && mb_strlen($itemB['norm_name']) >= 5) {
                            $isDuplicate = true;
                            $matchType = 'fuzzy_name';
                            $confidence = (int) round($similarityPercent);
                            $severity = 'warning';
                            $reasons[] = 'Độ tương đồng tên rất cao ('.$confidence.'%): "'.$itemA['model']->name.'" ~ "'.$itemB['model']->name.'"';

                            if (! empty($itemA['group']) && ! empty($itemB['group']) && $itemA['group'] === $itemB['group']) {
                                $reasons[] = 'Cùng nằm trong nhóm: '.$itemA['model']->group_name;
                            }
                        }
                    }
                }

                if ($isDuplicate) {
                    $pairId = "pair_{$idA}_{$idB}";
                    $duplicateGuestIds[$idA] = true;
                    $duplicateGuestIds[$idB] = true;

                    $pairs[] = [
                        'pair_id' => $pairId,
                        'match_type' => $matchType,
                        'confidence' => $confidence,
                        'severity' => $severity,
                        'reasons' => $reasons,
                        'guest_a' => $this->formatGuestData($itemA['model']),
                        'guest_b' => $this->formatGuestData($itemB['model']),
                    ];
                }
            }
        }

        $duplicatePairsCount = count($pairs);
        $savedCount = count($duplicateGuestIds) > 0 ? (int) floor(count($duplicateGuestIds) / 2) : 0;
        $cleanRate = $totalGuests > 0 ? round(max(0, 100 - (count($duplicateGuestIds) / $totalGuests) * 100), 1) : 100.0;

        return [
            'kpis' => [
                'total_guests' => $totalGuests,
                'duplicate_pairs_count' => $duplicatePairsCount,
                'critical_count' => $criticalCount,
                'potential_saved_invitations' => $savedCount,
                'clean_rate' => $cleanRate,
            ],
            'pairs' => $pairs,
        ];
    }

    /**
     * Thực hiện gộp 2 khách mời thành 1 bản ghi duy nhất.
     *
     * @param  array{
     *     name?: string,
     *     salutation?: string,
     *     phone?: string,
     *     group_name?: string,
     *     table_id?: int|null,
     *     combine_attendees?: bool
     * }  $options
     */
    public function merge(Wedding $wedding, int $primaryGuestId, int $secondaryGuestId, array $options = []): Guest
    {
        return DB::transaction(function () use ($wedding, $primaryGuestId, $secondaryGuestId, $options) {
            $primary = Guest::where('wedding_id', $wedding->id)->findOrFail($primaryGuestId);
            $secondary = Guest::where('wedding_id', $wedding->id)->findOrFail($secondaryGuestId);

            // 1. Xác định thông tin chuẩn được giữ lại (strip_tags chống XSS & kiểm tra table_id đúng đám cưới)
            $finalName = ! empty($options['name']) ? strip_tags(trim((string) $options['name'])) : ($primary->name ?: $secondary->name);
            $finalSalutation = ! empty($options['salutation']) ? strip_tags(trim((string) $options['salutation'])) : ($primary->salutation ?: $secondary->salutation);
            $finalPhone = ! empty($options['phone']) ? strip_tags(trim((string) $options['phone'])) : ($primary->phone ?: $secondary->phone);
            $finalGroup = ! empty($options['group_name']) ? strip_tags(trim((string) $options['group_name'])) : ($primary->group_name ?: $secondary->group_name);

            $candidateTableId = array_key_exists('table_id', $options)
                ? ($options['table_id'] ? (int) $options['table_id'] : null)
                : ($primary->table_id ?: $secondary->table_id);

            $finalTableId = null;
            $finalTableName = null;
            if ($candidateTableId !== null) {
                $validTable = WeddingTable::where('wedding_id', $wedding->id)->find($candidateTableId);
                if ($validTable) {
                    $finalTableId = $validTable->id;
                    $finalTableName = $validTable->name;
                }
            } elseif (! array_key_exists('table_id', $options)) {
                $finalTableName = $primary->table_name ?: $secondary->table_name;
            }

            $finalAttendees = (int) ($primary->expected_attendees ?: 1);
            if (! empty($options['combine_attendees'])) {
                $finalAttendees = (int) $primary->expected_attendees + (int) $secondary->expected_attendees;
            }

            // Bảo toàn trạng thái điểm danh nếu bản ghi phụ đã điểm danh
            $checkedInAt = $primary->checked_in_at ?: $secondary->checked_in_at;
            $checkedInAttendees = $primary->checked_in_attendees;
            if ($checkedInAttendees === null && $secondary->checked_in_attendees !== null) {
                $checkedInAttendees = $secondary->checked_in_attendees;
            } elseif ($primary->checked_in_attendees !== null && $secondary->checked_in_attendees !== null && ! empty($options['combine_attendees'])) {
                $checkedInAttendees = (int) $primary->checked_in_attendees + (int) $secondary->checked_in_attendees;
            }

            // Lưu token của bản ghi phụ vào revoked_tokens để link cũ trả về 410 lịch sự
            $revokedTokens = is_array($primary->revoked_tokens) ? $primary->revoked_tokens : [];
            if (! empty($secondary->guest_token)) {
                $revokedTokens[] = $secondary->guest_token;
            }
            if (is_array($secondary->revoked_tokens)) {
                $revokedTokens = array_merge($revokedTokens, $secondary->revoked_tokens);
            }
            $revokedTokens = array_values(array_unique(array_filter($revokedTokens)));

            // 2. Chuyển giao các quan hệ dữ liệu liên quan và khử trùng GuestResponse theo event_id
            GuestDelivery::where('guest_id', $secondary->id)->update(['guest_id' => $primary->id]);
            GuestResponseLog::where('guest_id', $secondary->id)->update(['guest_id' => $primary->id]);
            WeddingWish::where('guest_id', $secondary->id)->update(['guest_id' => $primary->id]);
            GuestPhoto::where('guest_id', $secondary->id)->update(['guest_id' => $primary->id]);

            $secondaryResponses = GuestResponse::where('guest_id', $secondary->id)->get();
            foreach ($secondaryResponses as $secResp) {
                $existingPrimaryQuery = GuestResponse::where('guest_id', $primary->id);
                if ($secResp->event_id !== null) {
                    $existingPrimaryQuery->where('event_id', $secResp->event_id);
                } else {
                    $existingPrimaryQuery->whereNull('event_id');
                }
                $existingPrimaryResp = $existingPrimaryQuery->first();

                if ($existingPrimaryResp) {
                    // Giữ bản ghi có số suất xác nhận cao hơn hoặc bản ghi attending thay cho declined
                    $secPeople = (int) $secResp->adults_count + (int) $secResp->children_count;
                    $priPeople = (int) $existingPrimaryResp->adults_count + (int) $existingPrimaryResp->children_count;
                    if ($secResp->attending_status === 'attending' && ($existingPrimaryResp->attending_status !== 'attending' || $secPeople > $priPeople)) {
                        $existingPrimaryResp->update([
                            'attending_status' => 'attending',
                            'adults_count' => $secResp->adults_count,
                            'children_count' => $secResp->children_count,
                            'dietary_requirements' => $existingPrimaryResp->dietary_requirements ?: $secResp->dietary_requirements,
                            'wishes' => $existingPrimaryResp->wishes ?: $secResp->wishes,
                        ]);
                    }
                    GuestResponseLog::where('guest_response_id', $secResp->id)
                        ->update(['guest_response_id' => $existingPrimaryResp->id]);
                    $secResp->delete();
                } else {
                    $secResp->update(['guest_id' => $primary->id]);
                }
            }

            // Cập nhật khách chính
            $primary->update([
                'name' => $finalName,
                'salutation' => $finalSalutation,
                'phone' => $finalPhone,
                'group_name' => $finalGroup,
                'table_id' => $finalTableId,
                'table_name' => $finalTableName,
                'expected_attendees' => min(50, max(1, $finalAttendees)),
                'is_sent' => $primary->is_sent || $secondary->is_sent,
                'opened_at' => $primary->opened_at ?: $secondary->opened_at,
                'open_count' => (int) $primary->open_count + (int) $secondary->open_count,
                'checked_in_at' => $checkedInAt,
                'checked_in_attendees' => $checkedInAttendees,
                'revoked_tokens' => $revokedTokens,
            ]);

            // 3. Xóa khách phụ bị gộp
            $secondary->delete();

            return $primary->fresh();
        });
    }

    /**
     * Xuất danh sách các cặp nghi vấn trùng lặp ra file CSV UTF-8 BOM (chống Formula Injection).
     */
    public function exportCsv(Wedding $wedding): string
    {
        $scanResult = $this->scan($wedding);
        $pairs = $scanResult['pairs'];

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, [
            'Mã Cặp', 'Mức Độ', 'Độ Tin Cậy', 'Lý Do Trùng Lặp',
            'Khách A (ID)', 'Khách A (Tên)', 'Khách A (Xưng Hô)', 'Khách A (SĐT)', 'Khách A (Nhóm)', 'Khách A (Bàn)',
            'Khách B (ID)', 'Khách B (Tên)', 'Khách B (Xưng Hô)', 'Khách B (SĐT)', 'Khách B (Nhóm)', 'Khách B (Bàn)',
        ]);

        foreach ($pairs as $p) {
            $a = $p['guest_a'];
            $b = $p['guest_b'];
            $reasonsStr = implode(' | ', $p['reasons']);

            fputcsv($stream, CsvSanitizerService::row([
                $p['pair_id'],
                $p['severity'] === 'critical' ? '🔴 Nghiêm trọng (Trùng SĐT)' : '🟡 Cảnh báo (Trùng tên)',
                $p['confidence'].'%',
                $reasonsStr,
                $a['id'],
                $a['name'],
                (string) $a['salutation'],
                (string) $a['phone'],
                (string) $a['group_name'],
                (string) $a['table_name'],
                $b['id'],
                $b['name'],
                (string) $b['salutation'],
                (string) $b['phone'],
                (string) $b['group_name'],
                (string) $b['table_name'],
            ]));
        }

        rewind($stream);
        $output = stream_get_contents($stream);
        fclose($stream);

        return (string) $output;
    }

    /**
     * Chuẩn hóa tên tiếng Việt: bỏ dấu thanh và bỏ các tiền tố xưng hô để so khớp.
     */
    protected function normalizeVietnameseName(string $name): string
    {
        $ascii = Str::ascii(trim($name));
        $lower = mb_strtolower($ascii);

        // Tách các từ
        $words = preg_split('/\s+/', $lower, -1, PREG_SPLIT_NO_EMPTY);
        if (empty($words)) {
            return '';
        }

        // Bỏ từ đầu tiên nếu là danh xưng (VD: "Anh Nam" -> "Nam")
        if (in_array($words[0], self::SALUTATIONS_TO_STRIP, true) && count($words) > 1) {
            array_shift($words);
        }

        return implode(' ', $words);
    }

    /**
     * Định dạng thông tin khách để hiển thị và trả về API.
     *
     * @return array<string, mixed>
     */
    protected function formatGuestData(Guest $guest): array
    {
        return [
            'id' => $guest->id,
            'name' => $guest->name,
            'salutation' => $guest->salutation,
            'phone' => $guest->phone,
            'group_name' => $guest->group_name,
            'table_id' => $guest->table_id,
            'table_name' => $guest->table_name,
            'expected_attendees' => $guest->expected_attendees,
            'is_sent' => (bool) $guest->is_sent,
        ];
    }
}
