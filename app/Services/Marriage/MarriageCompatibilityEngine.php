<?php

declare(strict_types=1);

namespace App\Services\Marriage;

use App\Services\Bazi\BaziRegistry;
use App\Services\Calendar\VietnameseCalendarEngine;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Engine Xem Cao Ly Đầu Hình & Xem Tuổi Vợ Chồng Tổng Hợp (Mây Hẹn – Duyên Hợp).
 *
 * - Tái sử dụng VietnameseCalendarEngine (xử lý ngày sinh trước/sau Tết, Nạp Âm) và BaziRegistry (10 Can, 12 Chi).
 * - Trình bày độc lập các phương pháp: Can Chi · Thiên Can · Địa Chi · Nạp Âm · Cung Phi & Du Niên · Cao Ly Đầu Hình · Kim Lâu năm cưới.
 * - TUYỆT ĐỐI không gộp điểm hay tạo tỷ lệ % hợp nhau giả.
 */
class MarriageCompatibilityEngine
{
    public const VERSION = 'mayhen_duyen_hop_v1.0';

    public function __construct(
        protected VietnameseCalendarEngine $calendar,
        protected KuaDuNienEngine $kuaEngine
    ) {}

    /**
     * Phân tích trọn bộ nhân duyên đa phương pháp cho 2 người.
     *
     * @param  array<string, mixed>  $husbandInput
     * @param  array<string, mixed>  $wifeInput
     * @return array<string, mixed>
     */
    public function analyze(array $husbandInput, array $wifeInput, ?int $weddingYear = null): array
    {
        $husband = $this->normalizePerson($husbandInput, 'male');
        $wife = $this->normalizePerson($wifeInput, 'female');

        $warnings = array_merge($husband['warnings'], $wife['warnings']);

        // 1. Cao Ly Đầu Hình (CÓ HƯỚNG: Can chồng × Chi vợ)
        $caoLy = CaoLyRegistry::lookup($husband['stem_index'], $wife['branch_index']);

        // 2. Thiên Can hợp hôn
        $stemRelation = $this->analyzeStems($husband['stem_index'], $wife['stem_index']);

        // 3. Địa Chi hợp hôn (phân biệt rõ Tam Hợp, Lục Hợp, Lục Xung, Lục Hại, Tự Hình)
        $branchRelation = $this->analyzeBranches($husband['branch_index'], $wife['branch_index']);

        // 4. Ngũ Hành Nạp Âm (tách bạch hoàn toàn với Cung Phi & Nhật Chủ Bát Tự)
        $napAmRelation = $this->analyzeElementPair($husband['nap_am']['element'], $wife['nap_am']['element'], 'Nạp Âm');

        // 5. Cung Phi & Bát Trạch Du Niên
        $kuaH = $this->kuaEngine->calculateKua($husband['lunar_year'], 'male');
        $kuaW = $this->kuaEngine->calculateKua($wife['lunar_year'], 'female');
        $duNien = $this->kuaEngine->lookupDuNien($kuaH['number'], $kuaW['number']);

        // 6. Xem Năm Kết Hôn (Kim Lâu tuổi mụ cô dâu + ghi chú minh bạch Tam Tai / Hoang Ốc)
        $targetWeddingYear = $weddingYear ?? ($this->calendar->todayVn()->year + 1);
        $weddingYearAnalysis = $this->analyzeWeddingYear($husband, $wife, $targetWeddingYear);

        // 7. Thống kê các lớp phương pháp đã kiểm tra (không chấm % hợp nhau giả)
        $evidence = $this->buildEvidenceList($husband, $wife, $caoLy, $stemRelation, $branchRelation, $napAmRelation, $duNien);

        // 8. 12 Chủ đề tình duyên ứng dụng & 8 câu hỏi trước hôn nhân
        $themes = $this->buildTwelveThemes($husband, $wife, $caoLy, $duNien);
        $preMarriageQuestions = [
            'Hai người mong muốn không gian sống sau kết hôn như thế nào (riêng tư, gần nhà nội hay nhà ngoại)?',
            'Kế hoạch tài chính chung và cách phân chia chi phí sinh hoạt hằng tháng sẽ vận hành ra sao?',
            'Khi hai người bất đồng quan điểm, quy tắc hạ nhiệt nào giúp cả hai cảm thấy được tôn trọng?',
            'Mỗi người kỳ vọng điều gì ở sự chia sẻ việc nhà và thời gian dành cho gia đình hai bên?',
            'Trong 3–5 năm đầu hôn nhân, mục tiêu sự nghiệp và kế hoạch sinh con của hai người là gì?',
            'Ngân sách đám cưới dự kiến bao nhiêu để ngày vui trọn vẹn mà không tạo áp lực tài chính?',
        ];

        $calcHash = hash('sha256', json_encode([
            'v' => self::VERSION,
            'h_y' => $husband['lunar_year'],
            'w_y' => $wife['lunar_year'],
            'cl' => $caoLy['id'],
            'dn' => $duNien['code'],
            'wy' => $targetWeddingYear,
        ]));

        return [
            'version' => self::VERSION,
            'calendar_version' => VietnameseCalendarEngine::ENGINE_VERSION,
            'caoly_version' => CaoLyRegistry::VERSION,
            'kua_version' => KuaDuNienEngine::VERSION,
            'calculation_hash' => $calcHash,
            'calculated_at' => Carbon::now('Asia/Ho_Chi_Minh')->toIso8601String(),
            'warnings' => $warnings,
            'husband' => array_merge($husband, ['kua' => $kuaH]),
            'wife' => array_merge($wife, ['kua' => $kuaW]),
            'cao_ly' => $caoLy,
            'stem_relation' => $stemRelation,
            'branch_relation' => $branchRelation,
            'nap_am_relation' => $napAmRelation,
            'du_nien' => $duNien,
            'wedding_year' => $weddingYearAnalysis,
            'evidence' => $evidence,
            'themes' => $themes,
            'pre_marriage_questions' => $preMarriageQuestions,
            'disclaimer' => 'Các phương pháp Cao Ly Đầu Hình, Can Chi, Nạp Âm và Cung Phi Bát Trạch là tri thức văn hóa truyền thống để tham khảo và đối thoại; Mây Hẹn không chấm điểm % hợp nhau và không phán xét hạnh phúc hôn nhân.',
        ];
    }

