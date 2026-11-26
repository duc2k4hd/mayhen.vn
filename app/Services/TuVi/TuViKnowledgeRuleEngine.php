<?php

declare(strict_types=1);

namespace App\Services\TuVi;

use Illuminate\Support\Facades\File;

/**
 * Knowledge & Rule Engine cho Mây Hẹn – Thiên Mệnh:
 * - Tính Tam Phương Tứ Chính, Xung Chiếu, Giáp Cung & Vô Chính Diệu cho toàn bộ 12 cung.
 * - Xác minh Cách Cục hoàn toàn bằng quy tắc kiểm chứng được (AI không được tự gán tên cách cục).
 * - Xây dựng khung đối chiếu Nhân Duyên Song Mệnh (10 chủ đề cặp đôi — KHÔNG chấm điểm % hợp tuổi hay % ly hôn).
 */
class TuViKnowledgeRuleEngine
{
    /** @var array<int, array{key: string, label: string, focus: string}> */
    public const SYNASTRY_TOPICS = [
        ['key' => 'su_thau_hieu', 'label' => 'Sự thấu hiểu', 'focus' => 'Cách hai người nhìn nhận thế giới và thấu cảm nội tâm của nhau'],
        ['key' => 'yeu_thuong', 'label' => 'Cách thể hiện yêu thương', 'focus' => 'Ngôn ngữ yêu thương tự nhiên và điều khiến đối phương thấy an tâm'],
        ['key' => 'giao_tiep', 'label' => 'Giao tiếp', 'focus' => 'Nhịp trò chuyện, phong cách trao đổi thẳng thắn hay tinh tế'],
        ['key' => 'mau_thuan', 'label' => 'Giải quyết mâu thuẫn', 'focus' => 'Cách hạ nhiệt cảm xúc và tìm điểm cân bằng khi khác quan điểm'],
        ['key' => 'tai_chinh', 'label' => 'Quan điểm tài chính', 'focus' => 'Phong cách tích lũy, chi tiêu và minh bạch ngân sách chung'],
        ['key' => 'muc_tieu', 'label' => 'Mục tiêu chung', 'focus' => 'Tầm nhìn 3–5 năm về sự nghiệp, phong cách sống và ưu tiên gia đình'],
        ['key' => 'gia_dinh', 'label' => 'Gia đình hai bên', 'focus' => 'Nghệ thuật kết nối ấm áp giữa hai gia đình nội ngoại'],
        ['key' => 'sau_hon_nhan', 'label' => 'Cuộc sống sau hôn nhân', 'focus' => 'Phân chia vai trò, nhịp sinh hoạt và nuôi dưỡng sự mới mẻ'],
        ['key' => 'to_am', 'label' => 'Xây dựng tổ ấm', 'focus' => 'Không gian sống, an cư và nề nếp ấm êm trong nhà'],
        ['key' => 'truoc_khi_cuoi', 'label' => 'Điều nên trao đổi trước khi cưới', 'focus' => 'Những câu hỏi thực tế hai bạn nên thống nhất sớm trước ngày trọng đại'],
    ];

    /** @var list<array<string, mixed>>|null */
    protected static ?array $patternsCatalog = null;

    protected TuViCalculationEngine $engine;

