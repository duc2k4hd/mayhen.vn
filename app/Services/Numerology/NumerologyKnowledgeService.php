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

        $viewedYear = (int) ($cycles['personal_year']['year'] ?? now('Asia/Ho_Chi_Minh')->year);
        $birthYear = (int) ($calc['birth']['year'] ?? $viewedYear);
        $currentAge = max(0, $viewedYear - $birthYear);

        $detailedIndicators = $this->buildDetailedIndicators($calc, $indicatorMeanings, $birthdayMeta);
        $bridgeInsights = $this->buildBridgeInsights($calc);
        $careerDomains = $this->buildCareerDomains($calc);
        $periodCyclesEnriched = $this->buildPeriodCyclesEnriched($calc, $currentAge);
        $twelveMonthsEnriched = $this->buildTwelveMonthsEnriched($cycles);
        $yearAspectsDefault = $this->buildPersonalYearAspects((int) $pyVal);
        $conceptGlossary = $this->getConceptGlossary();
        $karmicMasterInsights = $this->buildKarmicAndMasterInsights($calc);

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
            'current_age' => $currentAge,
            'detailed_indicators' => $detailedIndicators,
            'bridge_insights' => $bridgeInsights,
            'career_domains' => $careerDomains,
            'period_cycles_enriched' => $periodCyclesEnriched,
            'twelve_months_enriched' => $twelveMonthsEnriched,
            'year_aspects_default' => $yearAspectsDefault,
            'concept_glossary' => $conceptGlossary,
            'karmic_master_insights' => $karmicMasterInsights,
        ];
    }

    /**
     * Xây dựng danh sách luận giải chi tiết cho toàn bộ chỉ số cốt lõi & mở rộng.
     *
     * @param  array<string, mixed>  $calc
     * @param  array<string, array<string, mixed>>  $indicatorMeanings
     * @param  array<string, mixed>|null  $birthdayMeta
     * @return list<array<string, mixed>>
     */
    protected function buildDetailedIndicators(array $calc, array $indicatorMeanings, ?array $birthdayMeta): array
    {
        $core = $calc['core_numbers'] ?? [];
        $ext = $calc['extended_numbers'] ?? [];
        $items = [];

        $push = function (string $code, string $group, string $label, string $display, array $meta) use (&$items): void {
            $items[] = [
                'code' => $code,
                'group' => $group,
                'label' => $label,
                'display' => $display,
                'title' => (string) ($meta['title'] ?? ''),
                'scope' => (string) ($meta['indicator_scope'] ?? ''),
                'contextual_meaning' => (string) ($meta['contextual_meaning'] ?? ''),
                'traditional_view' => (string) ($meta['traditional_view'] ?? ''),
                'strengths' => (string) ($meta['strengths'] ?? ''),
                'watch_outs' => (string) ($meta['watch_outs'] ?? ''),
                'work_meaning' => (string) ($meta['work_meaning'] ?? ''),
                'love_meaning' => (string) ($meta['love_meaning'] ?? ''),
                'action_hints' => is_array($meta['action_hints'] ?? null) ? $meta['action_hints'] : [],
                'reflection_question' => (string) ($meta['reflection_question'] ?? ''),
            ];
        };

        if (! empty($core['life_path']) && isset($indicatorMeanings['life_path'])) {
            $push('life_path', 'core', 'Chỉ Số Đường Đời (Life Path)', (string) $core['life_path']['display'], $indicatorMeanings['life_path']);
        }
        if (! empty($core['expression']) && isset($indicatorMeanings['expression'])) {
            $push('expression', 'core', 'Chỉ Số Sứ Mệnh (Expression)', (string) $core['expression']['display'], $indicatorMeanings['expression']);
        }
        if (! empty($core['soul_urge']) && isset($indicatorMeanings['soul_urge'])) {
            $push('soul_urge', 'core', 'Chỉ Số Linh Hồn (Soul Urge)', (string) $core['soul_urge']['display'], $indicatorMeanings['soul_urge']);
        }
        if (! empty($core['personality']) && isset($indicatorMeanings['personality'])) {
            $push('personality', 'core', 'Chỉ Số Nhân Cách (Personality)', (string) $core['personality']['display'], $indicatorMeanings['personality']);
        }
        if (! empty($core['birthday'])) {
            $bRaw = (string) ($core['birthday']['raw_day'] ?? '');
            $bRoot = (int) ($core['birthday']['base_root'] ?? 1);
            $bArch = $this->getIndicatorMeaning('life_path', $bRoot);
            $items[] = [
                'code' => 'birthday',
                'group' => 'core',
                'label' => 'Chỉ Số Ngày Sinh (Birthday)',
                'display' => $bRaw,
                'title' => (string) ($birthdayMeta['title'] ?? $bArch['title']),
                'scope' => 'Năng khiếu tự nhiên và món quà bẩm sinh đi theo bạn suốt cuộc đời.',
                'contextual_meaning' => (string) ($birthdayMeta['summary'] ?? $bArch['contextual_meaning']),
                'traditional_view' => "Ngày sinh gốc {$bRaw} (rút gọn nền {$bRoot}) bổ sung sắc thái riêng biệt cho trục Đường Đời.",
                'strengths' => (string) ($birthdayMeta['gift'] ?? $bArch['strengths']),
                'watch_outs' => (string) ($birthdayMeta['balance_note'] ?? $bArch['watch_outs']),
                'work_meaning' => (string) $bArch['work_meaning'],
                'love_meaning' => (string) $bArch['love_meaning'],
                'action_hints' => $bArch['action_hints'],
                'reflection_question' => (string) $bArch['reflection_question'],
            ];
        }
        if (! empty($ext['maturity']) && isset($indicatorMeanings['maturity'])) {
            $push('maturity', 'core', 'Chỉ Số Trưởng Thành (Maturity)', (string) $ext['maturity']['display'], $indicatorMeanings['maturity']);
        }
        if (! empty($ext['attitude']) && isset($indicatorMeanings['attitude'])) {
            $push('attitude', 'extended', 'Chỉ Số Thái Độ (Attitude)', (string) $ext['attitude']['display'], $indicatorMeanings['attitude']);
        }
        if (! empty($ext['balance']) && isset($indicatorMeanings['balance'])) {
            $push('balance', 'extended', 'Chỉ Số Cân Bằng (Balance)', (string) $ext['balance']['display'], $indicatorMeanings['balance']);
        }
        if (! empty($ext['rational_thought']) && isset($indicatorMeanings['rational_thought'])) {
            $push('rational_thought', 'extended', 'Chỉ Số Tư Duy Lý Trí (Rational Thought)', (string) $ext['rational_thought']['display'], $indicatorMeanings['rational_thought']);
        }

        if (! empty($ext['hidden_passion']['numbers'])) {
            $hpNums = array_map('intval', $ext['hidden_passion']['numbers']);
            $primaryHp = $hpNums[0] ?? 1;
            $hpMeta = $this->getIndicatorMeaning('life_path', $primaryHp);
            $items[] = [
                'code' => 'hidden_passion',
                'group' => 'extended',
                'label' => 'Đam Mê Tiềm Ẩn (Hidden Passion)',
                'display' => implode(', ', $hpNums),
                'title' => 'Năng Lượng Lặp Lại Mạnh Nhất Trong Họ Tên',
                'scope' => 'Cho thấy tố chất xuất hiện nhiều lần nhất trong tên khai sinh — nguồn động lực tự nhiên giúp bạn làm việc say mê.',
                'contextual_meaning' => sprintf(
                    'Con số %s xuất hiện nổi trội nhất trong bảng chữ cái họ tên của bạn, khơi dậy khuynh hướng %s (%s). Khi được đặt vào môi trường phát huy đúng mạch năng lượng này, bạn thường tạo ra kết quả vượt trội mà ít thấy kiệt sức.',
                    implode(', ', $hpNums),
                    mb_strtolower((string) $hpMeta['title'], 'UTF-8'),
                    lcfirst((string) $hpMeta['strengths'])
                ),
                'traditional_view' => 'Theo trường phái Pythagorean, Đam Mê Tiềm Ẩn đóng vai trò như “nhiên liệu” nuôi dưỡng chỉ số Sứ Mệnh.',
                'strengths' => (string) $hpMeta['strengths'],
                'watch_outs' => (string) $hpMeta['watch_outs'],
                'work_meaning' => (string) $hpMeta['work_meaning'],
                'love_meaning' => '',
                'action_hints' => $hpMeta['action_hints'],
                'reflection_question' => '',
            ];
        }

        if (! empty($ext['karmic_lessons'])) {
            $missing = array_map('intval', $ext['karmic_lessons']['missing_numbers'] ?? []);
            $missingDesc = [];
            foreach (array_slice($missing, 0, 3) as $mn) {
                $mArch = $this->getIndicatorMeaning('life_path', $mn);
                $missingDesc[] = "Số {$mn} ({$mArch['title']}): cần chủ động rèn ".lcfirst((string) $mArch['strengths']);
            }
            $items[] = [
                'code' => 'karmic_lessons',
                'group' => 'extended',
                'label' => 'Bài Học Rèn Luyện (Số Thiếu Trong Tên)',
                'display' => $missing !== [] ? implode(', ', $missing) : 'Đủ 1–9',
                'title' => $missing !== [] ? 'Kỹ Năng Cần Chủ Động Bồi Đắp' : 'Họ Tên Đầy Đủ Dải Số 1–9',
                'scope' => 'Những con số không xuất hiện trong họ tên khai sinh — không phải điềm xấu mà là vùng kỹ năng cần rèn có ý thức.',
                'contextual_meaning' => $missing !== []
                    ? 'Họ tên khai sinh của bạn chưa có các con số '.implode(', ', $missing).'. Trong đời sống thực tế, đây là những khía cạnh bạn cần học dần qua trải nghiệm thay vì có sẵn theo bản năng: '.implode(' ', $missingDesc)
                    : 'Họ tên khai sinh của bạn quy tụ đủ cả 9 tần số từ 1 đến 9, cho thấy biên độ thích nghi rộng và khả năng tiếp cận đa dạng các bài học cuộc sống.',
                'traditional_view' => 'Hans Decoz gọi đây là Karmic Lessons — các môn học tự chọn để hoàn thiện bản thân một cách cân bằng.',
                'strengths' => 'Nhận diện sớm điểm còn mỏng giúp bạn chủ động học đúng kỹ năng và chọn người đồng hành bổ trợ.',
                'watch_outs' => 'Tránh né tránh những tình huống đòi hỏi kỹ năng thuộc nhóm số thiếu; mỗi lần đối diện là một bước nâng tầm rõ rệt.',
                'work_meaning' => '',
                'love_meaning' => '',
                'action_hints' => [],
                'reflection_question' => '',
            ];
        }

        if (! empty($ext['subconscious_self']['value'])) {
            $subVal = (int) $ext['subconscious_self']['value'];
            $items[] = [
                'code' => 'subconscious_self',
                'group' => 'extended',
                'label' => 'Sức Mạnh Tiềm Thức (Subconscious Self)',
                'display' => (string) $subVal,
                'title' => $subVal >= 7 ? 'Phản Xạ Ổn Định Trước Biến Động' : 'Nhạy Cảm & Cần Điểm Tựa Rõ Ràng',
                'scope' => 'Đo độ vững vàng và cách bạn phản ứng bản năng khi đối mặt với tình huống bất ngờ hoặc áp lực cao.',
                'contextual_meaning' => sprintf(
                    'Chỉ số Tiềm Thức %d/9 phản ánh số lượng nhóm kỹ năng nền tảng đã sẵn có trong họ tên của bạn. %s',
                    $subVal,
                    $subVal >= 7
                        ? 'Bạn có độ tự tin nội tại tốt, khả năng giữ bình tĩnh và xoay xở linh hoạt khi kế hoạch thay đổi đột ngột.'
                        : 'Bạn xử lý áp lực tốt nhất khi có sự chuẩn bị trước, có quy trình rõ ràng và một vài người tin cậy để tham vấn.'
                ),
                'traditional_view' => 'Tính bằng 9 trừ đi số lượng con số thiếu trong họ tên khai sinh.',
                'strengths' => $subVal >= 7 ? 'Ứng biến nhanh, ít hoảng loạn khi gặp việc ngoài dự kiến.' : 'Cẩn trọng, biết chuẩn bị phương án dự phòng kỹ lưỡng.',
                'watch_outs' => $subVal >= 7 ? 'Đừng chủ quan bỏ qua khâu kiểm tra chi tiết vì quá tin vào phản xạ.' : 'Tránh tự tạo áp lực phải giải quyết mọi biến cố một mình trong thời gian quá ngắn.',
                'work_meaning' => '',
                'love_meaning' => '',
                'action_hints' => [],
                'reflection_question' => '',
            ];
        }

        return $items;
    }

    /**
     * Phân tích 2 cầu nối tương quan quan trọng giữa các chỉ số cốt lõi.
     *
     * @param  array<string, mixed>  $calc
     * @return list<array<string, mixed>>
     */
    protected function buildBridgeInsights(array $calc): array
    {
        $core = $calc['core_numbers'] ?? [];
        $bridges = $calc['extended_numbers']['bridges'] ?? [];
        $out = [];

        if (isset($bridges['life_path_expression']) && ! empty($core['expression'])) {
            $diff = (int) $bridges['life_path_expression'];
            $out[] = [
                'title' => 'Tương Quan Đường Đời & Sứ Mệnh',
                'pair' => ($core['life_path']['display'] ?? '').' ↔ '.($core['expression']['display'] ?? ''),
                'bridge_value' => $diff,
                'summary' => $diff === 0
                    ? 'Đường Đời và Sứ Mệnh cùng tần số — con đường bạn đi và năng lực bạn thể hiện ra bên ngoài đồng nhất cao, dễ tập trung sâu vào một mục tiêu lớn.'
                    : "Cầu nối số {$diff} là chiếc chìa khóa giúp hòa hợp giữa bài học Đường Đời ({$core['life_path']['display']}) và cách bạn tạo giá trị qua Sứ Mệnh ({$core['expression']['display']}). ".($this->getIndicatorMeaning('life_path', $diff)['strengths'] ?? ''),
            ];
        }

        if (isset($bridges['soul_personality']) && ! empty($core['soul_urge']) && ! empty($core['personality'])) {
            $diff = (int) $bridges['soul_personality'];
            $out[] = [
                'title' => 'Tương Quan Linh Hồn & Nhân Cách',
                'pair' => ($core['soul_urge']['display'] ?? '').' ↔ '.($core['personality']['display'] ?? ''),
                'bridge_value' => $diff,
                'summary' => $diff === 0
                    ? 'Linh Hồn bên trong và Nhân Cách bên ngoài trùng nhịp — người khác dễ cảm nhận đúng con người thật của bạn ngay từ những lần tiếp xúc đầu tiên.'
                    : "Cầu nối số {$diff} kết nối mong muốn thầm kín bên trong (Linh Hồn {$core['soul_urge']['display']}) với ấn tượng bên ngoài (Nhân Cách {$core['personality']['display']}). Rèn thêm nét ".mb_strtolower((string) ($this->getIndicatorMeaning('life_path', $diff)['title'] ?? ''), 'UTF-8').' giúp bạn sống chân thật mà vẫn hài hòa với môi trường.',
            ];
        }

        return $out;
    }

    /**
     * Tính 6 nhóm năng lực nghề nghiệp từ trọng số các con số thật của hồ sơ (không bịa số ngẫu nhiên).
     *
     * @param  array<string, mixed>  $calc
     * @return array{domains: list<array<string, mixed>>, top_roles: list<string>}
     */
    protected function buildCareerDomains(array $calc): array
    {
        $catalog = [
            'management' => [
                'label' => 'Quản trị & Lãnh đạo',
                'desc' => 'Dẫn dắt đội nhóm, ra quyết định chiến lược, khởi xướng dự án và điều phối nguồn lực.',
                'roles' => ['Quản trị dự án / Sản phẩm', 'Điều hành doanh nghiệp', 'Phát triển kinh doanh & Đối tác'],
                'weights' => [1 => 1.0, 8 => 1.0, 4 => 0.55, 5 => 0.55, 9 => 0.5, 3 => 0.4],
            ],
            'research' => [
                'label' => 'Phân tích & Nghiên cứu',
                'desc' => 'Đào sâu bản chất, tư duy logic, chiến lược, công nghệ, dữ liệu và chuyên môn tinh sâu.',
                'roles' => ['Phân tích dữ liệu / Chiến lược', 'Công nghệ & Kỹ thuật phần mềm', 'Nghiên cứu, Tư vấn chuyên sâu'],
                'weights' => [7 => 1.0, 4 => 0.75, 1 => 0.6, 8 => 0.55, 2 => 0.45, 9 => 0.45],
            ],
            'creative' => [
                'label' => 'Sáng tạo & Nghệ thuật',
                'desc' => 'Thiết kế trải nghiệm, ngôn từ, truyền thông, thương hiệu và giải pháp giàu cảm hứng.',
                'roles' => ['Thiết kế UI/UX & Thương hiệu', 'Sáng tạo nội dung & Truyền thông', 'Nghệ thuật, Kiến trúc, Sự kiện'],
                'weights' => [3 => 1.0, 5 => 0.85, 6 => 0.7, 9 => 0.65, 2 => 0.5, 1 => 0.45],
            ],
            'social' => [
                'label' => 'Con người & Xã hội',
                'desc' => 'Thấu cảm, kết nối cộng đồng, đào tạo, chăm sóc khách hàng, nhân sự và đồng hành tinh thần.',
                'roles' => ['Đào tạo, Giảng dạy & Coaching', 'Quản trị nhân sự & Văn hóa', 'Tư vấn khách hàng & Cộng đồng'],
                'weights' => [2 => 1.0, 6 => 1.0, 9 => 0.9, 3 => 0.7, 5 => 0.5, 7 => 0.35],
            ],
            'technical' => [
                'label' => 'Kỹ thuật & Hệ thống',
                'desc' => 'Xây dựng quy trình chuẩn mực, kiến trúc hệ thống, tối ưu vận hành và bảo đảm độ chính xác.',
                'roles' => ['Kỹ sư hệ thống & Quy trình', 'Kiến trúc, Xây dựng & Sản xuất', 'Quản trị chất lượng & An toàn'],
                'weights' => [4 => 1.0, 7 => 0.85, 1 => 0.6, 8 => 0.65, 6 => 0.4, 2 => 0.35],
            ],
            'operations' => [
                'label' => 'Tài chính & Vận hành',
                'desc' => 'Quản trị ngân sách, kế hoạch thực thi, kiểm soát rủi ro và tạo tăng trưởng bền vững.',
                'roles' => ['Tài chính, Kế toán & Kiểm toán', 'Vận hành & Chuỗi cung ứng', 'Đầu tư & Quản trị tài sản'],
                'weights' => [8 => 1.0, 4 => 0.9, 6 => 0.6, 1 => 0.55, 7 => 0.5, 2 => 0.45],
            ],
        ];

        $core = $calc['core_numbers'] ?? [];
        $ext = $calc['extended_numbers'] ?? [];

        $sources = [
            ['root' => (int) ($core['life_path']['base_root'] ?? 1), 'w' => 3.2],
            ['root' => (int) ($core['birthday']['base_root'] ?? 1), 'w' => 1.3],
            ['root' => (int) ($ext['attitude']['base_root'] ?? 1), 'w' => 1.0],
        ];
        if (! empty($core['expression']['base_root'])) {
            $sources[] = ['root' => (int) $core['expression']['base_root'], 'w' => 2.5];
        }
        if (! empty($core['soul_urge']['base_root'])) {
            $sources[] = ['root' => (int) $core['soul_urge']['base_root'], 'w' => 1.6];
        }
        if (! empty($core['personality']['base_root'])) {
            $sources[] = ['root' => (int) $core['personality']['base_root'], 'w' => 1.2];
        }
        if (! empty($ext['maturity']['base_root'])) {
            $sources[] = ['root' => (int) $ext['maturity']['base_root'], 'w' => 1.1];
        }

        $totalWeight = array_sum(array_column($sources, 'w'));
        $rawScores = [];
        foreach ($catalog as $key => $meta) {
            $sum = 0.0;
            foreach ($sources as $src) {
                $r = max(1, min(9, $src['root']));
                $sum += ($meta['weights'][$r] ?? 0.3) * $src['w'];
            }
            $rawScores[$key] = $totalWeight > 0 ? ($sum / $totalWeight) : 0.5;
        }

        $maxRaw = max($rawScores) ?: 1.0;
        $domains = [];
        foreach ($catalog as $key => $meta) {
            $normalized = (int) round(42 + ($rawScores[$key] / $maxRaw) * 52);
            $domains[] = [
                'code' => $key,
                'label' => $meta['label'],
                'desc' => $meta['desc'],
                'score' => max(38, min(95, $normalized)),
                'roles' => $meta['roles'],
            ];
        }

        usort($domains, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        $topRoles = array_values(array_unique(array_merge(
            $domains[0]['roles'] ?? [],
            $domains[1]['roles'] ?? []
        )));

        return [
            'domains' => $domains,
            'top_roles' => array_slice($topRoles, 0, 5),
        ];
    }

    /**
     * Làm giàu 3 Giai Đoạn Cuộc Đời (Period Cycles) với mốc tuổi & ý nghĩa rõ ràng.
     *
     * @param  array<string, mixed>  $calc
     * @return list<array<string, mixed>>
     */
    protected function buildPeriodCyclesEnriched(array $calc, int $currentAge): array
    {
        $lpRoot = (int) ($calc['core_numbers']['life_path']['base_root'] ?? 1);
        $firstEnd = 36 - $lpRoot;
        $secondEnd = $firstEnd + 27;
        $stages = [
            1 => ['name' => 'Giai Đoạn 1 · Gieo Hạt', 'range' => "Từ đầu đời – {$firstEnd} tuổi", 'start' => 0, 'end' => $firstEnd],
            2 => ['name' => 'Giai Đoạn 2 · Kiến Tạo', 'range' => ($firstEnd + 1)." – {$secondEnd} tuổi", 'start' => $firstEnd + 1, 'end' => $secondEnd],
            3 => ['name' => 'Giai Đoạn 3 · Thu Hoạch', 'range' => 'Từ '.($secondEnd + 1).' tuổi trở đi', 'start' => $secondEnd + 1, 'end' => 120],
        ];

        $out = [];
        foreach ($calc['period_cycles'] ?? [] as $pc) {
            $ord = (int) ($pc['order'] ?? 1);
            $val = (int) ($pc['value'] ?? 1);
            $st = $stages[$ord] ?? $stages[1];
            $arch = $this->getIndicatorMeaning('life_path', $val);
            $out[] = [
                'order' => $ord,
                'stage_name' => $st['name'],
                'source_label' => (string) ($pc['label'] ?? ''),
                'age_range' => $st['range'],
                'value' => $val,
                'title' => (string) $arch['title'],
                'meaning' => (string) $arch['contextual_meaning'],
                'is_current' => $currentAge >= $st['start'] && $currentAge <= $st['end'],
            ];
        }

        return $out;
    }

    /**
     * Làm giàu 12 Tháng Cá Nhân trong năm đang xem.
     *
     * @param  array<string, mixed>  $cycles
     * @return list<array<string, mixed>>
     */
    protected function buildTwelveMonthsEnriched(array $cycles): array
    {
        $out = [];
        foreach ($cycles['twelve_months'] ?? [] as $m) {
            $val = (string) ($m['value'] ?? 1);
            $out[] = [
                'month' => (int) ($m['month'] ?? 1),
                'value' => (int) $val,
                'is_current' => (bool) ($m['is_current'] ?? false),
                'meaning' => (string) (self::$cyclesData['personal_months'][$val] ?? ''),
            ];
        }

        return $out;
    }

    /**
     * 4 khía cạnh thực tế của Năm Cá Nhân (Sự nghiệp, Tài chính, Tình cảm, Phát triển bản thân).
     *
     * @return array{career: string, finance: string, love: string, growth: string}
     */
    protected function buildPersonalYearAspects(int $personalYear): array
    {
        $map = [
            1 => [
                'career' => 'Thuận lợi mở đầu dự án mới, đề xuất ý tưởng riêng hoặc bước sang vai trò tự chủ hơn. Nên chọn 1–2 hướng mũi nhọn thay vì dàn trải.',
                'finance' => 'Giai đoạn gieo hạt thu nhập mới; ưu tiên giữ quỹ dự phòng ổn định và đầu tư vào kỹ năng tạo dòng tiền dài hạn.',
                'love' => 'Tình cảm cần sự rõ ràng và tôn trọng không gian cá nhân; người độc thân dễ gặp mối duyên mới khi chủ động mở rộng môi trường sống.',
                'growth' => 'Thiết lập kỷ luật cá nhân cho cả chu kỳ 9 năm tới — mỗi thói quen tốt bắt đầu năm nay đều tạo sức bật lâu dài.',
            ],
            2 => [
                'career' => 'Mạnh về hợp tác, xây dựng đội ngũ, đàm phán tinh tế và hoàn thiện chất lượng sản phẩm thay vì chạy đua tốc độ.',
                'finance' => 'Tài chính đi vào nhịp ổn định, nên rà soát các thỏa thuận chung minh bạch và tránh quyết định chi tiêu theo cảm xúc nhất thời.',
                'love' => 'Năm rất đẹp cho kết nối sâu sắc, lắng nghe và hàn gắn; giao tiếp dịu dàng nhưng thẳng thắn sẽ đưa quan hệ sang tầng tin cậy mới.',
                'growth' => 'Rèn sự kiên nhẫn, trí tuệ cảm xúc và năng lực lắng nghe trước khi phản hồi.',
            ],
            3 => [
                'career' => 'Rộng cửa cho sáng tạo, truyền thông, mở rộng mạng lưới đối tác và thể hiện dấu ấn cá nhân rõ nét hơn.',
                'finance' => 'Có thêm cơ hội tăng thu từ năng lực giao tiếp/sáng tạo, nhưng cần đặt giới hạn cho các khoản chi hưởng thụ bất chợt.',
                'love' => 'Bầu không khí tình cảm tươi sáng, giàu niềm vui; nên dành thời gian hẹn hò làm mới cảm xúc cùng người thương.',
                'growth' => 'Biến ý tưởng hay thành sản phẩm hoàn chỉnh thay vì để dở dang nhiều dự định cùng lúc.',
            ],
            4 => [
                'career' => 'Năm xây móng chắc chắn: chuẩn hóa quy trình, nâng trình độ chuyên môn sâu và chứng minh uy tín bằng kết quả bền bỉ.',
                'finance' => 'Phù hợp lập kế hoạch ngân sách chặt chẽ, tích lũy tài sản an toàn và cắt bỏ các khoản chi rò rỉ không cần thiết.',
                'love' => 'Tình yêu thể hiện qua cam kết thực tế và sự đồng hành đáng tin cậy; thêm một chút mềm mại sẽ giúp bầu không khí gia đình nhẹ nhàng hơn.',
                'growth' => 'Chăm sóc sức khỏe thể chất, giấc ngủ và nhịp sinh hoạt đều đặn — đây là vốn quý nhất trong năm số 4.',
            ],
            5 => [
                'career' => 'Nhiều chuyển động mới, dễ gặp cơ hội thay đổi vị trí, mở rộng thị trường hoặc thử phương pháp làm việc linh hoạt hơn.',
                'finance' => 'Dòng tiền biến thiên nhanh theo nhịp cơ hội; cần giữ nguyên tắc quản trị rủi ro và tránh quyết định vội vàng khi chưa đủ thông tin.',
                'love' => 'Cần sự tin tưởng và cảm giác cùng nhau trải nghiệm điều mới mẻ thay vì kiểm soát hay lặp lại đơn điệu.',
                'growth' => 'Chủ động học công cụ mới, mở rộng góc nhìn nhưng vẫn giữ 2–3 thói quen neo để không bị cuốn trôi.',
            ],
            6 => [
                'career' => 'Thuận lợi ở vai trò chăm sóc khách hàng, quản trị đội ngũ, đào tạo và các công việc tạo giá trị bền vững cho cộng đồng.',
                'finance' => 'Chi tiêu thường hướng về tổ ấm, gia đình hoặc nâng cấp không gian sống; nên phân bổ quỹ gia đình và quỹ cá nhân rõ ràng.',
                'love' => 'Năm trọng tâm của tình yêu, hôn nhân và hòa khí gia đạo — rất phù hợp để củng cố cam kết và chăm chút đời sống chung.',
                'growth' => 'Học cách yêu thương có ranh giới lành mạnh: chăm lo người thân mà không bỏ quên sức khỏe tinh thần của chính mình.',
            ],
            7 => [
                'career' => 'Phù hợp đào sâu chuyên môn, nghiên cứu chiến lược, nâng cấp chất lượng cốt lõi hơn là mở rộng ồ ạt bề nổi.',
                'finance' => 'Ưu tiên bảo toàn vốn, minh bạch sổ sách và tránh chạy theo các kênh đầu tư ngắn hạn thiếu kiểm chứng.',
                'love' => 'Cần những cuộc trò chuyện có chiều sâu và sự tôn trọng khoảng lặng riêng; chia sẻ suy nghĩ thành lời giúp tránh hiểu lầm.',
                'growth' => 'Năm vàng để đọc sách, học khóa chuyên sâu, nghỉ ngơi hồi phục và nhìn rõ hướng đi thật sự của bản thân.',
            ],
            8 => [
                'career' => 'Nhịp tăng tốc rõ rệt về hiệu suất, vị thế và khả năng điều hành — phù hợp chốt các mục tiêu lớn đã chuẩn bị từ trước.',
                'finance' => 'Trọng tâm gặt hái và tối ưu nguồn lực; quản trị tài chính minh bạch, công bằng sẽ giúp thành quả giữ được lâu bền.',
                'love' => 'Cần chủ động tách giờ làm việc khỏi giờ gia đình để sự bận rộn không làm nguội sự ấm áp với người thương.',
                'growth' => 'Rèn bản lĩnh ra quyết định dứt khoát đi kèm đạo đức nghề nghiệp và sự cân bằng giữa thành tựu với bình an.',
            ],
            9 => [
                'career' => 'Năm tổng kết chu kỳ 9 năm: hoàn tất các dự án lớn, chia sẻ kinh nghiệm và nhẹ nhàng khép lại hướng đi không còn phù hợp.',
                'finance' => 'Rà soát, tất toán các khoản tồn đọng và sắp xếp lại cơ cấu tài sản gọn gàng trước khi bước vào chu kỳ số 1 năm tới.',
                'love' => 'Bao dung, tháo gỡ những vướng mắc cũ bằng đối thoại chân thành để tình cảm bước sang giai đoạn trưởng thành hơn.',
                'growth' => 'Buông nhẹ thói quen cũ không còn phục vụ mục tiêu mới, dọn gọn không gian sống và tâm trí cho chặng đường sắp mở.',
            ],
        ];

        return $map[$personalYear] ?? $map[1];
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
     * Từ điển chuyên sâu các khái niệm Thần Số Học.
     *
     * Hệ thống tham chiếu:
     * - Thần số học Pythagorean theo phương pháp Hans Decoz.
     * - Biểu đồ ngày sinh và mũi tên theo David A. Phillips.
     *
     * Nguyên tắc luận giải:
     * - Phân biệt rõ từng chỉ số và phạm vi diễn giải.
     * - Giải thích bản chất, biểu hiện và khả năng ứng dụng.
     * - Không tuyệt đối hóa ý nghĩa của các con số.
     * - Không dự đoán chắc chắn các sự kiện trong tương lai.
     * - Không thay thế đánh giá khoa học, tâm lý hoặc y khoa.
     *
     * @return array<string, array{
     *     key: string,
     *     title: string,
     *     group: string,
     *     formula: string,
     *     summary: string,
     *     how_to_apply: string
     * }>
     */
    public function getConceptGlossary(): array
    {
        return [

            // =====================================================
            // I. NHÓM CHỈ SỐ CỐT LÕI
            // =====================================================

            'life_path' => [
                'key' => 'life_path',
                'title' => 'Chỉ Số Đường Đời (Life Path)',
                'group' => 'Chỉ số cốt lõi',

                'formula' => 'Sử dụng đầy đủ ngày, tháng và năm sinh dương lịch. Rút gọn riêng từng thành phần về một chữ số từ 1 đến 9, giữ nguyên 11, 22 hoặc 33 nếu xuất hiện trong quá trình rút gọn. Sau đó cộng ba kết quả và tiếp tục rút gọn theo cùng nguyên tắc. Ví dụ: người sinh ngày 24/08/1995 có ngày sinh 24 → 6, tháng sinh 8, năm sinh 1995 → 24 → 6. Tổng 6 + 8 + 6 = 20 → 2. Chỉ số Đường Đời là 2.',

                'summary' => 'Chỉ số Đường Đời được xem là một trong những nền tảng quan trọng nhất của hệ thống Thần Số Học Pythagorean. Chỉ số này mô tả chủ đề phát triển xuyên suốt cuộc đời, những phẩm chất một người được khuyến khích hoàn thiện và các dạng trải nghiệm có thể được sử dụng để nhận thức sâu sắc hơn về bản thân.

                Đường Đời không đơn thuần trả lời câu hỏi bạn là người như thế nào, mà tập trung vào câu hỏi bạn có thể trưởng thành theo hướng nào. Chẳng hạn, Đường Đời 1 gắn với tính tự chủ, khả năng khởi xướng và trách nhiệm đối với quyết định cá nhân. Đường Đời 2 nhấn mạnh sự hợp tác, khả năng lắng nghe và dung hòa khác biệt. Đường Đời 4 hướng đến tính tổ chức, sự kiên trì và việc xây dựng nền tảng ổn định.

                Mỗi Đường Đời đều có mặt thuận lợi và mặt cần điều chỉnh. Một phẩm chất có thể trở thành thế mạnh khi được sử dụng đúng hoàn cảnh, nhưng cũng có thể tạo ra trở ngại nếu bị đẩy đến mức cực đoan. Độc lập có thể chuyển thành cô lập, trách nhiệm có thể trở thành kiểm soát, còn sự nhạy cảm có thể dẫn đến việc quá phụ thuộc vào đánh giá của người khác.

                Chỉ số này không phải bản án số phận, không quy định một nghề nghiệp bắt buộc và không quyết định trước mức độ thành công. Nó nên được hiểu như một hệ quy chiếu biểu tượng để xem xét hành trình phát triển cá nhân.',

                'how_to_apply' => 'Hãy sử dụng Đường Đời để nhìn lại những lựa chọn quan trọng trong học tập, công việc, tình cảm và cách tổ chức cuộc sống. Thay vì hỏi mình có bắt buộc phải sống theo con số này hay không, hãy tự hỏi những phẩm chất mà con số đề cập có thực sự xuất hiện trong trải nghiệm của mình không.

                Ví dụ, nếu chủ đề Đường Đời liên quan đến sự độc lập, bạn có thể xem xét khả năng tự đưa ra quyết định, chịu trách nhiệm và xây dựng ranh giới cá nhân. Nếu chủ đề liên quan đến sự hợp tác, hãy đánh giá cách mình lắng nghe, giải quyết mâu thuẫn và duy trì tiếng nói riêng trong tập thể.

                Khi luận giải chuyên sâu, cần kết hợp Đường Đời với Sứ Mệnh để hiểu cách triển khai năng lực, Linh Hồn để nhận diện động lực bên trong, Ngày Sinh để xem xét thế mạnh bổ trợ và Trưởng Thành để tham khảo định hướng dài hạn. Mọi kết luận cần được kiểm chứng bằng hành vi, hoàn cảnh và trải nghiệm thực tế của chính bạn.',
            ],

            'expression' => [
                'key' => 'expression',
                'title' => 'Chỉ Số Sứ Mệnh (Expression / Destiny)',
                'group' => 'Chỉ số cốt lõi',

                'formula' => 'Sử dụng toàn bộ họ, tên đệm và tên riêng lúc khai sinh. Quy đổi từng chữ cái theo bảng Pythagorean: 1 = A, J, S; 2 = B, K, T; 3 = C, L, U; 4 = D, M, V; 5 = E, N, W; 6 = F, O, X; 7 = G, P, Y; 8 = H, Q, Z; 9 = I, R. Theo phương pháp Hans Decoz, tính và rút gọn riêng từng thành phần họ tên, sau đó cộng các kết quả và rút gọn lần cuối, giữ Master Numbers tại các bước được quy định. Đối với tên tiếng Việt, hệ thống cần thống nhất quy tắc chuyển về chữ cái Latin A–Z, chẳng hạn Đ thành D và các chữ có dấu thành chữ cái tương ứng.',

                'summary' => 'Chỉ số Sứ Mệnh, còn được gọi là chỉ số Biểu Đạt, tập trung vào cách một người sử dụng năng lực, phẩm chất và nguồn lực cá nhân để tạo ra giá trị trong cuộc sống. Nếu Đường Đời đề cập đến chủ đề phát triển dài hạn thì Sứ Mệnh mô tả phương thức mà một người có thể sử dụng để tham gia vào hành trình đó.

                Khái niệm Sứ Mệnh không có nghĩa mỗi người sinh ra đều được giao một nhiệm vụ duy nhất và bắt buộc phải hoàn thành. Trong hệ thống luận giải, đây là nhóm đặc điểm liên quan đến phương thức hành động, cách tổ chức công việc, khả năng biểu đạt và những dạng đóng góp có thể mang lại cảm giác phát huy được bản thân.

                Ví dụ, Sứ Mệnh 3 thường được liên hệ với biểu đạt ý tưởng, giao tiếp và sáng tạo. Sứ Mệnh 4 nhấn mạnh khả năng tổ chức, duy trì quy trình và xây dựng kết quả bền vững. Sứ Mệnh 8 tập trung vào chủ đề quản trị, trách nhiệm, nguồn lực và hiệu quả thực tế.

                Một người có thể có Đường Đời thiên về tự do, trải nghiệm nhưng Sứ Mệnh thiên về tổ chức và kỷ luật. Hai chỉ số này không loại trừ nhau. Chúng có thể gợi ý việc sử dụng năng lực xây dựng hệ thống để tạo ra một cuộc sống linh hoạt hơn.',

                'how_to_apply' => 'Đối chiếu ý nghĩa của Sứ Mệnh với những công việc bạn từng thực hiện tốt, những kỹ năng bạn muốn phát triển và loại trách nhiệm mà bạn cảm thấy có khả năng đảm nhận.

                Trong lựa chọn nghề nghiệp, không nên giới hạn bản thân vào một danh sách ngành nghề cố định theo con số. Hãy tìm hiểu phẩm chất cốt lõi mà chỉ số đề cập, sau đó xác định những môi trường cho phép bạn thực hành phẩm chất đó.

                Ví dụ, khả năng sáng tạo có thể được phát triển trong nghệ thuật, marketing, thiết kế sản phẩm, nghiên cứu hoặc lập trình. Khả năng tổ chức có thể được vận dụng trong quản lý dự án, vận hành, sản xuất hoặc quản trị doanh nghiệp.

                Sứ Mệnh phát huy giá trị tham khảo tốt nhất khi được đặt cạnh Đường Đời, sở thích thực tế, trình độ chuyên môn, kinh nghiệm và điều kiện sống của mỗi người.',
            ],

            'soul_urge' => [
                'key' => 'soul_urge',
                'title' => 'Chỉ Số Linh Hồn (Soul Urge / Heart’s Desire)',
                'group' => 'Chỉ số cốt lõi',

                'formula' => 'Lấy các nguyên âm trong toàn bộ họ tên khai sinh, quy đổi theo bảng Pythagorean. Các nguyên âm cơ bản gồm A, E, I, O, U. Chữ Y mang giá trị 7 và được phân loại theo vai trò ngữ âm cùng quy ước xử lý tên của hệ thống. Tính tổng nguyên âm riêng từng thành phần họ tên, rút gọn, sau đó cộng các kết quả và rút gọn lần cuối, giữ 11, 22, 33 theo quy tắc của phương pháp đã chọn. Mỗi chữ cái chỉ được tính vào nhóm nguyên âm hoặc phụ âm, không tính đồng thời vào cả hai.',

                'summary' => 'Chỉ số Linh Hồn tập trung vào thế giới nội tâm, đặc biệt là những mong muốn, giá trị và nhu cầu tinh thần có thể ảnh hưởng đến cảm giác hài lòng của một người. Đây là khía cạnh mà người khác không nhất thiết nhận ra thông qua cách giao tiếp, nghề nghiệp hoặc hình ảnh bên ngoài.

                Trong cuộc sống, một người có thể đạt được nhiều thành tựu nhưng vẫn cảm thấy thiếu ý nghĩa. Ngược lại, có những hoạt động không mang lại nhiều sự công nhận từ xã hội nhưng lại tạo ra cảm giác thỏa mãn sâu sắc. Chỉ số Linh Hồn được sử dụng như một cách tiếp cận biểu tượng để suy ngẫm về sự khác biệt giữa thành tựu bên ngoài và nhu cầu bên trong.

                Chẳng hạn, Linh Hồn 2 hướng sự chú ý đến cảm giác được kết nối và thấu hiểu. Linh Hồn 5 nhấn mạnh nhu cầu tự do, khám phá và thay đổi. Linh Hồn 7 liên hệ với chiều sâu suy nghĩ, không gian riêng và mong muốn tìm hiểu bản chất của vấn đề.

                Chỉ số Linh Hồn khác với Sứ Mệnh ở chỗ một bên tập trung vào điều khiến bạn cảm thấy có ý nghĩa, trong khi bên còn lại tập trung vào cách bạn triển khai năng lực. Nó cũng khác với Nhân Cách, vốn mô tả phương diện bạn biểu hiện ra bên ngoài.

                Những khác biệt giữa các chỉ số không nhất thiết là mâu thuẫn tiêu cực. Một người có thể thích không gian riêng nhưng vẫn giao tiếp tốt, hoặc có nhu cầu tự do nhưng vẫn đề cao trách nhiệm trong gia đình.',

                'how_to_apply' => 'Hãy xem xét những thời điểm bạn cảm thấy hài lòng nhất, những điều khiến bạn mất động lực và những nhu cầu thường bị bỏ qua khi phải đáp ứng kỳ vọng của người khác.

                Trong công việc, hãy phân biệt giữa điều bạn làm tốt và điều bạn thực sự muốn dành thời gian thực hiện. Trong tình cảm, hãy tập diễn đạt rõ ràng nhu cầu về sự quan tâm, tôn trọng, an toàn, riêng tư hoặc tự do thay vì kỳ vọng đối phương tự hiểu.

                Nếu Linh Hồn và Nhân Cách có chủ đề khác nhau, hãy sử dụng sự khác biệt đó để quan sát cách mình thể hiện mong muốn bên trong. Không cần thay đổi toàn bộ hình ảnh cá nhân; điều quan trọng là tìm được cách giao tiếp chân thành, phù hợp với hoàn cảnh và không làm tổn hại đến nhu cầu chính đáng của bản thân hay người khác.',
            ],

            'personality' => [
                'key' => 'personality',
                'title' => 'Chỉ Số Nhân Cách (Personality)',
                'group' => 'Chỉ số cốt lõi',

                'formula' => 'Quy đổi các phụ âm trong toàn bộ họ tên khai sinh theo bảng Pythagorean. Tính tổng riêng từng thành phần họ tên, rút gọn các tổng thành phần, sau đó cộng lại và tiếp tục rút gọn, giữ Master Numbers theo quy tắc áp dụng cho chỉ số này. Chữ Y chỉ được tính vào Nhân Cách khi được xác định là phụ âm theo quy ước xử lý tên đã sử dụng ở chỉ số Linh Hồn.',

                'summary' => 'Chỉ số Nhân Cách đề cập đến phương diện một người thể hiện ra thế giới bên ngoài, bao gồm phong cách giao tiếp, mức độ cởi mở, cách thiết lập ranh giới và những đặc điểm có thể góp phần hình thành ấn tượng ban đầu.

                Trong Thần Số Học, Nhân Cách không đại diện cho toàn bộ tính cách và cũng không phải công cụ đọc suy nghĩ của người khác. Nó chỉ tập trung vào lớp biểu hiện xã hội, trong khi Linh Hồn hướng đến động lực nội tâm và Sứ Mệnh hướng đến phương thức sử dụng năng lực.

                Chẳng hạn, Nhân Cách 1 thường được diễn giải thông qua phong thái chủ động và độc lập. Nhân Cách 4 hướng đến hình ảnh có tổ chức và thận trọng. Nhân Cách 7 có thể được liên hệ với sự kín đáo, quan sát và chọn lọc trong giao tiếp.

                Tuy nhiên, cùng một cách biểu hiện có thể được người khác cảm nhận theo nhiều hướng. Sự thận trọng có thể tạo cảm giác đáng tin cậy nhưng cũng có thể bị hiểu là thiếu linh hoạt. Sự độc lập có thể tạo ấn tượng mạnh mẽ nhưng đôi khi khiến người khác khó nhận ra nhu cầu được hỗ trợ.

                Sự khác biệt giữa hình ảnh bên ngoài và nội tâm là hiện tượng bình thường. Không nên sử dụng chỉ số Nhân Cách để kết luận một người giả tạo, che giấu bản chất hoặc thiếu chân thành.',

                'how_to_apply' => 'Sử dụng chỉ số Nhân Cách như một gợi ý để quan sát cách bạn giới thiệu bản thân, trao đổi ý kiến, phản hồi góp ý và xây dựng mối quan hệ trong những môi trường khác nhau.

                Nếu nhận thấy người khác thường hiểu sai ý định của mình, hãy xem xét cách lựa chọn từ ngữ, thái độ và thời điểm giao tiếp. Bạn cũng có thể chủ động hỏi những người đáng tin cậy về cách họ cảm nhận khi làm việc hoặc trò chuyện với bạn.

                Kết hợp Nhân Cách với Linh Hồn để tìm hiểu mức độ phù hợp giữa nhu cầu bên trong và cách biểu hiện bên ngoài. Mục tiêu không phải tạo ra một hình ảnh hoàn hảo mà là nâng cao khả năng giao tiếp chân thành, rõ ràng và phù hợp với từng hoàn cảnh.',
            ],

            'birthday' => [
                'key' => 'birthday',
                'title' => 'Chỉ Số Ngày Sinh (Birthday Number)',
                'group' => 'Chỉ số cốt lõi',

                'formula' => 'Lấy trực tiếp ngày sinh dương lịch trong tháng, từ 1 đến 31, không sử dụng tháng hoặc năm sinh. Giữ nguyên giá trị ngày sinh gốc để luận giải đặc điểm riêng, đồng thời xác định số nền thông qua phép cộng các chữ số. Ví dụ, ngày sinh 24 được biểu diễn là 24/6; ngày sinh 11 có thể biểu diễn là 11/2; ngày sinh 22 là 22/4.',

                'summary' => 'Chỉ số Ngày Sinh mô tả một nhóm phẩm chất hoặc năng lực bổ trợ trong hệ thống luận giải Thần Số Học. So với phạm vi rộng của Đường Đời, Ngày Sinh có phạm vi hẹp hơn và tập trung vào những đặc điểm có thể được sử dụng trong các hoạt động cụ thể.

                Điểm đáng chú ý là hai người có cùng số nền Ngày Sinh chưa chắc được luận giải giống nhau hoàn toàn. Chẳng hạn, ngày 6, ngày 15 và ngày 24 đều quy về số 6, nhưng cấu trúc số kép của từng ngày có thể mang thêm sắc thái biểu tượng riêng.

                Điều này giải thích vì sao hệ thống cần lưu cả ngày sinh gốc và số nền thay vì chỉ giữ lại kết quả rút gọn cuối cùng. Số nền hỗ trợ nhận diện chủ đề chung, còn ngày sinh gốc cung cấp thêm ngữ cảnh trong những phương pháp có luận giải số kép.

                Ngày Sinh không phải thước đo trí thông minh, năng khiếu thực tế hoặc mức độ thành công. Những năng lực được đề cập vẫn cần được hình thành và phát triển thông qua học tập, thực hành và môi trường sống.',

                'how_to_apply' => 'Đọc chỉ số Ngày Sinh như một gợi ý bổ sung để tìm hiểu những phẩm chất bạn muốn khai thác trong học tập và công việc.

                Hãy đối chiếu phần luận giải với những việc bạn tiếp thu nhanh, những nhiệm vụ bạn có thể duy trì tốt và những phản hồi thực tế từ người từng học tập hoặc làm việc cùng bạn.

                Nếu Ngày Sinh và Đường Đời có chủ đề tương đồng, bạn có thể tìm hiểu cách kết hợp những phẩm chất đó. Nếu chúng khác nhau, hãy xem Ngày Sinh như một góc nhìn bổ trợ chứ không phải dấu hiệu xung đột. Ví dụ, Đường Đời thiên về trải nghiệm có thể được hỗ trợ bởi phẩm chất tổ chức và tính trách nhiệm của chỉ số Ngày Sinh.',
            ],

            'maturity' => [
                'key' => 'maturity',
                'title' => 'Chỉ Số Trưởng Thành (Maturity)',
                'group' => 'Chỉ số cốt lõi',

                'formula' => 'Cộng chỉ số Đường Đời với chỉ số Sứ Mệnh, sau đó rút gọn kết quả về một chữ số hoặc giữ nguyên Master Number nếu tổng đạt 11, 22 hoặc 33 theo quy tắc rút gọn. Ví dụ, Đường Đời 4 và Sứ Mệnh 7 tạo thành Trưởng Thành 11/2.',

                'summary' => 'Chỉ số Trưởng Thành mô tả hướng hội tụ giữa hành trình phát triển cá nhân và cách một người sử dụng năng lực của mình sau khi tích lũy kinh nghiệm sống. Trong phương pháp của Hans Decoz, chủ đề của chỉ số này thường được cho là trở nên đáng chú ý hơn từ khoảng 35 tuổi trở đi, nhưng đây không phải mốc chuyển đổi tâm lý cố định.

                Khi còn trẻ, con người thường dành nhiều thời gian để thử nghiệm, học tập, xây dựng vị trí xã hội và tìm kiếm cơ hội. Khi kinh nghiệm tích lũy nhiều hơn, các ưu tiên có thể thay đổi. Một người từng đặt nặng thành tựu vật chất có thể bắt đầu quan tâm nhiều hơn đến gia đình, sự tự do, chiều sâu tinh thần hoặc giá trị mình để lại cho cộng đồng.

                Chỉ số Trưởng Thành được dùng để suy ngẫm về sự chuyển dịch đó. Nó không thay thế Đường Đời hay Sứ Mệnh mà được hình thành từ sự kết hợp của hai chỉ số này.

                Trưởng Thành không có nghĩa những phẩm chất tương ứng chỉ xuất hiện khi bước vào trung niên. Một người có thể nhận thấy và phát triển chúng từ sớm. Đồng thời, không có cơ sở để khẳng định tất cả mọi người sẽ thay đổi theo đúng một kịch bản khi đến độ tuổi nhất định.',

                'how_to_apply' => 'Sử dụng Trưởng Thành để suy nghĩ về mục tiêu dài hạn thay vì chỉ tập trung vào những thành tựu trước mắt. Hãy tự hỏi sau nhiều năm làm việc, điều gì sẽ khiến bạn cảm thấy cuộc sống của mình có giá trị và đáng hài lòng.

                Có thể xây dựng kế hoạch phát triển theo từng giai đoạn từ 3 đến 5 năm, bao gồm năng lực chuyên môn, sức khỏe, tài chính, gia đình và những mục tiêu cá nhân có ý nghĩa.

                Nếu chủ đề Trưởng Thành khác với những ưu tiên hiện tại, không cần vội vàng thay đổi công việc hoặc lối sống. Hãy thử nghiệm từng bước thông qua học tập, trải nghiệm mới và các dự án nhỏ để xác định hướng phát triển thực sự phù hợp với mình.',
            ],

            // =====================================================
            // II. NHÓM CHỈ SỐ MỞ RỘNG
            // =====================================================

            'attitude' => [
                'key' => 'attitude',
                'title' => 'Chỉ Số Thái Độ (Attitude)',
                'group' => 'Chỉ số mở rộng',

                'formula' => 'Cộng ngày sinh với tháng sinh dương lịch, sau đó rút gọn hoàn toàn về một chữ số từ 1 đến 9. Không sử dụng năm sinh và không giữ Master Numbers trong phép tính này. Ví dụ, người sinh ngày 24/08 có Thái Độ = 24 + 8 = 32 → 5.',

                'summary' => 'Chỉ số Thái Độ tập trung vào cách một người tiếp cận những hoàn cảnh mới, phản ứng trước sự thay đổi và thể hiện khuynh hướng ứng xử ban đầu khi tương tác với môi trường xung quanh.

                Chỉ số này có điểm gần với Nhân Cách nhưng không hoàn toàn giống nhau. Nhân Cách đề cập đến hình ảnh và phương diện biểu hiện xã hội, trong khi Thái Độ tập trung nhiều hơn vào cách tiếp nhận và phản hồi một tình huống.

                Ví dụ, Thái Độ 1 có thể được diễn giải thông qua khuynh hướng chủ động xử lý vấn đề. Thái Độ 4 hướng đến sự thận trọng và nhu cầu có cấu trúc rõ ràng. Thái Độ 5 gắn với khả năng thích nghi, sự linh hoạt và việc tìm kiếm phương án mới.

                Phản ứng ban đầu không nhất thiết phản ánh quyết định cuối cùng. Một người có thể tỏ ra thận trọng khi mới tiếp xúc với vấn đề nhưng sau khi có đủ thông tin lại đưa ra quyết định rất nhanh chóng. Hành vi còn chịu ảnh hưởng của kinh nghiệm, trạng thái cảm xúc và mức độ an toàn của hoàn cảnh.',

                'how_to_apply' => 'Quan sát phản ứng của mình trong những tình huống như bắt đầu công việc mới, nhận góp ý bất ngờ, thay đổi kế hoạch hoặc gặp người chưa quen.

                Khi nhận thấy một kiểu phản ứng lặp lại, hãy đánh giá xem nó đang hỗ trợ hay cản trở mục tiêu của bạn. Nếu có xu hướng quyết định quá nhanh, có thể tập thói quen kiểm tra thông tin trước khi hành động. Nếu thường do dự, hãy xác định tiêu chí và thời hạn ra quyết định rõ ràng.

                Kết hợp Thái Độ với Nhân Cách và Cân Bằng để có thêm góc nhìn về cách tiếp cận vấn đề, giao tiếp với người khác và điều chỉnh phản ứng khi chịu áp lực.',
            ],

            'balance' => [
                'key' => 'balance',
                'title' => 'Chỉ Số Cân Bằng (Balance)',
                'group' => 'Chỉ số mở rộng',

                'formula' => 'Lấy chữ cái đầu tiên của từng thành phần trong họ tên khai sinh, quy đổi theo bảng Pythagorean, cộng lại và rút gọn về một chữ số từ 1 đến 9. Không giữ Master Numbers. Ví dụ, tên Lê An có hai chữ cái đầu L = 3 và A = 1. Chỉ số Cân Bằng là 3 + 1 = 4.',

                'summary' => 'Chỉ số Cân Bằng được sử dụng để suy ngẫm về cách một người có thể điều chỉnh hành vi khi gặp áp lực, mâu thuẫn hoặc những tình huống làm mất đi cảm giác ổn định.

                Khác với Thái Độ, vốn đề cập đến cách tiếp cận ban đầu, Cân Bằng tập trung vào phẩm chất có thể được thực hành để xử lý vấn đề một cách bình tĩnh và có chủ đích hơn.

                Ví dụ, Cân Bằng 2 gợi ý chú trọng lắng nghe và hợp tác. Cân Bằng 4 nhấn mạnh việc tổ chức thông tin, xác định trình tự xử lý và tập trung vào các bước có thể kiểm soát. Cân Bằng 5 hướng đến khả năng thay đổi góc nhìn và tìm phương án linh hoạt.

                Chỉ số này không đo lường sức khỏe tinh thần, khả năng chịu đựng căng thẳng hay mức độ ổn định cảm xúc. Một người vẫn có thể cần sự hỗ trợ từ gia đình, bạn bè hoặc chuyên gia khi gặp những khó khăn vượt quá khả năng tự giải quyết.',

                'how_to_apply' => 'Khi gặp áp lực, hãy phân biệt sự việc thực tế với cảm xúc và suy đoán của bản thân. Xác định điều gì đang xảy ra, phần nào nằm trong khả năng kiểm soát và hành động nào có thể thực hiện ngay.

                Sử dụng chủ đề của Cân Bằng để xây dựng những thói quen ứng phó cụ thể. Nếu chủ đề liên quan đến tính tổ chức, hãy chia vấn đề thành các bước nhỏ. Nếu liên quan đến hợp tác, hãy tìm người có thể hỗ trợ hoặc trao đổi để làm rõ hiểu lầm.

                Trong các tình huống liên quan đến an toàn, sức khỏe hoặc quyết định quan trọng, cần ưu tiên thông tin thực tế và sự hỗ trợ chuyên môn thay vì dựa vào chỉ số Thần Số Học.',
            ],

            'rational_thought' => [
                'key' => 'rational_thought',
                'title' => 'Chỉ Số Tư Duy Lý Trí (Rational Thought)',
                'group' => 'Chỉ số mở rộng',

                'formula' => 'Quy đổi toàn bộ chữ cái trong tên riêng lúc khai sinh (First Name / Given Name) theo bảng Pythagorean. Cộng tổng giá trị đó với ngày sinh dương lịch, sau đó rút gọn hoàn toàn về một chữ số từ 1 đến 9. Không giữ Master Numbers. Ví dụ, tên riêng An có A = 1, N = 5. Nếu sinh ngày 14 thì Tư Duy Lý Trí = 1 + 5 + 14 = 20 → 2.',

                'summary' => 'Chỉ số Tư Duy Lý Trí đề cập đến phương thức một người có thể sử dụng để tiếp nhận thông tin, phân tích vấn đề, hình thành nhận định và lựa chọn phương án xử lý.

                Một số cách tư duy chú trọng dữ kiện, quy trình và tính nhất quán. Những cách khác ưu tiên khả năng nhìn nhận tổng thể, liên hệ kinh nghiệm, cân nhắc tác động đến con người hoặc phát triển nhiều phương án mới. Trong Thần Số Học, các con số được dùng để biểu tượng hóa những khuynh hướng tiếp cận khác nhau.

                Chẳng hạn, chủ đề số 4 thường liên hệ với phương pháp có cấu trúc và từng bước rõ ràng. Chủ đề số 7 hướng đến phân tích chiều sâu và tìm hiểu nguyên nhân. Chủ đề số 5 nhấn mạnh khả năng linh hoạt và xem xét nhiều lựa chọn.

                Chỉ số Tư Duy Lý Trí không phải bài kiểm tra IQ, không đo năng lực học thuật và không cho phép kết luận một người thông minh hay kém thông minh. Tất cả các phương thức tư duy đều có thể phát huy giá trị tùy thuộc vào loại vấn đề và hoàn cảnh sử dụng.',

                'how_to_apply' => 'Hãy quan sát cách mình thường giải quyết vấn đề: thu thập dữ liệu trước, trao đổi với người khác, phân tích từng bước hay thử nghiệm nhiều phương án.

                Sau đó bổ sung những phương thức còn ít sử dụng. Người quen quyết định dựa trên cảm nhận có thể tập đối chiếu số liệu và bằng chứng. Người quá tập trung vào chi tiết có thể luyện cách xác định mục tiêu tổng thể. Người thường thay đổi phương án có thể thiết lập tiêu chí để duy trì tính nhất quán.

                Trong công việc nhóm, nên sử dụng sự khác biệt về phương pháp tư duy để phân chia trách nhiệm và kiểm tra chéo kết quả, thay vì gắn nhãn năng lực của đồng nghiệp dựa trên một con số.',
            ],

            'hidden_passion' => [
                'key' => 'hidden_passion',
                'title' => 'Chỉ Số Đam Mê Tiềm Ẩn (Hidden Passion)',
                'group' => 'Chỉ số mở rộng',

                'formula' => 'Quy đổi toàn bộ chữ cái trong họ tên khai sinh thành các giá trị từ 1 đến 9. Đếm số lần xuất hiện của từng giá trị, không rút gọn tổng họ tên. Giá trị có tần suất cao nhất được xác định là Đam Mê Tiềm Ẩn. Nếu nhiều giá trị có cùng tần suất cao nhất thì ghi nhận tất cả. Ví dụ, tên Lê An được quy đổi thành L = 3, E = 5, A = 1, N = 5. Số 5 xuất hiện hai lần và có tần suất cao nhất.',

                'summary' => 'Đam Mê Tiềm Ẩn là chỉ số được hình thành từ sự lặp lại của các giá trị chữ cái trong họ tên khai sinh. Trong phương pháp luận giải, sự lặp lại này được xem như biểu tượng của một nhóm phẩm chất có mức độ nhấn mạnh tương đối cao trong cấu trúc tên.

                Khái niệm này khác với Linh Hồn. Linh Hồn tập trung vào những nhu cầu và giá trị bên trong, còn Đam Mê Tiềm Ẩn tập trung vào chủ đề có thể được biểu hiện thông qua sự chú ý, hứng thú hoặc việc sử dụng lặp lại một nhóm phẩm chất.

                Chẳng hạn, Đam Mê Tiềm Ẩn 3 có thể được diễn giải theo hướng biểu đạt và sáng tạo. Số 4 nhấn mạnh tính tổ chức và xây dựng. Số 5 hướng đến khám phá và trải nghiệm mới.

                Tuy nhiên, một phẩm chất được nhấn mạnh không đồng nghĩa với năng lực vượt trội. Việc quá tập trung vào một phương thức hành động cũng có thể tạo ra sự mất cân đối, chẳng hạn chú trọng tổ chức đến mức thiếu linh hoạt hoặc tìm kiếm trải nghiệm mới đến mức khó duy trì cam kết dài hạn.

                Khi có nhiều chỉ số đồng hạng, cần xem xét từng chủ đề và mối liên hệ giữa chúng thay vì tùy ý chọn một con số duy nhất.',

                'how_to_apply' => 'Xem lại những hoạt động bạn thường chủ động thực hiện, các chủ đề bạn muốn tìm hiểu sâu hơn và những kỹ năng khiến bạn có động lực duy trì việc luyện tập.

                Có thể thử nghiệm bằng các dự án cá nhân hoặc nhiệm vụ nhỏ trong công việc để xác định mức độ hứng thú thực tế. Sau mỗi giai đoạn, hãy đánh giá cả sự yêu thích lẫn khả năng tạo ra kết quả.

                Nếu một chủ đề xuất hiện nổi bật, hãy phát triển nó cùng những kỹ năng bổ trợ. Đam mê cần được kết hợp với kỷ luật, năng lực chuyên môn và khả năng hoàn thành trách nhiệm để trở thành giá trị lâu dài.',
            ],

            'karmic_lessons' => [
                'key' => 'karmic_lessons',
                'title' => 'Bài Học Rèn Luyện / Số Thiếu (Karmic Lessons)',
                'group' => 'Chỉ số mở rộng',

                'formula' => 'Quy đổi toàn bộ chữ cái trong họ tên khai sinh theo bảng Pythagorean. Kiểm tra sự hiện diện của từng giá trị từ 1 đến 9. Những giá trị không xuất hiện lần nào được xác định là Số Thiếu. Một người có thể có nhiều số thiếu hoặc không có số thiếu nào. Không sử dụng các chữ số trong ngày sinh cho phép tính này.',

                'summary' => 'Bài Học Rèn Luyện là khái niệm được sử dụng để diễn giải những giá trị không hiện diện trong bảng quy đổi họ tên. Trong truyền thống Thần Số Học, chúng được liên hệ với những phẩm chất mà một người có thể cần chú ý phát triển một cách chủ động.

                Ví dụ, thiếu số 1 có thể gợi ý suy ngẫm về khả năng tự chủ và đưa ra quyết định. Thiếu số 4 hướng đến tính tổ chức, sự kiên trì và phương pháp làm việc. Thiếu số 8 gợi ý xem xét cách quản lý trách nhiệm và nguồn lực thực tế.

                Tuy nhiên, sự vắng mặt của một giá trị trong họ tên không chứng minh rằng người đó thiếu phẩm chất tương ứng. Một người có số thiếu liên quan đến giao tiếp vẫn có thể trở thành nhà diễn thuyết xuất sắc nhờ giáo dục, kinh nghiệm và quá trình rèn luyện.

                Cần phân biệt Số Thiếu trong họ tên với ô trống trên biểu đồ ngày sinh. Hai phương pháp sử dụng dữ liệu khác nhau và không thể thay thế cho nhau. Bài Học Rèn Luyện cũng khác với Nợ Nghiệp, vốn được xác định từ một số giá trị kép đặc biệt trong quá trình tính toán.

                Không có số thiếu không đồng nghĩa với hoàn hảo, còn nhiều số thiếu không có nghĩa một người gặp nhiều bất hạnh hơn người khác.',

                'how_to_apply' => 'Chuyển ý nghĩa của mỗi Số Thiếu thành một mục tiêu phát triển có thể quan sát và đánh giá được.

                Nếu chủ đề là tính tổ chức, hãy thử sử dụng lịch làm việc và hoàn thành kế hoạch tuần. Nếu chủ đề là giao tiếp, hãy tập trình bày ý kiến rõ ràng và chủ động tiếp nhận phản hồi. Nếu chủ đề là quản lý nguồn lực, hãy học cách lập ngân sách và theo dõi các khoản chi tiêu.

                Không cần rèn luyện tất cả các chủ đề cùng lúc. Hãy ưu tiên những kỹ năng có liên quan trực tiếp đến khó khăn thực tế của mình.

                Quá trình rèn luyện không làm thay đổi các con số cố định trong họ tên khai sinh, nhưng có thể giúp bạn thay đổi hành vi và nâng cao năng lực thực tế.',
            ],

            'subconscious_self' => [
                'key' => 'subconscious_self',
                'title' => 'Chỉ Số Sức Mạnh Tiềm Thức (Subconscious Self)',
                'group' => 'Chỉ số mở rộng',

                'formula' => 'Lấy 9 trừ đi tổng số lượng Số Thiếu trong họ tên khai sinh. Công thức tương đương với việc đếm số lượng giá trị khác nhau từ 1 đến 9 xuất hiện trong bảng quy đổi họ tên. Ví dụ, nếu họ tên thiếu hai giá trị là 4 và 7 thì Sức Mạnh Tiềm Thức = 9 − 2 = 7.',

                'summary' => 'Chỉ số Sức Mạnh Tiềm Thức phản ánh mức độ đa dạng của các giá trị số hiện diện trong họ tên theo bảng Pythagorean. Trong hệ thống Thần Số Học, sự đa dạng này được sử dụng như một biểu tượng để diễn giải khả năng huy động những phương thức ứng xử khác nhau khi gặp tình huống mới.

                Chỉ số có giá trị cao thể hiện nhiều nhóm giá trị khác nhau xuất hiện trong tên, trong khi giá trị thấp hơn thể hiện cấu trúc tên có mức độ tập trung lớn hơn vào một số nhóm nhất định.

                Tuy nhiên, đây không phải thang đo khoa học về sức mạnh tâm lý, khả năng phản xạ, mức độ tự tin hay năng lực xử lý khủng hoảng. Không thể khẳng định người có chỉ số 9 luôn thích nghi tốt hơn người có chỉ số 5.

                Ý nghĩa của chỉ số cần được xem xét cùng các Số Thiếu cụ thể. Hai người cùng có Sức Mạnh Tiềm Thức bằng 7 có thể thiếu những nhóm giá trị hoàn toàn khác nhau, do đó nội dung luận giải không nhất thiết giống nhau.

                Chỉ số này phù hợp hơn khi được sử dụng để khuyến khích một người xem xét sự đa dạng trong kỹ năng và cách tiếp cận vấn đề.',

                'how_to_apply' => 'Xác định những phương thức giải quyết vấn đề bạn thường sử dụng và những phương thức bạn chưa có nhiều cơ hội thực hành.

                Có thể xây dựng khả năng thích ứng bằng cách chuẩn bị trước cho những tình huống quan trọng, học kỹ năng quản lý cảm xúc, cải thiện khả năng giao tiếp, luyện tập xử lý sự cố và tạo mạng lưới hỗ trợ đáng tin cậy.

                Không sử dụng chỉ số này để tự đánh giá mình yếu kém hoặc cho rằng bản thân có thể xử lý mọi tình huống mà không cần trợ giúp. Năng lực ứng phó thực tế cần được đánh giá thông qua kiến thức, kinh nghiệm và điều kiện cụ thể.',
            ],

            'bridges' => [
                'key' => 'bridges',
                'title' => 'Chỉ Số Cầu Nối (Bridge Numbers)',
                'group' => 'Chỉ số mở rộng',

                'formula' => 'Trước khi tính, đưa các Master Numbers trong từng cặp chỉ số về số nền tương ứng: 11 thành 2, 22 thành 4 và 33 thành 6. Cầu Nối Đường Đời – Sứ Mệnh = hiệu số tuyệt đối giữa số nền Đường Đời và số nền Sứ Mệnh. Cầu Nối Linh Hồn – Nhân Cách = hiệu số tuyệt đối giữa số nền Linh Hồn và số nền Nhân Cách. Kết quả nằm trong khoảng từ 0 đến 8. Ví dụ, Đường Đời 11/2 và Sứ Mệnh 7 tạo thành Cầu Nối bằng |2 − 7| = 5.',

                'summary' => 'Chỉ số Cầu Nối được sử dụng để xem xét mối liên hệ giữa hai phương diện có vai trò khác nhau trong biểu đồ Thần Số Học.

                Cầu Nối Đường Đời – Sứ Mệnh tập trung vào sự kết hợp giữa chủ đề phát triển dài hạn và phương thức sử dụng năng lực. Đây là góc nhìn giúp suy ngẫm về cách biến những định hướng cá nhân thành hành động và kết quả cụ thể.

                Cầu Nối Linh Hồn – Nhân Cách tập trung vào mối liên hệ giữa nhu cầu bên trong và hình ảnh biểu hiện ra bên ngoài. Chỉ số này được dùng để xem xét khả năng diễn đạt mong muốn cá nhân, xây dựng ranh giới và giao tiếp một cách chân thành.

                Giá trị của Cầu Nối không phải thước đo mức độ mâu thuẫn tâm lý. Số lớn hơn không có nghĩa một người gặp khó khăn nghiêm trọng hơn. Tương tự, Cầu Nối 0 chỉ cho biết hai chỉ số có cùng số nền, không chứng minh rằng nội tâm và hành vi đã hoàn toàn đồng nhất.

                Ý nghĩa chính của Cầu Nối nằm ở phẩm chất mà con số kết quả gợi ý, kết hợp với nội dung cụ thể của hai chỉ số ban đầu.',

                'how_to_apply' => 'Đối với Cầu Nối Đường Đời – Sứ Mệnh, hãy xem xét liệu cách mình đang học tập và làm việc có hỗ trợ những mục tiêu dài hạn hay không. Nếu có sự khác biệt, hãy xác định kỹ năng hoặc thói quen giúp kết nối hai phương diện.

                Đối với Cầu Nối Linh Hồn – Nhân Cách, hãy quan sát những thời điểm bạn muốn một điều nhưng lại thể hiện điều khác. Tập diễn đạt nhu cầu rõ ràng, tôn trọng ranh giới cá nhân và trao đổi trực tiếp với người liên quan.

                Nếu Cầu Nối bằng 0, có thể tập trung vào cách sử dụng linh hoạt chủ đề chung thay vì mặc định rằng mọi phương diện đã cân bằng hoàn toàn.',
            ],

            // =====================================================
            // III. NHÓM SỐ ĐẶC BIỆT
            // =====================================================

            'master_numbers' => [
                'key' => 'master_numbers',
                'title' => 'Chỉ Số Master (11/2 · 22/4 · 33/6)',
                'group' => 'Số đặc biệt',

                'formula' => 'Master Numbers bao gồm 11, 22 và 33. Chúng được giữ nguyên khi xuất hiện tại những bước tính mà phương pháp áp dụng cho phép bảo lưu Master Numbers. Số nền tương ứng là 11/2, 22/4 và 33/6. Không bảo lưu Master Numbers trong các phép tính yêu cầu rút gọn hoàn toàn, chẳng hạn Thái Độ, Cân Bằng, Tư Duy Lý Trí, Thử Thách, Năm Cá Nhân, Tháng Cá Nhân và Ngày Cá Nhân. Việc nhận diện Master Number phải dựa trên đúng trình tự tính của từng chỉ số.',

                'summary' => 'Trong Thần Số Học Pythagorean, Master Numbers là nhóm số kép được gắn với những chủ đề phát triển có mức độ nhấn mạnh đặc biệt. Mỗi Master Number vẫn mang ý nghĩa của số nền tương ứng, đồng thời bổ sung một lớp diễn giải mở rộng.

                Master 11/2 kết hợp chủ đề hợp tác, sự nhạy cảm và khả năng kết nối của số 2 với những nội dung liên quan đến cảm nhận, cảm hứng và chiều sâu tinh thần. Mặt cần chú ý là khả năng duy trì sự ổn định, kiểm tra cảm nhận bằng thông tin thực tế và tránh tạo áp lực quá lớn cho bản thân.

                Master 22/4 kết hợp tính tổ chức, phương pháp và sự bền bỉ của số 4 với chủ đề chuyển hóa những ý tưởng có quy mô lớn thành kết quả thực tế. Trọng tâm phát triển nằm ở năng lực thực thi, quản lý nguồn lực và chia mục tiêu lớn thành những bước khả thi.

                Master 33/6 kết hợp trách nhiệm, sự chăm sóc và các giá trị gia đình hoặc cộng đồng của số 6 với chủ đề hướng dẫn, phụng sự và đóng góp cho người khác. Mặt cần điều chỉnh là giữ gìn ranh giới cá nhân và không đồng nhất sự hy sinh với giá trị bản thân.

                Master Numbers không thể hiện cấp bậc linh hồn, không chứng minh năng lực siêu nhiên và không khiến người sở hữu có giá trị cao hơn người mang các số từ 1 đến 9. Đây là cách phân loại mang tính biểu tượng trong một hệ thống luận giải.',

                'how_to_apply' => 'Ưu tiên phát triển những phẩm chất thực tế của số nền trước khi đặt ra các mục tiêu có quy mô lớn.

                Với 11/2, có thể chú trọng kỹ năng lắng nghe, điều chỉnh cảm xúc, hợp tác và phân biệt cảm nhận chủ quan với bằng chứng. Với 22/4, nên phát triển phương pháp làm việc, quản lý tiến độ và năng lực xây dựng hệ thống. Với 33/6, hãy thực hành sự quan tâm có ranh giới, cân bằng trách nhiệm cá nhân với trách nhiệm đối với người khác.

                Không cần chứng minh rằng mình phải đạt một thành tựu đặc biệt chỉ vì sở hữu Master Number. Việc phát triển ổn định, sống có trách nhiệm và duy trì chất lượng cuộc sống có giá trị hơn việc theo đuổi những kỳ vọng không phù hợp với hoàn cảnh.',
            ],

            'karmic_debts' => [
                'key' => 'karmic_debts',
                'title' => 'Chỉ Số Nợ Nghiệp (13/4 · 14/5 · 16/7 · 19/1)',
                'group' => 'Số đặc biệt',

                'formula' => 'Các số Nợ Nghiệp truyền thống gồm 13, 14, 16 và 19, có số nền tương ứng là 4, 5, 7 và 1. Việc nhận diện được thực hiện dựa trên số kép của ngày sinh hoặc giá trị kép được hình thành trong phép tính của các chỉ số đủ điều kiện theo phương pháp đã chọn. Cần lưu lại quá trình rút gọn để xác định nguồn xuất hiện của số kép. Không tự động gán Nợ Nghiệp chỉ vì kết quả cuối cùng bằng 1, 4, 5 hoặc 7.',

                'summary' => 'Nợ Nghiệp là một khái niệm mang tính tâm linh trong một số trường phái Thần Số Học, có liên hệ với niềm tin về nghiệp và sự tái sinh. Những niềm tin này không phải bằng chứng khoa học về quá khứ hay hành vi của một cá nhân. Khi sử dụng trong nội dung phát triển bản thân, Nợ Nghiệp nên được diễn giải theo hướng suy ngẫm về trách nhiệm và thói quen.

                Nợ Nghiệp 13/4 tập trung vào chủ đề lao động có phương pháp, tính kiên trì và việc xây dựng kết quả bằng nỗ lực nhất quán. Nội dung rèn luyện có thể liên quan đến việc tránh bỏ cuộc giữa chừng hoặc tìm kiếm đường tắt thiếu bền vững.

                Nợ Nghiệp 14/5 tập trung vào mối quan hệ giữa tự do và trách nhiệm. Chủ đề này gợi ý xem xét khả năng tự chủ, tính điều độ và việc duy trì cam kết khi có nhiều lựa chọn hoặc sự thay đổi.

                Nợ Nghiệp 16/7 tập trung vào khả năng nhìn nhận lại bản thân, tiếp nhận phản hồi và phát triển sự khiêm nhường trong suy nghĩ. Nội dung này không nên được sử dụng để dự báo sự đổ vỡ, tai họa hoặc mất mát.

                Nợ Nghiệp 19/1 tập trung vào việc xây dựng tính độc lập đồng thời nhận thức được giá trị của sự hợp tác và hỗ trợ lẫn nhau.

                Ý nghĩa của từng Nợ Nghiệp còn phụ thuộc vào vị trí xuất hiện. Nợ Nghiệp trong Đường Đời và Nợ Nghiệp trong Linh Hồn không nên được diễn giải bằng một đoạn nội dung hoàn toàn giống nhau.',

                'how_to_apply' => 'Chuyển chủ đề Nợ Nghiệp thành những hành vi có thể thực hành trong hiện tại, thay vì cố gắng suy đoán mình đã làm gì trong một kiếp sống khác.

                Với 13/4, hãy xây dựng quy trình và duy trì cam kết. Với 14/5, có thể thiết lập giới hạn hợp lý cho những thói quen dễ mất kiểm soát. Với 16/7, hãy luyện khả năng tiếp nhận góp ý và đánh giá lại những niềm tin chưa được kiểm chứng. Với 19/1, cần phát triển tính tự chủ đồng thời học cách nhờ hỗ trợ và chia sẻ trách nhiệm.

                Không quy kết bệnh tật, khó khăn tài chính, mâu thuẫn gia đình hoặc những sự kiện bất lợi là hậu quả của Nợ Nghiệp. Những vấn đề đó cần được xem xét dựa trên nguyên nhân và điều kiện thực tế.',
            ],

            // =====================================================
            // IV. BIỂU ĐỒ NGÀY SINH VÀ MŨI TÊN
            // =====================================================

            'birth_chart' => [
                'key' => 'birth_chart',
                'title' => 'Biểu Đồ Ngày Sinh 3×3 (David A. Phillips)',
                'group' => 'Biểu đồ ngày sinh 3×3',

                'formula' => 'Tách toàn bộ chữ số trong ngày, tháng và năm sinh dương lịch, sử dụng đầy đủ bốn chữ số của năm sinh. Bỏ qua chữ số 0. Điền các chữ số còn lại vào vị trí cố định của biểu đồ 3×3: hàng trên gồm 3, 6, 9; hàng giữa gồm 2, 5, 8; hàng dưới gồm 1, 4, 7. Nếu một chữ số xuất hiện nhiều lần thì ghi nhận đầy đủ số lần xuất hiện trong cùng một ô. Không rút gọn ngày, tháng hoặc năm trước khi lập biểu đồ.',

                'summary' => 'Biểu đồ ngày sinh theo David A. Phillips là phương pháp trình bày trực quan sự hiện diện và tần suất của các chữ số từ 1 đến 9 trong ngày sinh. Khác với Đường Đời, vốn tổng hợp ngày sinh thành một chỉ số, biểu đồ này giữ lại cấu trúc phân bố của từng chữ số.

                Hàng trên gồm 3, 6 và 9 được gọi là trục Trí não. Trục này được liên hệ với những chủ đề như ghi nhớ, tư duy, sáng tạo, nhận thức và lý tưởng.

                Hàng giữa gồm 2, 5 và 8 được gọi là trục Tâm hồn hoặc Cảm xúc. Trục này tập trung vào các nội dung liên quan đến sự nhạy cảm, cảm nhận, kết nối, cân bằng và đời sống nội tâm.

                Hàng dưới gồm 1, 4 và 7 được gọi là trục Thực tế hoặc Thể chất. Trục này được sử dụng để diễn giải những chủ đề về biểu đạt cá nhân, tổ chức, hành động và học hỏi thông qua trải nghiệm.

                Một chữ số xuất hiện nhiều lần được xem như chủ đề có mức độ nhấn mạnh cao trong biểu đồ. Tuy nhiên, nhiều lần xuất hiện không đồng nghĩa với phẩm chất tốt hơn. Tùy theo cách luận giải, sự tập trung quá mức cũng có thể gợi ý nhu cầu điều chỉnh.

                Ô trống là vị trí không chứa chữ số tương ứng trong dữ liệu ngày sinh. Không thể sử dụng ô trống để kết luận một người thiếu trí tuệ, thiếu cảm xúc, kém năng lực hoặc có vấn đề về sức khỏe.',

                'how_to_apply' => 'Đọc biểu đồ theo ba cấp độ: ý nghĩa từng chữ số, sự phân bố giữa ba hàng và các đường mũi tên được hình thành.

                Khi nhận thấy một chủ đề được nhấn mạnh, hãy xem xét những biểu hiện thực tế của chủ đề đó, cả mặt thuận lợi lẫn những thói quen có thể cần điều chỉnh. Đối với ô trống, có thể lựa chọn một kỹ năng liên quan để thử nghiệm và rèn luyện.

                Không cần tìm cách thay đổi biểu đồ ngày sinh hoặc bổ sung chữ số bằng cách thay tên. Biểu đồ ngày sinh là dữ liệu cố định, còn hành vi, kỹ năng và chất lượng cuộc sống có thể thay đổi thông qua quá trình phát triển thực tế.',
            ],

            'arrows' => [
                'key' => 'arrows',
                'title' => '8 Đường Mũi Tên Trên Biểu Đồ 3×3',
                'group' => 'Biểu đồ ngày sinh 3×3',

                'formula' => 'Kiểm tra tám đường thẳng trên biểu đồ ngày sinh: ba hàng ngang 1-4-7, 2-5-8, 3-6-9; ba cột dọc 1-2-3, 4-5-6, 7-8-9; hai đường chéo 1-5-9 và 3-5-7. Một Mũi Tên Đủ được hình thành khi cả ba ô đều có ít nhất một chữ số. Một Mũi Tên Trống được hình thành khi cả ba ô đều không có chữ số. Nếu chỉ có một hoặc hai ô hiện diện thì không hình thành mũi tên hoàn chỉnh.',

                'summary' => 'Các mũi tên trên biểu đồ ngày sinh được sử dụng để diễn giải mối liên hệ giữa ba nhóm phẩm chất nằm trên cùng một đường thẳng. Trong phương pháp của David A. Phillips, một đường có đầy đủ chữ số và một đường hoàn toàn trống được gán những chủ đề khác nhau.

                Tám đường mũi tên bao gồm:

                Đường 1-4-7: chủ đề thực tế, tổ chức và hành động.

                Đường 2-5-8: chủ đề cảm xúc, sự nhạy cảm và cân bằng nội tâm.

                Đường 3-6-9: chủ đề trí não, ghi nhớ và hoạt động tư duy.

                Đường 1-2-3: chủ đề lập kế hoạch và sắp xếp ý tưởng.

                Đường 4-5-6: chủ đề ý chí và khả năng duy trì nỗ lực.

                Đường 7-8-9: chủ đề hoạt động và sự tham gia vào trải nghiệm thực tế.

                Đường 1-5-9: chủ đề quyết tâm và theo đuổi mục tiêu.

                Đường 3-5-7: chủ đề chiêm nghiệm và tìm hiểu những giá trị tinh thần.

                Mũi Tên Đủ được dùng để nhận diện một nhóm phẩm chất có thể phối hợp với nhau. Mũi Tên Trống được dùng để suy ngẫm về những phẩm chất có thể cần được phát triển có chủ đích.

                Không nên sử dụng cách gọi truyền thống của một số mũi tên trống để chẩn đoán trí nhớ, đánh giá đạo đức hoặc gắn nhãn tiêu cực cho người sở hữu biểu đồ.',

                'how_to_apply' => 'Đối với Mũi Tên Đủ, hãy xác định cách vận dụng những phẩm chất liên quan một cách phù hợp với hoàn cảnh. Ví dụ, sự quyết tâm có thể hỗ trợ hoàn thành mục tiêu nhưng cần đi kèm khả năng điều chỉnh khi xuất hiện thông tin mới.

                Đối với Mũi Tên Trống, có thể lựa chọn một thói quen thực tế để phát triển. Chủ đề lập kế hoạch có thể được rèn luyện bằng việc xác định mục tiêu và theo dõi tiến độ. Chủ đề cân bằng cảm xúc có thể được tiếp cận thông qua khả năng nhận diện cảm xúc, giao tiếp và tìm kiếm sự hỗ trợ khi cần.

                Không có mũi tên nào bảo đảm thành công hoặc thất bại. Việc rèn luyện có thể thay đổi hành vi nhưng không làm thay đổi các ô số cố định trong biểu đồ ngày sinh.',
            ],

            // =====================================================
            // V. CHU KỲ PHÁT TRIỂN VÀ KIM TỰ THÁP
            // =====================================================

            'pinnacles' => [
                'key' => 'pinnacles',
                'title' => '4 Đỉnh Cao Kim Tự Tháp (Pinnacles)',
                'group' => 'Chu kỳ & Kim tự tháp',

                'formula' => 'Gọi M, D, Y lần lượt là tháng, ngày và năm sinh đã được rút gọn theo quy tắc bảo lưu Master Numbers. Đỉnh 1 = rút gọn(M + D). Đỉnh 2 = rút gọn(D + Y). Đỉnh 3 = rút gọn(Đỉnh 1 + Đỉnh 2). Đỉnh 4 = rút gọn(M + Y). Tuổi mốc thứ nhất = 36 trừ số nền Đường Đời; tuổi mốc thứ hai = tuổi mốc thứ nhất + 9; tuổi mốc thứ ba = tuổi mốc thứ hai + 9. Đỉnh 1 bắt đầu từ khi sinh, Đỉnh 2 và Đỉnh 3 mỗi giai đoạn kéo dài 9 năm, còn Đỉnh 4 tiếp tục đến cuối đời.',

                'summary' => 'Bốn Đỉnh Cao là hệ thống phân chia cuộc đời thành các giai đoạn dài hạn trong Thần Số Học. Mỗi giai đoạn được gắn với một chủ đề biểu tượng về cơ hội phát triển, môi trường trải nghiệm, định hướng hành động và những giá trị có thể được chú trọng.

                Đỉnh Cao thứ nhất bao quát thời kỳ hình thành nền tảng, bao gồm quá trình học tập, phát triển bản sắc, tích lũy kinh nghiệm và bước đầu xây dựng vị trí trong xã hội.

                Đỉnh Cao thứ hai và thứ ba bao quát hai giai đoạn liên tiếp, mỗi giai đoạn kéo dài chín năm. Chúng có thể được sử dụng như những khung tham khảo để suy ngẫm về sự thay đổi trong nghề nghiệp, trách nhiệm, các mối quan hệ và mục tiêu cá nhân.

                Đỉnh Cao thứ tư là giai đoạn dài hạn còn lại của cuộc đời, không giới hạn trong chín năm. Nội dung luận giải có thể hướng đến việc duy trì, chuyển đổi hoặc mở rộng những giá trị đã được xây dựng từ các giai đoạn trước.

                Từ Đỉnh Cao không đồng nghĩa với thời điểm chắc chắn giàu có, thành công hoặc đạt thành tựu lớn nhất. Con số của mỗi đỉnh chỉ cung cấp một chủ đề để diễn giải, không dự báo sự kiện bắt buộc xảy ra tại một độ tuổi cụ thể.

                Các Đỉnh Cao cũng không phải toàn bộ nội dung của Đường Đời mà là một lớp luận giải về những giai đoạn dài hạn bên trong hành trình đó.',

                'how_to_apply' => 'Xác định Đỉnh Cao hiện tại và tìm hiểu những phẩm chất mà con số của giai đoạn đề cập. Sau đó liên hệ với hoàn cảnh công việc, gia đình, tài chính và mục tiêu cá nhân thực tế.

                Nếu chủ đề liên quan đến xây dựng nền tảng, có thể tập trung phát triển kỹ năng, quy trình và khả năng duy trì cam kết. Nếu chủ đề liên quan đến thay đổi, hãy đánh giá các phương án mới một cách có chuẩn bị thay vì thay đổi chỉ vì đến tuổi chuyển đỉnh.

                Khi gần một mốc chuyển tiếp, có thể sử dụng thời điểm đó để tổng kết những gì đã học được và điều chỉnh kế hoạch dài hạn. Không nên quyết định nghỉ việc, kết hôn, ly hôn hoặc đầu tư lớn chỉ dựa trên kết quả Kim Tự Tháp.',
            ],

            'challenges' => [
                'key' => 'challenges',
                'title' => '4 Thử Thách Trưởng Thành (Challenges)',
                'group' => 'Chu kỳ & Kim tự tháp',

                'formula' => 'Rút gọn tháng, ngày và năm sinh lần lượt thành M, D, Y, đều là các chữ số từ 1 đến 9, kể cả khi gặp 11, 22 hoặc 33. Thử Thách 1 = |M − D|. Thử Thách 2 = |D − Y|. Thử Thách 3 = |Thử Thách 1 − Thử Thách 2|. Thử Thách 4 = |M − Y|. Các kết quả nằm trong khoảng từ 0 đến 8. Thử Thách 3 được gọi là Thử Thách Chính và được luận giải xuyên suốt cuộc đời.',

                'summary' => 'Bốn Thử Thách là một hệ thống luận giải tập trung vào những phẩm chất, thói quen và cách ứng xử mà một người có thể xem xét hoàn thiện trong quá trình trưởng thành.

                Nếu các Đỉnh Cao tập trung vào chủ đề phát triển và cơ hội của từng giai đoạn, các Thử Thách hướng sự chú ý đến những nội dung cần điều chỉnh hoặc học hỏi để phát triển một cách cân bằng hơn.

                Theo Hans Decoz, Thử Thách thứ nhất chủ yếu được liên hệ với giai đoạn đầu đời. Thử Thách thứ hai được liên hệ với giai đoạn trưởng thành ở giữa cuộc đời. Thử Thách thứ tư được chú trọng ở giai đoạn sau. Thử Thách thứ ba có vai trò đặc biệt vì được xem là chủ đề xuyên suốt, không chỉ giới hạn trong một giai đoạn chín năm.

                Chẳng hạn, Thử Thách 1 có thể gợi ý xem xét khả năng tự chủ và trách nhiệm đối với quyết định cá nhân. Thử Thách 4 hướng đến tính kiên trì và phương pháp làm việc. Thử Thách 8 tập trung vào cách sử dụng quyền hạn, nguồn lực và trách nhiệm thực tế.

                Thử Thách 0 là một kết quả hợp lệ. Trong truyền thống luận giải, nó thường được hiểu theo phạm vi bài học rộng hơn, không phải không có khó khăn và cũng không có nghĩa sẽ phải trải qua tất cả bất hạnh.

                Các Thử Thách không được sử dụng để xác định thời điểm xảy ra biến cố hoặc dự báo mức độ nghiêm trọng của khó khăn trong tương lai.',

                'how_to_apply' => 'Chuyển mỗi chủ đề Thử Thách thành một câu hỏi phát triển cụ thể. Chẳng hạn, bạn có đang trì hoãn những nhiệm vụ quan trọng, gặp khó khăn khi thiết lập ranh giới hoặc chưa biết cách quản lý trách nhiệm một cách phù hợp hay không.

                Khi xác định được vấn đề thực tế, hãy lựa chọn một hành vi có thể thay đổi và theo dõi sự tiến bộ theo thời gian.

                Đối với Thử Thách Chính, có thể định kỳ nhìn lại cách mình đã phát triển những phẩm chất liên quan. Tuy nhiên, không nên cho rằng mọi khó khăn trong cuộc sống đều bắt nguồn từ chỉ số này. Mỗi vấn đề cần được phân tích dựa trên nguyên nhân, hoàn cảnh và thông tin cụ thể.',
            ],

            'period_cycles' => [
                'key' => 'period_cycles',
                'title' => '3 Giai Đoạn Cuộc Đời (Period Cycles)',
                'group' => 'Chu kỳ & Kim tự tháp',

                'formula' => 'Giai đoạn thứ nhất được xác định từ tháng sinh, giai đoạn thứ hai từ ngày sinh và giai đoạn thứ ba từ năm sinh. Rút gọn từng thành phần theo quy tắc bảo lưu Master Numbers. Theo công thức thời lượng phổ biến trong phương pháp Hans Decoz, giai đoạn đầu kéo dài khoảng 36 trừ số nền Đường Đời năm; giai đoạn giữa kéo dài 27 năm; giai đoạn cuối tiếp tục đến cuối đời. Khi hiển thị tuổi bắt đầu và kết thúc, cần thống nhất quy ước chuyển tiếp theo tuổi hoặc năm lịch của hệ thống tính toán.',

                'summary' => 'Ba Giai Đoạn Cuộc Đời là hệ thống mô tả ba chặng phát triển dài hạn, thường được liên hệ với thời kỳ hình thành, thời kỳ phát triển năng lực và thời kỳ tiếp nối hoặc tổng hợp kinh nghiệm.

                Giai đoạn thứ nhất, có thể gọi là Gieo Hạt, tập trung vào những chủ đề liên quan đến quá trình hình thành nhận thức, giáo dục, trải nghiệm gia đình, khám phá bản sắc và chuẩn bị cho đời sống trưởng thành.

                Giai đoạn thứ hai, có thể gọi là Kiến Tạo, tập trung vào việc phát triển năng lực, xây dựng vị trí xã hội, đảm nhận trách nhiệm và hình thành những giá trị có tính ổn định hơn trong cuộc sống.

                Giai đoạn thứ ba, có thể gọi là Thu Hoạch, hướng đến việc tiếp tục phát triển trên cơ sở kinh nghiệm đã tích lũy, xem xét các ưu tiên dài hạn và xác định những giá trị muốn duy trì hoặc chia sẻ.

                Những tên gọi này chỉ mang tính minh họa. Con người vẫn có thể bắt đầu học tập, xây dựng sự nghiệp, khám phá đam mê hoặc thay đổi hướng sống ở bất kỳ độ tuổi nào.

                Period Cycles khác với Pinnacles ở cách tính, số lượng giai đoạn và trọng tâm luận giải. Period Cycles mô tả chủ đề phát triển dài hạn, trong khi Pinnacles bổ sung góc nhìn về những dạng cơ hội và hoàn cảnh của từng chặng.',

                'how_to_apply' => 'Sử dụng ba giai đoạn như khung tham khảo để xem xét các ưu tiên dài hạn và những phẩm chất bạn muốn tiếp tục phát triển.

                Trong giai đoạn đầu, có thể chú trọng xây dựng kiến thức và khả năng tự nhận thức. Trong giai đoạn giữa, hãy cân nhắc cách duy trì sự cân bằng giữa công việc, gia đình và những mục tiêu cá nhân. Trong giai đoạn sau, tiếp tục phát triển những hoạt động mang lại ý nghĩa, sự kết nối và chất lượng cuộc sống.

                Không sử dụng tên gọi của các giai đoạn để giới hạn cơ hội theo độ tuổi. Việc bắt đầu lại hoặc thay đổi hướng phát triển ở bất kỳ giai đoạn nào đều có thể phù hợp nếu dựa trên nhu cầu và điều kiện thực tế.',
            ],

            'personal_year' => [
                'key' => 'personal_year',
                'title' => 'Năm Cá Nhân & Chu Kỳ 9 Năm (Personal Year)',
                'group' => 'Chu kỳ & Kim tự tháp',

                'formula' => 'Cộng ngày sinh, tháng sinh và năm dương lịch đang xem, sau đó rút gọn hoàn toàn về một chữ số từ 1 đến 9. Không sử dụng năm sinh và không bảo lưu Master Numbers. Hệ thống này sử dụng quy ước năm dương lịch từ ngày 01/01 đến ngày 31/12. Ví dụ, người sinh ngày 24/08 có Năm Cá Nhân 2026 = 24 + 8 + 2026 = 2058 → 15 → 6.',

                'summary' => 'Năm Cá Nhân là một chỉ số thay đổi theo từng năm dương lịch và được sử dụng để diễn giải các chủ đề phát triển ngắn hạn trong chu kỳ chín năm của Thần Số Học.

                Mỗi năm tương ứng với một chủ đề biểu tượng:

                Năm 1: Khởi đầu, tính chủ động, xác lập mục tiêu và thử nghiệm hướng đi mới.

                Năm 2: Hợp tác, kiên nhẫn, xây dựng mối quan hệ và điều chỉnh khả năng giao tiếp.

                Năm 3: Biểu đạt, sáng tạo, kết nối xã hội và phát triển ý tưởng.

                Năm 4: Xây dựng nền tảng, duy trì kỷ luật, tổ chức công việc và hoàn thiện quy trình.

                Năm 5: Linh hoạt, khám phá, thích nghi và xem xét những khả năng thay đổi.

                Năm 6: Trách nhiệm, gia đình, sự chăm sóc và cân bằng các cam kết.

                Năm 7: Chiêm nghiệm, học tập chiều sâu, đánh giá kinh nghiệm và hoàn thiện nhận thức.

                Năm 8: Quản lý nguồn lực, trách nhiệm, hiệu quả công việc và đánh giá thành quả thực tế.

                Năm 9: Tổng kết, hoàn thành những việc còn dang dở và chuẩn bị cho một chu kỳ mới.

                Sau Năm Cá Nhân 9, chu kỳ quay trở lại Năm Cá Nhân 1. Điều này tạo ra một cấu trúc lặp lại để tham khảo khi lập kế hoạch và nhìn nhận sự phát triển.

                Tuy nhiên, không có năm nào tuyệt đối tốt hoặc xấu. Năm 8 không bảo đảm giàu có, năm 9 không bắt buộc xảy ra chia ly và năm 1 không có nghĩa mọi dự án mới đều thành công. Những kết quả thực tế vẫn phụ thuộc vào hành động, hoàn cảnh và các yếu tố khách quan.',

                'how_to_apply' => 'Sử dụng chủ đề Năm Cá Nhân để lựa chọn một trọng tâm phát triển bổ sung cho kế hoạch hằng năm.

                Chẳng hạn, năm mang chủ đề xây dựng có thể được sử dụng để rà soát quy trình, nâng cao kỹ năng và củng cố những cam kết hiện tại. Năm mang chủ đề tổng kết có thể được dùng để đánh giá các dự án đang thực hiện và xác định điều gì cần tiếp tục hoặc điều chỉnh.

                Không nên trì hoãn một cơ hội tốt, từ chối một công việc phù hợp hoặc thực hiện quyết định tài chính mạo hiểm chỉ vì ý nghĩa của Năm Cá Nhân.

                Khi luận giải, cần phân biệt Năm Cá Nhân với Đường Đời và các chu kỳ dài hạn. Đường Đời là chỉ số cố định, còn Năm Cá Nhân thay đổi theo năm đang xem và chỉ cung cấp một góc nhìn mang tính thời điểm.',
            ],

            'personal_month_day' => [
                'key' => 'personal_month_day',
                'title' => 'Tháng Cá Nhân & Ngày Cá Nhân',
                'group' => 'Chu kỳ & Kim tự tháp',

                'formula' => 'Tháng Cá Nhân = rút gọn hoàn toàn của Năm Cá Nhân + số thứ tự tháng dương lịch đang xem. Ngày Cá Nhân = rút gọn hoàn toàn của Tháng Cá Nhân + ngày dương lịch đang xem. Cả hai kết quả đều nằm trong khoảng từ 1 đến 9 và không bảo lưu Master Numbers. Ví dụ, Năm Cá Nhân bằng 6, tháng đang xem là tháng 11 thì Tháng Cá Nhân = 6 + 11 = 17 → 8. Ngày 27 của tháng đó có Ngày Cá Nhân = 8 + 27 = 35 → 8.',

                'summary' => 'Tháng Cá Nhân và Ngày Cá Nhân là hai lớp luận giải có phạm vi thời gian ngắn hơn Năm Cá Nhân. Chúng được tính từ chu kỳ lớn hơn để tạo thành một hệ thống tham khảo theo từng năm, từng tháng và từng ngày.

                Năm Cá Nhân đóng vai trò chủ đề tổng quát của cả năm. Tháng Cá Nhân bổ sung một chủ đề cụ thể hơn trong từng tháng. Ngày Cá Nhân tiếp tục thu hẹp phạm vi xuống các hoạt động và mục tiêu ngắn hạn.

                Chẳng hạn, một người đang ở Năm Cá Nhân 4, mang chủ đề xây dựng nền tảng và tính tổ chức, có thể trải qua Tháng Cá Nhân 5, mang chủ đề linh hoạt và điều chỉnh. Sự kết hợp này có thể được diễn giải như một lời gợi ý xem xét những cải tiến mới trong khi vẫn duy trì mục tiêu dài hạn.

                Các lớp chu kỳ không loại bỏ hoặc thay thế lẫn nhau. Một Tháng Cá Nhân có chủ đề khác với Năm Cá Nhân không có nghĩa hai chu kỳ xung đột hoặc tạo ra vận hạn.

                Tháng và Ngày Cá Nhân không dự báo chính xác sự kiện sẽ xảy ra, không xác định ngày may mắn tuyệt đối và không chứng minh một thời điểm chắc chắn thuận lợi cho giao dịch, chữa bệnh, ký kết hoặc các quyết định quan trọng khác.',

                'how_to_apply' => 'Có thể sử dụng Tháng Cá Nhân để xây dựng kế hoạch rà soát định kỳ, lựa chọn chủ đề học tập, sắp xếp công việc và đánh giá tiến độ thực hiện mục tiêu năm.

                Ngày Cá Nhân có thể được dùng như một câu hỏi tự phản tỉnh ngắn vào đầu hoặc cuối ngày. Ví dụ, ngày mang chủ đề tổ chức có thể gợi ý kiểm tra danh sách công việc; ngày mang chủ đề kết nối có thể gợi ý dành thời gian trao đổi với những người quan trọng.

                Không cần thay đổi lịch trình chỉ để phù hợp với con số. Những công việc có thời hạn, trách nhiệm gia đình, nhu cầu sức khỏe và các cơ hội thực tế cần được ưu tiên.

                Khi sử dụng lộ trình 12 tháng, hãy xem Tháng Cá Nhân như lớp nội dung bổ trợ cho kế hoạch đã được xây dựng, thay vì một lịch dự báo tương lai có tính bắt buộc.',
            ],
        ];
    }

    /**
     * Tra cứu giải thích các khái niệm Thần Số Học xuất hiện trong câu hỏi người dùng.
     *
     * @return list<array{key: string, title: string, group: string, formula: string, summary: string, how_to_apply: string}>
     */
    public function lookupConceptsInText(string $text): array
    {
        $glossary = $this->getConceptGlossary();
        $normalized = $this->stripVietnameseForLookup($text);

        $keywordMap = [
            'life_path' => ['duong doi', 'so chu dao', 'life path'],
            'expression' => ['su menh', 'expression', 'destiny'],
            'soul_urge' => ['linh hon', 'soul urge'],
            'personality' => ['nhan cach', 'personality'],
            'birthday' => ['chi so ngay sinh', 'ngay sinh goc', 'birthday'],
            'maturity' => ['truong thanh', 'maturity'],
            'attitude' => ['thai do', 'attitude'],
            'balance' => ['can bang', 'balance'],
            'rational_thought' => ['tu duy ly tri', 'ly tri', 'rational thought'],
            'hidden_passion' => ['dam me tiem an', 'hidden passion'],
            'karmic_lessons' => ['so thieu', 'thieu so', 'bai hoc ren luyen', 'karmic lesson'],
            'subconscious_self' => ['tiem thuc', 'suc manh tiem thuc', 'subconscious'],
            'bridges' => ['cau noi', 'bridge'],
            'master_numbers' => ['master', '11/2', '22/4', '33/6', 'so 11', 'so 22', 'so 33'],
            'karmic_debts' => ['no nghiep', '13/4', '14/5', '16/7', '19/1', 'karmic debt'],
            'birth_chart' => ['bieu do ngay sinh', '3x3', 'o trong', 'truc the chat', 'truc tinh than', 'truc tri nao'],
            'arrows' => ['mui ten', 'quyet tam', 'tri tue', 'tam linh', 'cam xuc', 'ke hoach', 'y chi', 'hoat dong', '1-5-9', '3-5-7', '3-6-9', '2-5-8', '1-4-7', '1-2-3', '4-5-6', '7-8-9'],
            'pinnacles' => ['dinh cao', 'kim tu thap', 'pinnacle'],
            'challenges' => ['thu thach', 'challenge'],
            'period_cycles' => ['3 giai doan', 'ba giai doan', 'gieo hat', 'kien tao', 'thu hoach'],
            'personal_year' => ['nam ca nhan', 'chu ky 9 nam', 'song van so'],
            'personal_month_day' => ['thang ca nhan', 'ngay ca nhan', '12 thang'],
        ];

        $matched = [];
        foreach ($keywordMap as $key => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($normalized, $needle) && isset($glossary[$key])) {
                    $matched[$key] = $glossary[$key];
                    break;
                }
            }
        }

        return array_values(array_slice($matched, 0, 5));
    }

    /**
     * Phân tích rõ các con số Master Number & Nợ Nghiệp xuất hiện trong hồ sơ (ngôn ngữ khoa học, không mê tín).
     *
     * @param  array<string, mixed>  $calc
     * @return array{masters: list<array{location: string, display: string, root: int, note: string}>, karmic_debts: list<array{location: string, code: string, note: string}>}
     */
    public function buildKarmicAndMasterInsights(array $calc): array
    {
        $core = $calc['core_numbers'] ?? [];
        $ext = $calc['extended_numbers'] ?? [];
        $masters = [];
        $karmicNotes = [
            '13/4' => 'Bài học về tính kiên trì và kỷ luật từng bước: tránh nóng vội muốn đi tắt, biến áp lực thành nền móng vững chắc qua lao động đều đặn.',
            '14/5' => 'Bài học về sự tự chủ và điều độ: biết chọn lọc trải nghiệm đáng giá, hoàn thành trọn vẹn cam kết thay vì chiều theo cảm hứng nhất thời.',
            '16/7' => 'Bài học về sự khiêm nhường và chiêm nghiệm sâu sắc: không đặt cái tôi quá cao trước phản hồi thực tế, lắng nghe để xây dựng lại nền tảng bền hơn.',
            '19/1' => 'Bài học về tự lập biết sẻ chia: giữ vững bản lĩnh dẫn dắt nhưng chủ động hỏi ý kiến và trân trọng đóng góp của người đồng hành.',
        ];

        $candidates = [
            'Đường Đời' => $core['life_path'] ?? null,
            'Sứ Mệnh' => $core['expression'] ?? null,
            'Linh Hồn' => $core['soul_urge'] ?? null,
            'Nhân Cách' => $core['personality'] ?? null,
            'Ngày Sinh' => $core['birthday'] ?? null,
            'Trưởng Thành' => $ext['maturity'] ?? null,
        ];

        foreach ($candidates as $loc => $item) {
            if (! is_array($item) || ! empty($item['is_empty_category'])) {
                continue;
            }
            if (! empty($item['is_master'])) {
                $val = (int) ($item['value'] ?? 11);
                $root = (int) ($item['base_root'] ?? ($val === 22 ? 4 : ($val === 33 ? 6 : 2)));
                $masters[] = [
                    'location' => $loc,
                    'display' => (string) ($item['display'] ?? "{$val}/{$root}"),
                    'root' => $root,
                    'note' => "Xuất hiện ở {$loc} — năng lượng nhạy bén cao độ nhưng luôn cần bám chắc nền tảng thực tế của số {$root} để không bị quá tải.",
                ];
            }
        }

        $debtsOut = [];
        foreach ($ext['karmic_debts'] ?? [] as $kd) {
            $code = (string) ($kd['code'] ?? '');
            $debtsOut[] = [
                'location' => (string) ($kd['source'] ?? 'Chỉ số chính'),
                'code' => $code,
                'note' => $karmicNotes[$code] ?? 'Lời nhắc rèn luyện tính nhất quán và trách nhiệm trong lựa chọn hằng ngày.',
            ];
        }

        return [
            'masters' => $masters,
            'karmic_debts' => $debtsOut,
        ];
    }

    protected function stripVietnameseForLookup(string $text): string
    {
        $clean = mb_strtolower(trim($text), 'UTF-8');
        $map = [
            'á' => 'a', 'à' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
            'ă' => 'a', 'ắ' => 'a', 'ằ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
            'â' => 'a', 'ấ' => 'a', 'ầ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
            'é' => 'e', 'è' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
            'ê' => 'e', 'ế' => 'e', 'ề' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
            'í' => 'i', 'ì' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
            'ô' => 'o', 'ố' => 'o', 'ồ' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
            'ơ' => 'o', 'ớ' => 'o', 'ờ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
            'ư' => 'u', 'ứ' => 'u', 'ừ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
            'ý' => 'y', 'ỳ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
            'đ' => 'd',
        ];

        return strtr($clean, $map);
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
