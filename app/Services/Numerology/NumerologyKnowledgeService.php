<?php

declare(strict_types=1);

namespace App\Services\Numerology;

use Illuminate\Support\Facades\File;

/**
 * Truy xuất Kho Tri Thức Thần Số Học theo ngữ cảnh (chỉ số × giá trị) & Động cơ Đối Chiếu Tình Duyên 45 cặp đối xứng.
 */
class NumerologyKnowledgeService
{
    /** @var list<array{key: string, label: string, focus: string}> 10 chủ đề ứng dụng hiện đại của Mây Hẹn */
    public const LOVE_TOPICS = [
        ['key' => 'dau_an_rieng', 'label' => 'Dấu ấn riêng của mỗi người', 'focus' => 'Bản sắc tự nhiên và giá trị cốt lõi mỗi người mang vào mối quan hệ'],
        ['key' => 'diem_dong_dieu', 'label' => 'Những điểm có thể đồng điệu', 'focus' => 'Nơi hai người dễ bắt nhịp cảm xúc và chia sẻ chung tầm nhìn'],
        ['key' => 'the_hien_tinh_yeu', 'label' => 'Cách thể hiện tình yêu', 'focus' => 'Ngôn ngữ yêu thương quen thuộc và cách bày tỏ sự quan tâm'],
        ['key' => 'nhu_cau_tinh_cam', 'label' => 'Nhu cầu tình cảm', 'focus' => 'Điều khiến mỗi người cảm thấy an toàn, được tôn trọng và trân quý'],
        ['key' => 'giao_tiep', 'label' => 'Phong cách giao tiếp', 'focus' => 'Nhịp trò chuyện, mức độ thẳng thắn và cách lắng nghe nhau'],
        ['key' => 'khac_biet', 'label' => 'Những khác biệt cần thấu hiểu', 'focus' => 'Điểm lệch nhịp dễ gây hiểu lầm nếu không gọi tên từ sớm'],
        ['key' => 'mau_thuan', 'label' => 'Cách giải quyết mâu thuẫn', 'focus' => 'Cách hạ nhiệt cảm xúc và cùng tìm giải pháp thay vì hơn thua'],
        ['key' => 'tai_chinh_muc_tieu', 'label' => 'Tài chính & mục tiêu chung', 'focus' => 'Quan điểm tích lũy, chi tiêu và tầm nhìn 3–5 năm của hai người'],
        ['key' => 'gia_dinh_hai_ben', 'label' => 'Gia đình hai bên', 'focus' => 'Cách ứng xử tinh tế, giữ hòa khí và ranh giới lành mạnh với nội ngoại'],
        ['key' => 'chuan_bi_hon_nhan', 'label' => 'Sự chuẩn bị cho hôn nhân lâu dài', 'focus' => 'Những thỏa thuận thực tế giúp tổ ấm bền vững sau ngày chung đôi'],
    ];

    /** @var list<string> 5 câu hỏi tương tác cho Không gian "Câu chuyện hai chúng ta" */
    public const COUPLE_QUESTIONS = [
        'Khi gặp bất đồng, bạn muốn được lắng nghe ngay hay cần khoảng lặng 15–30 phút trước khi nói chuyện tiếp?',
        'Một hành động nhỏ nào từ đối phương khiến bạn cảm thấy được yêu thương rõ rệt nhất?',
        'Hai bạn hình dung không khí tổ ấm tương lai của mình sẽ như thế nào?',
        'Có nguyên tắc minh bạch tài chính chung – riêng nào hai bạn muốn thống nhất trước khi cưới?',
        'Điều gì hai bạn muốn cùng trao đổi nhẹ nhàng với gia đình hai bên trong thời gian tới?',
    ];

    /** @var array<string, mixed>|null */
    protected static ?array $numbersData = null;

    /** @var array<string, mixed>|null */
    protected static ?array $birthdaysData = null;

    /** @var array<string, mixed>|null */
    protected static ?array $cyclesData = null;

    /** @var array<string, mixed>|null */
    protected static ?array $arrowsData = null;

    /** @var array<string, mixed>|null */
    protected static ?array $loveMatrixData = null;