    /**
     * Chuẩn hóa năm sinh (hỗ trợ cả tra cứu nhanh theo năm và tra cứu chính xác theo ngày tháng năm Âm/Dương).
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function normalizePerson(array $input, string $role): array
    {
        $year = (int) ($input['year'] ?? 1995);
        if ($year < VietnameseCalendarEngine::MIN_YEAR || $year > VietnameseCalendarEngine::MAX_YEAR) {
            throw new InvalidArgumentException('Năm sinh hỗ trợ nằm trong phạm vi 1900–2100.');
        }

        $calType = (string) ($input['calendar_type'] ?? 'solar');
        $hasFullDate = ! empty($input['day']) && ! empty($input['month']);
        $warnings = [];
        $roleLabel = $role === 'male' ? 'người chồng' : 'người vợ';

        if ($hasFullDate) {
            $d = (int) $input['day'];
            $m = (int) $input['month'];
            $isLeap = (bool) ($input['is_leap_month'] ?? false);
            if ($calType === 'lunar') {
                $conv = $this->calendar->convertLunarToSolar($d, $m, $year, $isLeap);
            } else {
                $conv = $this->calendar->convertSolarToLunar($d, $m, $year);
            }
            $lunarYear = (int) $conv['lunar']['year'];
            $solarLabel = $conv['solar']['label'];
            $lunarLabel = $conv['lunar']['label_full'];
            if ($calType === 'solar' && $lunarYear !== $year) {
                $warnings[] = "Ngày sinh {$solarLabel} của {$roleLabel} rơi vào trước Mùng 1 Tết Nguyên Đán nên thuộc năm Âm lịch {$lunarYear}.";
            }
        } else {
            $lunarYear = $year;
            $solarLabel = "Năm {$year}";
            $lunarLabel = "Năm Âm lịch giả định {$year}";
            if ($calType === 'solar') {
                $warnings[] = "Bạn chỉ nhập năm Dương lịch {$year} cho {$roleLabel}: nếu sinh vào tháng 1 hoặc đầu tháng 2 trước Tết Nguyên Đán, Can Chi có thể thuộc năm ".($year - 1).'. Nhập đủ ngày tháng sinh để xác định chính xác.';
            }
        }

        $stemIdx = (($lunarYear - 4) % 10 + 10) % 10;
        $branchIdx = (($lunarYear - 4) % 12 + 12) % 12;
        $stem = BaziRegistry::STEMS[$stemIdx];
        $branch = BaziRegistry::BRANCHES[$branchIdx];
        $canChiLabel = $stem['name'].' '.$branch['name'];
        $napAm = $this->calendar->getNapAm($canChiLabel);

        return [
            'name' => trim((string) ($input['name'] ?? '')),
            'input_year' => $year,
            'lunar_year' => $lunarYear,
            'has_full_date' => $hasFullDate,
            'solar_label' => $solarLabel,
            'lunar_label' => $lunarLabel,
            'stem_index' => $stemIdx,
            'branch_index' => $branchIdx,
            'stem' => $stem,
            'branch' => $branch,
            'can_chi' => $canChiLabel,
            'hanzi' => $stem['hanzi'].$branch['hanzi'],
            'zodiac' => $branch['zodiac'],
            'nap_am' => $napAm,
            'warnings' => $warnings,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function analyzeStems(int $s1, int $s2): array
    {
        $isNguHop = false;
        $nguHopLabel = 'Không thuộc Ngũ Hợp';
        foreach (BaziRegistry::STEM_COMBINATIONS as $sc) {
            if (($s1 === $sc['stems'][0] && $s2 === $sc['stems'][1]) || ($s1 === $sc['stems'][1] && $s2 === $sc['stems'][0])) {
                $isNguHop = true;
                $nguHopLabel = $sc['label'];
                break;
            }
        }

        $st1 = BaziRegistry::STEMS[$s1];
        $st2 = BaziRegistry::STEMS[$s2];
        $elPair = $this->analyzeElementPair($st1['element'], $st2['element'], 'Thiên Can');

        return [
            'is_ngu_hop' => $isNguHop,
            'label' => $isNguHop ? 'Thiên Can Ngũ Hợp' : 'Không thuộc Ngũ Hợp',
            'detail' => $nguHopLabel,
            'element_relation' => $elPair,
        ];
    }

    /**
     * Phân tích Địa Chi chính xác: phân biệt rõ Lục Hợp, Tam Hợp (2 Chi cùng nhóm, không gọi là đủ Tam Hợp Cục), Lục Xung, Lục Hại, Tự Hình.
     *
     * @return array<string, mixed>
     */
    public function analyzeBranches(int $b1, int $b2): array
    {
        $tags = [];
        $details = [];

        foreach (BaziRegistry::BRANCH_SIX_COMBINATIONS as $bc) {
            if (($b1 === $bc['branches'][0] && $b2 === $bc['branches'][1]) || ($b1 === $bc['branches'][1] && $b2 === $bc['branches'][0])) {
                $tags[] = 'Lục Hợp';
                $details[] = "{$bc['label']}: hai Địa Chi có quan hệ hòa hợp theo phép Lục Hợp cổ truyền.";
            }
        }

        foreach (BaziRegistry::BRANCH_THREE_HARMONIES as $th) {
            if ($b1 !== $b2 && in_array($b1, $th['branches'], true) && in_array($b2, $th['branches'], true)) {
                $tags[] = 'Cùng nhóm Tam Hợp';
                $details[] = "Hai Địa Chi cùng nằm trong nhóm {$th['label']} (chưa thành đủ Tam Hợp Cục vì mới có 2 Địa Chi).";
            }
        }

        foreach (BaziRegistry::BRANCH_CLASHES as $cl) {
            if (($b1 === $cl['branches'][0] && $b2 === $cl['branches'][1]) || ($b1 === $cl['branches'][1] && $b2 === $cl['branches'][0])) {
                $tags[] = 'Lục Xung';
                $details[] = "{$cl['label']}: biểu thị sự khác biệt rõ về nhịp sống và phản xạ — cần thống nhất cách giao tiếp thay vì né tránh.";
            }
        }

        foreach (BaziRegistry::BRANCH_HARMS as $hm) {
            if (($b1 === $hm['branches'][0] && $b2 === $hm['branches'][1]) || ($b1 === $hm['branches'][1] && $b2 === $hm['branches'][0])) {
                $tags[] = 'Lục Hại';
                $details[] = "{$hm['label']}: nhắc sự tinh tế trong kỳ vọng để tránh hiểu lầm tích tụ (không có nghĩa là gây hại thực tế).";
            }
        }

        if ($b1 === $b2 && in_array($b1, [4, 6, 9, 11], true)) {
            $tags[] = 'Tự Hình';
            $details[] = BaziRegistry::BRANCHES[$b1]['name'].' đồng chi Tự Hình: nhắc cả hai tránh cầu toàn quá mức với nhau.';
        }

        if ($tags === []) {
            $tags[] = 'Bình Hòa';
            $details[] = 'Hai Địa Chi không nằm trong cặp trực xung hay trực hợp, mối quan hệ phát triển tự nhiên theo sự vun đắp của cả hai.';
        }

        return [
            'primary_label' => implode(' · ', $tags),
            'tags' => $tags,
            'details' => $details,
        ];
    }

