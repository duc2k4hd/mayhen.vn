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

        return [
            'relations_by_palace' => $relationsByPalace,
            'verified_patterns' => $verifiedPatterns,
            'palace_digests' => $palaceDigests,
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
