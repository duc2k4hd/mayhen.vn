<?php

declare(strict_types=1);

namespace App\Services\TuVi;

use Illuminate\Support\Facades\File;

/**
 * Engine Lập Lá Số Tử Vi Đẩu Số Chuẩn Nam Phái Việt Nam (`vn_nam_phai_v1` — Tham chiếu Tử Vi Đẩu Số Tân Biên).
 *
 * Nguyên tắc bất biến:
 * - 100% thuật toán tất định (Deterministic), hoàn toàn độc lập với AI.
 * - Cùng dữ liệu sinh + cùng phiên bản quy tắc luôn trả về cùng 1 kết quả y hệt nhau.
 * - Hỗ trợ đầy đủ 4 chế độ giờ sinh (exact_time, earthly_branch, time_window, unknown — không bao giờ gán giờ giả 12:00).
 */
class TuViCalculationEngine
{
    public const SCHOOL_CODE = 'vn_nam_phai_v1';

    public const SCHOOL_NAME = 'Nam Phái Việt Nam (Tử Vi Đẩu Số Tân Biên)';

    public const RULE_VERSION = 'vn_nam_phai_v1.0.0';

    /** @var array<int, string> Offset thuận chiều kim đồng hồ từ Cung Mệnh (0..11) */
    protected const PALACE_CLOCKWISE_OFFSETS = [
        0 => 'menh',
        1 => 'phu_mau',
        2 => 'phuc_duc',
        3 => 'dien_trach',
        4 => 'quan_loc',
        5 => 'no_boc',
        6 => 'thien_di',
        7 => 'tat_ach',
        8 => 'tai_bach',
        9 => 'tu_tuc',
        10 => 'phu_the',
        11 => 'huynh_de',
    ];

    /** @var array<string, array{number: int, name: string, element: string}> */
    protected const ELEMENT_TO_CUC = [
        'Thủy' => ['number' => 2, 'name' => 'Thủy Nhị Cục', 'element' => 'Thủy'],
        'Mộc' => ['number' => 3, 'name' => 'Mộc Tam Cục', 'element' => 'Mộc'],
        'Kim' => ['number' => 4, 'name' => 'Kim Tứ Cục', 'element' => 'Kim'],
        'Thổ' => ['number' => 5, 'name' => 'Thổ Ngũ Cục', 'element' => 'Thổ'],
        'Hỏa' => ['number' => 6, 'name' => 'Hỏa Lục Cục', 'element' => 'Hỏa'],
    ];

    /** @var array<int, int> Lộc Tồn theo Thiên Can năm (0 Giáp .. 9 Quý) */
    protected const LOC_TON_BY_STEM = [
        0 => 2, 1 => 3, 2 => 5, 3 => 6, 4 => 5,
        5 => 6, 6 => 8, 7 => 9, 8 => 11, 9 => 0,
    ];

    /** @var array<int, array{0: int, 1: int}> Khôi / Việt theo Thiên Can năm */
    protected const KHOI_VIET_BY_STEM = [
        0 => [1, 7], 1 => [0, 8], 2 => [11, 9], 3 => [11, 9], 4 => [1, 7],
        5 => [0, 8], 6 => [6, 2], 7 => [6, 2], 8 => [3, 5], 9 => [3, 5],
    ];

    /** @var list<string> */
    protected const THAI_TUE_RING = [
        'Thái Tuế', 'Thiếu Dương', 'Tang Môn', 'Thiếu Âm',
        'Quan Phù', 'Tử Phù', 'Tuế Phá', 'Long Đức',
        'Bạch Hổ', 'Phúc Đức', 'Điếu Khách', 'Trực Phù',
    ];

    /** @var list<string> */
    protected const BAC_SI_RING = [
        'Bác Sĩ', 'Lực Sĩ', 'Thanh Long', 'Tiểu Hao',
        'Tướng Quân', 'Tấu Thư', 'Phi Liêm', 'Hỷ Thần',
        'Bệnh Phù', 'Đại Hao', 'Phục Binh', 'Quan Phủ',
    ];

    /** @var list<string> */
    protected const TRANG_SINH_RING = [
        'Tràng Sinh', 'Mộc Dục', 'Quan Đới', 'Lâm Quan',
        'Đế Vượng', 'Suy', 'Bệnh', 'Tử',
        'Mộ', 'Tuyệt', 'Thai', 'Dưỡng',
    ];

    /** @var array<string, mixed>|null */
    protected static ?array $manifest = null;

