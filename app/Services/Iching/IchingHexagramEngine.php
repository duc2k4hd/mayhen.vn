<?php

declare(strict_types=1);

namespace App\Services\Iching;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use RuntimeException;

class IchingHexagramEngine
{
    /**
     * Tiên Thiên Bát Quái chuẩn (1..8) — bits biểu diễn 3 hào từ DƯỚI LÊN TRÊN (Hào 1 -> Hào 3).
     *
     * @var array<string, array{key: string, number: int, name: string, hanzi: string, symbol: string, nature: string, element: string, bits: string}>
     */
    public const TRIGRAMS = [
        'can' => ['key' => 'can', 'number' => 1, 'name' => 'Càn', 'hanzi' => '乾', 'symbol' => '☰', 'nature' => 'Trời', 'element' => 'Kim', 'bits' => '111'],
        'doai' => ['key' => 'doai', 'number' => 2, 'name' => 'Đoài', 'hanzi' => '兌', 'symbol' => '☱', 'nature' => 'Đầm', 'element' => 'Kim', 'bits' => '110'],
        'ly' => ['key' => 'ly', 'number' => 3, 'name' => 'Ly', 'hanzi' => '離', 'symbol' => '☲', 'nature' => 'Lửa', 'element' => 'Hỏa', 'bits' => '101'],
        'chan' => ['key' => 'chan', 'number' => 4, 'name' => 'Chấn', 'hanzi' => '震', 'symbol' => '☳', 'nature' => 'Sấm', 'element' => 'Mộc', 'bits' => '100'],
        'ton' => ['key' => 'ton', 'number' => 5, 'name' => 'Tốn', 'hanzi' => '巽', 'symbol' => '☴', 'nature' => 'Gió', 'element' => 'Mộc', 'bits' => '011'],
        'kham' => ['key' => 'kham', 'number' => 6, 'name' => 'Khảm', 'hanzi' => '坎', 'symbol' => '☵', 'nature' => 'Nước', 'element' => 'Thủy', 'bits' => '010'],
        'can_nui' => ['key' => 'can_nui', 'number' => 7, 'name' => 'Cấn', 'hanzi' => '艮', 'symbol' => '☶', 'nature' => 'Núi', 'element' => 'Thổ', 'bits' => '001'],
        'khon' => ['key' => 'khon', 'number' => 8, 'name' => 'Khôn', 'hanzi' => '坤', 'symbol' => '☷', 'nature' => 'Đất', 'element' => 'Thổ', 'bits' => '000'],
    ];

    /**
     * 5 chủ đề hỏi việc chuẩn (có liên kết tinh tế với mùa cưới & đầu năm).
     *
     * @var array<string, array{key: string, label: string, field: string, hint: string}>
     */
    public const TOPICS = [
        'tong_quan' => ['key' => 'tong_quan', 'label' => 'Tổng Quan & Vận Trình', 'field' => 'summary', 'hint' => 'Hỏi chung về vận khí, năm mới hoặc một quyết định lớn'],
        'tinh_duyen' => ['key' => 'tinh_duyen', 'label' => 'Tình Duyên & Hôn Nhân', 'field' => 'love', 'hint' => 'Hỏi về nhân duyên, hòa hợp đôi lứa, tiến tới cưới hỏi'],
        'cong_danh' => ['key' => 'cong_danh', 'label' => 'Công Danh & Sự Nghiệp', 'field' => 'career', 'hint' => 'Hỏi về chuyển việc, thăng tiến, thi cử, khởi nghiệp'],
        'tai_loc' => ['key' => 'tai_loc', 'label' => 'Tài Lộc & Kinh Doanh', 'field' => 'wealth', 'hint' => 'Hỏi về hợp tác, buôn bán, đầu tư, dòng tiền'],
        'gia_dao' => ['key' => 'gia_dao', 'label' => 'Gia Đạo & Bình An', 'field' => 'family', 'hint' => 'Hỏi về gia đình, sức khỏe, chuyển nhà, xuất hành'],
    ];

    /**
     * Vòng tương sinh: A sinh B.
     *
     * @var array<string, string>
     */
    protected const GENERATES = [
        'Kim' => 'Thủy',
        'Thủy' => 'Mộc',
        'Mộc' => 'Hỏa',
        'Hỏa' => 'Thổ',
        'Thổ' => 'Kim',
    ];

