<?php

declare(strict_types=1);

namespace App\Services\Iching;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use RuntimeException;

/**
 * Rule Engine V2 — Mây Hẹn Dịch Tâm.
 *
 * Nguyên tắc cốt lõi:
 * - Tách biệt rõ trường phái: Chu Dịch (3 đồng xu, quy tắc Chu Hy đầy đủ) vs. Mai Hoa Dịch Số (Âm lịch VN / 2 số, Thể–Dụng Ngũ Hành 25 tổ hợp).
 * - Minh bạch 100% chuỗi phép tính (`calculation_trace`) và phiên bản quy tắc (`rule_version`, `kb_version`).
 * - Không gán điểm may rủi 0–100; sử dụng 5 trạng thái hướng dẫn chuẩn Mây Hẹn (`Thuận thế`, `Cân bằng`, `Tích lũy`, `Điều chỉnh`, `Thận trọng`).
 * - Đánh dấu nguồn gốc dữ liệu (`provenance`) và tình trạng thẩm định cổ thư (`verification_status`) để chống AI bịa Hào Từ.
 */
class IchingHexagramEngine
{
    public const METHOD_COINS_3 = 'coins_3';

    public const METHOD_COINS_MANUAL = 'coins_manual';

    public const METHOD_MAI_HOA_TIME = 'mai_hoa_time';

    public const METHOD_MAI_HOA_NUMBERS = 'mai_hoa_numbers';

    public const METHOD_SPRING_BLESSING = 'spring_blessing';

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
     * 5 chủ đề hỏi việc chuẩn (có liên kết tinh tế với mùa cưới, gia đình & đầu năm).
     *
     * @var array<string, array{key: string, label: string, field: string, hint: string}>
     */
    public const TOPICS = [
        'tong_quan' => ['key' => 'tong_quan', 'label' => 'Tổng Quan & Vận Trình', 'field' => 'summary', 'hint' => 'Hỏi chung về định hướng, năm mới hoặc một quyết định lớn'],
        'tinh_duyen' => ['key' => 'tinh_duyen', 'label' => 'Tình Duyên & Hôn Nhân', 'field' => 'love', 'hint' => 'Hỏi về tìm hiểu, yêu thương, chuẩn bị cưới hỏi và gắn kết'],
        'cong_danh' => ['key' => 'cong_danh', 'label' => 'Công Danh & Sự Nghiệp', 'field' => 'career', 'hint' => 'Hỏi về chuyển việc, phát triển năng lực, định hướng nghề nghiệp'],
        'tai_loc' => ['key' => 'tai_loc', 'label' => 'Tài Lộc & Kinh Doanh', 'field' => 'wealth', 'hint' => 'Hỏi về hợp tác, mở rộng, dự án mới và quản lý rủi ro'],
        'gia_dao' => ['key' => 'gia_dao', 'label' => 'Gia Đạo & Bình An', 'field' => 'family', 'hint' => 'Hỏi về hòa khí gia đình, hai bên nội ngoại và quyết định chung'],
    ];

    /**
     * 5 trạng thái hướng dẫn chuẩn Mây Hẹn (thay thế hoàn toàn thang điểm may rủi 0–100).
     *
     * @var array<string, array{key: string, label: string, tone: string, description: string}>
     */
    public const GUIDANCE_STATES = [
        'Thuận thế' => [
            'key' => 'thuan_the',
            'label' => 'Thuận thế',
            'tone' => 'positive',
            'description' => 'Điều kiện hiện tại tương đối hài hòa và ủng hộ bước tiến chủ động; vẫn cần giữ sự khiêm cung và chuẩn bị chu toàn.',
        ],
        'Cân bằng' => [
            'key' => 'can_bang',
            'label' => 'Cân bằng',
            'tone' => 'balanced',
            'description' => 'Nhịp vận động ổn định, đôi bên có cơ sở đồng thuận; thích hợp duy trì đều đặn và đối thoại minh bạch.',
        ],
        'Tích lũy' => [
            'key' => 'tich_luy',
            'label' => 'Tích lũy',
            'tone' => 'steady',
            'description' => 'Cần thêm thời gian rèn nội lực, thu thập dữ kiện và hoàn thiện nền tảng trước khi bước sang chặng lớn hơn.',
        ],
        'Điều chỉnh' => [
            'key' => 'dieu_chinh',
            'label' => 'Điều chỉnh',
            'tone' => 'adjust',
            'description' => 'Nên xem lại nhịp phân bổ nguồn lực, cách giao tiếp hoặc kỳ vọng để tránh hao sức vào điểm chưa trọng yếu.',
        ],
        'Thận trọng' => [
            'key' => 'than_trong',
            'label' => 'Thận trọng',
            'tone' => 'caution',
            'description' => 'Hoàn cảnh đang có lực cản hoặc biến số cần kiểm chứng kỹ; ưu tiên bảo toàn nền tảng, bình tâm lắng nghe trước khi quyết định.',
        ],
    ];

    /**
     * Ứng kỳ tham khảo theo Ngũ Hành (chỉ dùng cho trường phái Mai Hoa Dịch Số).
     *
     * @var array<string, string>
     */
    protected const ELEMENT_TIMING = [
        'Mộc' => 'Tham khảo nhịp hành Mộc: mùa Xuân (tháng 1–2 Âm lịch), các ngày Dần – Mão hoặc buổi sáng sớm (3h–7h).',
        'Hỏa' => 'Tham khảo nhịp hành Hỏa: mùa Hạ (tháng 4–5 Âm lịch), các ngày Tỵ – Ngọ hoặc khung giờ trưa (9h–13h).',
        'Thổ' => 'Tham khảo nhịp hành Thổ: cuối bốn mùa (tháng 3, 6, 9, 12 Âm lịch), các ngày Thìn – Tuất – Sửu – Mùi.',
        'Kim' => 'Tham khảo nhịp hành Kim: mùa Thu (tháng 7–8 Âm lịch), các ngày Thân – Dậu hoặc buổi chiều (15h–19h).',
        'Thủy' => 'Tham khảo nhịp hành Thủy: mùa Đông (tháng 10–11 Âm lịch), các ngày Hợi – Tý hoặc khung giờ tối (21h–1h).',
    ];

    /** @var array<int, array<string, mixed>>|null */
    protected static ?array $byNo = null;

    /** @var array<string, array<string, mixed>>|null */
    protected static ?array $byCode = null;

    /** @var array<string, mixed>|null */
    protected static ?array $manifest = null;

    /** @var array<string, mixed>|null */
    protected static ?array $wuXingMatrix = null;

    /** @var array<string, mixed>|null */
    protected static ?array $topicsTaxonomy = null;

    /** @var array<string, mixed>|null */
    protected static ?array $verifiedClassical = null;

    protected VietnameseLunarCalendarService $lunarCalendar;

    public function __construct(?VietnameseLunarCalendarService $lunarCalendar = null)
    {
        $this->lunarCalendar = $lunarCalendar ?? new VietnameseLunarCalendarService;
    }

