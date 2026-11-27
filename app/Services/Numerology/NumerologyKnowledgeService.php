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
     * Từ điển giải thích khái niệm Thần Số Học dành cho người mới (hỗ trợ Popover tức thì & Hỏi Sâu AI).
     *
     * @return array<string, array{key: string, title: string, group: string, formula: string, summary: string, how_to_apply: string}>
     */
    public function getConceptGlossary(): array
    {
        return [
            'life_path' => [
                'key' => 'life_path',
                'title' => 'Chỉ Số Đường Đời (Life Path)',
                'group' => 'Chỉ số cốt lõi',
                'formula' => 'Rút gọn riêng Ngày, Tháng, Năm sinh dương lịch rồi cộng lại (giữ Master 11, 22, 33 theo Hans Decoz).',
                'summary' => 'Là trục xương sống quan trọng nhất trong Thần Số Học, mô tả bài học lớn suốt cuộc đời, thiên hướng phát triển tự nhiên và những kiểu cơ hội – thử thách bạn dễ gặp nhất.',
                'how_to_apply' => 'Dùng Đường Đời để kiểm tra xem lựa chọn công việc, môi trường và nhịp sống hiện tại có đang thuận theo bản chất của bạn hay không.',
            ],
            'expression' => [
                'key' => 'expression',
                'title' => 'Chỉ Số Sứ Mệnh (Expression / Destiny)',
                'group' => 'Chỉ số cốt lõi',
                'formula' => 'Tổng quy đổi toàn bộ chữ cái trong Họ và Tên khai sinh theo bảng Pythagorean (1–9).',
                'summary' => 'Thể hiện bộ công cụ bẩm sinh, phong cách làm việc, cách bạn biến ý tưởng thành giá trị cụ thể và tạo đóng góp cho xã hội.',
                'how_to_apply' => 'Nếu Đường Đời là con đường bạn đi thì Sứ Mệnh là phương tiện và năng lực thực thi bạn mang theo để đi hết con đường đó.',
            ],
            'soul_urge' => [
                'key' => 'soul_urge',
                'title' => 'Chỉ Số Linh Hồn (Soul Urge)',
                'group' => 'Chỉ số cốt lõi',
                'formula' => 'Tổng các nguyên âm (U, E, O, A, I và Y theo quy ước ngữ âm) trong Họ Tên khai sinh.',
                'summary' => 'Phản ánh khát khao thầm kín bên trong, điều thực sự làm bạn thấy bình an, có ý nghĩa và động lực sâu thẳm khi không chịu áp lực bên ngoài.',
                'how_to_apply' => 'Giúp bạn hiểu vì sao một công việc hay mối quan hệ bề ngoài tưởng tốt nhưng bên trong bạn vẫn thấy trống trải nếu lệch nhịp Linh Hồn.',
            ],
            'personality' => [
                'key' => 'personality',
                'title' => 'Chỉ Số Nhân Cách (Personality)',
                'group' => 'Chỉ số cốt lõi',
                'formula' => 'Tổng các phụ âm trong Họ Tên khai sinh.',
                'summary' => 'Là lớp ấn tượng đầu tiên người lạ cảm nhận về bạn qua phong thái giao tiếp, ngôn từ, trang phục và cách phản xạ ban đầu.',
                'how_to_apply' => 'So sánh Nhân Cách với Linh Hồn để nhận ra mình có đang gồng vỏ bọc khác xa nội tâm hay đang sống chân thật tự nhiên.',
            ],
            'birthday' => [
                'key' => 'birthday',
                'title' => 'Chỉ Số Ngày Sinh (Birthday Number)',
                'group' => 'Chỉ số cốt lõi',
                'formula' => 'Giữ nguyên ngày sinh dương lịch gốc (1–31) kèm số nền rút gọn.',
                'summary' => 'Đại diện cho món quà năng khiếu bẩm sinh và tài năng phụ trợ đi kèm cùng Đường Đời giúp bạn tạo bản sắc riêng.',
                'how_to_apply' => 'Khai thác điểm mạnh của Ngày Sinh trong công việc hằng ngày như một lợi thế cạnh tranh tự nhiên.',
            ],
            'maturity' => [
                'key' => 'maturity',
                'title' => 'Chỉ Số Trưởng Thành (Maturity)',
                'group' => 'Chỉ số cốt lõi',
                'formula' => 'Đường Đời + Sứ Mệnh (rút gọn giữ Master nếu có).',
                'summary' => 'Mở ra rõ rệt nhất từ khoảng 35–40 tuổi trở đi, cho thấy phiên bản chín muồi, hướng hội tụ cuộc đời và điều bạn hướng tới trong nửa sau hành trình.',
                'how_to_apply' => 'Chủ động chuẩn bị kỹ năng cho chỉ số Trưởng Thành từ sớm giúp bước chuyển trung niên diễn ra nhẹ nhàng, vững vàng.',
            ],
            'attitude' => [
                'key' => 'attitude',
                'title' => 'Chỉ Số Thái Độ (Attitude)',
                'group' => 'Chỉ số mở rộng',
                'formula' => 'Ngày sinh + Tháng sinh dương lịch (rút gọn 1–9).',
                'summary' => 'Cho thấy phản ứng tự động đầu tiên của bạn trước tình huống bất ngờ, áp lực mới hoặc lần đầu làm quen với người lạ.',
                'how_to_apply' => 'Quan sát phản ứng đầu tiên để không bị cảm xúc bản năng dẫn dắt vội vàng khi gặp việc đột xuất.',
            ],
            'balance' => [
                'key' => 'balance',
                'title' => 'Chỉ Số Cân Bằng (Balance)',
                'group' => 'Chỉ số mở rộng',
                'formula' => 'Tổng các chữ cái đầu tiên của từng từ trong Họ Tên khai sinh.',
                'summary' => 'Chiếc mỏ neo tâm lý gợi ý phương pháp giúp bạn lấy lại bình tĩnh, giữ sáng suốt mỗi khi đối diện mâu thuẫn hoặc khủng hoảng.',
                'how_to_apply' => 'Khi rối trí hoặc mất bình tĩnh, hãy quay lại hành động mang tính chất của Chỉ số Cân Bằng để hạ nhiệt và chọn hướng xử lý đúng.',
            ],
            'rational_thought' => [
                'key' => 'rational_thought',
                'title' => 'Chỉ Số Tư Duy Lý Trí (Rational Thought)',
                'group' => 'Chỉ số mở rộng',
                'formula' => 'Tên chính (First Name) + Ngày sinh dương lịch.',
                'summary' => 'Mô tả cách bạn phân tích vấn đề, xâu chuỗi logic, ra quyết định có tính toán hay nghiêng về trực giác và cảm nhận tổng thể.',
                'how_to_apply' => 'Dùng để phối hợp hiệu quả hơn giữa suy nghĩ lý trí và cảm xúc khi giải quyết việc quan trọng.',
            ],
            'hidden_passion' => [
                'key' => 'hidden_passion',
                'title' => 'Đam Mê Tiềm Ẩn (Hidden Passion)',
                'group' => 'Chỉ số mở rộng',
                'formula' => 'Con số (hoặc nhóm số 1–9) xuất hiện lặp lại nhiều lần nhất trong bảng chữ cái Họ Tên.',
                'summary' => 'Là nguồn năng lượng thôi thúc mạnh mẽ giúp bạn làm việc quên thời gian nếu được đặt vào đúng lĩnh vực phù hợp.',
                'how_to_apply' => 'Ưu tiên chọn vai trò hoặc dự án cho phép bạn dùng lặp đi lặp lại thế mạnh thuộc nhóm Đam Mê Tiềm Ẩn.',
            ],
            'karmic_lessons' => [
                'key' => 'karmic_lessons',
                'title' => 'Bài Học Rèn Luyện / Số Thiếu (Karmic Lessons)',
                'group' => 'Chỉ số mở rộng',
                'formula' => 'Những chữ số từ 1 đến 9 không xuất hiện lần nào trong Họ Tên khai sinh.',
                'summary' => 'Không phải điềm xấu hay khuyết tật cố định, mà là những kỹ năng cuộc đời chưa có sẵn theo bản năng để bạn chủ động học và trưởng thành.',
                'how_to_apply' => 'Gọi tên rõ kỹ năng thiếu để tập rèn từng bước nhỏ hoặc kết hợp với người đồng hành bổ trợ thế mạnh đó.',
            ],
            'subconscious_self' => [
                'key' => 'subconscious_self',
                'title' => 'Sức Mạnh Tiềm Thức (Subconscious Self)',
                'group' => 'Chỉ số mở rộng',
                'formula' => '9 trừ đi số lượng chữ số bị thiếu trong Họ Tên khai sinh.',
                'summary' => 'Phản ánh độ tự tin bản năng và khả năng xoay xở ứng biến khi rơi vào tình huống bất ngờ (thang 1–9; từ 7 trở lên thường phản xạ nhanh).',
                'how_to_apply' => 'Nếu chỉ số thấp hơn 7, bạn càng tỏa sáng khi chuẩn bị kỹ lưỡng quy trình và có điểm tựa thông tin rõ ràng.',
            ],
            'bridges' => [
                'key' => 'bridges',
                'title' => 'Chỉ Số Cầu Nối (Bridge Numbers)',
                'group' => 'Chỉ số mở rộng',
                'formula' => 'Hiệu số tuyệt đối giữa các cặp cốt lõi (Đường Đời – Sứ Mệnh; Linh Hồn – Nhân Cách).',
                'summary' => 'Chỉ ra chiếc cầu hành động thực tế giúp bạn thu hẹp khoảng cách giữa con đường phải đi và cách bạn biểu đạt ra bên ngoài.',
                'how_to_apply' => 'Rèn phẩm chất của số Cầu Nối giúp nội tâm và hành động bên ngoài đồng nhất, giảm cảm giác tự giằng co.',
            ],
            'master_numbers' => [
                'key' => 'master_numbers',
                'title' => 'Master Numbers (11/2 · 22/4 · 33/6)',
                'group' => 'Số đặc biệt',
                'formula' => 'Tổng trung gian hoặc rút gọn giữ lại các số đôi 11, 22, 33 trước khi quy về số nền 2, 4, 6.',
                'summary' => 'Biểu trưng cho cường độ nhạy cảm, tầm nhìn hoặc trách nhiệm phụng sự lớn hơn mức thông thường — đi kèm áp lực tự đòi hỏi cao nếu chưa giữ vững số nền.',
                'how_to_apply' => 'Muốn phát huy Master 11, 22, 33 bền vững mà không kiệt sức, trước hết hãy thực hành vững vàng các bài học thực tế của số nền 2, 4, 6.',
            ],
            'karmic_debts' => [
                'key' => 'karmic_debts',
                'title' => 'Chỉ Số Nợ Nghiệp (13/4 · 14/5 · 16/7 · 19/1)',
                'group' => 'Số đặc biệt',
                'formula' => 'Xuất hiện khi tổng trước lúc rút gọn cuối cùng của các chỉ số chính rơi vào 13, 14, 16 hoặc 19.',
                'summary' => 'Là lời nhắc rèn kỷ luật ở một khía cạnh cụ thể (13: sự bền bỉ; 14: điều độ & tự chủ; 16: khiêm nhường & buông cái tôi; 19: độc lập biết sẻ chia), tuyệt đối không phải lời nguyền.',
                'how_to_apply' => 'Đối diện trung thực với thói quen dễ vấp và rèn tính nhất quán mỗi ngày chính là cách chuyển hóa nhẹ nhàng nhất.',
            ],
            'birth_chart' => [
                'key' => 'birth_chart',
                'title' => 'Biểu Đồ Ngày Sinh 3×3 (David A. Phillips)',
                'group' => 'Biểu đồ ngày sinh 3×3',
                'formula' => 'Điền từng chữ số trong ngày/tháng/năm sinh (dd/mm/yyyy) vào 9 ô cố định từ 1 đến 9.',
                'summary' => 'Bản đồ phân bố năng lượng bẩm sinh qua 3 tầng: Hàng ngang dưới 1-4-7 (Thể chất/Thực tế), Hàng giữa 2-5-8 (Tinh thần/Cảm xúc), Hàng trên 3-6-9 (Trí não/Sáng tạo).',
                'how_to_apply' => 'Chạm vào từng ô số để xem tần suất xuất hiện (1 lần vừa đủ, 2+ lần mạnh mẽ, ô trống là vùng rèn luyện mở).',
            ],
            'arrows' => [
                'key' => 'arrows',
                'title' => '8 Đường Mũi Tên Trên Biểu Đồ 3×3',
                'group' => 'Biểu đồ ngày sinh 3×3',
                'formula' => '3 ô liên tiếp thẳng hàng ngang, dọc hoặc chéo cùng có mặt (Mũi tên Đủ) hoặc cùng trống (Mũi tên Trống).',
                'summary' => 'Mũi tên Đủ biểu trưng cho một thế mạnh tự nhiên nổi bật (như Quyết tâm 1-5-9, Trí tuệ 3-6-9, Cân bằng cảm xúc 2-5-8); Mũi tên Trống gợi ý điểm cần rèn để không bị mất nhịp.',
                'how_to_apply' => 'Không thần thánh hóa mũi tên đủ hay lo sợ mũi tên trống — mọi mũi tên trống đều lấp đầy được bằng thói quen chủ động và môi trường rèn luyện.',
            ],
            'pinnacles' => [
                'key' => 'pinnacles',
                'title' => '4 Đỉnh Cao Kim Tự Tháp (Pinnacles)',
                'group' => 'Chu kỳ & Kim tự tháp',
                'formula' => 'Tính theo tổ hợp Tháng, Ngày, Năm sinh; mốc Đỉnh 1 kết thúc ở tuổi 36 trừ số nền Đường Đời, mỗi đỉnh tiếp theo kéo dài 9 năm.',
                'summary' => 'Mỗi Đỉnh Cao là một chương đời 9 năm (riêng Đỉnh 1 kéo dài ~27–35 năm đầu đời) mang sắc thái cơ hội, thành tựu và môi trường trưởng thành riêng.',
                'how_to_apply' => 'Xem Đỉnh Cao đang đi qua để chuẩn bị đúng năng lực ngay trước giai đoạn chuyển giao tuổi.',
            ],
            'challenges' => [
                'key' => 'challenges',
                'title' => '4 Thử Thách Trưởng Thành (Challenges)',
                'group' => 'Chu kỳ & Kim tự tháp',
                'formula' => 'Hiệu số tuyệt đối giữa các cặp rút gọn Tháng, Ngày, Năm sinh song hành với 4 Đỉnh Cao (có thể mang giá trị 0–8).',
                'summary' => 'Là bài kiểm tra tính cách hoặc rào cản tâm lý dễ gặp trong cùng giai đoạn Đỉnh Cao — vượt qua được sẽ gặt hái trọn vẹn thành quả của Đỉnh Cao đó.',
                'how_to_apply' => 'Nhìn Thử Thách như danh sách những thói quen cần điều chỉnh thay vì vận hạn bi quan.',
            ],
            'period_cycles' => [
                'key' => 'period_cycles',
                'title' => '3 Giai Đoạn Cuộc Đời (Gieo Hạt · Kiến Tạo · Thu Hoạch)',
                'group' => 'Chu kỳ & Kim tự tháp',
                'formula' => 'Tháng sinh rút gọn (Giai đoạn 1) · Ngày sinh rút gọn (Giai đoạn 2) · Năm sinh rút gọn (Giai đoạn 3).',
                'summary' => 'Phản ánh phông nền tâm lý tổng quát qua 3 chặng lớn của cuộc đời từ tuổi trẻ, trung niên tới hậu vận.',
                'how_to_apply' => 'Hiểu phông nền dài hạn để không vội nản lòng ở chặng đầu hoặc lơ là việc xây dựng ở chặng giữa.',
            ],
            'personal_year' => [
                'key' => 'personal_year',
                'title' => 'Năm Cá Nhân & Sóng Chu Kỳ 9 Năm',
                'group' => 'Chu kỳ & Kim tự tháp',
                'formula' => 'Ngày sinh + Tháng sinh + Năm dương lịch đang xem (01/01–31/12), rút về 1 chữ số (1–9).',
                'summary' => 'Giúp định vị bạn đang ở mùa nào trong chu kỳ 9 năm: năm 1–3 gieo hạt & mở rộng, năm 4–6 xây nền & chăm tổ ấm, năm 7–9 nhìn sâu, gặt hái & chuyển giao.',
                'how_to_apply' => 'Điều chỉnh mục tiêu công việc, tài chính và tình cảm thuận theo nhịp từng năm thay vì gồng mình chạy ngược mùa.',
            ],
            'personal_month_day' => [
                'key' => 'personal_month_day',
                'title' => 'Tháng Cá Nhân & Ngày Cá Nhân',
                'group' => 'Chu kỳ & Kim tự tháp',
                'formula' => 'Tháng cá nhân = Năm cá nhân + Tháng dương lịch; Ngày cá nhân = Tháng cá nhân + Ngày dương lịch.',
                'summary' => 'Là nhịp vi mô trong từng tháng và từng ngày giúp bạn chọn thời điểm khởi sự, đàm phán hoặc nghỉ ngơi hợp lý.',
                'how_to_apply' => 'Kết hợp xem Lộ trình 12 tháng cá nhân để phân bổ lịch làm việc nặng – nhẹ theo tháng.',
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
