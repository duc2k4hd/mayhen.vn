<?php

declare(strict_types=1);

namespace App\Services\Personality;

/**
 * Method Registry minh bạch cho hệ thống Trắc nghiệm Tính cách Mây Hẹn (The Art of Personality).
 *
 * - Công bố rõ phương pháp, phiên bản, thang đo, quy tắc điểm hòa, vùng ranh giới UX và trạng thái kiểm định.
 * - Tuyệt đối không gắn nhãn "Đã kiểm định khoa học" khi đang ở giai đoạn thử nghiệm Pilot/Beta.
 * - Không trộn biến thể NERIS (-A/-T) vào 4 cặp xu hướng Jung–Myers.
 */
class PersonalityMethodRegistry
{
    public const STATUS_PILOT = 'pilot';

    public const QUICK_16 = 'mayhen_16types_quick_v1';

    public const STANDARD_16 = 'mayhen_16types_standard_v1';

    public const DEEP_16 = 'mayhen_16types_deep_v1';

    public const BIG_FIVE_IPIP50 = 'mayhen_bigfive_ipip50_v1';

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return [
            self::QUICK_16 => [
                'method_id' => self::QUICK_16,
                'model_code' => 'sixteen_types',
                'name' => 'Bản Khám Phá Nhanh (32 câu)',
                'short_name' => 'Quick 32',
                'version' => 'v1.0',
                'scoring_version' => 'v1.0',
                'theoretical_model' => '4 cặp xu hướng tâm lý tham chiếu khung Jung–Myers (E/I, S/N, T/F, J/P) — bộ câu hỏi độc lập của Mây Hẹn',
                'dimensions' => ['EI', 'SN', 'TF', 'JP'],
                'question_count' => 32,
                'questions_per_dimension' => 8,
                'estimated_minutes' => '5–7 phút',
                'scale_min' => 1,
                'scale_max' => 7,
                'scale_labels' => $this->likert7Labels(),
                'allow_unsure' => true,
                'min_valid_ratio_per_dimension' => 0.5,
                'tie_threshold' => 50.0,
                'boundary_band' => [45.0, 55.0],
                'validation_status' => self::STATUS_PILOT,
                'validation_badge' => 'Phiên bản Thử nghiệm (Pilot Beta v1.0)',
                'validation_note' => 'Thuật toán chấm điểm đã kiểm thử đơn vị 100% xác định. Bộ câu hỏi đang trong giai đoạn thu thập dữ liệu Pilot tiếng Việt; chưa tự công bố chuẩn hóa dân số (percentile).',
                'sources' => ['SRC-JUNG-1921', 'SRC-MYERS-1980', 'SRC-MCCRAE-1989', 'SRC-ITC-2017'],
                'trademark_disclaimer' => 'Đây là công cụ trắc nghiệm 16 nhóm tính cách độc lập do Mây Hẹn phát triển, không phải bài đánh giá MBTI® chính thức có cấp phép của The Myers-Briggs Company.',
            ],
            self::STANDARD_16 => [
                'method_id' => self::STANDARD_16,
                'model_code' => 'sixteen_types',
                'name' => 'Bản Tiêu Chuẩn Toàn Diện (64 câu)',
                'short_name' => 'Standard 64',
                'version' => 'v1.0',
                'scoring_version' => 'v1.0',
                'theoretical_model' => '4 cặp xu hướng tâm lý tham chiếu khung Jung–Myers (E/I, S/N, T/F, J/P) — cân bằng 8 câu thuận và 8 câu đảo mỗi chiều',
                'dimensions' => ['EI', 'SN', 'TF', 'JP'],
                'question_count' => 64,
                'questions_per_dimension' => 16,
                'estimated_minutes' => '10–15 phút',
                'scale_min' => 1,
                'scale_max' => 7,
                'scale_labels' => $this->likert7Labels(),
                'allow_unsure' => true,
                'min_valid_ratio_per_dimension' => 0.5,
                'tie_threshold' => 50.0,
                'boundary_band' => [45.0, 55.0],
                'validation_status' => self::STATUS_PILOT,
                'validation_badge' => 'Phiên bản Thử nghiệm Khuyên Dùng (Pilot Beta v1.0)',
                'validation_note' => 'Độ phân giải cao hơn bản 32 câu nhờ 16 tình huống đời sống Việt Nam trên mỗi chiều. Không tự gắn nhãn -A/-T và không tạo percentile giả.',
                'sources' => ['SRC-JUNG-1921', 'SRC-MYERS-1980', 'SRC-MCCRAE-1989', 'SRC-ITC-2017'],
                'trademark_disclaimer' => 'Đây là công cụ trắc nghiệm 16 nhóm tính cách độc lập do Mây Hẹn phát triển, không phải bài đánh giá MBTI® chính thức có cấp phép của The Myers-Briggs Company.',
            ],
            self::DEEP_16 => [
                'method_id' => self::DEEP_16,
                'model_code' => 'sixteen_types',
                'name' => 'Bản Chuyên Sâu Đa Bối Cảnh (96 câu)',
                'short_name' => 'Deep 96',
                'version' => 'v1.0',
                'scoring_version' => 'v1.0',
                'theoretical_model' => '4 cặp xu hướng tâm lý tham chiếu khung Jung–Myers (E/I, S/N, T/F, J/P) — 24 câu mỗi chiều qua nhiều ngữ cảnh giao tiếp, công việc và tình cảm',
                'dimensions' => ['EI', 'SN', 'TF', 'JP'],
                'question_count' => 96,
                'questions_per_dimension' => 24,
                'estimated_minutes' => '15–25 phút',
                'scale_min' => 1,
                'scale_max' => 7,
                'scale_labels' => $this->likert7Labels(),
                'allow_unsure' => true,
                'min_valid_ratio_per_dimension' => 0.5,
                'tie_threshold' => 50.0,
                'boundary_band' => [45.0, 55.0],
                'validation_status' => self::STATUS_PILOT,
                'validation_badge' => 'Phiên bản Chuyên Sâu (Pilot Beta v1.0)',
                'validation_note' => 'Cung cấp nguồn dẫn chứng phong phú nhất cho Best-Fit Exploration và AI 12 tầng có căn cứ.',
                'sources' => ['SRC-JUNG-1921', 'SRC-MYERS-1980', 'SRC-MCCRAE-1989', 'SRC-ITC-2017'],
                'trademark_disclaimer' => 'Đây là công cụ trắc nghiệm 16 nhóm tính cách độc lập do Mây Hẹn phát triển, không phải bài đánh giá MBTI® chính thức có cấp phép của The Myers-Briggs Company.',
            ],
            self::BIG_FIVE_IPIP50 => [
                'method_id' => self::BIG_FIVE_IPIP50,
                'model_code' => 'big_five',
                'name' => 'Trắc Nghiệm Big Five OCEAN (IPIP-50)',
                'short_name' => 'Big Five IPIP-50',
                'version' => 'v1.0',
                'scoring_version' => 'v1.0',
                'theoretical_model' => 'Mô hình 5 yếu tố tính cách (Five-Factor Model — O, C, E, A, N) theo IPIP 50-Item Big-Five Factor Markers (Public Domain)',
                'dimensions' => ['O', 'C', 'E', 'A', 'N'],
                'question_count' => 50,
                'questions_per_dimension' => 10,
                'estimated_minutes' => '8–12 phút',
                'scale_min' => 1,
                'scale_max' => 5,
                'scale_labels' => $this->likert5Labels(),
                'allow_unsure' => false,
                'min_valid_ratio_per_dimension' => 0.6,
                'tie_threshold' => 50.0,
                'boundary_band' => [45.0, 55.0],
                'validation_status' => self::STATUS_PILOT,
                'validation_badge' => 'IPIP Public Domain — Bản Việt hóa Thử nghiệm (Pilot v1.0)',
                'validation_note' => 'Hiển thị điểm trên thang chuẩn hóa 0–100 của từng yếu tố OCEAN. Không hiển thị Percentile dân số vì chưa có mẫu chuẩn hóa Việt Nam độc lập. Chiều N (Nhạy cảm cảm xúc / Neuroticism) là đặc điểm tính cách, tuyệt đối không phải chẩn đoán bệnh tâm thần.',
                'sources' => ['SRC-IPIP-2006', 'SRC-MCCRAE-1989', 'SRC-JOHNSON-2014', 'SRC-ITC-2017'],
                'trademark_disclaimer' => 'Sử dụng cấu trúc thang đo IPIP 50-Item Big-Five Factor Markers (Goldberg et al., Public Domain — ipip.ori.org), triển khai độc lập hoàn toàn với mô hình 16 nhóm tính cách.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $methodId): array
    {
        $all = $this->all();

        return $all[$methodId] ?? $all[self::STANDARD_16];
    }

    /**
     * @return array<int, string>
     */
    public function likert7Labels(): array
    {
        return [
            1 => 'Hoàn toàn không đồng ý',
            2 => 'Không đồng ý',
            3 => 'Hơi không đồng ý',
            4 => 'Trung lập',
            5 => 'Hơi đồng ý',
            6 => 'Đồng ý',
            7 => 'Hoàn toàn đồng ý',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function likert5Labels(): array
    {
        return [
            1 => 'Rất không đúng với tôi',
            2 => 'Không đúng lắm',
            3 => 'Trung lập / Phân vân',
            4 => 'Khá đúng với tôi',
            5 => 'Rất đúng với tôi',
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function sources(): array
    {
        return [
            'SRC-JUNG-1921' => [
                'id' => 'SRC-JUNG-1921',
                'author' => 'Carl Gustav Jung',
                'year' => '1921',
                'title' => 'Psychological Types',
                'scope' => 'Nền tảng lý thuyết thái độ hướng nội/hướng ngoại và các chức năng nhận thức.',
                'url' => 'https://www.myersbriggs.org/',
            ],
            'SRC-MYERS-1980' => [
                'id' => 'SRC-MYERS-1980',
                'author' => 'Isabel Briggs Myers & Peter B. Myers',
                'year' => '1980',
                'title' => 'Gifts Differing: Understanding Personality Type',
                'scope' => 'Hệ thống 4 cặp xu hướng E/I, S/N, T/F, J/P và khung tham chiếu 16 nhóm tính cách.',
                'url' => 'https://www.themyersbriggs.com/',
            ],
            'SRC-MCCRAE-1989' => [
                'id' => 'SRC-MCCRAE-1989',
                'author' => 'Robert R. McCrae & Paul T. Costa Jr.',
                'year' => '1989',
                'title' => 'Reinterpreting the Myers-Briggs Type Indicator From the Perspective of the Five-Factor Model',
                'scope' => 'Đối chiếu khoa học giữa 4 cặp xu hướng và mô hình Big Five, lưu ý tính liên tục của thang đo.',
                'url' => 'https://pubmed.ncbi.nlm.nih.gov/2709300/',
            ],
            'SRC-IPIP-2006' => [
                'id' => 'SRC-IPIP-2006',
                'author' => 'Lewis R. Goldberg et al.',
                'year' => '2006',
                'title' => 'International Personality Item Pool (IPIP) — 50-Item Big-Five Factor Markers',
                'scope' => 'Kho câu hỏi Big Five thuộc phạm vi công cộng (Public Domain).',
                'url' => 'https://ipip.ori.org/',
            ],
            'SRC-JOHNSON-2014' => [
                'id' => 'SRC-JOHNSON-2014',
                'author' => 'John A. Johnson',
                'year' => '2014',
                'title' => 'Measuring thirty facets of the Five Factor Model with a 120-item public domain inventory (IPIP-NEO-120)',
                'scope' => 'Tham chiếu cấu trúc facet cho mô hình Big Five.',
                'url' => 'https://ipip.ori.org/',
            ],
            'SRC-ITC-2017' => [
                'id' => 'SRC-ITC-2017',
                'author' => 'International Test Commission (ITC)',
                'year' => '2017',
                'title' => 'ITC Guidelines for Translating and Adapting Tests (Second Edition)',
                'scope' => 'Chuẩn quốc tế về bản địa hóa, dịch ngược và kiểm định bài trắc nghiệm đa văn hóa.',
                'url' => 'https://www.intestcom.org/page/14',
            ],
        ];
    }
}