    /**
     * Vòng tương khắc: A khắc B.
     *
     * @var array<string, string>
     */
    protected const OVERCOMES = [
        'Kim' => 'Mộc',
        'Mộc' => 'Thổ',
        'Thổ' => 'Thủy',
        'Thủy' => 'Hỏa',
        'Hỏa' => 'Kim',
    ];

    /**
     * Ứng kỳ theo Ngũ Hành vượng tướng.
     *
     * @var array<string, string>
     */
    protected const ELEMENT_TIMING = [
        'Mộc' => 'Mùa Xuân (tháng 1–2 Âm lịch), các ngày hành Mộc (Dần, Mão) hoặc khung giờ sáng sớm (3h–7h).',
        'Hỏa' => 'Mùa Hạ (tháng 4–5 Âm lịch), các ngày hành Hỏa (Tỵ, Ngọ) hoặc khung giờ trưa (9h–13h).',
        'Thổ' => 'Cuối bốn mùa (tháng 3, 6, 9, 12 Âm lịch), các ngày Thìn – Tuất – Sửu – Mùi.',
        'Kim' => 'Mùa Thu (tháng 7–8 Âm lịch), các ngày hành Kim (Thân, Dậu) hoặc khung giờ chiều (15h–19h).',
        'Thủy' => 'Mùa Đông (tháng 10–11 Âm lịch), các ngày hành Thủy (Hợi, Tý) hoặc khung giờ tối (21h–1h).',
    ];

    /** @var array<int, array<string, mixed>>|null */
    protected static ?array $byNo = null;

    /** @var array<string, array<string, mixed>>|null */
    protected static ?array $byCode = null;

    /**
     * Nạp toàn bộ 64 quẻ Chu Dịch chuẩn hóa từ JSON.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllHexagrams(): array
    {
        $this->bootCatalog();

        return self::$byNo ?? [];
    }

    /**
     * Lấy 1 quẻ theo số thứ tự Văn Vương (1..64).
     *
     * @return array<string, mixed>|null
     */
    public function getByNumber(int $no): ?array
    {
        $this->bootCatalog();

        return self::$byNo[$no] ?? null;
    }

    /**
     * Lấy 1 quẻ theo mã nhị phân 6 hào từ Hào 1 (Sơ) đến Hào 6 (Thượng).
     *
     * @return array<string, mixed>|null
     */
    public function getByCode(string $code6Bits): ?array
    {
        $this->bootCatalog();

        return self::$byCode[$code6Bits] ?? null;
    }

    /**
     * Lập lá quẻ đầy đủ từ 1 trong 4 phương thức:
     * - coins_3: Nhận 6 giá trị [6|7|8|9] hoặc tự tung 3 đồng xu 6 lần.
     * - mai_hoa_time: Mai Hoa Dịch Số theo thời điểm động tâm.
     * - mai_hoa_numbers: Mai Hoa Dịch Số theo 2 con số người dùng chọn.
     * - new_year: Gieo Quẻ Đầu Năm (xin quẻ xuân 6 hào).
     *
     * @param  array<int, int>|null  $providedLines
     * @return array<string, mixed>
     */
    public function cast(
        string $method = 'coins_3',
        ?array $providedLines = null,
        ?int $numberUpper = null,
        ?int $numberLower = null,
        ?Carbon $moment = null
    ): array {
        $moment ??= now('Asia/Ho_Chi_Minh');

        $lines = match ($method) {
            'mai_hoa_time' => $this->buildMaiHoaByTime($moment),
            'mai_hoa_numbers' => $this->buildMaiHoaByNumbers($numberUpper ?? random_int(1, 99), $numberLower ?? random_int(1, 99)),
            default => $this->normalizeOrRollCoinLines($providedLines),
        };

        return $this->analyzeLines($lines, $method, $moment);
    }

