<?php

declare(strict_types=1);

namespace App\Services\Marriage;

use App\Services\Bazi\BaziRegistry;

/**
 * Kho Dữ Liệu 120 Tổ Hợp Cao Ly Đầu Hình (Nam dụng Thiên Can × Nữ dụng Địa Chi) & Lục Cảnh.
 *
 * - 10 Thiên Can × 12 Địa Chi = đúng 120 khóa duy nhất `CLDH-{STEM}-{BRANCH}`.
 * - Phép tra CÓ HƯỚNG: Can chồng × Chi vợ (A→B không đồng nhất B→A).
 * - Minh bạch nguồn tham chiếu (Cao Ly Đầu Hình — Đoàn Văn Đẩu / Bản biên tập hiện đại Mây Hẹn),
 *   không sao chép nguyên văn sách có bản quyền và không dùng ngôn từ hù dọa mê tín.
 */
class CaoLyRegistry
{
    public const VERSION = 'caoly_mayhen_v1.0';

    public const SOURCE_ID = 'src_caoly_doan_van_dau_ref';

    public const SOURCE_LABEL = 'Cao Ly Đầu Hình (tham khảo hệ thống Đoàn Văn Đẩu & bản biên tập hiện đại Mây Hẹn)';

    /** @var array<int, string> */
    public const STEM_SLUGS = [
        0 => 'giap', 1 => 'at', 2 => 'binh', 3 => 'dinh', 4 => 'mau',
        5 => 'ky', 6 => 'canh', 7 => 'tan', 8 => 'nham', 9 => 'quy',
    ];

    /** @var array<int, string> */
    public const BRANCH_SLUGS = [
        0 => 'ty', 1 => 'suu', 2 => 'dan', 3 => 'mao', 4 => 'thin', 5 => 'ti',
        6 => 'ngo', 7 => 'mui', 8 => 'than', 9 => 'dau', 10 => 'tuat', 11 => 'hoi',
    ];