    /** @var array<string, array<string, mixed>>|null */
    protected static ?array $palacesMeta = null;

    /** @var array<string, array<string, mixed>>|null */
    protected static ?array $majorStarsMeta = null;

    /** @var array<string, mixed>|null */
    protected static ?array $auxData = null;

    /** @var array<int, array<string, mixed>>|null */
    protected static ?array $zodiacMeta = null;

    protected VietnameseCalendarService $calendar;

    public function __construct(?VietnameseCalendarService $calendar = null)
    {
        $this->calendar = $calendar ?? new VietnameseCalendarService;
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
     * @return array<string, array<string, mixed>>
     */
    public function getPalacesMeta(): array
    {
        $this->bootKnowledgeBase();

        return self::$palacesMeta ?? [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getMajorStarsMeta(): array
    {
        $this->bootKnowledgeBase();

        return self::$majorStarsMeta ?? [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getAuxiliaryStarsMeta(): array
    {
        $this->bootKnowledgeBase();

        return self::$auxData['stars'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getZodiacsMeta(): array
    {
        $this->bootKnowledgeBase();

        return self::$zodiacMeta ?? [];
    }

    /**
     * Lập lá số hoàn chỉnh (hỗ trợ cả exact_time, earthly_branch, time_window và unknown).
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function generateChart(array $input): array
    {
        $this->bootKnowledgeBase();

        $gender = ($input['gender_yin_yang'] ?? 'female') === 'male' ? 'male' : 'female';
        $viewedYear = max(1900, min(2100, (int) ($input['viewed_year'] ?? now('Asia/Ho_Chi_Minh')->year)));
        $nickname = trim(strip_tags((string) ($input['nickname'] ?? '')));

        $birth = $this->calendar->normalizeBirthData($input);
        $timeMode = (string) $birth['birth_time_mode'];

        // 1. Trường hợp KHÔNG BIẾT GIỜ SINH: tuyệt đối không gán giờ giả 12:00
        if ($timeMode === 'unknown') {
            $yearBranch = $birth['pillars']['year']['branch_index'];
            $zodiac = $this->getZodiacsMeta()[$yearBranch] ?? null;

            return [
                'status' => 'partial_no_birth_hour',
                'school_code' => self::SCHOOL_CODE,
                'school_name' => self::SCHOOL_NAME,
                'rule_version' => self::RULE_VERSION,
                'kb_version' => (string) ($this->getManifest()['kb_version'] ?? '1.0.0'),
                'nickname' => $nickname !== '' ? $nickname : 'Bạn',
                'gender_yin_yang' => $gender,
                'viewed_year' => $viewedYear,
                'birth' => $birth,
                'completeness_label' => 'Thiếu giờ sinh — Chỉ hiển thị nền tảng không phụ thuộc giờ sinh',
                'unknown_hour_notice' => 'Hệ thống không tự chọn 12:00 trưa hay tạo giờ sinh ngẫu nhiên. Để lập đầy đủ 12 cung, Cung Mệnh, Cung Thân và Đại Hạn, bạn cần biết giờ sinh hoặc chọn khoảng giờ sinh gần đúng để đối chiếu các lá số khả dĩ.',
                'zodiac_overview' => $zodiac,
                'palaces' => [],
                'candidates' => [],
            ];
        }

        // 2. Trường hợp BIẾT KHOẢNG GIỜ SINH: lập toàn bộ các lá số khả dĩ trong khoảng giờ đó
        if ($timeMode === 'time_window') {
            $windowKey = (string) ($birth['birth_time_window'] ?? 'morning');
            $branches = VietnameseCalendarService::TIME_WINDOWS[$windowKey]['branches'] ?? [3, 4, 5];
            $candidates = [];

            foreach ($branches as $bIdx) {
                $candidateInput = array_merge($input, [
                    'birth_time_mode' => 'earthly_branch',
                    'birth_branch_index' => $bIdx,
                ]);
                $candidateBirth = $this->calendar->normalizeBirthData($candidateInput);
                $built = $this->buildSingleDeterministicChart($candidateBirth, $gender, $viewedYear, $nickname);
                $menhPalace = $built['palaces_by_code']['menh'] ?? null;
                $candidates[] = [
                    'branch_index' => $bIdx,
                    'branch_name' => VietnameseCalendarService::BRANCHES[$bIdx],
                    'hour_range' => VietnameseCalendarService::HOUR_BRANCH_META[$bIdx]['range'],
                    'menh_branch' => $built['summary']['menh_branch_name'],
                    'cuc_name' => $built['summary']['cuc']['name'],
                    'menh_major_stars' => array_map(fn (array $s): string => $s['name'], $menhPalace['major_stars'] ?? []),
                    'chart' => $built,
                ];
            }

            $primaryCandidate = $candidates[0]['chart'];
            $primaryCandidate['status'] = 'window_candidates';
            $primaryCandidate['completeness_label'] = 'Biết khoảng giờ sinh ('.count($candidates).' lá số khả dĩ)';
            $primaryCandidate['window_meta'] = [
                'window_key' => $windowKey,
                'window_label' => VietnameseCalendarService::TIME_WINDOWS[$windowKey]['label'] ?? '',
                'stable_factors' => [
                    'Can Chi Năm sinh: '.$birth['pillars']['year']['label'],
                    'Bản Mệnh Nạp Âm: '.$birth['nap_am']['name'].' (hành '.$birth['nap_am']['element'].')',
                    'Vòng Thái Tuế & Lộc Tồn theo năm sinh không đổi giữa các khung giờ',
                ],
                'shifting_factors' => [
                    'Vị trí Cung Mệnh, Cung Thân và 12 Cung chức năng thay đổi theo từng giờ Địa Chi',
                    'Ngũ Hành Cục, 14 Chính Tinh và Đại Hạn sẽ dịch chuyển theo giờ sinh thực tế',
                ],
            ];
            $primaryCandidate['candidates'] = $candidates;

            return $primaryCandidate;
        }

        // 3. Đủ dữ liệu giờ sinh (exact_time hoặc earthly_branch)
        return $this->buildSingleDeterministicChart($birth, $gender, $viewedYear, $nickname);
    }

    /**
     * Thuật toán lập 1 lá số đơn tất định đầy đủ 16 lớp dữ liệu theo Nam Phái Việt Nam.
     *
     * @param  array<string, mixed>  $birth
     * @return array<string, mixed>
     */
    protected function buildSingleDeterministicChart(array $birth, string $gender, int $viewedYear, string $nickname): array
    {
        $yearStem = (int) $birth['pillars']['year']['stem_index'];
        $yearBranch = (int) $birth['pillars']['year']['branch_index'];
        $isYangYear = (bool) $birth['pillars']['year']['is_yang_stem'];
        $lunarMonth = (int) $birth['lunar']['effective_month'];
        $lunarDay = (int) $birth['lunar']['day'];
        $lunarYear = (int) $birth['lunar']['year'];
        $hourBranch = (int) $birth['pillars']['hour']['branch_index'];

        // Âm Dương Nam Nữ: Dương Nam / Âm Nữ tiến thuận (+1), Âm Nam / Dương Nữ tiến nghịch (-1)
        $isForward = ($isYangYear && $gender === 'male') || (! $isYangYear && $gender === 'female');
        $direction = $isForward ? 1 : -1;
        $yinYangGenderLabel = ($isYangYear ? 'Dương' : 'Âm').' '.($gender === 'male' ? 'Nam' : 'Nữ');

        // 1. Cung Mệnh & Cung Thân
        $menhBranch = (2 + ($lunarMonth - 1) - $hourBranch + 24) % 12;
        $thanBranch = (2 + ($lunarMonth - 1) + $hourBranch) % 12;

        // 2. Thiên Can 12 cung theo Ngũ Hổ Độn (Dần = branch 2)
        $danStem = (($yearStem % 5) * 2 + 2) % 10;
        $branchStems = [];
        for ($b = 0; $b < 12; $b++) {
            $distFromDan = ($b - 2 + 12) % 12;
            $branchStems[$b] = ($danStem + $distFromDan) % 10;
        }

        // 3. Ngũ Hành Cục từ Can-Chi cung Mệnh
        $menhNapAm = $this->calendar->getNapAm($branchStems[$menhBranch], $menhBranch);
        $cuc = self::ELEMENT_TO_CUC[$menhNapAm['element']] ?? self::ELEMENT_TO_CUC['Thủy'];
        $cucNum = $cuc['number'];

        // Khởi tạo 12 cung theo Địa Chi (0=Tý .. 11=Hợi)
        $palacesByBranch = [];
        for ($b = 0; $b < 12; $b++) {
            $clockwiseOffset = ($b - $menhBranch + 12) % 12;
            $palaceCode = self::PALACE_CLOCKWISE_OFFSETS[$clockwiseOffset];
            $meta = self::$palacesMeta[$palaceCode] ?? [];

            $palacesByBranch[$b] = [
                'branch_index' => $b,
                'branch_name' => VietnameseCalendarService::BRANCHES[$b],
                'stem_index' => $branchStems[$b],
                'stem_name' => VietnameseCalendarService::STEMS[$branchStems[$b]],
                'can_chi' => VietnameseCalendarService::STEMS[$branchStems[$b]].' '.VietnameseCalendarService::BRANCHES[$b],
                'code' => $palaceCode,
                'name' => (string) ($meta['name'] ?? $palaceCode),
                'short_name' => (string) ($meta['short_name'] ?? $palaceCode),
                'hanzi' => (string) ($meta['hanzi'] ?? ''),
                'card_title' => (string) ($meta['card_title'] ?? ''),
                'card_subtitle' => (string) ($meta['card_subtitle'] ?? ''),
                'theme_color' => (string) ($meta['theme_color'] ?? '#CFA860'),
                'is_menh' => $b === $menhBranch,
                'is_than' => $b === $thanBranch,
                'is_vo_chinh_dieu' => false,
                'major_stars' => [],
                'aux_stars' => [],
                'tu_hoa' => [],
                'ring_thai_tue' => '',
                'ring_bac_si' => '',
                'ring_trang_sinh' => '',
                'has_tuan' => false,
                'has_triet' => false,
                'dai_han_start' => 0,
                'dai_han_end' => 0,
                'tieu_han_branches' => [],
                'luu_nien_stars' => [],
            ];
        }

        // 4. An 14 Chính Tinh (Tử Vi & Thiên Phủ)
        $tuViBranch = $this->computeTuViBranch($lunarDay, $cucNum);
        $thienPhuBranch = (4 - $tuViBranch + 12) % 12;

        $majorPlacements = [
            'tu_vi' => $tuViBranch,
            'thien_co' => ($tuViBranch - 1 + 12) % 12,
            'thai_duong' => ($tuViBranch - 3 + 12) % 12,
            'vu_khuc' => ($tuViBranch - 4 + 12) % 12,
            'thien_dong' => ($tuViBranch - 5 + 12) % 12,
            'liem_trinh' => ($tuViBranch - 8 + 12) % 12,
            'thien_phu' => $thienPhuBranch,
            'thai_am' => ($thienPhuBranch + 1) % 12,
            'tham_lang' => ($thienPhuBranch + 2) % 12,
            'cu_mon' => ($thienPhuBranch + 3) % 12,
            'thien_tuong' => ($thienPhuBranch + 4) % 12,
            'thien_luong' => ($thienPhuBranch + 5) % 12,
            'that_sat' => ($thienPhuBranch + 6) % 12,
            'pha_quan' => ($thienPhuBranch + 10) % 12,
        ];

        $starBranchMap = [];
        foreach ($majorPlacements as $starCode => $b) {
            $meta = self::$majorStarsMeta[$starCode] ?? null;
            if (! $meta) {
                continue;
            }
            $brightness = (string) ($meta['brightness'][$b] ?? 'B');
            $palacesByBranch[$b]['major_stars'][] = [
                'code' => $starCode,
                'name' => $meta['name'],
                'hanzi' => $meta['hanzi'],
                'element' => $meta['element'],
                'brightness' => $brightness,
                'brightness_label' => $this->brightnessLabel($brightness),
                'archetype' => $meta['archetype'],
            ];
            $starBranchMap[$starCode] = $b;
        }

        // Đánh dấu Vô Chính Diệu
        for ($b = 0; $b < 12; $b++) {
            $palacesByBranch[$b]['is_vo_chinh_dieu'] = $palacesByBranch[$b]['major_stars'] === [];
        }

        // 5. An Hệ Phụ Tinh Chuẩn Nam Phái
        $locTon = self::LOC_TON_BY_STEM[$yearStem];
        $taPhu = (4 + $lunarMonth - 1) % 12;
        $huuBat = (10 - ($lunarMonth - 1) + 24) % 12;
        $vanXuong = (10 - $hourBranch + 12) % 12;
        $vanKhuc = (4 + $hourBranch) % 12;
        [$thienKhoi, $thienViet] = self::KHOI_VIET_BY_STEM[$yearStem];
        $kinhDuong = ($locTon + 1) % 12;
        $daLa = ($locTon - 1 + 12) % 12;
        $diaKhong = (11 - $hourBranch + 12) % 12;
        $diaKiep = (11 + $hourBranch) % 12;
        [$hoaBase, $linhBase] = $this->resolveHoaLinhBase($yearBranch);
        $hoaTinh = ($hoaBase + $hourBranch) % 12;
        $linhTinh = ($linhBase + $hourBranch) % 12;
        $hongLoan = (3 - $yearBranch + 12) % 12;
        $thienHy = ($hongLoan + 6) % 12;
        [$daoHoa, $thienMa] = $this->resolveDaoHoaThienMa($yearBranch);
        [$coThan, $quaTu] = $this->resolveCoThanQuaTu($yearBranch);
        $thienHinh = (9 + $lunarMonth - 1) % 12;
        $thienRieu = (1 + $lunarMonth - 1) % 12;
        $longTri = (4 + $yearBranch) % 12;
        $phuongCac = (10 - $yearBranch + 12) % 12;
        $giaiThan = $phuongCac;
        $thienGiai = (8 + $lunarMonth - 1) % 12;
        $diaGiai = (7 + $lunarMonth - 1) % 12;
        $tamThai = ($taPhu + $lunarDay - 1) % 12;
        $batToa = ($huuBat - ($lunarDay - 1) + 36) % 12;
        $anQuang = ($vanXuong + $lunarDay - 2 + 36) % 12;
        $thienQuy = ($vanKhuc - ($lunarDay - 2) + 36) % 12;
        $thienQuan = [7, 4, 5, 2, 3, 9, 11, 9, 10, 6][$yearStem];
        $thienPhuc = [9, 8, 0, 11, 3, 2, 6, 5, 6, 5][$yearStem];

        $auxPlacements = [
            'ta_phu' => $taPhu,
            'huu_bat' => $huuBat,
            'van_xuong' => $vanXuong,
            'van_khuc' => $vanKhuc,
            'thien_khoi' => $thienKhoi,
            'thien_viet' => $thienViet,
            'loc_ton' => $locTon,
            'thien_ma' => $thienMa,
            'kinh_duong' => $kinhDuong,
            'da_la' => $daLa,
            'hoa_tinh' => $hoaTinh,
            'linh_tinh' => $linhTinh,
            'dia_khong' => $diaKhong,
            'dia_kiep' => $diaKiep,
            'hong_loan' => $hongLoan,
            'thien_hy' => $thienHy,
            'dao_hoa' => $daoHoa,
            'co_than' => $coThan,
            'qua_tu' => $quaTu,
            'thien_hinh' => $thienHinh,
            'thien_rieu' => $thienRieu,
            'long_tri' => $longTri,
            'phuong_cac' => $phuongCac,
            'giai_than' => $giaiThan,
            'thien_giai' => $thienGiai,
            'dia_giai' => $diaGiai,
            'tam_thai' => $tamThai,
            'bat_toa' => $batToa,
            'an_quang' => $anQuang,
            'thien_quy' => $thienQuy,
            'thien_quan' => $thienQuan,
            'thien_phuc' => $thienPhuc,
        ];

        $auxMeta = self::$auxData['stars'] ?? [];
        foreach ($auxPlacements as $code => $b) {
            $m = $auxMeta[$code] ?? null;
            if (! $m) {
                continue;
            }
            $palacesByBranch[$b]['aux_stars'][] = [
                'code' => $code,
                'name' => $m['name'],
                'hanzi' => $m['hanzi'] ?? '',
                'group' => $m['group'],
                'element' => $m['element'],
                'nature' => $m['nature'],
            ];
            $starBranchMap[$code] = $b;
        }

        // 6. An Tứ Hóa nguyên cục theo bảng Nam Phái (`vn_nam_phai_v1`)
        $tuHoaTable = self::$auxData['tu_hoa_by_stem'][(string) $yearStem] ?? [];
        $tuHoaSummary = [];
        $tuHoaLabels = [
            'loc' => ['code' => 'hoa_loc', 'name' => 'Hóa Lộc'],
            'quyen' => ['code' => 'hoa_quyen', 'name' => 'Hóa Quyền'],
            'khoa' => ['code' => 'hoa_khoa', 'name' => 'Hóa Khoa'],
            'ky' => ['code' => 'hoa_ky', 'name' => 'Hóa Kỵ'],
        ];
        foreach ($tuHoaLabels as $key => $metaH) {
            $targetStarCode = (string) ($tuHoaTable[$key] ?? '');
            $targetBranch = $starBranchMap[$targetStarCode] ?? null;
            if ($targetBranch !== null) {
                $targetStarName = self::$majorStarsMeta[$targetStarCode]['name']
                    ?? ($auxMeta[$targetStarCode]['name'] ?? $targetStarCode);
                $palacesByBranch[$targetBranch]['tu_hoa'][] = [
                    'code' => $metaH['code'],
                    'name' => $metaH['name'],
                    'attached_star_code' => $targetStarCode,
                    'attached_star_name' => $targetStarName,
                ];
                $tuHoaSummary[$key] = [
                    'hoa_name' => $metaH['name'],
                    'star_code' => $targetStarCode,
                    'star_name' => $targetStarName,
                    'branch_index' => $targetBranch,
                    'palace_code' => $palacesByBranch[$targetBranch]['code'],
                    'palace_name' => $palacesByBranch[$targetBranch]['name'],
                ];
            }
        }

        // 7. Vòng Thái Tuế (12 sao thuận), Vòng Bác Sĩ (12 sao thuận/nghịch), Vòng Tràng Sinh (12 sao thuận/nghịch)
        $trangSinhStart = match ($cucNum) {
            3 => 11, // Mộc Tam Cục -> Hợi
            4 => 5,  // Kim Tứ Cục -> Tỵ
            6 => 2,  // Hỏa Lục Cục -> Dần
            default => 8, // Thủy Nhị / Thổ Ngũ -> Thân
        };

        for ($i = 0; $i < 12; $i++) {
            $palacesByBranch[($yearBranch + $i) % 12]['ring_thai_tue'] = self::THAI_TUE_RING[$i];
            $palacesByBranch[($locTon + $direction * $i + 24) % 12]['ring_bac_si'] = self::BAC_SI_RING[$i];
            $palacesByBranch[($trangSinhStart + $direction * $i + 24) % 12]['ring_trang_sinh'] = self::TRANG_SINH_RING[$i];
        }

        // 8. Tuần Không & Triệt Lộ
        $tuanStart = ($yearBranch - $yearStem + 12) % 12;
        $tuanBranches = [($tuanStart + 10) % 12, ($tuanStart + 11) % 12];
        $trietBranches = match ($yearStem % 5) {
            0 => [8, 9],
            1 => [6, 7],
            2 => [4, 5],
            3 => [2, 3],
            default => [0, 1],
        };
        foreach ($tuanBranches as $tb) {
            $palacesByBranch[$tb]['has_tuan'] = true;
        }
        foreach ($trietBranches as $tb) {
            $palacesByBranch[$tb]['has_triet'] = true;
        }

        // 9. Đại Hạn (12 chu kỳ x 10 năm khởi tại Mệnh = Số Cục)
        $daiHanTimeline = [];
        for ($i = 0; $i < 12; $i++) {
            $b = ($menhBranch + $direction * $i + 24) % 12;
            $ageStart = $cucNum + ($i * 10);
            $ageEnd = $ageStart + 9;
            $palacesByBranch[$b]['dai_han_start'] = $ageStart;
            $palacesByBranch[$b]['dai_han_end'] = $ageEnd;

            $daiHanTimeline[] = [
                'order' => $i + 1,
                'age_start' => $ageStart,
                'age_end' => $ageEnd,
                'year_start' => $lunarYear + $ageStart - 1,
                'year_end' => $lunarYear + $ageEnd - 1,
                'branch_index' => $b,
                'branch_name' => $palacesByBranch[$b]['branch_name'],
                'palace_code' => $palacesByBranch[$b]['code'],
                'palace_name' => $palacesByBranch[$b]['name'],
                'major_stars' => array_map(fn (array $s): string => $s['name'].' ('.$s['brightness'].')', $palacesByBranch[$b]['major_stars']),
            ];
        }

        // 10. Tiểu Hạn (Nam thuận, Nữ nghịch theo Tam Hợp năm sinh)
        $tieuHanStart = match ($yearBranch % 4) {
            2 => 4,  // Dần-Ngọ-Tuất -> Thìn
            0 => 10, // Thân-Tý-Thìn -> Tuất
            1 => 7,  // Tỵ-Dậu-Sửu -> Mùi
            default => 1, // Hợi-Mão-Mùi -> Sửu
        };
        $tieuHanDir = $gender === 'male' ? 1 : -1;
        for ($i = 0; $i < 12; $i++) {
            $b = ($tieuHanStart + $tieuHanDir * $i + 24) % 12;
            $targetYearBranch = ($yearBranch + $i) % 12;
            $palacesByBranch[$b]['tieu_han_branches'][] = VietnameseCalendarService::BRANCHES[$targetYearBranch];
        }

        // 11. Lưu Niên cho năm xem ($viewedYear)
        $viewedYearCanChi = $this->calendar->getYearCanChi($viewedYear);
        $ageMu = max(1, $viewedYear - $lunarYear + 1);
        $vStem = $viewedYearCanChi['stem_index'];
        $vBranch = $viewedYearCanChi['branch_index'];
        $luuLocTon = self::LOC_TON_BY_STEM[$vStem];
        [, $luuThienMa] = $this->resolveDaoHoaThienMa($vBranch);

        $luuNienPlacements = [
            ['name' => 'L. Thái Tuế', 'branch' => $vBranch],
            ['name' => 'L. Lộc Tồn', 'branch' => $luuLocTon],
            ['name' => 'L. Kình Dương', 'branch' => ($luuLocTon + 1) % 12],
            ['name' => 'L. Đà La', 'branch' => ($luuLocTon - 1 + 12) % 12],
            ['name' => 'L. Thiên Mã', 'branch' => $luuThienMa],
            ['name' => 'L. Tang Môn', 'branch' => ($vBranch + 2) % 12],
            ['name' => 'L. Bạch Hổ', 'branch' => ($vBranch + 8) % 12],
        ];
        foreach ($luuNienPlacements as $lp) {
            $palacesByBranch[$lp['branch']]['luu_nien_stars'][] = $lp['name'];
        }

        $tieuHanOffset = ($vBranch - $yearBranch + 12) % 12;
        $currentTieuHanBranch = ($tieuHanStart + $tieuHanDir * $tieuHanOffset + 24) % 12;

        $currentDaiHan = null;
        foreach ($daiHanTimeline as $dh) {
            if ($ageMu >= $dh['age_start'] && $ageMu <= $dh['age_end']) {
                $currentDaiHan = $dh;
                break;
            }
        }
        $currentDaiHan ??= $daiHanTimeline[0];

        // Bản đồ theo mã cung (menh .. phu_mau) giữ đúng thứ tự 1..12
        $palacesByCode = [];
        foreach (array_keys(self::$palacesMeta ?? []) as $pCode) {
            foreach ($palacesByBranch as $p) {
                if ($p['code'] === $pCode) {
                    $palacesByCode[$pCode] = $p;
                    break;
                }
            }
        }

        return [
            'status' => 'complete',
            'school_code' => self::SCHOOL_CODE,
            'school_name' => self::SCHOOL_NAME,
            'rule_version' => self::RULE_VERSION,
            'kb_version' => (string) ($this->getManifest()['kb_version'] ?? '1.0.0'),
            'nickname' => $nickname !== '' ? $nickname : 'Bạn',
            'gender_yin_yang' => $gender,
            'yin_yang_gender_label' => $yinYangGenderLabel,
            'viewed_year' => $viewedYear,
            'viewed_year_can_chi' => $viewedYearCanChi,
            'age_mu' => $ageMu,
            'birth' => $birth,
            'completeness_label' => $birth['birth_time_mode'] === 'exact_time'
                ? 'Đủ ngày & giờ phút sinh — Lá số hoàn chỉnh 12 cung'
                : 'Đủ ngày & giờ Địa Chi — Lá số hoàn chỉnh 12 cung',
            'summary' => [
                'nap_am' => $birth['nap_am'],
                'cuc' => $cuc,
                'menh_branch_index' => $menhBranch,
                'menh_branch_name' => VietnameseCalendarService::BRANCHES[$menhBranch],
                'than_branch_index' => $thanBranch,
                'than_branch_name' => VietnameseCalendarService::BRANCHES[$thanBranch],
                'than_palace_code' => $palacesByBranch[$thanBranch]['code'],
                'than_palace_name' => $palacesByBranch[$thanBranch]['name'],
                'tuan_branches' => array_map(fn (int $i): string => VietnameseCalendarService::BRANCHES[$i], $tuanBranches),
                'triet_branches' => array_map(fn (int $i): string => VietnameseCalendarService::BRANCHES[$i], $trietBranches),
                'tu_hoa' => $tuHoaSummary,
                'menh_major_stars' => $palacesByBranch[$menhBranch]['major_stars'],
                'current_dai_han' => $currentDaiHan,
                'current_tieu_han' => [
                    'branch_index' => $currentTieuHanBranch,
                    'branch_name' => VietnameseCalendarService::BRANCHES[$currentTieuHanBranch],
                    'palace_code' => $palacesByBranch[$currentTieuHanBranch]['code'],
                    'palace_name' => $palacesByBranch[$currentTieuHanBranch]['name'],
                ],
                'current_luu_nien' => [
                    'year' => $viewedYear,
                    'can_chi' => $viewedYearCanChi['label'],
                    'branch_index' => $vBranch,
                    'palace_code' => $palacesByBranch[$vBranch]['code'],
                    'palace_name' => $palacesByBranch[$vBranch]['name'],
                ],
            ],
            'palaces_by_branch' => array_values($palacesByBranch),
            'palaces_by_code' => $palacesByCode,
            'dai_han_timeline' => $daiHanTimeline,
            'candidates' => [],
        ];
    }

    /**
     * Công thức an sao Tử Vi chuẩn Nam Phái Việt Nam (Tử Vi Đẩu Số Tân Biên).
     */
    public function computeTuViBranch(int $lunarDay, int $cucNumber): int
    {
        $remainder = $lunarDay % $cucNumber;
        $add = $remainder === 0 ? 0 : ($cucNumber - $remainder);
        $quotient = intdiv($lunarDay + $add, $cucNumber);
        $base = (2 + $quotient - 1) % 12;

        if ($add === 0) {
            return $base;
        }

        if (($add % 2) === 1) {
            return ($base - $add + 24) % 12;
        }

        return ($base + $add) % 12;
    }

    /**
     * @return array{0: int, 1: int}
     */
    protected function resolveHoaLinhBase(int $yearBranch): array
    {
        return match ($yearBranch) {
            2, 6, 10 => [1, 3],   // Dần-Ngọ-Tuất: Hỏa Sửu, Linh Mão
            8, 0, 4 => [2, 10],   // Thân-Tý-Thìn: Hỏa Dần, Linh Tuất
            5, 9, 1 => [3, 10],   // Tỵ-Dậu-Sửu: Hỏa Mão, Linh Tuất
            default => [9, 10],   // Hợi-Mão-Mùi: Hỏa Dậu, Linh Tuất
        };
    }

    /**
     * @return array{0: int, 1: int} [Đào Hoa, Thiên Mã]
     */
    protected function resolveDaoHoaThienMa(int $yearBranch): array
    {
        return match ($yearBranch) {
            2, 6, 10 => [3, 8],   // Dần-Ngọ-Tuất
            8, 0, 4 => [9, 2],    // Thân-Tý-Thìn
            5, 9, 1 => [6, 11],   // Tỵ-Dậu-Sửu
            default => [0, 5],    // Hợi-Mão-Mùi
        };
    }

    /**
     * @return array{0: int, 1: int} [Cô Thần, Quả Tú]
     */
    protected function resolveCoThanQuaTu(int $yearBranch): array
    {
        return match ($yearBranch) {
            11, 0, 1 => [2, 10],  // Hợi-Tý-Sửu
            2, 3, 4 => [5, 1],    // Dần-Mão-Thìn
            5, 6, 7 => [8, 4],    // Tỵ-Ngọ-Mùi
            default => [11, 7],   // Thân-Dậu-Tuất
        };
    }

    protected function brightnessLabel(string $code): string
    {
        return match ($code) {
            'M' => 'Miếu địa',
            'V' => 'Vượng địa',
            'Đ' => 'Đắc địa',
            'H' => 'Hãm địa',
            default => 'Bình hòa',
        };
    }

    protected function bootKnowledgeBase(): void
    {
        if (self::$manifest !== null) {
            return;
        }

        self::$manifest = $this->readJson(database_path('data/tuvi/manifest.json'));
        self::$auxData = $this->readJson(database_path('data/tuvi/auxiliary_stars.json'));

        $palacesRaw = $this->readJson(database_path('data/tuvi/palaces_12.json'));
        $pMap = [];
        foreach (($palacesRaw['palaces'] ?? []) as $p) {
            $pMap[(string) $p['code']] = $p;
        }
        self::$palacesMeta = $pMap;

        $majorRaw = $this->readJson(database_path('data/tuvi/major_stars_14.json'));
        $mMap = [];
        foreach (($majorRaw['stars'] ?? []) as $s) {
            $mMap[(string) $s['code']] = $s;
        }
        self::$majorStarsMeta = $mMap;

        $zodiacRaw = $this->readJson(database_path('data/tuvi/zodiac_12.json'));
        $zMap = [];
        foreach (($zodiacRaw['zodiacs'] ?? []) as $z) {
            $zMap[(int) $z['branch_index']] = $z;
        }
        self::$zodiacMeta = $zMap;
    }

    /**
     * @return array<string, mixed>
     */
    protected function readJson(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }
        $decoded = json_decode((string) File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }
}