    /**
     * Phân tích dịch lý toàn diện từ mảng 6 hào [hào 1 .. hào 6] (giá trị 6, 7, 8, 9).
     *
     * @param  array<int, int>  $lines
     * @return array<string, mixed>
     */
    public function analyzeLines(array $lines, string $method = 'coins_3', ?Carbon $moment = null): array
    {
        $moment ??= now('Asia/Ho_Chi_Minh');
        $lines = array_values(array_slice($lines, 0, 6));
        if (count($lines) !== 6) {
            throw new RuntimeException('Một quẻ Kinh Dịch bắt buộc có đủ 6 hào.');
        }

        $primaryBits = '';
        $changedBits = '';
        $movingLines = [];
        $lineDetails = [];

        $lineNames = [
            1 => 'Hào Sơ (1)',
            2 => 'Hào Nhị (2)',
            3 => 'Hào Tam (3)',
            4 => 'Hào Tứ (4)',
            5 => 'Hào Ngũ (5)',
            6 => 'Hào Thượng (6)',
        ];

        foreach ($lines as $idx => $val) {
            $pos = $idx + 1;
            $val = in_array((int) $val, [6, 7, 8, 9], true) ? (int) $val : 7;
            $isYang = in_array($val, [7, 9], true);
            $isMoving = in_array($val, [6, 9], true);

            $primaryBit = $isYang ? '1' : '0';
            $changedBit = $isMoving ? ($isYang ? '0' : '1') : $primaryBit;

            $primaryBits .= $primaryBit;
            $changedBits .= $changedBit;

            if ($isMoving) {
                $movingLines[] = $pos;
            }

            $lineDetails[] = [
                'position' => $pos,
                'label' => $lineNames[$pos],
                'value' => $val,
                'type_label' => match ($val) {
                    9 => 'Lão Dương (Động)',
                    8 => 'Thiếu Âm (Tĩnh)',
                    7 => 'Thiếu Dương (Tĩnh)',
                    6 => 'Lão Âm (Động)',
                },
                'is_yang' => $isYang,
                'is_moving' => $isMoving,
                'changed_is_yang' => $changedBit === '1',
            ];
        }

        $primaryHex = $this->getByCode($primaryBits);
        if (! $primaryHex) {
            throw new RuntimeException("Không tìm thấy quẻ Chủ cho mã nhị phân {$primaryBits}.");
        }

        // Quẻ Hỗ: Hạ quái = Hào 2-3-4 (index 1,2,3), Thượng quái = Hào 3-4-5 (index 2,3,4)
        $mutualBits = $primaryBits[1].$primaryBits[2].$primaryBits[3].$primaryBits[2].$primaryBits[3].$primaryBits[4];
        $mutualHex = $this->getByCode($mutualBits) ?? $primaryHex;

        // Quẻ Biến: Chỉ có khi có ít nhất 1 hào động
        $changedHex = $movingLines !== [] ? ($this->getByCode($changedBits) ?? $primaryHex) : null;

        // Phân tích Thể – Dụng & Ngũ Hành Sinh Khắc
        $tiYong = $this->analyzeTiYong($primaryHex, $changedHex, $movingLines);

        return [
            'method' => $method,
            'cast_at' => $moment->format('H:i • d/m/Y'),
            'lines' => $lines,
            'line_details' => $lineDetails,
            'moving_lines' => $movingLines,
            'moving_rule' => $this->getChuHyReadingRule($movingLines),
            'primary' => $primaryHex,
            'mutual' => $mutualHex,
            'changed' => $changedHex,
            'ti_yong' => $tiYong,
        ];
    }

    /**
     * Gieo 3 đồng xu cổ 6 lần bằng random_int bảo mật (chuẩn xác suất 1/8, 3/8, 3/8, 1/8).
     *
     * @param  array<int, int>|null  $providedLines
     * @return array<int, int>
     */
    protected function normalizeOrRollCoinLines(?array $providedLines): array
    {
        if (is_array($providedLines) && count($providedLines) === 6) {
            $valid = [];
            foreach ($providedLines as $v) {
                $intVal = (int) $v;
                if (! in_array($intVal, [6, 7, 8, 9], true)) {
                    $valid = [];
                    break;
                }
                $valid[] = $intVal;
            }
            if (count($valid) === 6) {
                return $valid;
            }
        }

        $lines = [];
        for ($i = 0; $i < 6; $i++) {
            $sum = 0;
            for ($c = 0; $c < 3; $c++) {
                // Mặt Ngửa (Dương) = 3, Mặt Sấp (Âm) = 2
                $sum += random_int(0, 1) === 1 ? 3 : 2;
            }
            $lines[] = $sum;
        }

        return $lines;
    }