    /**
     * 6 Huy Chương Lục Cảnh Cao Ly Đầu Hình — ngôn ngữ trung lập, nhân văn, không hình ảnh đáng sợ.
     *
     * @var array<string, array{id: string, order: int, name: string, hanzi: string, keyword: string, color: string, tradition_group: string, historical_note: string, modern_insight: string, couple_tip: string}>
     */
    public const LUC_CANH = [
        'nhat_phu_quy' => [
            'id' => 'nhat_phu_quy',
            'order' => 1,
            'name' => 'Nhất Phú Quý',
            'hanzi' => '富貴',
            'keyword' => 'Sung túc',
            'color' => '#8C703B',
            'tradition_group' => 'Cát',
            'historical_note' => 'Trong quan niệm cổ truyền, Nhất Phú Quý tượng trưng cho nền tảng kinh tế thuận lợi và gia đạo đủ đầy.',
            'modern_insight' => 'Gợi mở tinh thần cùng nhau hoạch định tài chính minh bạch, tích lũy bền vững thay vì chủ quan.',
            'couple_tip' => 'Thống nhất quỹ chung – quỹ riêng và mục tiêu tài chính 3–5 năm đầu hôn nhân.',
        ],
        'nhi_ban_tien' => [
            'id' => 'nhi_ban_tien',
            'order' => 2,
            'name' => 'Nhì Bần Tiện',
            'hanzi' => '勤儉',
            'keyword' => 'Sẻ chia',
            'color' => '#5D7066',
            'tradition_group' => 'Cần lưu tâm',
            'historical_note' => 'Thuật ngữ cổ nhắc về giai đoạn khởi đầu cần nhiều nỗ lực vật chất; không phải lời phán xét gia cảnh.',
            'modern_insight' => 'Mây Hẹn diễn giải theo tinh thần Cần Kiệm (勤儉): đề cao sự sẻ chia, quản trị chi tiêu tỉnh táo và đồng lòng vượt thử thách.',
            'couple_tip' => 'Lập ngân sách cưới vừa sức, tránh vay nợ lớn để khởi đầu cuộc sống chung nhẹ nhàng.',
        ],
        'tam_vinh_hien' => [
            'id' => 'tam_vinh_hien',
            'order' => 3,
            'name' => 'Tam Vinh Hiển',
            'hanzi' => '榮顯',
            'keyword' => 'Phát triển',
            'color' => '#85182A',
            'tradition_group' => 'Cát',
            'historical_note' => 'Trong sách cổ tượng trưng cho uy tín, sự nghiệp hanh thông và được người xung quanh quý trọng.',
            'modern_insight' => 'Gợi nhắc hai người cùng ủng hộ lộ trình phát triển sự nghiệp và giữ cân bằng giữa công việc với gia đình.',
            'couple_tip' => 'Chia sẻ rõ kỳ vọng về công việc, giờ giấc và trách nhiệm chăm lo tổ ấm.',
        ],
        'tu_dat_dao' => [
            'id' => 'tu_dat_dao',
            'order' => 4,
            'name' => 'Tứ Đạt Đạo',
            'hanzi' => '達道',
            'keyword' => 'Đồng hành',
            'color' => '#1E4D3B',
            'tradition_group' => 'Cát',
            'historical_note' => 'Biểu trưng cho sự thông suốt trong mưu sự, vợ chồng thuận lòng dễ tìm được tiếng nói chung.',
            'modern_insight' => 'Nhấn mạnh sức mạnh của sự phối hợp nhịp nhàng, biết lắng nghe và tôn trọng thế mạnh của nhau.',
            'couple_tip' => 'Duy trì thói quen trò chuyện định kỳ mỗi tuần để giữ sự đồng điệu lâu dài.',
        ],
        'ngu_biet_ly' => [
            'id' => 'ngu_biet_ly',
            'order' => 5,
            'name' => 'Ngũ Biệt Ly',
            'hanzi' => '相思',
            'keyword' => 'Kết nối',
            'color' => '#5F7486',
            'tradition_group' => 'Cần lưu tâm',
            'historical_note' => 'Tên gọi lịch sử nhắc về khả năng xa cách do công việc hoặc khác biệt nhịp sống; tuyệt đối không phải kết luận chia tay hay ly hôn.',
            'modern_insight' => 'Mây Hẹn diễn giải theo tinh thần Tương Tư – Kết Nối (相思): nhắc hai người chủ động nuôi dưỡng gắn kết cảm xúc và không để sự im lặng kéo dài.',
            'couple_tip' => 'Thống nhất nguyên tắc giải quyết bất đồng trong ngày, không để chiến tranh lạnh.',
        ],
        'luc_tuyet_mang' => [
            'id' => 'luc_tuyet_mang',
            'order' => 6,
            'name' => 'Lục Tuyệt Mạng',
            'hanzi' => '珍重',
            'keyword' => 'Trân trọng',
            'color' => '#7C6E58',
            'tradition_group' => 'Cần lưu tâm',
            'historical_note' => 'Thuật ngữ cổ học mang sắc thái cảnh báo mạnh trong sách xưa — hoàn toàn KHÔNG liên quan tới tuổi thọ, bệnh tật hay cái chết thực tế.',
            'modern_insight' => 'Mây Hẹn diễn giải theo tinh thần Trân Trọng (珍重): nhắc cặp đôi đặc biệt đề cao sự kiên nhẫn, chăm sóc sức khỏe tinh thần và tôn trọng ranh giới của nhau.',
            'couple_tip' => 'Cùng rèn luyện kỹ năng giao tiếp phi bạo lực và xây dựng điểm tựa gia đình hai bên vững vàng.',
        ],
    ];

    /**
     * Bảng ánh xạ phân loại & Lục Cảnh tham khảo (10 hàng Thiên Can × 12 cột Địa Chi).
     * Giá trị: 0=nhat_phu_quy, 1=nhi_ban_tien, 2=tam_vinh_hien, 3=tu_dat_dao, 4=ngu_biet_ly, 5=luc_tuyet_mang.
     *
     * @var array<int, list<int>>
     */
    protected const SCENE_MATRIX = [
        0 => [0, 1, 3, 2, 3, 1, 4, 0, 1, 4, 2, 3], // Giáp
        1 => [3, 0, 1, 2, 0, 3, 2, 1, 4, 5, 3, 0], // Ất
        2 => [4, 3, 0, 2, 1, 3, 0, 2, 3, 1, 0, 4], // Bính
        3 => [1, 2, 3, 0, 2, 4, 3, 0, 2, 3, 1, 0], // Đinh
        4 => [0, 3, 4, 1, 2, 0, 3, 2, 0, 3, 4, 2], // Mậu
        5 => [3, 0, 2, 3, 0, 2, 1, 3, 2, 0, 3, 1], // Kỷ
        6 => [2, 1, 4, 0, 3, 2, 1, 3, 0, 2, 3, 4], // Canh
        7 => [0, 2, 3, 4, 0, 3, 2, 0, 1, 3, 2, 0], // Tân
        8 => [3, 4, 0, 2, 3, 1, 4, 2, 0, 3, 1, 2], // Nhâm
        9 => [2, 0, 3, 1, 4, 0, 2, 3, 2, 0, 3, 5], // Quý
    ];

    /** @var array<int, string> */
    protected const SCENE_KEYS = [
        0 => 'nhat_phu_quy',
        1 => 'nhi_ban_tien',
        2 => 'tam_vinh_hien',
        3 => 'tu_dat_dao',
        4 => 'ngu_biet_ly',
        5 => 'luc_tuyet_mang',
    ];