    /**
     * @return array{type: string, label: string, detail: string}
     */
    public function analyzeElementPair(string $elA, string $elB, string $layerName): array
    {
        $meta = BaziRegistry::FIVE_ELEMENTS[$elA];
        if ($elA === $elB) {
            return ['type' => 'dong_hanh', 'label' => 'Đồng hành', 'detail' => "Hai {$layerName} cùng hành {$elA}: dễ thấu hiểu nhưng nên giữ sự linh hoạt khi cùng giữ chính kiến."];
        }
        if ($meta['generates'] === $elB) {
            return ['type' => 'a_sinh_b', 'label' => 'Tương sinh (Chồng sinh Vợ)', 'detail' => "{$elA} sinh {$elB}: tạo dòng hỗ trợ thuận chiều theo Ngũ Hành {$layerName}."];
        }
        if ($meta['generated_by'] === $elB) {
            return ['type' => 'b_sinh_a', 'label' => 'Tương sinh (Vợ sinh Chồng)', 'detail' => "{$elB} sinh {$elA}: tạo dòng bồi đắp thuận chiều theo Ngũ Hành {$layerName}."];
        }
        if ($meta['controls'] === $elB) {
            return ['type' => 'a_khac_b', 'label' => 'Tương khắc (Chồng khắc Vợ)', 'detail' => "{$elA} khắc {$elB}: biểu thị sự khác nhịp trong cách tiếp cận vấn đề — cần tôn trọng góc nhìn đối lập để bổ trợ nhau."];
        }

        return ['type' => 'b_khac_a', 'label' => 'Tương khắc (Vợ khắc Chồng)', 'detail' => "{$elB} khắc {$elA}: biểu thị sự khác nhịp trong cách tiếp cận vấn đề — nên giữ đối thoại cởi mở thay vì áp đặt."];
    }