    /**
     * Chuẩn hóa tên phương thức (giữ tương thích ngược `new_year` -> `spring_blessing`).
     */
    public function normalizeMethod(string $method): string
    {
        return match ($method) {
            'new_year', self::METHOD_SPRING_BLESSING => self::METHOD_SPRING_BLESSING,
            self::METHOD_COINS_MANUAL => self::METHOD_COINS_MANUAL,
            self::METHOD_MAI_HOA_TIME => self::METHOD_MAI_HOA_TIME,
            self::METHOD_MAI_HOA_NUMBERS => self::METHOD_MAI_HOA_NUMBERS,
            default => self::METHOD_COINS_3,
        };
    }

    public function isMaiHoaMethod(string $method): bool
    {
        $normalized = $this->normalizeMethod($method);

        return in_array($normalized, [self::METHOD_MAI_HOA_TIME, self::METHOD_MAI_HOA_NUMBERS], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function getManifest(): array
    {
        $this->bootKnowledgeBase();

        return self::$manifest ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getTopicsTaxonomy(): array
    {
        $this->bootKnowledgeBase();

        return self::$topicsTaxonomy ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllHexagrams(): array
    {
        $this->bootCatalog();

        return self::$byNo ?? [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getByNumber(int $no): ?array
    {
        $this->bootCatalog();

        return self::$byNo[$no] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getByCode(string $code6Bits): ?array
    {
        $this->bootCatalog();

        return self::$byCode[$code6Bits] ?? null;
    }

    /**
     * Lấy bộ quẻ liên quan cho Thư Viện 64 Quẻ: Quẻ Hỗ (Mutual), Quẻ Thác (Opposite — đảo toàn bộ Âm Dương), Quẻ Tông (Reverse — lật ngược 6 hào).
     *
     * @return array{mutual: ?array<string, mixed>, opposite: ?array<string, mixed>, reverse: ?array<string, mixed>}
     */
    public function getRelatedHexagrams(int $no): array
    {
        $hex = $this->getByNumber($no);
        if (! $hex) {
            return ['mutual' => null, 'opposite' => null, 'reverse' => null];
        }

        $bits = (string) $hex['code'];
        $mutualBits = $bits[1].$bits[2].$bits[3].$bits[2].$bits[3].$bits[4];
        $oppositeBits = strtr($bits, ['0' => '1', '1' => '0']);
        $reverseBits = strrev($bits);

        return [
            'mutual' => $this->getByCode($mutualBits),
            'opposite' => $this->getByCode($oppositeBits),
            'reverse' => $this->getByCode($reverseBits),
        ];
    }

    /**
     * Lập lá quẻ đầy đủ từ 1 trong 5 phương thức minh bạch.
     *
     * @param  array<int, int>|null  $providedLines
     * @param  array<int, array<int, string>>|null  $coinFaces
     * @return array<string, mixed>
     */
    public function cast(
        string $method = self::METHOD_COINS_3,
        ?array $providedLines = null,
        ?int $numberUpper = null,
        ?int $numberLower = null,
        ?Carbon $moment = null,
        ?array $coinFaces = null
    ): array {
        $moment = ($moment ?? now('Asia/Ho_Chi_Minh'))->copy()->setTimezone('Asia/Ho_Chi_Minh');
        $normalizedMethod = $this->normalizeMethod($method);

        if ($normalizedMethod === self::METHOD_MAI_HOA_TIME) {
            $built = $this->buildMaiHoaByTime($moment);

            return $this->analyzeLines(
                lines: $built['lines'],
                method: $normalizedMethod,
                moment: $moment,
                calculationTrace: $built['trace']
            );
        }

        if ($normalizedMethod === self::METHOD_MAI_HOA_NUMBERS) {
            if ($numberUpper === null || $numberLower === null || $numberUpper < 1 || $numberLower < 1) {
                throw new InvalidArgumentException('Phương pháp Mai Hoa 2 số yêu cầu nhập đủ 2 số nguyên dương (từ 1 trở lên).');
            }
            $built = $this->buildMaiHoaByNumbers($numberUpper, $numberLower);

            return $this->analyzeLines(
                lines: $built['lines'],
                method: $normalizedMethod,
                moment: $moment,
                calculationTrace: $built['trace']
            );
        }

        if ($normalizedMethod === self::METHOD_COINS_MANUAL) {
            if (! $this->isValidSixLines($providedLines)) {
                throw new InvalidArgumentException('Chế độ Nhập 6 Hào Thủ Công yêu cầu đủ 6 giá trị hào hợp lệ (6, 7, 8 hoặc 9) từ Hào 1 lên Hào 6.');
            }
            $lines = array_map('intval', array_values($providedLines));
            $trace = [
                'school' => 'Chu Dịch — Tự gieo 3 đồng xu thật và nhập kết quả',
                'rule_note' => 'Người dùng tự tung 3 đồng xu thật bên ngoài và ghi lại 6 hào từ dưới lên trên (6: Lão Âm, 7: Thiếu Dương, 8: Thiếu Âm, 9: Lão Dương).',
                'coin_throws' => $this->formatCoinThrowsTrace($lines, $coinFaces),
            ];

            return $this->analyzeLines(
                lines: $lines,
                method: $normalizedMethod,
                moment: $moment,
                calculationTrace: $trace
            );
        }

        // coins_3 hoặc spring_blessing
        $builtCoins = $this->rollOrNormalizeCoinLines($providedLines, $coinFaces);
        $schoolLabel = $normalizedMethod === self::METHOD_SPRING_BLESSING
            ? 'Gieo Quẻ Đầu Năm (dùng lõi thuật toán 3 đồng xu chuẩn Chu Dịch)'
            : 'Chu Dịch — Gieo 3 đồng xu chuẩn (xác suất 1/8 : 3/8 : 3/8 : 1/8)';
        $trace = [
            'school' => $schoolLabel,
            'rule_note' => 'Mỗi hào gieo 3 đồng xu độc lập (Ngửa = 3, Sấp = 2): 6 = 3 Sấp (Lão Âm động), 7 = 2 Sấp 1 Ngửa (Thiếu Dương tĩnh), 8 = 1 Sấp 2 Ngửa (Thiếu Âm tĩnh), 9 = 3 Ngửa (Lão Dương động).',
            'source_rng' => $builtCoins['from_client'] ? 'Tương tác tung xu trên bàn gieo' : 'PHP random_int() mật mã học',
            'coin_throws' => $builtCoins['coin_throws'],
        ];

        return $this->analyzeLines(
            lines: $builtCoins['lines'],
            method: $normalizedMethod,
            moment: $moment,
            calculationTrace: $trace
        );
    }

    /**
     * Phân tích dịch lý toàn diện từ mảng 6 hào [hào 1 .. hào 6] (giá trị 6, 7, 8, 9).
     *
     * @param  array<int, int>  $lines
     * @param  array<string, mixed>|null  $calculationTrace
     * @return array<string, mixed>
     */
    public function analyzeLines(
        array $lines,
        string $method = self::METHOD_COINS_3,
        ?Carbon $moment = null,
        ?array $calculationTrace = null
    ): array {
        $this->bootCatalog();
        $this->bootKnowledgeBase();

        $moment = ($moment ?? now('Asia/Ho_Chi_Minh'))->copy()->setTimezone('Asia/Ho_Chi_Minh');
        $normalizedMethod = $this->normalizeMethod($method);

        if (! $this->isValidSixLines($lines)) {
            throw new InvalidArgumentException('Một quẻ Kinh Dịch bắt buộc có đủ 6 hào với giá trị hợp lệ 6, 7, 8 hoặc 9.');
        }

        $lines = array_map('intval', array_values($lines));

        $primaryBits = '';
        $changedBits = '';
        $movingLines = [];
        $staticLines = [];

        foreach ($lines as $idx => $val) {
            $pos = $idx + 1;
            $isYang = in_array($val, [7, 9], true);
            $isMoving = in_array($val, [6, 9], true);

            $primaryBit = $isYang ? '1' : '0';
            $changedBit = $isMoving ? ($isYang ? '0' : '1') : $primaryBit;

            $primaryBits .= $primaryBit;
            $changedBits .= $changedBit;

            if ($isMoving) {
                $movingLines[] = $pos;
            } else {
                $staticLines[] = $pos;
            }
        }

        $primaryHex = $this->getByCode($primaryBits);
        if (! $primaryHex) {
            throw new RuntimeException("Không tìm thấy quẻ Chủ cho mã nhị phân {$primaryBits}.");
        }

        $changedHex = $movingLines !== [] ? ($this->getByCode($changedBits) ?? $primaryHex) : null;

        // Xử lý Quẻ Hỗ — gồm ngoại lệ "Càn Khôn vô hỗ, hỗ kỳ biến quái" cho Mai Hoa
        $isMaiHoa = $this->isMaiHoaMethod($normalizedMethod);
        $mutualMeta = $this->resolveMutualHexagram($primaryHex, $changedHex, $primaryBits, $changedBits, $isMaiHoa);
        $mutualHex = $mutualMeta['hexagram'];

        // Gắn tên hào chuẩn (Sơ Cửu/Sơ Lục..Thượng Cửu/Thượng Lục) và cổ thư đã thẩm định cho từng hào
        $lineDetails = [];
        foreach ($lines as $idx => $val) {
            $pos = $idx + 1;
            $isYang = in_array($val, [7, 9], true);
            $isMoving = in_array($val, [6, 9], true);
            $classicalLine = $primaryHex['classical_data']['lines'][(string) $pos] ?? null;

            $lineDetails[] = [
                'position' => $pos,
                'label' => $this->buildClassicalLineName($pos, $isYang),
                'short_label' => 'Hào '.$pos,
                'value' => $val,
                'symbol' => match ($val) {
                    9 => '○',
                    6 => '×',
                    default => '',
                },
                'type_label' => match ($val) {
                    9 => 'Lão Dương (Động ○)',
                    8 => 'Thiếu Âm (Tĩnh)',
                    7 => 'Thiếu Dương (Tĩnh)',
                    6 => 'Lão Âm (Động ×)',
                },
                'is_yang' => $isYang,
                'is_moving' => $isMoving,
                'changed_is_yang' => $changedBits[$idx] === '1',
                'classical_verified' => $classicalLine !== null,
                'classical_text' => $classicalLine,
            ];
        }

        // Quy tắc chọn văn bản luận giải chuẩn Chu Hy (Dịch Học Khải Mông — đủ 20 trường hợp 3 hào động & Dụng Cửu/Dụng Lục)
        $chuHy = $this->resolveChuHyTargetTexts($primaryHex, $changedHex, $movingLines, $staticLines);

        // Phân tách trường phái Thể–Dụng: chỉ áp dụng chuẩn cho Mai Hoa (1 hào động)
        $tiYong = $this->analyzeTiYongSeparated($primaryHex, $mutualHex, $changedHex, $movingLines, $isMaiHoa);

        // Trạng thái hướng dẫn chuẩn Mây Hẹn (5 mức, không chấm điểm 0-100)
        $guidanceStateKey = $this->resolveGuidanceState($primaryHex, $changedHex, $tiYong, $isMaiHoa);
        $guidanceState = self::GUIDANCE_STATES[$guidanceStateKey] ?? self::GUIDANCE_STATES['Cân bằng'];

        $manifest = $this->getManifest();
        $ruleVersion = (string) ($manifest['rule_versions'][$normalizedMethod] ?? 'coins_v2+chu_hy_v2_full');
        $kbVersion = (string) ($manifest['kb_version'] ?? '2.0.0');

        return [
            'method' => $normalizedMethod,
            'method_label' => $this->getMethodLabel($normalizedMethod),
            'school' => $isMaiHoa ? 'mai_hoa' : 'chu_dich',
            'rule_version' => $ruleVersion,
            'kb_version' => $kbVersion,
            'cast_at' => $moment->format('H:i • d/m/Y'),
            'timezone' => 'Asia/Ho_Chi_Minh',
            'lines' => $lines,
            'line_details' => $lineDetails,
            'moving_lines' => $movingLines,
            'static_lines' => $staticLines,
            'moving_rule' => $chuHy['summary_text'],
            'chu_hy' => $chuHy,
            'primary' => $primaryHex,
            'mutual' => $mutualHex,
            'mutual_meta' => [
                'rule' => $mutualMeta['rule'],
                'is_applicable_traditionally' => $mutualMeta['is_applicable_traditionally'],
                'note' => $mutualMeta['note'],
                'provenance' => $isMaiHoa ? 'scholar_commentary' : 'mayhen_presentation',
            ],
            'changed' => $changedHex,
            'ti_yong' => $tiYong,
            'guidance_state' => array_merge($guidanceState, [
                'provenance' => 'mayhen_presentation',
                'disclaimer' => 'Trạng thái hướng dẫn mang tính tổng hợp định hướng của Mây Hẹn, không phải phán quyết cát hung tuyệt đối.',
            ]),
            'calculation_trace' => $calculationTrace ?? [
                'school' => $this->getMethodLabel($normalizedMethod),
                'rule_note' => 'Phân tích trực tiếp từ bộ 6 hào đã lưu.',
            ],
        ];
    }

    /**
     * Lập quẻ Mai Hoa Dịch Số theo thời khắc Âm lịch Việt Nam (UTC+7, quy ước `vn_lunar_v1`).
     *
     * @return array{lines: array<int, int>, trace: array<string, mixed>}
     */
    public function buildMaiHoaByTime(Carbon $moment): array
    {
        $lunar = $this->lunarCalendar->fromCarbon($moment);

        $yearBranch = $lunar['year_branch_index'];
        $month = $lunar['lunar_month'];
        $day = $lunar['lunar_day'];
        $hourBranch = $lunar['hour_branch_index'];

        $upperSum = $yearBranch + $month + $day;
        $lowerSum = $upperSum + $hourBranch;

        $upperNo = $upperSum % 8 === 0 ? 8 : $upperSum % 8;
        $lowerNo = $lowerSum % 8 === 0 ? 8 : $lowerSum % 8;
        $movingPos = $lowerSum % 6 === 0 ? 6 : $lowerSum % 6;

        $upperTri = $this->findTrigramByNumber($upperNo);
        $lowerTri = $this->findTrigramByNumber($lowerNo);

        $notes = [];
        if ($lunar['is_late_rat_hour']) {
            $notes[] = 'Thời điểm rơi vào Giờ Tý đêm (23:00–23:59) nên theo quy ước vn_lunar_v1 được tính sang ngày Âm lịch kế tiếp với Chi Giờ = 1 (Tý).';
        }
        if ($lunar['is_leap_month']) {
            $notes[] = 'Thời điểm rơi vào tháng nhuận Âm lịch; quy ước vn_lunar_v1 giữ nguyên số tháng gốc ('.$month.').';
        }

        return [
            'lines' => $this->composeLinesFromTrigrams($upperNo, $lowerNo, $movingPos),
            'trace' => [
                'school' => 'Mai Hoa Dịch Số — Theo Thời Khắc Âm Lịch Việt Nam (vn_lunar_v1)',
                'rule_note' => 'Thượng Quái = (Chi Năm + Tháng ÂL + Ngày ÂL) mod 8; Hạ Quái = (Tổng trên + Chi Giờ) mod 8; Hào Động = Tổng mod 6 (chia hết lấy 8 hoặc 6). Đổi Chi Năm tại Mùng 1 Tết Nguyên Đán.',
                'lunar' => $lunar,
                'steps' => [
                    sprintf('Chi Năm (%s) = %d; Tháng ÂL = %d%s; Ngày ÂL = %d; Chi Giờ (%s) = %d', $lunar['year_branch_name'], $yearBranch, $month, $lunar['is_leap_month'] ? ' nhuận' : '', $day, $lunar['hour_branch_name'], $hourBranch),
                    sprintf('Thượng Quái: (%d + %d + %d) = %d → dư 8 = %d (%s %s)', $yearBranch, $month, $day, $upperSum, $upperNo, $upperTri['symbol'], $upperTri['name']),
                    sprintf('Hạ Quái: (%d + %d) = %d → dư 8 = %d (%s %s)', $upperSum, $hourBranch, $lowerSum, $lowerNo, $lowerTri['symbol'], $lowerTri['name']),
                    sprintf('Hào Động: %d → dư 6 = Hào %d', $lowerSum, $movingPos),
                ],
                'edge_notes' => $notes,
            ],
        ];
    }

    /**
     * Lập quẻ Mai Hoa Dịch Số theo 2 số (quy ước minh bạch của Mây Hẹn: mai_hoa_2num_mayhen_v2).
     *
     * @return array{lines: array<int, int>, trace: array<string, mixed>}
     */
    public function buildMaiHoaByNumbers(int $numUpper, int $numLower): array
    {
        if ($numUpper < 1 || $numLower < 1) {
            throw new InvalidArgumentException('Hai con số lập quẻ Mai Hoa phải là số nguyên dương (≥ 1).');
        }

        $upperNo = $numUpper % 8 === 0 ? 8 : $numUpper % 8;
        $lowerNo = $numLower % 8 === 0 ? 8 : $numLower % 8;
        $total = $numUpper + $numLower;
        $movingPos = $total % 6 === 0 ? 6 : $total % 6;

        $upperTri = $this->findTrigramByNumber($upperNo);
        $lowerTri = $this->findTrigramByNumber($lowerNo);

        return [
            'lines' => $this->composeLinesFromTrigrams($upperNo, $lowerNo, $movingPos),
            'trace' => [
                'school' => 'Mai Hoa Dịch Số — Biến Thể 2 Số Chuẩn Hóa Mây Hẹn (mai_hoa_2num_mayhen_v2)',
                'rule_note' => 'Quy ước công bố: Số thứ nhất chia 8 lấy dư (dư 0 lấy 8) lập Thượng Quái; Số thứ hai chia 8 lấy dư lập Hạ Quái; Tổng 2 số chia 6 lấy dư (dư 0 lấy 6) lập Hào Động.',
                'inputs' => ['number_upper' => $numUpper, 'number_lower' => $numLower],
                'steps' => [
                    sprintf('Thượng Quái: %d mod 8 = %d (%s %s)', $numUpper, $upperNo, $upperTri['symbol'], $upperTri['name']),
                    sprintf('Hạ Quái: %d mod 8 = %d (%s %s)', $numLower, $lowerNo, $lowerTri['symbol'], $lowerTri['name']),
                    sprintf('Hào Động: (%d + %d) = %d mod 6 = Hào %d', $numUpper, $numLower, $total, $movingPos),
                ],
                'edge_notes' => [],
            ],
        ];
    }

    /**
     * Tên Hào chuẩn Chu Dịch theo vị trí (1..6) và tính Âm/Dương.
     */
    public function buildClassicalLineName(int $position, bool $isYang): string
    {
        $num = $isYang ? 'Cửu' : 'Lục';

        return match ($position) {
            1 => "Sơ {$num}",
            2 => "{$num} Nhị",
            3 => "{$num} Tam",
            4 => "{$num} Tứ",
            5 => "{$num} Ngũ",
            6 => "Thượng {$num}",
            default => "Hào {$position}",
        };
    }

    public function getMethodLabel(string $method): string
    {
        return match ($this->normalizeMethod($method)) {
            self::METHOD_COINS_MANUAL => 'Nhập 6 Hào Tự Gieo Xu Thật',
            self::METHOD_MAI_HOA_TIME => 'Mai Hoa Dịch Số (Âm Lịch Việt Nam)',
            self::METHOD_MAI_HOA_NUMBERS => 'Mai Hoa Dịch Số (Theo 2 Số)',
            self::METHOD_SPRING_BLESSING => 'Gieo Quẻ Đầu Năm / Khai Xuân',
            default => 'Gieo 3 Đồng Xu Chuẩn Chu Dịch',
        };
    }

    /**
     * @param  array<int, int>|null  $lines
     */
    protected function isValidSixLines(?array $lines): bool
    {
        if (! is_array($lines) || count($lines) !== 6) {
            return false;
        }
        foreach ($lines as $v) {
            if (! is_numeric($v) || ! in_array((int) $v, [6, 7, 8, 9], true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gieo 3 đồng xu cổ 6 lần bằng random_int bảo mật (chuẩn xác suất 1/8, 3/8, 3/8, 1/8).
     *
     * @param  array<int, int>|null  $providedLines
     * @param  array<int, array<int, string>>|null  $coinFaces
     * @return array{lines: array<int, int>, from_client: bool, coin_throws: list<array<string, mixed>>}
     */
    protected function rollOrNormalizeCoinLines(?array $providedLines, ?array $coinFaces = null): array
    {
        if ($this->isValidSixLines($providedLines)) {
            $lines = array_map('intval', array_values($providedLines));

            return [
                'lines' => $lines,
                'from_client' => true,
                'coin_throws' => $this->formatCoinThrowsTrace($lines, $coinFaces),
            ];
        }

        $lines = [];
        $throws = [];
        for ($i = 0; $i < 6; $i++) {
            $faces = [];
            $sum = 0;
            for ($c = 0; $c < 3; $c++) {
                // Mặt Ngửa (Dương) = 3, Mặt Sấp (Âm) = 2
                $isHeads = random_int(0, 1) === 1;
                $sum += $isHeads ? 3 : 2;
                $faces[] = $isHeads ? 'Ngửa (3)' : 'Sấp (2)';
            }
            $lines[] = $sum;
            $throws[] = [
                'position' => $i + 1,
                'faces' => $faces,
                'sum' => $sum,
            ];
        }

        return [
            'lines' => $lines,
            'from_client' => false,
            'coin_throws' => $throws,
        ];
    }

    /**
     * @param  array<int, int>  $lines
     * @param  array<int, array<int, string>>|null  $coinFaces
     * @return list<array<string, mixed>>
     */
    protected function formatCoinThrowsTrace(array $lines, ?array $coinFaces): array
    {
        $throws = [];
        foreach ($lines as $idx => $sum) {
            $faces = $coinFaces[$idx] ?? match ($sum) {
                9 => ['Ngửa (3)', 'Ngửa (3)', 'Ngửa (3)'],
                8 => ['Ngửa (3)', 'Ngửa (3)', 'Sấp (2)'],
                7 => ['Ngửa (3)', 'Sấp (2)', 'Sấp (2)'],
                6 => ['Sấp (2)', 'Sấp (2)', 'Sấp (2)'],
                default => [],
            };
            $throws[] = [
                'position' => $idx + 1,
                'faces' => $faces,
                'sum' => $sum,
            ];
        }

        return $throws;
    }

    /**
     * Chuyển (Thượng Quái 1..8, Hạ Quái 1..8, Hào Động 1..6) thành mảng 6 hào [6|7|8|9].
     *
     * @return array<int, int>
     */
    protected function composeLinesFromTrigrams(int $upperNo, int $lowerNo, int $movingPos): array
    {
        $lowerBits = $this->findTrigramByNumber($lowerNo)['bits'];
        $upperBits = $this->findTrigramByNumber($upperNo)['bits'];
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
     * @return array{key: string, number: int, name: string, hanzi: string, symbol: string, nature: string, element: string, bits: string}
     */
    protected function findTrigramByNumber(int $no): array
    {
        foreach (self::TRIGRAMS as $trigram) {
            if ($trigram['number'] === $no) {
                return $trigram;
            }
        }

        return self::TRIGRAMS['can'];
    }

    /**
     * Tính Quẻ Hỗ tôn trọng ngoại lệ "Càn Khôn vô hỗ, hỗ kỳ biến quái" của Mai Hoa Dịch Số.
     *
     * @param  array<string, mixed>  $primaryHex
     * @param  array<string, mixed>|null  $changedHex
     * @return array{hexagram: array<string, mixed>, rule: string, is_applicable_traditionally: bool, note: string}
     */
    protected function resolveMutualHexagram(
        array $primaryHex,
        ?array $changedHex,
        string $primaryBits,
        string $changedBits,
        bool $isMaiHoa
    ): array {
        if ($isMaiHoa && in_array($primaryHex['no'], [1, 2], true) && $changedHex !== null) {
            $mutualBits = $changedBits[1].$changedBits[2].$changedBits[3].$changedBits[2].$changedBits[3].$changedBits[4];
            $mutualHex = $this->getByCode($mutualBits) ?? $changedHex;

            return [
                'hexagram' => $mutualHex,
                'rule' => 'can_khon_ho_bien',
                'is_applicable_traditionally' => true,
                'note' => 'Áp dụng quy tắc Mai Hoa Dịch Số ("Càn Khôn vô hỗ, hỗ kỳ biến quái"): Quẻ Chủ là Thuần Càn/Thuần Khôn nên lấy Quẻ Hỗ từ hào 2-3-4 và 3-4-5 của Quẻ Biến.',
            ];
        }

        $mutualBits = $primaryBits[1].$primaryBits[2].$primaryBits[3].$primaryBits[2].$primaryBits[3].$primaryBits[4];
        $mutualHex = $this->getByCode($mutualBits) ?? $primaryHex;

        if ($isMaiHoa) {
            return [
                'hexagram' => $mutualHex,
                'rule' => 'standard_mutual',
                'is_applicable_traditionally' => true,
                'note' => 'Quẻ Hỗ được lập từ hào 2-3-4 (Hạ quái) và hào 3-4-5 (Thượng quái) của Quẻ Chủ, phản ánh tiến trình diễn biến nội tại giữa chặng.',
            ];
        }

        return [
            'hexagram' => $mutualHex,
            'rule' => 'supplementary_mutual',
            'is_applicable_traditionally' => false,
            'note' => 'Trong phép gieo đồng xu (Chu Dịch), Quẻ Hỗ được hiển thị như lớp tham khảo bổ sung về nội tình giữa chặng, không thay thế Quái Từ và Hào Từ.',
        ];
    }

    /**
     * Bộ chọn văn bản luận giải chuẩn Chu Hy (Dịch Học Khải Mông — Khảo Biến Chiêm đệ tứ).
     * Xử lý đủ 0..6 hào động, bao gồm đầy đủ 20 trường hợp 3 hào động (Tiền thập quái / Hậu thập quái) và Dụng Cửu / Dụng Lục.
     *
     * @param  array<string, mixed>  $primaryHex
     * @param  array<string, mixed>|null  $changedHex
     * @param  array<int, int>  $movingLines
     * @param  array<int, int>  $staticLines
     * @return array<string, mixed>
     */
    protected function resolveChuHyTargetTexts(
        array $primaryHex,
        ?array $changedHex,
        array $movingLines,
        array $staticLines
    ): array {
        $count = count($movingLines);
        $selectedTexts = [];

        if ($count === 0) {
            $selectedTexts[] = $this->extractHexagramTextExcerpt($primaryHex, 'guaci', 'Trọng tâm chính');
            $selectedTexts[] = $this->extractHexagramTextExcerpt($primaryHex, 'daxiang', 'Tượng dẫn hành động');

            return [
                'rule_version' => 'chu_hy_v2_full',
                'rule_code' => 'chu_hy_0',
                'moving_count' => 0,
                'primary_focus_hex' => 'primary',
                'focus_lines' => [],
                'summary_text' => 'Quẻ Tĩnh (0 hào động): Lấy trọn vẹn Quái Từ và Đại Tượng của Quẻ Chủ '.$primaryHex['name'].' làm trục luận chính.',
                'selected_texts' => $selectedTexts,
            ];
        }

        if ($count === 1) {
            $pos = $movingLines[0];
            $selectedTexts[] = $this->extractLineTextExcerpt($primaryHex, $pos, 'Trọng tâm chính (Hào động Quẻ Chủ)');
            if ($changedHex) {
                $selectedTexts[] = $this->extractHexagramTextExcerpt($changedHex, 'guaci', 'Tham khảo hướng chuyển hóa (Quẻ Biến)');
            }

            return [
                'rule_version' => 'chu_hy_v2_full',
                'rule_code' => 'chu_hy_1',
                'moving_count' => 1,
                'primary_focus_hex' => 'primary',
                'focus_lines' => [$pos],
                'summary_text' => "Có 1 Hào Động (Hào {$pos} Quẻ Chủ): Luận trọng tâm tại Hào Từ Hào {$pos} của Quẻ Chủ {$primaryHex['name']}, tham khảo thêm Quẻ Biến.",
                'selected_texts' => $selectedTexts,
            ];
        }

        if ($count === 2) {
            $lowerPos = min($movingLines);
            $upperPos = max($movingLines);
            $selectedTexts[] = $this->extractLineTextExcerpt($primaryHex, $upperPos, 'Trọng tâm chính (Hào động cao hơn)');
            $selectedTexts[] = $this->extractLineTextExcerpt($primaryHex, $lowerPos, 'Hào động phối hợp (vị trí thấp hơn)');

            return [
                'rule_version' => 'chu_hy_v2_full',
                'rule_code' => 'chu_hy_2',
                'moving_count' => 2,
                'primary_focus_hex' => 'primary',
                'focus_lines' => [$upperPos, $lowerPos],
                'summary_text' => "Có 2 Hào Động (Hào {$lowerPos} & Hào {$upperPos}): Luận cả hai hào động của Quẻ Chủ, lấy hào ở vị trí cao hơn (Hào {$upperPos}) làm trọng tâm chính.",
                'selected_texts' => $selectedTexts,
            ];
        }

        if ($count === 3) {
            // Đầy đủ 20 trường hợp 3 hào động theo Chu Hy (Khảo Biến Chiêm):
            // - 10 trường hợp có Hào 1 động (Tiền thập quái): Lấy Quái Từ Quẻ Chủ (Trinh) làm chính, Quẻ Biến (Hối) làm phụ.
            // - 10 trường hợp Hào 1 tĩnh (Hậu thập quái): Lấy Quái Từ Quẻ Biến (Hối) làm chính, Quẻ Chủ (Trinh) làm phụ.
            $hasFirstLineMoving = in_array(1, $movingLines, true);
            if ($hasFirstLineMoving) {
                $selectedTexts[] = $this->extractHexagramTextExcerpt($primaryHex, 'guaci', 'Trọng tâm chính — Trinh (Tiền thập quái)');
                if ($changedHex) {
                    $selectedTexts[] = $this->extractHexagramTextExcerpt($changedHex, 'guaci', 'Tham khảo bổ trợ — Hối (Quẻ Biến)');
                }

                return [
                    'rule_version' => 'chu_hy_v2_full',
                    'rule_code' => 'chu_hy_3_qian_shi_gua',
                    'moving_count' => 3,
                    'primary_focus_hex' => 'primary',
                    'focus_lines' => $movingLines,
                    'summary_text' => 'Có 3 Hào Động thuộc Tiền Thập Quái (có Hào Sơ động — Hào '.implode(', ', $movingLines).'): Luận kết hợp Quái Từ Quẻ Chủ (Trinh) và Quái Từ Quẻ Biến (Hối), lấy Quẻ Chủ làm chính.',
                    'selected_texts' => $selectedTexts,
                ];
            }

            if ($changedHex) {
                $selectedTexts[] = $this->extractHexagramTextExcerpt($changedHex, 'guaci', 'Trọng tâm chính — Hối (Hậu thập quái)');
            }
            $selectedTexts[] = $this->extractHexagramTextExcerpt($primaryHex, 'guaci', 'Tham khảo nền tảng — Trinh (Quẻ Chủ)');

            return [
                'rule_version' => 'chu_hy_v2_full',
                'rule_code' => 'chu_hy_3_hou_shi_gua',
                'moving_count' => 3,
                'primary_focus_hex' => 'changed',
                'focus_lines' => $movingLines,
                'summary_text' => 'Có 3 Hào Động thuộc Hậu Thập Quái (Hào Sơ tĩnh — Hào '.implode(', ', $movingLines).' động): Luận kết hợp Quái Từ Quẻ Chủ và Quẻ Biến, lấy Quái Từ Quẻ Biến (Hối) làm chính.',
                'selected_texts' => $selectedTexts,
            ];
        }

        if ($count === 4 && $changedHex !== null) {
            $lowerStatic = min($staticLines);
            $upperStatic = max($staticLines);
            $selectedTexts[] = $this->extractLineTextExcerpt($changedHex, $lowerStatic, 'Trọng tâm chính (Hào tĩnh thấp hơn ở Quẻ Biến)');
            $selectedTexts[] = $this->extractLineTextExcerpt($changedHex, $upperStatic, 'Hào tĩnh phối hợp (ở Quẻ Biến)');

            return [
                'rule_version' => 'chu_hy_v2_full',
                'rule_code' => 'chu_hy_4',
                'moving_count' => 4,
                'primary_focus_hex' => 'changed',
                'focus_lines' => [$lowerStatic, $upperStatic],
                'summary_text' => "Có 4 Hào Động (2 hào tĩnh là Hào {$lowerStatic} & Hào {$upperStatic}): Luận 2 hào tĩnh trong Quẻ Biến {$changedHex['name']}, lấy hào tĩnh thấp hơn (Hào {$lowerStatic}) làm trọng tâm.",
                'selected_texts' => $selectedTexts,
            ];
        }

        if ($count === 5 && $changedHex !== null) {
            $onlyStatic = $staticLines[0];
            $selectedTexts[] = $this->extractLineTextExcerpt($changedHex, $onlyStatic, 'Trọng tâm chính (Hào tĩnh duy nhất ở Quẻ Biến)');

            return [
                'rule_version' => 'chu_hy_v2_full',
                'rule_code' => 'chu_hy_5',
                'moving_count' => 5,
                'primary_focus_hex' => 'changed',
                'focus_lines' => [$onlyStatic],
                'summary_text' => "Có 5 Hào Động (duy nhất Hào {$onlyStatic} tĩnh): Lấy Hào Từ của Hào {$onlyStatic} trong Quẻ Biến {$changedHex['name']} làm trọng tâm luận giải.",
                'selected_texts' => $selectedTexts,
            ];
        }

        // 6 hào đều động
        if ($primaryHex['no'] === 1) {
            $yongJiu = $primaryHex['classical_data']['yong_jiu'] ?? null;
            $selectedTexts[] = [
                'target' => 'Quẻ Thuần Càn — Dụng Cửu (用九)',
                'role' => 'Trọng tâm chính (6 hào Dương đều động)',
                'verified' => $yongJiu !== null,
                'original' => $yongJiu['original'] ?? null,
                'han_viet' => $yongJiu['han_viet'] ?? null,
                'translation' => $yongJiu['translation'] ?? 'Thấy đàn rồng mà không tranh làm đầu, cát lành.',
                'provenance' => $yongJiu !== null ? 'classical_original' : 'mayhen_modern_guide',
            ];

            return [
                'rule_version' => 'chu_hy_v2_full',
                'rule_code' => 'chu_hy_6_yong_jiu',
                'moving_count' => 6,
                'primary_focus_hex' => 'primary_yong_jiu',
                'focus_lines' => [1, 2, 3, 4, 5, 6],
                'summary_text' => 'Quẻ Thuần Càn có đủ 6 hào Dương động: Theo Chu Hy, lấy lời Dụng Cửu (用九 — Kiến quần long vô thủ, cát) làm trọng tâm.',
                'selected_texts' => $selectedTexts,
            ];
        }

        if ($primaryHex['no'] === 2) {
            $yongLiu = $primaryHex['classical_data']['yong_liu'] ?? null;
            $selectedTexts[] = [
                'target' => 'Quẻ Thuần Khôn — Dụng Lục (用六)',
                'role' => 'Trọng tâm chính (6 hào Âm đều động)',
                'verified' => $yongLiu !== null,
                'original' => $yongLiu['original'] ?? null,
                'han_viet' => $yongLiu['han_viet'] ?? null,
                'translation' => $yongLiu['translation'] ?? 'Thuận lợi nhờ giữ vững chính đạo lâu dài.',
                'provenance' => $yongLiu !== null ? 'classical_original' : 'mayhen_modern_guide',
            ];

            return [
                'rule_version' => 'chu_hy_v2_full',
                'rule_code' => 'chu_hy_6_yong_liu',
                'moving_count' => 6,
                'primary_focus_hex' => 'primary_yong_liu',
                'focus_lines' => [1, 2, 3, 4, 5, 6],
                'summary_text' => 'Quẻ Thuần Khôn có đủ 6 hào Âm động: Theo Chu Hy, lấy lời Dụng Lục (用六 — Lợi vĩnh trinh) làm trọng tâm.',
                'selected_texts' => $selectedTexts,
            ];
        }

        if ($changedHex) {
            $selectedTexts[] = $this->extractHexagramTextExcerpt($changedHex, 'guaci', 'Trọng tâm chính (6 hào động — Quái Từ Quẻ Biến)');
        }

        return [
            'rule_version' => 'chu_hy_v2_full',
            'rule_code' => 'chu_hy_6_changed_guaci',
            'moving_count' => 6,
            'primary_focus_hex' => 'changed',
            'focus_lines' => [1, 2, 3, 4, 5, 6],
            'summary_text' => 'Cả 6 hào đều động (Biến cực): Theo Chu Hy, lấy Quái Từ của Quẻ Biến '.($changedHex['name'] ?? '').' làm trọng tâm luận giải.',
            'selected_texts' => $selectedTexts,
        ];
    }

    /**
     * @param  array<string, mixed>  $hex
     * @return array<string, mixed>
     */
    protected function extractHexagramTextExcerpt(array $hex, string $part, string $role): array
    {
        $classical = $hex['classical_data'][$part] ?? null;
        $label = $part === 'daxiang' ? 'Đại Tượng' : 'Quái Từ';

        if (is_array($classical)) {
            return [
                'target' => "Quẻ #{$hex['no']} {$hex['name']} — {$label}",
                'role' => $role,
                'verified' => true,
                'original' => $classical['original'] ?? null,
                'han_viet' => $classical['han_viet'] ?? null,
                'translation' => $classical['translation'] ?? null,
                'provenance' => 'classical_original',
            ];
        }

        $fallbackText = $part === 'daxiang' ? (string) ($hex['image'] ?? '') : (string) ($hex['judgment'] ?? '');

        return [
            'target' => "Quẻ #{$hex['no']} {$hex['name']} — {$label}",
            'role' => $role,
            'verified' => false,
            'original' => null,
            'han_viet' => null,
            'translation' => $fallbackText,
            'provenance' => 'mayhen_modern_guide',
            'note' => 'Nguyên văn chữ Hán chi tiết đang trong lộ trình thẩm định cổ thư; sử dụng bản lược dịch chuẩn của kho Mây Hẹn.',
        ];
    }

    /**
     * @param  array<string, mixed>  $hex
     * @return array<string, mixed>
     */
    protected function extractLineTextExcerpt(array $hex, int $position, string $role): array
    {
        $bits = (string) ($hex['code'] ?? '111111');
        $isYang = ($bits[$position - 1] ?? '1') === '1';
        $lineName = $this->buildClassicalLineName($position, $isYang);
        $classicalLine = $hex['classical_data']['lines'][(string) $position] ?? null;

        if (is_array($classicalLine)) {
            return [
                'target' => "Quẻ #{$hex['no']} {$hex['name']} — {$lineName} (Hào {$position})",
                'role' => $role,
                'verified' => true,
                'original' => $classicalLine['original'] ?? null,
                'han_viet' => $classicalLine['han_viet'] ?? null,
                'translation' => $classicalLine['translation'] ?? null,
                'xiaoxiang' => $classicalLine['xiaoxiang'] ?? null,
                'provenance' => 'classical_original',
            ];
        }

        return [
            'target' => "Quẻ #{$hex['no']} {$hex['name']} — {$lineName} (Hào {$position})",
            'role' => $role,
            'verified' => false,
            'original' => null,
            'han_viet' => null,
            'translation' => null,
            'xiaoxiang' => null,
            'provenance' => 'mayhen_modern_guide',
            'note' => "Nguyên văn Hào Từ {$lineName} của quẻ {$hex['name']} chưa có trong tập cổ thư đã thẩm định — hệ thống không tự sáng tác nguyên văn hào từ mà luận theo vị trí hào ({$lineName}) kết hợp Quái Từ, Đại Tượng và chuyển biến sang Quẻ Biến.",
        ];
    }

    /**
     * Phân tích Thể – Dụng chuẩn Mai Hoa Dịch Số (25 tổ hợp Ngũ Hành).
     * Tách biệt nghiêm ngặt: KHÔNG áp ép lên phép gieo 3 đồng xu (Chu Dịch).
     *
     * @param  array<string, mixed>  $primaryHex
     * @param  array<string, mixed>  $mutualHex
     * @param  array<string, mixed>|null  $changedHex
     * @param  array<int, int>  $movingLines
     * @return array<string, mixed>
     */
    protected function analyzeTiYongSeparated(
        array $primaryHex,
        array $mutualHex,
        ?array $changedHex,
        array $movingLines,
        bool $isMaiHoa
    ): array {
        if (! $isMaiHoa) {
            return [
                'applicable' => false,
                'school_note' => 'Phép Gieo 3 Đồng Xu thuộc hệ Chu Dịch — luận trọng tâm theo Quái Từ, Hào Từ và quy tắc Hào Động của Chu Hy, không trộn quy tắc Thể – Dụng của Mai Hoa Dịch Số.',
                'ti_trigram' => null,
                'yong_trigram' => null,
                'relation' => null,
                'guidance_state' => null,
                'explanation' => null,
                'mutual_relation' => null,
                'changed_relation' => null,
                'timing_hint' => null,
            ];
        }

        $movingPos = $movingLines[0] ?? 1;
        $isLowerMoving = $movingPos <= 3;

        if ($isLowerMoving) {
            $tiTrigram = $primaryHex['upper_trigram'];
            $yongTrigram = $primaryHex['lower_trigram'];
            $tiPosition = 'Thượng Quái (Quái tĩnh — Bản thể)';
            $yongPosition = 'Hạ Quái (Quái có Hào Động — Sự việc/Ngoại cảnh)';
        } else {
            $tiTrigram = $primaryHex['lower_trigram'];
            $yongTrigram = $primaryHex['upper_trigram'];
            $tiPosition = 'Hạ Quái (Quái tĩnh — Bản thể)';
            $yongPosition = 'Thượng Quái (Quái có Hào Động — Sự việc/Ngoại cảnh)';
        }

        $tiEl = (string) $tiTrigram['element'];
        $yongEl = (string) $yongTrigram['element'];
        $primaryRel = $this->lookupWuXingRelation($tiEl, $yongEl);

        // Xét thêm tiến trình Quẻ Hỗ đối với Thể (giữa chặng)
        $mutualLowerRel = $this->lookupWuXingRelation($tiEl, (string) $mutualHex['lower_trigram']['element']);
        $mutualUpperRel = $this->lookupWuXingRelation($tiEl, (string) $mutualHex['upper_trigram']['element']);
        $mutualNote = sprintf(
            'Giữa chặng qua Quẻ Hỗ %s: Hạ hỗ %s (%s — %s), Thượng hỗ %s (%s — %s) tác động lên Thể %s.',
            $mutualHex['name'],
            $mutualHex['lower_trigram']['name'],
            $mutualHex['lower_trigram']['element'],
            $mutualLowerRel['relation'],
            $mutualHex['upper_trigram']['name'],
            $mutualHex['upper_trigram']['element'],
            $mutualUpperRel['relation'],
            $tiEl
        );

        // Xét hậu vận Quẻ Biến đối với Thể
        $changedRel = null;
        $futureNote = 'Giữ theo bản chất Quẻ Chủ và Thể–Dụng hiện tại.';
        if ($changedHex !== null) {
            $changedYongTri = $isLowerMoving ? $changedHex['lower_trigram'] : $changedHex['upper_trigram'];
            $changedRel = $this->lookupWuXingRelation($tiEl, (string) $changedYongTri['element']);
            $futureNote = sprintf(
                'Hậu vận sang Quẻ Biến %s: Dụng biến sang %s (hành %s) tạo quan hệ "%s" với Thể %s (%s). %s',
                $changedHex['name'],
                $changedYongTri['name'],
                $changedYongTri['element'],
                $changedRel['relation'],
                $tiTrigram['name'],
                $tiEl,
                $changedRel['summary']
            );
        }

        return [
            'applicable' => true,
            'provenance' => 'scholar_commentary',
            'school_note' => 'Áp dụng quy tắc Thể tĩnh – Dụng động và ma trận 25 tổ hợp Ngũ Hành sinh khắc của Mai Hoa Dịch Số.',
            'ti_trigram' => $tiTrigram,
            'yong_trigram' => $yongTrigram,
            'ti_position' => $tiPosition,
            'yong_position' => $yongPosition,
            'relation' => $primaryRel['relation'],
            'guidance_state' => $primaryRel['guidance_state'],
            'explanation' => $primaryRel['summary'],
            'mutual_note' => $mutualNote,
            'changed_relation' => $changedRel,
            'future_note' => $futureNote,
            'timing_hint' => self::ELEMENT_TIMING[$tiEl] ?? self::ELEMENT_TIMING['Thổ'],
        ];
    }

    /**
     * @return array{relation: string, guidance_state: string, summary: string}
     */
    protected function lookupWuXingRelation(string $tiElement, string $yongElement): array
    {
        $relations = self::$wuXingMatrix['relations'] ?? [];
        $key = "{$tiElement}|{$yongElement}";

        if (isset($relations[$key]) && is_array($relations[$key])) {
            return [
                'relation' => (string) $relations[$key]['relation'],
                'guidance_state' => (string) $relations[$key]['guidance_state'],
                'summary' => (string) $relations[$key]['summary'],
            ];
        }

        return [
            'relation' => 'Thể – Dụng Tỷ Hòa',
            'guidance_state' => 'Cân bằng',
            'summary' => 'Thể và Dụng giữ nhịp hài hòa, phù hợp trao đổi minh bạch và tiến hành chắc chắn.',
        ];
    }

    /**
     * @param  array<string, mixed>  $primaryHex
     * @param  array<string, mixed>|null  $changedHex
     * @param  array<string, mixed>  $tiYong
     */
    protected function resolveGuidanceState(array $primaryHex, ?array $changedHex, array $tiYong, bool $isMaiHoa): string
    {
        if ($isMaiHoa && ! empty($tiYong['applicable']) && ! empty($tiYong['guidance_state'])) {
            return (string) $tiYong['guidance_state'];
        }

        $level = (string) ($primaryHex['level'] ?? 'Bình');

        return match ($level) {
            'Đại Cát' => 'Thuận thế',
            'Cát' => 'Cân bằng',
            'Thận Trọng' => 'Thận trọng',
            default => $changedHex !== null && ($changedHex['level'] ?? '') === 'Thận Trọng' ? 'Điều chỉnh' : 'Tích lũy',
        };
    }

    protected function bootKnowledgeBase(): void
    {
        if (self::$manifest !== null) {
            return;
        }

        self::$manifest = $this->readJsonFile(database_path('data/iching/manifest.json'));
        self::$wuXingMatrix = $this->readJsonFile(database_path('data/iching/wu_xing_matrix_25.json'));
        self::$topicsTaxonomy = $this->readJsonFile(database_path('data/iching/topics_taxonomy.json'));
        self::$verifiedClassical = $this->readJsonFile(database_path('data/iching/verified_classical_texts.json'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function readJsonFile(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }
        $decoded = json_decode((string) File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function bootCatalog(): void
    {
        if (self::$byNo !== null && self::$byCode !== null) {
            return;
        }

        $this->bootKnowledgeBase();

        $path = database_path('data/iching-64-hexagrams.json');
        if (! File::exists($path)) {
            throw new RuntimeException('Thiếu tệp dữ liệu 64 Quẻ Kinh Dịch: database/data/iching-64-hexagrams.json');
        }

        $decoded = json_decode((string) File::get($path), true);
        if (! is_array($decoded) || count($decoded) !== 64) {
            throw new RuntimeException('Dữ liệu 64 Quẻ Kinh Dịch không hợp lệ hoặc không đủ 64 quẻ.');
        }

        // Map theme tags từ topics_taxonomy.json
        $hexThemes = [];
        foreach ((self::$topicsTaxonomy['library_themes'] ?? []) as $theme) {
            $tKey = (string) ($theme['key'] ?? '');
            foreach (($theme['hexagrams'] ?? []) as $hNo) {
                $hexThemes[(int) $hNo][] = $tKey;
            }
        }

        $classicalMap = self::$verifiedClassical['hexagrams'] ?? [];

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

            $no = (int) $item['no'];
            $code = $lower['bits'].$upper['bits'];
            $unicode = mb_chr(0x4DC0 + $no - 1, 'UTF-8') ?: '';
            $classicalData = is_array($classicalMap[(string) $no] ?? null) ? $classicalMap[(string) $no] : null;

            $lineNames = [];
            for ($pos = 1; $pos <= 6; $pos++) {
                $lineNames[$pos] = $this->buildClassicalLineName($pos, $code[$pos - 1] === '1');
            }

            $guidanceRef = match ((string) ($item['level'] ?? 'Bình')) {
                'Đại Cát' => 'Thuận thế',
                'Cát' => 'Cân bằng',
                'Thận Trọng' => 'Thận trọng',
                default => 'Tích lũy',
            };

            $enriched = array_merge($item, [
                'code' => $code,
                'unicode' => $unicode,
                'upper_trigram' => $upper,
                'lower_trigram' => $lower,
                'trigram_pair_label' => "Trên {$upper['name']} ({$upper['nature']}) • Dưới {$lower['name']} ({$lower['nature']})",
                'themes' => array_values(array_unique($hexThemes[$no] ?? ['can_bang'])),
                'line_names' => $lineNames,
                'guidance_state_ref' => $guidanceRef,
                'classical_verified' => $classicalData !== null,
                'classical_data' => $classicalData,
                'provenance' => $classicalData !== null ? 'classical_original' : 'mayhen_modern_guide',
            ]);

            $byNo[$no] = $enriched;
            $byCode[$code] = $enriched;
        }

        ksort($byNo);
        self::$byNo = $byNo;
        self::$byCode = $byCode;
    }
}