    /**
     * Lập quẻ Mai Hoa Dịch Số theo thời khắc động tâm (Thiệu Khang Tiết):
     * - Chi năm (1..12) + Tháng + Ngày -> mod 8 = Thượng Quái
     * - Chi năm + Tháng + Ngày + Chi giờ (1..12) -> mod 8 = Hạ Quái
     * - Tổng trên -> mod 6 = Hào Động (1..6)
     *
     * @return array<int, int>
     */
    protected function buildMaiHoaByTime(Carbon $moment): array
    {
        $yearBranch = (($moment->year - 4) % 12 + 12) % 12 + 1;
        $month = $moment->month;
        $day = $moment->day;
        $hourBranch = (int) floor((($moment->hour + 1) % 24) / 2) + 1;

        $upperSum = $yearBranch + $month + $day;
        $lowerSum = $upperSum + $hourBranch;

        $upperNo = $upperSum % 8 === 0 ? 8 : $upperSum % 8;
        $lowerNo = $lowerSum % 8 === 0 ? 8 : $lowerSum % 8;
        $movingPos = $lowerSum % 6 === 0 ? 6 : $lowerSum % 6;

        return $this->composeLinesFromTrigrams($upperNo, $lowerNo, $movingPos);
    }

    /**
     * Lập quẻ Mai Hoa Dịch Số theo 2 số người dùng khởi tâm nhập.
     *
     * @return array<int, int>
     */
    protected function buildMaiHoaByNumbers(int $numUpper, int $numLower): array
    {
        $numUpper = max(1, abs($numUpper));
        $numLower = max(1, abs($numLower));

        $upperNo = $numUpper % 8 === 0 ? 8 : $numUpper % 8;
        $lowerNo = $numLower % 8 === 0 ? 8 : $numLower % 8;
        $total = $numUpper + $numLower;
        $movingPos = $total % 6 === 0 ? 6 : $total % 6;

        return $this->composeLinesFromTrigrams($upperNo, $lowerNo, $movingPos);
    }

    /**
     * Chuyển (Thượng Quái 1..8, Hạ Quái 1..8, Hào Động 1..6) thành mảng 6 hào [6|7|8|9].
     *
     * @return array<int, int>
     */
    protected function composeLinesFromTrigrams(int $upperNo, int $lowerNo, int $movingPos): array
    {
        $trigramByNo = [];
        foreach (self::TRIGRAMS as $t) {
            $trigramByNo[$t['number']] = $t;
        }

        $lowerBits = $trigramByNo[$lowerNo]['bits'] ?? '111';
        $upperBits = $trigramByNo[$upperNo]['bits'] ?? '111';
        $allBits = $lowerBits.$upperBits;

        $lines = [];
        for ($i = 0; $i < 6; $i++) {
            $pos = $i + 1;
            $isYang = $allBits[$i] === '1';
            if ($pos === $movingPos) {
                $lines[] = $isYang ? 9 : 6;
            } else {
                $lines[] = $isYang ? 7 : 8;
            }
        }

        return $lines;
    }