    /**
     * Tra cứu chính xác 1 bản ghi trong 120 tổ hợp Cao Ly Đầu Hình (CÓ HƯỚNG: Can chồng × Chi vợ).
     *
     * @return array<string, mixed>
     */
    public static function lookup(int $husbandStemIdx, int $wifeBranchIdx): array
    {
        $s = (($husbandStemIdx % 10) + 10) % 10;
        $b = (($wifeBranchIdx % 12) + 12) % 12;

        $stem = BaziRegistry::STEMS[$s];
        $branch = BaziRegistry::BRANCHES[$b];
        $stemSlug = self::STEM_SLUGS[$s];
        $branchSlug = self::BRANCH_SLUGS[$b];

        $key = 'CLDH-'.strtoupper($stemSlug).'-'.strtoupper($branchSlug);
        $slug = "{$stemSlug}-{$branchSlug}";
        $orderNumber = $s * 12 + $b + 1;

        $sceneKey = self::SCENE_KEYS[self::SCENE_MATRIX[$s][$b] ?? 3];
        $scene = self::LUC_CANH[$sceneKey];

        $stemEl = $stem['element'];
        $branchEl = $branch['element'];
        $elMeta = BaziRegistry::FIVE_ELEMENTS[$stemEl];
        $elRelation = match (true) {
            $stemEl === $branchEl => 'Đồng hành ('.$stemEl.')',
            $elMeta['generates'] === $branchEl => "Can sinh Chi ({$stemEl} sinh {$branchEl})",
            $elMeta['generated_by'] === $branchEl => "Chi sinh Can ({$branchEl} sinh {$stemEl})",
            $elMeta['controls'] === $branchEl => "Can khắc Chi ({$stemEl} khắc {$branchEl})",
            default => "Chi khắc Can ({$branchEl} khắc {$stemEl})",
        };

        $traditionSummary = sprintf(
            'Tổ hợp %s – %s (số %d/120) kết hợp Thiên Can %s (%s %s) của người chồng và Địa Chi %s (%s · hành %s) của người vợ. Trong bảng đối chiếu tham khảo, tổ hợp này thường được xếp về nhóm %s (%s).',
            $stem['name'],
            $branch['name'],
            $orderNumber,
            $stem['name'],
            $stem['yin_yang'],
            $stem['element'],
            $branch['name'],
            $branch['zodiac'],
            $branch['element'],
            $scene['name'],
            $scene['tradition_group']
        );

        return [
            'id' => $key,
            'slug' => $slug,
            'order' => $orderNumber,
            'husband_stem_index' => $s,
            'wife_branch_index' => $b,
            'husband_stem' => $stem,
            'wife_branch' => $branch,
            'combination_name' => $stem['name'].' – '.$branch['name'],
            'hanzi' => $stem['hanzi'].$branch['hanzi'],
            'figure_title' => "Đồ hình {$stem['name']} {$branch['name']} ({$scene['name']})",
            'scene_key' => $sceneKey,
            'scene' => $scene,
            'element_relation' => $elRelation,
            'tradition_summary' => $traditionSummary,
            'modern_guidance' => $scene['modern_insight'],
            'couple_tip' => $scene['couple_tip'],
            'source_id' => self::SOURCE_ID,
            'source_label' => self::SOURCE_LABEL,
            'verification_status' => 'reference_mapped',
            'variant_note' => 'Các bản in Cao Ly Đầu Hình dân gian có thể khác nhau ở một số phân loại chi tiết; Mây Hẹn trình bày bản biên tập tham khảo chuẩn hóa và không xem đây là kết luận định mệnh.',
            'version' => self::VERSION,
        ];
    }

    /**
     * Tìm tổ hợp Cao Ly theo slug (ví dụ `canh-mui`, `giap-ty`).
     *
     * @return array<string, mixed>|null
     */
    public static function findBySlug(string $slug): ?array
    {
        $parts = explode('-', strtolower(trim($slug)));
        if (count($parts) !== 2) {
            return null;
        }
        $s = array_search($parts[0], self::STEM_SLUGS, true);
        $b = array_search($parts[1], self::BRANCH_SLUGS, true);
        if ($s === false || $b === false) {
            return null;
        }

        return self::lookup((int) $s, (int) $b);
    }

    /**
     * Lấy trọn bộ 120 bản ghi Cao Ly (10 Thiên Can × 12 Địa Chi).
     *
     * @return list<array<string, mixed>>
     */
    public static function getAll120(): array
    {
        $list = [];
        for ($s = 0; $s < 10; $s++) {
            for ($b = 0; $b < 12; $b++) {
                $list[] = self::lookup($s, $b);
            }
        }

        return $list;
    }
}