    /**
     * Xem năm kết hôn (Kim Lâu theo tuổi mụ cô dâu: mod 9 ∈ {1, 3, 6, 8}) kèm ghi chú vùng miền & thực tế.
     *
     * @param  array<string, mixed>  $husband
     * @param  array<string, mixed>  $wife
     * @return array<string, mixed>
     */
    public function analyzeWeddingYear(array $husband, array $wife, int $weddingYear): array
    {
        $weddingYear = max(1950, min(2100, $weddingYear));
        $wifeLunarAge = max(1, $weddingYear - $wife['lunar_year'] + 1);
        $husbandLunarAge = max(1, $weddingYear - $husband['lunar_year'] + 1);
        $rem = $wifeLunarAge % 9;

        $kimLauTypes = [
            1 => 'Kim Lâu Thân',
            3 => 'Kim Lâu Thê',
            6 => 'Kim Lâu Tử',
            8 => 'Kim Lâu Lục Súc',
        ];
        $isKimLau = array_key_exists($rem, $kimLauTypes);

        $wStem = BaziRegistry::STEMS[(($weddingYear - 4) % 10 + 10) % 10]['name'];
        $wBranch = BaziRegistry::BRANCHES[(($weddingYear - 4) % 12 + 12) % 12]['name'];

        return [
            'year' => $weddingYear,
            'year_can_chi' => "{$wStem} {$wBranch}",
            'husband_lunar_age' => $husbandLunarAge,
            'wife_lunar_age' => $wifeLunarAge,
            'is_kim_lau' => $isKimLau,
            'kim_lau_label' => $isKimLau ? "Tuổi mụ {$wifeLunarAge} rơi vào {$kimLauTypes[$rem]} (dư {$rem})" : "Tuổi mụ {$wifeLunarAge} không phạm Kim Lâu",
            'note' => 'Theo phong tục cưới hỏi miền Bắc thường xem tuổi mụ cô dâu chia 9 (dư 1, 3, 6, 8); nhiều vùng miền và gia đình hiện đại ưu tiên lịch thuận tiện, qua Đông Chí hoặc sự đồng thuận hai bên. Hoang Ốc chủ yếu dùng cho xây nhà nên không áp đặt máy móc vào cưới hỏi.',
        ];
    }