    /**
     * Phân tích Thể – Dụng & Ngũ Hành Sinh Khắc + Ứng Kỳ chuẩn Mai Hoa Dịch Số.
     *
     * @param  array<string, mixed>  $primaryHex
     * @param  array<string, mixed>|null  $changedHex
     * @param  array<int, int>  $movingLines
     * @return array<string, mixed>
     */
    protected function analyzeTiYong(array $primaryHex, ?array $changedHex, array $movingLines): array
    {
        $hasLowerMoving = false;
        $hasUpperMoving = false;
        foreach ($movingLines as $pos) {
            if ($pos <= 3) {
                $hasLowerMoving = true;
            } else {
                $hasUpperMoving = true;
            }
        }

        // Quy tắc Thể tĩnh – Dụng động
        if ($hasLowerMoving && ! $hasUpperMoving) {
            $tiTrigram = $primaryHex['upper_trigram'];
            $yongTrigram = $primaryHex['lower_trigram'];
            $tiPosition = 'Thượng Quái (Ngoại)';
            $yongPosition = 'Hạ Quái (Nội — có Hào Động)';
        } elseif ($hasUpperMoving && ! $hasLowerMoving) {
            $tiTrigram = $primaryHex['lower_trigram'];
            $yongTrigram = $primaryHex['upper_trigram'];
            $tiPosition = 'Hạ Quái (Nội)';
            $yongPosition = 'Thượng Quái (Ngoại — có Hào Động)';
        } else {
            $tiTrigram = $primaryHex['lower_trigram'];
            $yongTrigram = $primaryHex['upper_trigram'];
            $tiPosition = 'Hạ Quái (Nội Quái — Bản thể)';
            $yongPosition = 'Thượng Quái (Ngoại Quái — Ngoại cảnh)';
        }

        $tiEl = $tiTrigram['element'];
        $yongEl = $yongTrigram['element'];

        if ($tiEl === $yongEl) {
            $relation = 'Thể – Dụng Tỷ Hòa';
            $verdict = 'Cát';
            $score = 84;
            $explanation = "Quẻ Thể ({$tiTrigram['name']} – hành {$tiEl}) và Quẻ Dụng ({$yongTrigram['name']} – hành {$yongEl}) cùng hành tương hòa: nội ngoại đồng thuận, dễ gặp người cùng chí hướng và thuận lợi triển khai.";
        } elseif ((self::GENERATES[$yongEl] ?? '') === $tiEl) {
            $relation = 'Dụng sinh Thể';
            $verdict = 'Đại Cát';
            $score = 92;
            $explanation = "Quẻ Dụng ({$yongTrigram['name']} – hành {$yongEl}) tương sinh cho Quẻ Thể ({$tiTrigram['name']} – hành {$tiEl}): thời thế và quý nhân trợ lực mạnh, việc hỏi dễ thu hoạch kết quả mỹ mãn.";
        } elseif ((self::OVERCOMES[$tiEl] ?? '') === $yongEl) {
            $relation = 'Thể khắc Dụng';
            $verdict = 'Tiểu Cát';
            $score = 78;
            $explanation = "Quẻ Thể ({$tiTrigram['name']} – hành {$tiEl}) chế ngự Quẻ Dụng ({$yongTrigram['name']} – hành {$yongEl}): bạn nắm quyền chủ động, dù cần bỏ công sức nhưng kết quả vẫn đạt được như ý.";
        } elseif ((self::GENERATES[$tiEl] ?? '') === $yongEl) {
            $relation = 'Thể sinh Dụng';
            $verdict = 'Bình (Hao Lực)';
            $score = 64;
            $explanation = "Quẻ Thể ({$tiTrigram['name']} – hành {$tiEl}) phải sinh xuất cho Quẻ Dụng ({$yongTrigram['name']} – hành {$yongEl}): bạn cần đầu tư nhiều tâm sức hoặc chi phí; nên tính kỹ điểm dừng để tránh kiệt sức.";
        } else {
            $relation = 'Dụng khắc Thể';
            $verdict = 'Thận Trọng';
            $score = 52;
            $explanation = "Quẻ Dụng ({$yongTrigram['name']} – hành {$yongEl}) khắc chế Quẻ Thể ({$tiTrigram['name']} – hành {$tiEl}): ngoại cảnh tạm thời tạo áp lực hoặc trở ngại, nên lấy thủ làm công, rà soát kỹ trước khi quyết định.";
        }

        // Xét thêm biến quẻ đối với Thể để đánh giá hậu vận
        $futureNote = 'Quẻ tĩnh (không có hào động) nên tình thế duy trì ổn định theo bản chất Quẻ Chủ.';
        if ($changedHex !== null) {
            $changedUpperEl = $changedHex['upper_trigram']['element'];
            $changedLowerEl = $changedHex['lower_trigram']['element'];
            $changedYongEl = ($hasLowerMoving && ! $hasUpperMoving) ? $changedLowerEl : $changedUpperEl;

            if ((self::GENERATES[$changedYongEl] ?? '') === $tiEl || $changedYongEl === $tiEl) {
                $score = min(96, $score + 5);
                $futureNote = "Hậu vận sang quẻ Biến {$changedHex['name']} (hành {$changedYongEl} trợ cho Thể hành {$tiEl}) rất sáng sủa, càng về sau càng thuận.";
            } elseif ((self::OVERCOMES[$changedYongEl] ?? '') === $tiEl) {
                $score = max(45, $score - 5);
                $futureNote = "Sang quẻ Biến {$changedHex['name']} có yếu tố hành {$changedYongEl} khắc Thể ({$tiEl}), nhắc bạn cần giữ kỷ luật và phương án dự phòng ở chặng sau.";
            } else {
                $futureNote = "Quẻ chuyển sang {$changedHex['name']}, xu hướng vận động đang mở ra bước ngoặt mới cần linh hoạt thích ứng.";
            }
        }

        return [
            'ti_trigram' => $tiTrigram,
            'yong_trigram' => $yongTrigram,
            'ti_position' => $tiPosition,
            'yong_position' => $yongPosition,
            'relation' => $relation,
            'verdict' => $verdict,
            'auspicious_score' => $score,
            'explanation' => $explanation,
            'future_note' => $futureNote,
            'timing_hint' => self::ELEMENT_TIMING[$tiEl] ?? self::ELEMENT_TIMING['Thổ'],
        ];
    }