    protected function boot(): void
    {
        if (self::$numbersData !== null) {
            return;
        }
        $base = database_path('data/numerology');
        self::$numbersData = $this->readJson("{$base}/numbers_by_indicator.json");
        self::$birthdaysData = $this->readJson("{$base}/birthdays_31.json");
        self::$cyclesData = $this->readJson("{$base}/cycles_and_pinnacles.json");
        self::$arrowsData = $this->readJson("{$base}/birth_chart_arrows.json");
        self::$loveMatrixData = $this->readJson("{$base}/relationship_matrix_45.json");
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getArchetypes(): array
    {
        $this->boot();

        return is_array(self::$numbersData['archetypes'] ?? null) ? self::$numbersData['archetypes'] : [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getIndicatorLenses(): array
    {
        $this->boot();

        return is_array(self::$numbersData['indicator_lenses'] ?? null) ? self::$numbersData['indicator_lenses'] : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getCyclesCatalog(): array
    {
        $this->boot();

        return self::$cyclesData ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getBirthChartArrowsCatalog(): array
    {
        $this->boot();

        return self::$arrowsData ?? [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getRelationshipPairs(): array
    {
        $this->boot();

        return is_array(self::$loveMatrixData['pairs'] ?? null) ? self::$loveMatrixData['pairs'] : [];
    }

    /**
     * Lấy nội dung tri thức tách bạch theo ĐÚNG cặp (chỉ số × giá trị số) — không dùng chung 1 đoạn văn cho mọi chỉ số.
     *
     * @return array<string, mixed>
     */
    public function getIndicatorMeaning(string $indicatorCode, int $value): array
    {
        $this->boot();
        $archetypes = $this->getArchetypes();
        $lenses = $this->getIndicatorLenses();

        $key = (string) $value;
        if (! isset($archetypes[$key])) {
            $root = $value;
            while ($root > 9) {
                $root = array_sum(array_map('intval', str_split((string) $root)));
            }
            $key = (string) max(1, min(9, $root));
        }

        $arch = $archetypes[$key] ?? $archetypes['1'];
        $lens = $lenses[$indicatorCode] ?? ['label' => $indicatorCode, 'scope' => '', 'prefix_template' => ''];
        $prefix = str_replace(['{value}', '{title}'], [(string) $value, (string) $arch['title']], (string) ($lens['prefix_template'] ?? ''));

        return [
            'indicator_code' => $indicatorCode,
            'indicator_label' => (string) ($lens['label'] ?? $indicatorCode),
            'indicator_scope' => (string) ($lens['scope'] ?? ''),
            'value' => $value,
            'title' => (string) $arch['title'],
            'geometry' => (string) ($arch['geometry'] ?? 'complete_circle'),
            'contextual_meaning' => trim($prefix.lcfirst((string) $arch['core_meaning'])),
            'traditional_view' => (string) ($arch['traditional_view'] ?? ''),
            'strengths' => (string) ($arch['strengths'] ?? ''),
            'watch_outs' => (string) ($arch['watch_outs'] ?? ''),
            'love_meaning' => (string) ($arch['love_meaning'] ?? ''),
            'marriage_meaning' => (string) ($arch['marriage_meaning'] ?? ''),
            'family_meaning' => (string) ($arch['family_meaning'] ?? ''),
            'work_meaning' => (string) ($arch['work_meaning'] ?? ''),
            'action_hints' => is_array($arch['action_hints'] ?? null) ? $arch['action_hints'] : [],
            'reflection_question' => (string) ($arch['reflection_question'] ?? ''),
            'provenance' => 'verified_kb',
        ];
    }

    /**
     * Làm giàu toàn bộ kết quả từ Calculation Engine bằng tri thức khớp ngữ cảnh (chỉ gửi phần liên quan sang AI).
     *
     * @param  array<string, mixed>  $calc
     * @return array<string, mixed>
     */
    public function enrichCalculation(array $calc): array
    {
        $this->boot();
        $core = $calc['core_numbers'] ?? [];
        $ext = $calc['extended_numbers'] ?? [];
        $cycles = $calc['personal_cycles'] ?? [];

        $indicatorMeanings = [
            'life_path' => $this->getIndicatorMeaning('life_path', (int) ($core['life_path']['value'] ?? 1)),
            'attitude' => $this->getIndicatorMeaning('attitude', (int) ($ext['attitude']['value'] ?? 1)),
        ];

        foreach (['expression', 'soul_urge', 'personality'] as $code) {
            if (! empty($core[$code]) && empty($core[$code]['is_empty_category'])) {
                $indicatorMeanings[$code] = $this->getIndicatorMeaning($code, (int) $core[$code]['value']);
            }
        }
        foreach (['maturity', 'balance', 'rational_thought'] as $code) {
            if (! empty($ext[$code])) {
                $indicatorMeanings[$code] = $this->getIndicatorMeaning($code, (int) $ext[$code]['value']);
            }
        }

        $bDay = (string) ($core['birthday']['raw_day'] ?? 1);
        $birthdayMeta = self::$birthdaysData['birthdays'][$bDay] ?? null;

        $pyVal = (string) ($cycles['personal_year']['value'] ?? 1);
        $pmVal = (string) ($cycles['personal_month']['value'] ?? 1);
        $pdVal = (string) ($cycles['personal_day']['value'] ?? 1);

        $pinnaclesMeta = [];
        foreach ($calc['pinnacles'] ?? [] as $p) {
            $pKey = (string) $p['value'];
            $pinnaclesMeta[] = array_merge($p, [
                'meaning' => (string) (self::$cyclesData['pinnacles'][$pKey] ?? ''),
            ]);
        }

        $challengesMeta = [];
        foreach ($calc['challenges'] ?? [] as $c) {
            $cKey = (string) $c['value'];
            $challengesMeta[] = array_merge($c, [
                'meaning' => (string) (self::$cyclesData['challenges'][$cKey] ?? ''),
            ]);
        }

        $arrowsCatalog = collect(self::$arrowsData['arrows'] ?? [])->keyBy('code');
        $enrichedArrows = [];
        foreach ($calc['birth_chart']['arrows'] ?? [] as $ar) {
            $meta = $arrowsCatalog->get($ar['code'], []);
            $enrichedArrows[] = array_merge($ar, [
                'axis' => (string) ($meta['axis'] ?? ''),
                'name' => $ar['status'] === 'full'
                    ? (string) ($meta['full_name'] ?? $ar['code'])
                    : ($ar['status'] === 'empty' ? ($meta['empty_name'] ?? null) : null),
                'meaning' => $ar['status'] === 'full'
                    ? (string) ($meta['full_meaning'] ?? '')
                    : ($ar['status'] === 'empty' ? (string) ($meta['empty_meaning'] ?? '') : ''),
            ]);
        }

        $digitNotes = [];
        foreach ($calc['birth_chart']['cells'] ?? [] as $d => $cell) {
            $count = (int) $cell['count'];
            $dMeta = self::$arrowsData['digits'][(string) $d] ?? [];
            $tierKey = $count >= 3 ? '3+' : (string) $count;
            $digitNotes[$d] = [
                'plane' => (string) ($dMeta['plane'] ?? ''),
                'note' => $count > 0
                    ? (string) ($dMeta[$tierKey] ?? '')
                    : 'Ô trống trong ngày sinh — không phải khuyết điểm cố định mà là vùng linh hoạt để rèn luyện qua trải nghiệm sống.',
            ];
        }

        return [
            'indicator_meanings' => $indicatorMeanings,
            'birthday_meaning' => $birthdayMeta,
            'personal_year_meaning' => self::$cyclesData['personal_years'][$pyVal] ?? null,
            'personal_month_meaning' => self::$cyclesData['personal_months'][$pmVal] ?? '',
            'personal_day_meaning' => self::$cyclesData['personal_days'][$pdVal] ?? '',
            'pinnacles_enriched' => $pinnaclesMeta,
            'challenges_enriched' => $challengesMeta,
            'arrows_enriched' => $enrichedArrows,
            'digit_notes' => $digitNotes,
        ];
    }

    /**
     * Đối chiếu 2 hồ sơ Thần Số Học độc lập (45 tổ hợp đối xứng, giữ đúng vai trò Người A & Người B, KHÔNG chấm % hợp tuổi).
     *
     * @param  array<string, mixed>  $calcA
     * @param  array<string, mixed>  $calcB
     * @return array<string, mixed>
     */
    public function buildRelationshipComparison(array $calcA, array $calcB, string $stage = 'dang_yeu'): array
    {
        $this->boot();
        $pairs = $this->getRelationshipPairs();

        $lpA = $calcA['core_numbers']['life_path'];
        $lpB = $calcB['core_numbers']['life_path'];
        $rootA = (int) $lpA['base_root'];
        $rootB = (int) $lpB['base_root'];

        // Khóa đối xứng chuẩn hóa min-max để đảo A <-> B vẫn giữ nguyên cặp cơ sở
        $pairKey = min($rootA, $rootB).'-'.max($rootA, $rootB);
        $pairData = $pairs[$pairKey] ?? [
            'dynamic' => 'Đồng Hành & Thấu Hiểu',
            'harmony' => 'Hai con số có nhiều điểm có thể bổ trợ cho nhau nếu đối thoại cởi mở.',
            'difference' => 'Khác biệt nhịp độ là cơ hội hiểu thêm góc nhìn mới.',
            'nurture' => 'Luôn lắng nghe và thống nhất kỳ vọng thực tế.',
        ];

        $archA = $this->getIndicatorMeaning('life_path', (int) $lpA['value']);
        $archB = $this->getIndicatorMeaning('life_path', (int) $lpB['value']);

        $bothHaveNames = ! empty($calcA['has_full_name']) && ! empty($calcB['has_full_name']);
        $masterNotes = [];
        if (! empty($lpA['is_master'])) {
            $masterNotes[] = "{$calcA['display_name']} mang Master Number {$lpA['display']} (đối chiếu qua số nền {$rootA} đồng thời giữ cường độ Master {$lpA['value']}).";
        }
        if (! empty($lpB['is_master'])) {
            $masterNotes[] = "{$calcB['display_name']} mang Master Number {$lpB['display']} (đối chiếu qua số nền {$rootB} đồng thời giữ cường độ Master {$lpB['value']}).";
        }

        return [
            'provenance' => 'mayhen_modern_guide',
            'pair_key' => $pairKey,
            'relationship_stage' => $stage,
            'both_have_full_names' => $bothHaveNames,
            'limitation_note' => $bothHaveNames
                ? 'Đủ ngày sinh và họ tên khai sinh của cả hai người để đối chiếu đa tầng (Đường đời, Sứ mệnh, Linh hồn, Nhân cách, Ngày sinh, Năm cá nhân).'
                : 'Một hoặc cả hai hồ sơ hiện mới có ngày sinh — hệ thống chỉ đối chiếu các chỉ số tính được từ ngày sinh và không suy đoán chỉ số từ họ tên còn thiếu.',
            'disclaimer' => 'Thần Số Học Tình Duyên của Mây Hẹn là khung gợi ý đối thoại dựa trên 10 chủ đề ứng dụng hiện đại — không chấm % hợp nhau và không thay thế sự thấu hiểu thực tế giữa hai người.',
            'person_a' => [
                'display_name' => $calcA['display_name'],
                'life_path_display' => $lpA['display'],
                'life_path_value' => $lpA['value'],
                'life_path_root' => $rootA,
                'life_path_title' => $archA['title'],
                'expression_display' => $calcA['core_numbers']['expression']['display'] ?? null,
                'soul_urge_display' => $calcA['core_numbers']['soul_urge']['display'] ?? null,
                'personality_display' => $calcA['core_numbers']['personality']['display'] ?? null,
                'birthday_raw' => $calcA['core_numbers']['birthday']['raw_day'] ?? null,
                'personal_year' => $calcA['personal_cycles']['personal_year']['value'] ?? null,
            ],
            'person_b' => [
                'display_name' => $calcB['display_name'],
                'life_path_display' => $lpB['display'],
                'life_path_value' => $lpB['value'],
                'life_path_root' => $rootB,
                'life_path_title' => $archB['title'],
                'expression_display' => $calcB['core_numbers']['expression']['display'] ?? null,
                'soul_urge_display' => $calcB['core_numbers']['soul_urge']['display'] ?? null,
                'personality_display' => $calcB['core_numbers']['personality']['display'] ?? null,
                'birthday_raw' => $calcB['core_numbers']['birthday']['raw_day'] ?? null,
                'personal_year' => $calcB['personal_cycles']['personal_year']['value'] ?? null,
            ],
            'base_pair' => $pairData,
            'master_notes' => $masterNotes,
            'topics' => self::LOVE_TOPICS,
            'couple_questions' => self::COUPLE_QUESTIONS,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function readJson(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        return json_decode((string) File::get($path), true) ?: [];
    }
}