    /**
     * @return list<array{layer: string, rule_id: string, source: string, result: string}>
     */
    protected function buildEvidenceList(array $h, array $w, array $caoLy, array $stemRel, array $branchRel, array $napAmRel, array $duNien): array
    {
        return [
            ['layer' => 'Cao Ly Đầu Hình', 'rule_id' => $caoLy['id'], 'source' => $caoLy['source_label'], 'result' => "{$caoLy['combination_name']} → {$caoLy['scene']['name']} ({$caoLy['scene']['tradition_group']})"],
            ['layer' => 'Thiên Can', 'rule_id' => 'stem_pair_'.$h['stem_index'].'_'.$w['stem_index'], 'source' => 'Uyên Hải Tử Bình / Ngũ Hợp', 'result' => "{$h['stem']['name']} – {$w['stem']['name']}: {$stemRel['label']}"],
            ['layer' => 'Địa Chi', 'rule_id' => 'branch_pair_'.$h['branch_index'].'_'.$w['branch_index'], 'source' => 'Tam Mệnh Thông Hội', 'result' => "{$h['branch']['name']} ({$h['zodiac']}) – {$w['branch']['name']} ({$w['zodiac']}): {$branchRel['primary_label']}"],
            ['layer' => 'Ngũ Hành Nạp Âm', 'rule_id' => 'nap_am_pair', 'source' => 'Lục Thập Hoa Giáp (30 Nạp Âm)', 'result' => "{$h['nap_am']['name']} – {$w['nap_am']['name']}: {$napAmRel['label']}"],
            ['layer' => 'Cung Phi & Du Niên', 'rule_id' => 'du_nien_'.$duNien['code'], 'source' => 'Bát Trạch Minh Cảnh (Ma trận 8×8)', 'result' => "{$duNien['kua_a']['name']} ({$duNien['kua_a']['trigram']}) × {$duNien['kua_b']['name']} ({$duNien['kua_b']['trigram']}) → {$duNien['name']}"],
        ];
    }

    /**
     * @return list<array{id: int, title: string, insight: string}>
     */
    protected function buildTwelveThemes(array $h, array $w, array $caoLy, array $duNien): array
    {
        return [
            ['id' => 1, 'title' => 'Giá trị cá nhân', 'insight' => "Chồng tuổi {$h['can_chi']} ({$h['nap_am']['name']}), vợ tuổi {$w['can_chi']} ({$w['nap_am']['name']}): nền tảng tôn trọng bản sắc riêng là gốc của hòa hợp."],
            ['id' => 2, 'title' => 'Nhu cầu tình cảm', 'insight' => "Gợi mở từ {$caoLy['scene']['name']} ({$caoLy['scene']['keyword']}): hãy nói rõ cách bạn muốn được yêu thương thay vì đợi đối phương tự hiểu."],
            ['id' => 3, 'title' => 'Cách thể hiện sự quan tâm', 'insight' => 'Những cử chỉ nhỏ nhưng đều đặn mỗi ngày luôn có giá trị hơn những dịp hình thức hiếm hoi.'],
            ['id' => 4, 'title' => 'Giao tiếp', 'insight' => 'Lắng nghe để thấu hiểu trước khi phản biện giúp mọi khác biệt về tuổi tác hay thói quen trở nên nhẹ nhàng.'],
            ['id' => 5, 'title' => 'Sự tin tưởng', 'insight' => 'Minh bạch trong lịch trình, tài chính và các mối quan hệ tạo cảm giác an tâm bền vững.'],
            ['id' => 6, 'title' => 'Ranh giới cá nhân', 'insight' => 'Giữ không gian riêng cho sở thích và bạn bè lành mạnh giúp tình yêu luôn tươi mới.'],
            ['id' => 7, 'title' => 'Giải quyết bất đồng', 'insight' => "Góc nhìn từ Bát Trạch ({$duNien['name']}): {$duNien['modern_note']}"],
            ['id' => 8, 'title' => 'Mục tiêu nghề nghiệp', 'insight' => 'Ủng hộ nhau phát triển chuyên môn và phân chia thời gian linh hoạt theo từng giai đoạn sự nghiệp.'],
            ['id' => 9, 'title' => 'Tài chính gia đình', 'insight' => $caoLy['couple_tip']],
            ['id' => 10, 'title' => 'Quan hệ hai bên nội ngoại', 'insight' => 'Ứng xử khéo léo, tôn trọng phong tục hai gia đình nhưng vẫn giữ sự đồng thuận cốt lõi giữa hai vợ chồng.'],
            ['id' => 11, 'title' => 'Sự chuẩn bị cho hôn nhân', 'insight' => 'Lên checklist cưới sớm, chọn ngày thuận tiện cho hai gia đình và chuẩn bị thiệp mời chu đáo.'],
            ['id' => 12, 'title' => 'Xây dựng cuộc sống chung lâu dài', 'insight' => 'Hạnh phúc trăm năm được tạo nên từ sự tử tế và cam kết mỗi ngày, không bị định đoạt bởi một bảng tra duy nhất.'],
        ];
    }
}