    /**
     * Quy tắc luận hào động chuẩn Chu Hy (Dịch Học Khải Mông).
     *
     * @param  array<int, int>  $movingLines
     */
    protected function getChuHyReadingRule(array $movingLines): string
    {
        $count = count($movingLines);

        return match ($count) {
            0 => 'Quẻ Tĩnh (0 hào động): Lấy trọn vẹn Quái Từ và Đại Tượng của Quẻ Chủ làm trục luận chính.',
            1 => 'Có 1 Hào Động (Hào '.$movingLines[0].'): Lấy ý nghĩa biến chuyển tại Hào '.$movingLines[0].' kết hợp Quẻ Biến làm trọng tâm giải đáp.',
            2 => 'Có 2 Hào Động (Hào '.implode(' & ', $movingLines).'): Luận cả hai hào động, ưu tiên trọng tâm vào hào ở vị trí cao hơn (Hào '.max($movingLines).').',
            3 => 'Có 3 Hào Động: Kết hợp Quái Từ Quẻ Chủ (hiện trạng) và Quái Từ Quẻ Biến (hướng chuyển hóa), lấy Quẻ Chủ làm nền.',
            4, 5 => 'Có '.$count.' Hào Động: Tình thế đang chuyển biến mạnh; kết hợp xem hào tĩnh còn lại và lấy Quẻ Biến làm trọng tâm tương lai.',
            default => 'Cả 6 hào đều động (Biến cực): Tình thế chuyển hóa toàn diện sang Quẻ Biến, lấy Quái Từ Quẻ Biến làm kim chỉ nam.',
        };
    }

    protected function bootCatalog(): void
    {
        if (self::$byNo !== null && self::$byCode !== null) {
            return;
        }

        $path = database_path('data/iching-64-hexagrams.json');
        if (! File::exists($path)) {
            throw new RuntimeException('Thiếu tệp dữ liệu 64 Quẻ Kinh Dịch: database/data/iching-64-hexagrams.json');
        }

        $decoded = json_decode((string) File::get($path), true);
        if (! is_array($decoded) || count($decoded) !== 64) {
            throw new RuntimeException('Dữ liệu 64 Quẻ Kinh Dịch không hợp lệ hoặc không đủ 64 quẻ.');
        }

        $byNo = [];
        $byCode = [];

        foreach ($decoded as $item) {
            $upperKey = (string) ($item['upper'] ?? '');
            $lowerKey = (string) ($item['lower'] ?? '');
            $upper = self::TRIGRAMS[$upperKey] ?? null;
            $lower = self::TRIGRAMS[$lowerKey] ?? null;

            if (! $upper || ! $lower) {
                throw new RuntimeException('Quái đơn không hợp lệ ở quẻ số '.($item['no'] ?? '?'));
            }

            $code = $lower['bits'].$upper['bits'];
            $enriched = array_merge($item, [
                'code' => $code,
                'upper_trigram' => $upper,
                'lower_trigram' => $lower,
                'trigram_pair_label' => "Trên {$upper['name']} ({$upper['nature']}) • Dưới {$lower['name']} ({$lower['nature']})",
            ]);

            $no = (int) $item['no'];
            $byNo[$no] = $enriched;
            $byCode[$code] = $enriched;
        }

        ksort($byNo);
        self::$byNo = $byNo;
        self::$byCode = $byCode;
    }
}