    public function __construct(?TuViCalculationEngine $engine = null)
    {
        $this->engine = $engine ?? new TuViCalculationEngine;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getPatternsCatalog(): array
    {
        if (self::$patternsCatalog === null) {
            $path = database_path('data/tuvi/patterns_rules.json');
            $raw = File::exists($path) ? json_decode((string) File::get($path), true) : [];
            self::$patternsCatalog = is_array($raw['patterns'] ?? null) ? $raw['patterns'] : [];
        }

        return self::$patternsCatalog;
    }

    /**
     * Làm giàu lá số bằng quan hệ Tam Phương Tứ Chính, Vô Chính Diệu và danh sách Cách Cục đã xác minh.
     *
     * @param  array<string, mixed>  $chart
     * @return array<string, mixed>
     */
    public function analyzeChart(array $chart): array
    {
        if (($chart['status'] ?? '') === 'partial_no_birth_hour' || empty($chart['palaces_by_branch'])) {
            return [
                'relations_by_palace' => [],
                'verified_patterns' => [],
                'palace_digests' => [],
            ];
        }

        $byBranch = $chart['palaces_by_branch'];
        $palacesMeta = $this->engine->getPalacesMeta();
        $majorMeta = $this->engine->getMajorStarsMeta();

        $relationsByPalace = [];
        $palaceDigests = [];

        foreach ($byBranch as $p) {
            $b = (int) $p['branch_index'];
            $triadA = ($b + 4) % 12;
            $triadB = ($b + 8) % 12;
            $opposite = ($b + 6) % 12;
            $prevAdj = ($b + 11) % 12;
            $nextAdj = ($b + 1) % 12;

            $tptcBranches = [$b, $triadA, $triadB, $opposite];
            $tptcMajorCodes = [];
            $tptcAllCodes = [];
            foreach ($tptcBranches as $tb) {
                foreach ($byBranch[$tb]['major_stars'] as $ms) {
                    $tptcMajorCodes[] = $ms['code'];
                    $tptcAllCodes[] = $ms['code'];
                }
                foreach ($byBranch[$tb]['aux_stars'] as $as) {
                    $tptcAllCodes[] = $as['code'];
                }
                foreach ($byBranch[$tb]['tu_hoa'] as $th) {
                    $tptcAllCodes[] = $th['code'];
                }
            }

            $borrowedMajors = $p['is_vo_chinh_dieu'] ? $byBranch[$opposite]['major_stars'] : [];

            $relationsByPalace[$p['code']] = [
                'branch_index' => $b,
                'triad_branches' => [$triadA, $triadB],
                'triad_palace_codes' => [$byBranch[$triadA]['code'], $byBranch[$triadB]['code']],
                'opposite_branch' => $opposite,
                'opposite_palace_code' => $byBranch[$opposite]['code'],
                'adjacent_branches' => [$prevAdj, $nextAdj],
                'tptc_major_codes' => array_values(array_unique($tptcMajorCodes)),
                'tptc_all_codes' => array_values(array_unique($tptcAllCodes)),
                'borrowed_majors_if_vcd' => $borrowedMajors,
            ];

            $pMeta = $palacesMeta[$p['code']] ?? [];
            $focusMajors = ! empty($p['major_stars']) ? $p['major_stars'] : $borrowedMajors;
            $leadStar = $focusMajors[0] ?? null;
            $leadMeta = $leadStar ? ($majorMeta[$leadStar['code']] ?? null) : null;

            $headlineStars = ! empty($p['major_stars'])
                ? implode(' • ', array_map(fn (array $s): string => $s['name'].' ('.$s['brightness'].')', $p['major_stars']))
                : 'Vô Chính Diệu (chiếu từ '.$byBranch[$opposite]['name'].': '.implode(', ', array_map(fn (array $s): string => $s['name'], $borrowedMajors)).')';

            $palaceDigests[$p['code']] = [
                'palace_code' => $p['code'],
                'palace_name' => $p['name'],
                'hanzi' => $p['hanzi'],
                'branch_name' => $p['branch_name'],
                'is_than' => $p['is_than'],
                'is_vo_chinh_dieu' => $p['is_vo_chinh_dieu'],
                'stars_headline' => $headlineStars,
                'classical_meaning' => (string) ($pMeta['classical_meaning'] ?? ''),
                'modern_meaning' => (string) ($pMeta['modern_meaning'] ?? ''),
                'lead_star_insight' => $leadMeta ? (string) $leadMeta['core_meaning'] : (string) ($pMeta['vcd_note'] ?? ''),
                'strength_hint' => $leadMeta ? (string) $leadMeta['strengths'] : 'Linh hoạt thích ứng, biết tận dụng nguồn lực xung chiếu.',
                'watch_out_hint' => $leadMeta ? (string) $leadMeta['watch_outs'] : 'Cần xác định trọng tâm rõ ràng thay vì phân tán theo hoàn cảnh.',
                'common_misconception' => (string) ($pMeta['common_misconception'] ?? ''),
                'vcd_note' => $p['is_vo_chinh_dieu'] ? (string) ($pMeta['vcd_note'] ?? '') : null,
            ];
        }

        $verifiedPatterns = $this->evaluatePatterns($chart, $relationsByPalace);
        $auxMeta = $this->engine->getAuxiliaryStarsMeta();
        $palaceDeepReports = $this->buildPalaceDeepReports($chart, $relationsByPalace, $palaceDigests, $majorMeta, $auxMeta);
        $careerOrientation = $this->buildCareerOrientation($chart, $relationsByPalace, $majorMeta);
        $thienBanOverview = $this->buildThienBanOverview($chart);

        return [
            'relations_by_palace' => $relationsByPalace,
            'verified_patterns' => $verifiedPatterns,
            'palace_digests' => $palaceDigests,
            'palace_deep_reports' => $palaceDeepReports,
            'career_orientation' => $careerOrientation,
            'thien_ban_overview' => $thienBanOverview,
        ];
    }

    /**
     * Xây dựng báo cáo luận giải chi tiết & chỉ số cân bằng minh bạch cho đủ 12 cung.
     *
     * @param  array<string, mixed>  $chart
     * @param  array<string, array<string, mixed>>  $relations
     * @param  array<string, array<string, mixed>>  $digests
     * @param  array<string, array<string, mixed>>  $majorMeta
     * @param  array<string, array<string, mixed>>  $auxMeta
     * @return list<array<string, mixed>>
     */
    protected function buildPalaceDeepReports(
        array $chart,
        array $relations,
        array $digests,
        array $majorMeta,
        array $auxMeta
    ): array {
        $byBranch = $chart['palaces_by_branch'] ?? [];
        $byCode = $chart['palaces_by_code'] ?? [];
        $groupMap = [
            'menh' => 'core', 'quan_loc' => 'core', 'tai_bach' => 'core', 'thien_di' => 'core',
            'phu_the' => 'family', 'tu_tuc' => 'family', 'phu_mau' => 'family', 'huynh_de' => 'family',
            'phuc_duc' => 'foundation', 'dien_trach' => 'foundation', 'tat_ach' => 'foundation', 'no_boc' => 'foundation',
        ];
        $order = ['menh', 'quan_loc', 'tai_bach', 'phu_the', 'thien_di', 'phuc_duc', 'dien_trach', 'phu_mau', 'huynh_de', 'tu_tuc', 'tat_ach', 'no_boc'];
        $brightnessWeight = ['M' => 14, 'V' => 10, 'Đ' => 6, 'B' => 2, 'H' => -7];

        $reports = [];
        foreach ($order as $code) {
            $p = $byCode[$code] ?? null;
            if (! $p) {
                continue;
            }
            $rel = $relations[$code] ?? [];
            $digest = $digests[$code] ?? [];

            // 1. Tính điểm cân bằng tất định minh bạch căn cứ sao (35..95)
            $score = 62.0;
            $positivePoints = [];
            $cautionPoints = [];

            if (! empty($p['major_stars'])) {
                foreach ($p['major_stars'] as $ms) {
                    $b = (string) ($ms['brightness'] ?? 'B');
                    $score += $brightnessWeight[$b] ?? 0;
                    $mInfo = $majorMeta[$ms['code']] ?? [];
                    $sTitle = $ms['name'].' ('.$ms['brightness_label'].')';
                    if (in_array($b, ['M', 'V', 'Đ'], true)) {
                        $positivePoints[] = [
                            'star' => $sTitle,
                            'text' => (string) ($mInfo['strengths'] ?? $mInfo['core_meaning'] ?? 'Chính tinh đắc cách tạo nền lực rõ ràng cho cung này.'),
                        ];
                    } elseif ($b === 'H') {
                        $cautionPoints[] = [
                            'star' => $sTitle,
                            'text' => (string) ($mInfo['watch_outs'] ?? 'Chính tinh ở thế Hãm địa đòi hỏi sự kiên trì, điềm tĩnh và rèn kỷ luật thực tế.'),
                        ];
                    } else {
                        $positivePoints[] = [
                            'star' => $sTitle,
                            'text' => (string) ($mInfo['core_meaning'] ?? 'Chính tinh ở thế Bình hòa, phát huy tốt khi gặp cát tinh trợ lực.'),
                        ];
                    }
                }
            } else {
                $borrowed = $rel['borrowed_majors_if_vcd'] ?? [];
                foreach ($borrowed as $bm) {
                    $score += (($brightnessWeight[$bm['brightness'] ?? 'B'] ?? 0) * 0.65);
                }
                $positivePoints[] = [
                    'star' => 'Vô Chính Diệu (Mượn chính tinh xung chiếu)',
                    'text' => (string) ($digest['vcd_note'] ?: 'Cung Vô Chính Diệu giàu khả năng thích nghi, tiếp nhận năng lượng linh hoạt từ cung xung chiếu và Tam Hợp.'),
                ];
            }

            // Tứ Hóa tại cung
            foreach ($p['tu_hoa'] ?? [] as $th) {
                $thCode = (string) ($th['code'] ?? '');
                $thMeta = $auxMeta[$thCode] ?? [];
                if ($thCode === 'hoa_ky') {
                    $score -= 6;
                    $cautionPoints[] = [
                        'star' => $th['name'].' đi cùng '.$th['attached_star_name'],
                        'text' => (string) ($thMeta['meaning'] ?? 'Cần minh bạch giao tiếp, tránh cầu toàn quá mức hoặc tự giữ bận tâm trong lòng.'),
                    ];
                } else {
                    $score += 7;
                    $positivePoints[] = [
                        'star' => $th['name'].' đi cùng '.$th['attached_star_name'],
                        'text' => (string) ($thMeta['meaning'] ?? 'Tứ Hóa cát tinh gia tăng thuận lợi, uy tín và nguồn lực thực tế.'),
                    ];
                }
            }

            // Phụ tinh Cát / Sát tại cung
            foreach ($p['aux_stars'] ?? [] as $aux) {
                $aMeta = $auxMeta[$aux['code']] ?? [];
                $nature = (string) ($aux['nature'] ?? 'trung');
                if ($nature === 'cat') {
                    $score += ($aux['group'] ?? '') === 'luc_cat' ? 4.5 : 2.5;
                    if (count($positivePoints) < 5) {
                        $positivePoints[] = [
                            'star' => $aux['name'],
                            'text' => (string) ($aMeta['meaning'] ?? 'Cát tinh bổ trợ thuận lợi và tạo điểm tựa quý nhân.'),
                        ];
                    }
                } elseif ($nature === 'thach_thuc') {
                    $score -= 5.0;
                    if (count($cautionPoints) < 4) {
                        $cautionPoints[] = [
                            'star' => $aux['name'],
                            'text' => (string) ($aMeta['meaning'] ?? 'Sao mang tính thử thách, nhắc nhở rèn bản lĩnh và giữ sự tỉnh táo trước áp lực.'),
                        ];
                    }
                }
            }

            // Tuần / Triệt điều tiết biên độ
            if (! empty($p['has_tuan'])) {
                $score = 60 + ($score - 60) * 0.78;
                $cautionPoints[] = [
                    'star' => 'Tuần Không',
                    'text' => 'Đóng vai trò vùng chuyển nhịp: làm dịu bớt tính cực đoan của sát tinh nhưng cũng khiến thành quả đến chậm chắc hơn ở giai đoạn đầu.',
                ];
            }
            if (! empty($p['has_triet'])) {
                $score = 60 + ($score - 60) * 0.76;
                $cautionPoints[] = [
                    'star' => 'Triệt Lộ',
                    'text' => 'Tạo bước ngoặt tự lập rõ rệt trong chặng đầu đời; sau tuổi 30 ảnh hưởng dần thoáng mở khi bạn đã vững nền tảng.',
                ];
            }

            $finalScore = (int) max(38, min(94, round($score)));
            if ($finalScore >= 80) {
                $tierCode = 'excellent';
                $tierLabel = 'Rất Vượng';
            } elseif ($finalScore >= 65) {
                $tierCode = 'good';
                $tierLabel = 'Khá Tốt';
            } elseif ($finalScore >= 52) {
                $tierCode = 'balanced';
                $tierLabel = 'Bình Hòa';
            } else {
                $tierCode = 'watch';
                $tierLabel = 'Cần Lưu Tâm';
            }

            $triadNames = array_map(
                fn (int $tb): string => $byBranch[$tb]['name'] ?? '',
                $rel['triad_branches'] ?? []
            );
            $oppName = isset($rel['opposite_branch']) ? ($byBranch[$rel['opposite_branch']]['name'] ?? '') : '';

            if ($cautionPoints === []) {
                $cautionPoints[] = [
                    'star' => 'Nhịp cân bằng ổn định',
                    'text' => 'Cung ít chịu xung lực sát tinh mạnh — vẫn nên chủ động đặt mục tiêu rõ ràng để tránh sự an nhàn làm chậm nhịp phát triển.',
                ];
            }

            $reports[] = [
                'code' => $code,
                'group' => $groupMap[$code] ?? 'foundation',
                'name' => $p['name'],
                'short_name' => $p['short_name'],
                'hanzi' => $p['hanzi'],
                'can_chi' => $p['can_chi'],
                'dai_han' => ($p['dai_han_start'] ?? 0).'–'.($p['dai_han_end'] ?? 0).' tuổi',
                'is_than' => (bool) ($p['is_than'] ?? false),
                'is_lai_nhan' => (bool) ($p['is_lai_nhan'] ?? false),
                'is_current_tieu_han' => (bool) ($p['is_current_tieu_han'] ?? false),
                'is_current_luu_nien' => (bool) ($p['is_current_luu_nien'] ?? false),
                'score' => $finalScore,
                'tier_code' => $tierCode,
                'tier_label' => $tierLabel,
                'stars_headline' => (string) ($digest['stars_headline'] ?? ''),
                'modern_meaning' => (string) ($digest['modern_meaning'] ?? ''),
                'strength_hint' => (string) ($digest['strength_hint'] ?? ''),
                'watch_out_hint' => (string) ($digest['watch_out_hint'] ?? ''),
                'tptc_summary' => 'Tam hợp: '.implode(' & ', array_filter($triadNames)).($oppName !== '' ? ' • Xung chiếu: '.$oppName : ''),
                'rings_summary' => 'Thái Tuế: '.($p['ring_thai_tue'] ?? '').' • Bác Sĩ: '.($p['ring_bac_si'] ?? '').' • Tràng Sinh: '.($p['ring_trang_sinh'] ?? ''),
                'positive_points' => $positivePoints,
                'caution_points' => $cautionPoints,
            ];
        }

        return $reports;
    }

    /**
     * Suy luận thiên hướng nghề nghiệp thực tế từ Tam Hợp Mệnh – Tài – Quan.
     *
     * @param  array<string, mixed>  $chart
     * @param  array<string, array<string, mixed>>  $relations
     * @param  array<string, array<string, mixed>>  $majorMeta
     * @return array{headline: string, summary: string, roles: list<string>}
     */
    protected function buildCareerOrientation(array $chart, array $relations, array $majorMeta): array
    {
        $quan = $chart['palaces_by_code']['quan_loc'] ?? null;
        $menh = $chart['palaces_by_code']['menh'] ?? null;
        $tai = $chart['palaces_by_code']['tai_bach'] ?? null;

        $leadStars = array_merge(
            $quan['major_stars'] ?? [],
            $menh['major_stars'] ?? [],
            $tai['major_stars'] ?? []
        );
        $codes = array_values(array_unique(array_column($leadStars, 'code')));

        $roleMap = [
            'tu_vi' => ['Quản trị cấp cao & Điều hành', 'Hoạch định chiến lược doanh nghiệp'],
            'thien_phu' => ['Tài chính, Ngân hàng & Quản trị tài sản', 'Điều hành hệ thống quy mô lớn'],
            'thai_duong' => ['Truyền thông, Ngoại giao & Cộng đồng', 'Giáo dục, Đào tạo & Dẫn dắt đội ngũ'],
            'vu_khuc' => ['Tài chính, Đầu tư & Kinh doanh thực chiến', 'Quản trị dự án & Tối ưu hiệu suất'],
            'thien_co' => ['Công nghệ, Phân tích hệ thống & Tư vấn', 'Nghiên cứu chiến lược & Thiết kế giải pháp'],
            'thien_dong' => ['Sáng tạo dịch vụ, Du lịch & Nghệ thuật', 'Chăm sóc khách hàng & Văn hóa tổ chức'],
            'liem_trinh' => ['Pháp lý, Quản trị rủi ro & Kỷ luật vận hành', 'Thiết kế thẩm mỹ & Kinh doanh sáng tạo'],
            'thai_am' => ['Bất động sản, Kiến trúc & Quản trị quỹ', 'Tâm lý, Biên tập & Nghệ thuật tinh tế'],
            'tham_lang' => ['Marketing, Đối ngoại & Phát triển thị trường', 'Ẩm thực, Thời trang & Trải nghiệm khách hàng'],
            'cu_mon' => ['Tư vấn chuyên môn, Luật & Đàm phán', 'Giảng dạy, Phân tích & Truyền thông nội dung'],
            'thien_tuong' => ['Điều phối nhân sự, Cố vấn & Dịch vụ cao cấp', 'Y tế, Giáo dục & Quản trị vận hành'],
            'thien_luong' => ['Giáo dục, Cố vấn chiến lược & Kiểm toán', 'Y tế, Công tác xã hội & Chuyên môn chuẩn mực'],
            'that_sat' => ['Khởi nghiệp, Tái cấu trúc & Mở thị trường mới', 'Kỹ thuật, Công nghiệp & Quản lý dự án tốc độ cao'],
            'pha_quan' => ['Đổi mới sản phẩm, Sáng tạo & Chuyển đổi số', 'Kinh doanh đột phá & Xây dựng mô hình mới'],
        ];

        $roles = [];
        foreach (array_slice($codes, 0, 4) as $c) {
            foreach ($roleMap[$c] ?? [] as $r) {
                $roles[] = $r;
            }
        }
        $roles = array_values(array_unique($roles));
        if ($roles === []) {
            $roles = ['Cố vấn & Điều phối đa nhiệm', 'Kinh doanh dịch vụ linh hoạt', 'Quản trị vận hành & Phát triển đội nhóm'];
        }

        $primaryStarName = $quan['major_stars'][0]['name'] ?? ($menh['major_stars'][0]['name'] ?? 'Vô Chính Diệu');
        $primaryMeta = $majorMeta[$quan['major_stars'][0]['code'] ?? ($menh['major_stars'][0]['code'] ?? '')] ?? null;

        return [
            'headline' => 'Thiên hướng phát huy mạnh theo trục Mệnh – Tài – Quan ('.$primaryStarName.')',
            'summary' => $primaryMeta
                ? (string) ($primaryMeta['career_style'] ?? $primaryMeta['strengths'] ?? 'Trục Mệnh – Tài – Quan cho thấy bạn đạt hiệu quả cao nhất khi chọn công việc phát huy đúng thế mạnh chủ đạo và có lộ trình tăng trưởng minh bạch.')
                : 'Trục Mệnh – Tài – Quan của bạn giàu độ linh hoạt — phù hợp các vai trò kết nối, cố vấn hoặc điều phối đa nguồn lực.',
            'roles' => array_slice($roles, 0, 6),
        ];
    }

    /**
     * Tổng hợp 6 trụ Thiên Bàn chuẩn Nam Phái (có fallback cho lá số cũ lưu trước bản cập nhật).
     *
     * @param  array<string, mixed>  $chart
     * @return array<string, mixed>
     */
    protected function buildThienBanOverview(array $chart): array
    {
        $summary = $chart['summary'] ?? [];
        $birth = $chart['birth'] ?? [];
        $yearBranch = (int) ($birth['pillars']['year']['branch_index'] ?? 0);
        $branches = VietnameseCalendarService::BRANCHES;

        $zodiacHarmony = $summary['zodiac_harmony'] ?? [
            'tam_hop' => [$branches[($yearBranch + 4) % 12], $branches[($yearBranch + 8) % 12]],
            'luc_hop' => $branches[[0 => 1, 1 => 0, 2 => 11, 11 => 2, 3 => 10, 10 => 3, 4 => 9, 9 => 4, 5 => 8, 8 => 5, 6 => 7, 7 => 6][$yearBranch] ?? 0],
            'tu_hanh_xung' => [$branches[($yearBranch + 3) % 12], $branches[($yearBranch + 6) % 12], $branches[($yearBranch + 9) % 12]],
        ];

        return [
            'nap_am' => ($summary['nap_am']['name'] ?? '').' (Hành '.($summary['nap_am']['element'] ?? '').')',
            'cuc' => $summary['cuc']['name'] ?? '',
            'cuc_menh_relation' => $summary['cuc_menh_relation'] ?? 'Mệnh Cục tương phối hài hòa',
            'am_duong_gender' => $chart['yin_yang_gender_label'] ?? '',
            'am_duong_ly' => $summary['am_duong_ly'] ?? 'Âm Dương Thuận Lý',
            'chu_menh' => $summary['chu_menh'] ?? '',
            'chu_than' => $summary['chu_than'] ?? '',
            'than_palace_name' => $summary['than_palace_name'] ?? '',
            'lai_nhan_palace_name' => $summary['lai_nhan_palace_name'] ?? ($chart['palaces_by_code']['menh']['name'] ?? 'Cung Mệnh'),
            'can_luong' => $summary['can_luong'] ?? null,
            'zodiac_harmony' => $zodiacHarmony,
        ];
    }

    /**
     * So sánh có căn cứ giữa 2 lá số độc lập cho Nhân Duyên Song Mệnh (KHÔNG chấm % hợp tuổi/ly hôn).
     *
     * @param  array<string, mixed>  $chartA
     * @param  array<string, mixed>  $chartB
     * @return array<string, mixed>
     */
    public function buildSynastryBasis(array $chartA, array $chartB): array
    {
        $elA = (string) ($chartA['birth']['nap_am']['element'] ?? 'Thổ');
        $elB = (string) ($chartB['birth']['nap_am']['element'] ?? 'Thổ');
        $elementDynamic = $this->describeElementDynamic($elA, $elB);

        $menhA = $chartA['palaces_by_code']['menh'] ?? null;
        $menhB = $chartB['palaces_by_code']['menh'] ?? null;
        $phuTheA = $chartA['palaces_by_code']['phu_the'] ?? null;
        $phuTheB = $chartB['palaces_by_code']['phu_the'] ?? null;
        $dienA = $chartA['palaces_by_code']['dien_trach'] ?? null;
        $dienB = $chartB['palaces_by_code']['dien_trach'] ?? null;
        $phucA = $chartA['palaces_by_code']['phuc_duc'] ?? null;
        $phucB = $chartB['palaces_by_code']['phuc_duc'] ?? null;

        $starsToStr = static fn (?array $p): string => $p && ! empty($p['major_stars'])
            ? implode(', ', array_map(fn (array $s): string => $s['name'].' ('.$s['brightness'].')', $p['major_stars']))
            : ($p ? 'Vô Chính Diệu' : 'Thiếu giờ sinh');

        return [
            'provenance' => 'mayhen_modern_guide',
            'disclaimer' => 'Nhân Duyên Song Mệnh là khung tư vấn thấu hiểu mối quan hệ của Mây Hẹn dựa trên đối chiếu hai lá số độc lập — không phải điểm số hợp tuổi hay lời tiên đoán hôn nhân.',
            'person_a' => [
                'nickname' => $chartA['nickname'] ?? 'Người thứ nhất',
                'nap_am' => $chartA['birth']['nap_am']['name'] ?? '',
                'element' => $elA,
                'menh_stars' => $starsToStr($menhA),
                'phu_the_stars' => $starsToStr($phuTheA),
                'dien_trach_stars' => $starsToStr($dienA),
                'phuc_duc_stars' => $starsToStr($phucA),
            ],
            'person_b' => [
                'nickname' => $chartB['nickname'] ?? 'Người thứ hai',
                'nap_am' => $chartB['birth']['nap_am']['name'] ?? '',
                'element' => $elB,
                'menh_stars' => $starsToStr($menhB),
                'phu_the_stars' => $starsToStr($phuTheB),
                'dien_trach_stars' => $starsToStr($dienB),
                'phuc_duc_stars' => $starsToStr($phucB),
            ],
            'element_dynamic' => $elementDynamic,
            'topics' => self::SYNASTRY_TOPICS,
        ];
    }

    /**
     * Đánh giá Cách Cục hoàn toàn tất định theo `patterns_rules.json`.
     *
     * @param  array<string, mixed>  $chart
     * @param  array<string, array<string, mixed>>  $relations
     * @return list<array<string, mixed>>
     */
    protected function evaluatePatterns(array $chart, array $relations): array
    {
        $catalog = $this->getPatternsCatalog();
        $menhPalace = $chart['palaces_by_code']['menh'] ?? null;
        $menhRel = $relations['menh'] ?? null;
        $phuTheRel = $relations['phu_the'] ?? null;

        if (! $menhPalace || ! $menhRel) {
            return [];
        }

        $byBranch = $chart['palaces_by_branch'];
        $triadBranches = array_merge([$menhRel['branch_index']], $menhRel['triad_branches']);
        $menhTriadMajors = [];
        foreach ($triadBranches as $tb) {
            foreach ($byBranch[$tb]['major_stars'] as $ms) {
                $menhTriadMajors[] = $ms['code'];
            }
        }
        $menhTriadMajors = array_values(array_unique($menhTriadMajors));

        $matched = [];
        foreach ($catalog as $rule) {
            $type = (string) ($rule['type'] ?? '');
            $isMatch = false;
            $evidence = '';

            if ($type === 'tam_hop_menh') {
                $req = $rule['required_stars_in_menh_triad'] ?? [];
                $minMatch = (int) ($rule['min_match'] ?? count($req));
                $hit = array_values(array_intersect($req, $menhTriadMajors));
                if (count($hit) >= $minMatch) {
                    $isMatch = true;
                    $evidence = 'Tam hợp Mệnh–Tài–Quan hội đủ '.count($hit).' chính tinh trong bộ: '.implode(', ', $hit);
                }
            } elseif ($type === 'menh_tptc') {
                $req = $rule['required_stars_in_menh_tptc'] ?? [];
                $hit = array_values(array_intersect($req, $menhRel['tptc_all_codes'] ?? []));
                if (count($hit) === count($req) && $req !== []) {
                    $isMatch = true;
                    $evidence = 'Tam Phương Tứ Chính cung Mệnh hội đủ: '.implode(', ', $hit);
                }
            } elseif ($type === 'phu_the_tptc' && $phuTheRel) {
                $req = $rule['required_stars_in_phu_the_tptc'] ?? [];
                $hit = array_values(array_intersect($req, $phuTheRel['tptc_all_codes'] ?? []));
                if (count($hit) === count($req) && $req !== []) {
                    $isMatch = true;
                    $evidence = 'Tam Phương Tứ Chính cung Phu Thê hội: '.implode(', ', $hit);
                }
            } elseif ($type === 'special_vcd' && ! empty($menhPalace['is_vo_chinh_dieu'])) {
                $isMatch = true;
                $evidence = 'Cung Mệnh tại '.$menhPalace['branch_name'].' không có chính tinh tọa thủ (mượn ánh sáng cung Thiên Di).';
            }

            if ($isMatch) {
                $matched[] = [
                    'code' => (string) $rule['code'],
                    'name' => (string) $rule['name'],
                    'hanzi' => (string) ($rule['hanzi'] ?? ''),
                    'source' => (string) ($rule['source'] ?? 'Tử Vi Nam Phái'),
                    'evidence' => $evidence,
                    'plain_explanation' => (string) ($rule['plain_explanation'] ?? ''),
                    'expert_explanation' => (string) ($rule['expert_explanation'] ?? ''),
                    'provenance' => 'school_rule',
                ];
            }
        }

        return $matched;
    }

    /**
     * @return array{label: string, summary: string}
     */
    protected function describeElementDynamic(string $elA, string $elB): array
    {
        $generates = ['Kim' => 'Thủy', 'Thủy' => 'Mộc', 'Mộc' => 'Hỏa', 'Hỏa' => 'Thổ', 'Thổ' => 'Kim'];

        if ($elA === $elB) {
            return [
                'label' => "Tương Hòa ({$elA} – {$elB})",
                'summary' => "Hai bản mệnh cùng hành {$elA}: dễ tìm tiếng nói chung về giá trị sống và nhịp sinh hoạt; cần tránh giữ cái tôi quá giống nhau khi bất đồng.",
            ];
        }
        if (($generates[$elA] ?? '') === $elB || ($generates[$elB] ?? '') === $elA) {
            return [
                'label' => "Tương Sinh Bổ Trợ ({$elA} – {$elB})",
                'summary' => "Năng lượng bản mệnh {$elA} và {$elB} bổ trợ tự nhiên cho nhau: một người tạo điểm tựa để người kia phát triển; nên giữ sự cân bằng cho – nhận hai chiều.",
            ];
        }

        return [
            'label' => "Khác Biệt Mài Giũa ({$elA} – {$elB})",
            'summary' => "Bản mệnh {$elA} và {$elB} mang hai góc nhìn khác biệt — không đồng nghĩa không hợp nhau, mà là cơ hội bù trừ điểm mù nếu hai bạn giao tiếp cởi mở và tôn trọng nhịp riêng.",
        ];
    }
}
