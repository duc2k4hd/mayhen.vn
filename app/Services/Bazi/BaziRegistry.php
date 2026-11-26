<?php

declare(strict_types=1);

namespace App\Services\Bazi;

use App\Services\Calendar\VietnameseCalendarEngine;

/**
 * Single Source of Truth cho toàn bộ dữ liệu cổ học Bát Tự – Tứ Trụ (Mây Hẹn – Tứ Trụ Nhân Sinh).
 *
 * - 10 Thiên Can, 12 Địa Chi (Mão = Mèo, Sửu = Trâu chuẩn Việt Nam), 60 Hoa Giáp + 30 Nạp Âm + Không Vong.
 * - Bảng Tàng Can (Bản Khí / Trung Khí / Dư Khí) có nguồn rõ ràng.
 * - Ma trận 10×10 = 100 Thập Thần xác định.
 * - Vòng Trường Sinh 12 trạng thái (10 Thiên Can × 12 Địa Chi).
 * - Quan hệ Thiên Can Ngũ Hợp, Địa Chi Lục Hợp / Tam Hợp / Tam Hội / Lục Xung / Lục Hại / Lục Phá / Tam Hình / Tự Hình.
 * - Thần Sát Bát Tự (không trộn sao Tử Vi).
 * - Hệ thống Method Profile minh bạch và danh mục địa điểm sinh (kinh độ + IANA timezone).
 */
class BaziRegistry
{
    public const ENGINE_VERSION = 'mayhen_bazi_v1.0';

    public const RULE_VERSION = 'tu_binh_mayhen_v1.0';

    /** @var array<int, array{id: string, index: int, name: string, hanzi: string, pinyin: string, element: string, yin_yang: string, symbol: string, nature: string}> */
    public const STEMS = [
        0 => ['id' => 'stem_0', 'index' => 0, 'name' => 'Giáp', 'hanzi' => '甲', 'pinyin' => 'Jiǎ', 'element' => 'Mộc', 'yin_yang' => 'Dương', 'symbol' => 'Cây đại thụ vươn thẳng', 'nature' => 'Trực trực, nguyên tắc, giàu chí tiến thủ và tinh thần che chở.'],
        1 => ['id' => 'stem_1', 'index' => 1, 'name' => 'Ất', 'hanzi' => '乙', 'pinyin' => 'Yǐ', 'element' => 'Mộc', 'yin_yang' => 'Âm', 'symbol' => 'Cành liễu, hoa thảo mềm dẻo', 'nature' => 'Linh hoạt, tinh tế, bền bỉ và khéo thích ứng với hoàn cảnh.'],
        2 => ['id' => 'stem_2', 'index' => 2, 'name' => 'Bính', 'hanzi' => '丙', 'pinyin' => 'Bǐng', 'element' => 'Hỏa', 'yin_yang' => 'Dương', 'symbol' => 'Ánh mặt trời rực rỡ', 'nature' => 'Quang minh, ấm áp, lan tỏa nhiệt huyết và dễ truyền cảm hứng.'],
        3 => ['id' => 'stem_3', 'index' => 3, 'name' => 'Đinh', 'hanzi' => '丁', 'pinyin' => 'Dīng', 'element' => 'Hỏa', 'yin_yang' => 'Âm', 'symbol' => 'Ngọn đèn ấm, lửa lò luyện', 'nature' => 'Sâu sắc, điềm tĩnh, chu đáo và có khả năng tập trung bền bỉ.'],
        4 => ['id' => 'stem_4', 'index' => 4, 'name' => 'Mậu', 'hanzi' => '戊', 'pinyin' => 'Wù', 'element' => 'Thổ', 'yin_yang' => 'Dương', 'symbol' => 'Núi cao, thành lũy vững chãi', 'nature' => 'Điềm đạm, trọng chữ tín, ổn định và đáng tin cậy.'],
        5 => ['id' => 'stem_5', 'index' => 5, 'name' => 'Kỷ', 'hanzi' => '己', 'pinyin' => 'Jǐ', 'element' => 'Thổ', 'yin_yang' => 'Âm', 'symbol' => 'Đất vườn màu mỡ nuôi dưỡng', 'nature' => 'Bao dung, tỉ mỉ, kiên nhẫn và giỏi chăm lo nền tảng thực tế.'],
        6 => ['id' => 'stem_6', 'index' => 6, 'name' => 'Canh', 'hanzi' => '庚', 'pinyin' => 'Gēng', 'element' => 'Kim', 'yin_yang' => 'Dương', 'symbol' => 'Kim loại rèn đúc, kiếm khí', 'nature' => 'Quyết đoán, thẳng thắn, trọng nghĩa khí và có sức chịu áp lực.'],
        7 => ['id' => 'stem_7', 'index' => 7, 'name' => 'Tân', 'hanzi' => '辛', 'pinyin' => 'Xīn', 'element' => 'Kim', 'yin_yang' => 'Âm', 'symbol' => 'Ngọc quý, trang sức tinh xảo', 'nature' => 'Nhạy cảm thẩm mỹ, cầu toàn, giữ khí chất thanh tao và sắc sảo.'],
        8 => ['id' => 'stem_8', 'index' => 8, 'name' => 'Nhâm', 'hanzi' => '壬', 'pinyin' => 'Rén', 'element' => 'Thủy', 'yin_yang' => 'Dương', 'symbol' => 'Sông lớn, đại dương rộng mở', 'nature' => 'Khoáng đạt, thông tuệ, giàu tầm nhìn và thích sự tự do sáng tạo.'],
        9 => ['id' => 'stem_9', 'index' => 9, 'name' => 'Quý', 'hanzi' => '癸', 'pinyin' => 'Guǐ', 'element' => 'Thủy', 'yin_yang' => 'Âm', 'symbol' => 'Mưa xuân, sương sớm thấm nhuần', 'nature' => 'Trực giác nhạy bén, mềm mỏng, tinh tường và giàu chiều sâu nội tâm.'],
    ];

    /** @var array<int, array{id: string, index: int, name: string, hanzi: string, pinyin: string, zodiac: string, element: string, yin_yang: string, season: string, hours: string}> */
    public const BRANCHES = [
        0 => ['id' => 'branch_0', 'index' => 0, 'name' => 'Tý', 'hanzi' => '子', 'pinyin' => 'Zǐ', 'zodiac' => 'Chuột', 'element' => 'Thủy', 'yin_yang' => 'Dương', 'season' => 'Đông', 'hours' => '23:00–01:00'],
        1 => ['id' => 'branch_1', 'index' => 1, 'name' => 'Sửu', 'hanzi' => '丑', 'pinyin' => 'Chǒu', 'zodiac' => 'Trâu', 'element' => 'Thổ', 'yin_yang' => 'Âm', 'season' => 'Cuối Đông', 'hours' => '01:00–03:00'],
        2 => ['id' => 'branch_2', 'index' => 2, 'name' => 'Dần', 'hanzi' => '寅', 'pinyin' => 'Yín', 'zodiac' => 'Hổ', 'element' => 'Mộc', 'yin_yang' => 'Dương', 'season' => 'Xuân', 'hours' => '03:00–05:00'],
        3 => ['id' => 'branch_3', 'index' => 3, 'name' => 'Mão', 'hanzi' => '卯', 'pinyin' => 'Mǎo', 'zodiac' => 'Mèo', 'element' => 'Mộc', 'yin_yang' => 'Âm', 'season' => 'Xuân', 'hours' => '05:00–07:00'],
        4 => ['id' => 'branch_4', 'index' => 4, 'name' => 'Thìn', 'hanzi' => '辰', 'pinyin' => 'Chén', 'zodiac' => 'Rồng', 'element' => 'Thổ', 'yin_yang' => 'Dương', 'season' => 'Cuối Xuân', 'hours' => '07:00–09:00'],
        5 => ['id' => 'branch_5', 'index' => 5, 'name' => 'Tỵ', 'hanzi' => '巳', 'pinyin' => 'Sì', 'zodiac' => 'Rắn', 'element' => 'Hỏa', 'yin_yang' => 'Âm', 'season' => 'Hạ', 'hours' => '09:00–11:00'],
        6 => ['id' => 'branch_6', 'index' => 6, 'name' => 'Ngọ', 'hanzi' => '午', 'pinyin' => 'Wǔ', 'zodiac' => 'Ngựa', 'element' => 'Hỏa', 'yin_yang' => 'Dương', 'season' => 'Hạ', 'hours' => '11:00–13:00'],
        7 => ['id' => 'branch_7', 'index' => 7, 'name' => 'Mùi', 'hanzi' => '未', 'pinyin' => 'Wèi', 'zodiac' => 'Dê', 'element' => 'Thổ', 'yin_yang' => 'Âm', 'season' => 'Cuối Hạ', 'hours' => '13:00–15:00'],
        8 => ['id' => 'branch_8', 'index' => 8, 'name' => 'Thân', 'hanzi' => '申', 'pinyin' => 'Shēn', 'zodiac' => 'Khỉ', 'element' => 'Kim', 'yin_yang' => 'Dương', 'season' => 'Thu', 'hours' => '15:00–17:00'],
        9 => ['id' => 'branch_9', 'index' => 9, 'name' => 'Dậu', 'hanzi' => '酉', 'pinyin' => 'Yǒu', 'zodiac' => 'Gà', 'element' => 'Kim', 'yin_yang' => 'Âm', 'season' => 'Thu', 'hours' => '17:00–19:00'],
        10 => ['id' => 'branch_10', 'index' => 10, 'name' => 'Tuất', 'hanzi' => '戌', 'pinyin' => 'Xū', 'zodiac' => 'Chó', 'element' => 'Thổ', 'yin_yang' => 'Dương', 'season' => 'Cuối Thu', 'hours' => '19:00–21:00'],
        11 => ['id' => 'branch_11', 'index' => 11, 'name' => 'Hợi', 'hanzi' => '亥', 'pinyin' => 'Hài', 'zodiac' => 'Lợn', 'element' => 'Thủy', 'yin_yang' => 'Âm', 'season' => 'Đông', 'hours' => '21:00–23:00'],
    ];

    /**
     * Tàng Can cơ sở theo Uyên Hải Tử Bình & Tam Mệnh Thông Hội.
     * Mỗi phần tử: [stem_index, role ('ban_khi'|'trung_khi'|'du_khi'), role_label].
     *
     * @var array<int, list<array{stem_index: int, role: string, role_label: string}>>
     */
    public const HIDDEN_STEMS = [
        0 => [['stem_index' => 9, 'role' => 'ban_khi', 'role_label' => 'Bản Khí']], // Tý: Quý
        1 => [
            ['stem_index' => 5, 'role' => 'ban_khi', 'role_label' => 'Bản Khí'],
            ['stem_index' => 9, 'role' => 'trung_khi', 'role_label' => 'Trung Khí'],
            ['stem_index' => 7, 'role' => 'du_khi', 'role_label' => 'Dư Khí'],
        ], // Sửu: Kỷ, Quý, Tân
        2 => [
            ['stem_index' => 0, 'role' => 'ban_khi', 'role_label' => 'Bản Khí'],
            ['stem_index' => 2, 'role' => 'trung_khi', 'role_label' => 'Trung Khí'],
            ['stem_index' => 4, 'role' => 'du_khi', 'role_label' => 'Dư Khí'],
        ], // Dần: Giáp, Bính, Mậu
        3 => [['stem_index' => 1, 'role' => 'ban_khi', 'role_label' => 'Bản Khí']], // Mão: Ất
        4 => [
            ['stem_index' => 4, 'role' => 'ban_khi', 'role_label' => 'Bản Khí'],
            ['stem_index' => 1, 'role' => 'trung_khi', 'role_label' => 'Trung Khí'],
            ['stem_index' => 9, 'role' => 'du_khi', 'role_label' => 'Dư Khí'],
        ], // Thìn: Mậu, Ất, Quý
        5 => [
            ['stem_index' => 2, 'role' => 'ban_khi', 'role_label' => 'Bản Khí'],
            ['stem_index' => 4, 'role' => 'trung_khi', 'role_label' => 'Trung Khí'],
            ['stem_index' => 6, 'role' => 'du_khi', 'role_label' => 'Dư Khí'],
        ], // Tỵ: Bính, Mậu, Canh
        6 => [
            ['stem_index' => 3, 'role' => 'ban_khi', 'role_label' => 'Bản Khí'],
            ['stem_index' => 5, 'role' => 'trung_khi', 'role_label' => 'Trung Khí'],
        ], // Ngọ: Đinh, Kỷ
        7 => [
            ['stem_index' => 5, 'role' => 'ban_khi', 'role_label' => 'Bản Khí'],
            ['stem_index' => 3, 'role' => 'trung_khi', 'role_label' => 'Trung Khí'],
            ['stem_index' => 1, 'role' => 'du_khi', 'role_label' => 'Dư Khí'],
        ], // Mùi: Kỷ, Đinh, Ất
        8 => [
            ['stem_index' => 6, 'role' => 'ban_khi', 'role_label' => 'Bản Khí'],
            ['stem_index' => 8, 'role' => 'trung_khi', 'role_label' => 'Trung Khí'],
            ['stem_index' => 4, 'role' => 'du_khi', 'role_label' => 'Dư Khí'],
        ], // Thân: Canh, Nhâm, Mậu
        9 => [['stem_index' => 7, 'role' => 'ban_khi', 'role_label' => 'Bản Khí']], // Dậu: Tân
        10 => [
            ['stem_index' => 4, 'role' => 'ban_khi', 'role_label' => 'Bản Khí'],
            ['stem_index' => 7, 'role' => 'trung_khi', 'role_label' => 'Trung Khí'],
            ['stem_index' => 3, 'role' => 'du_khi', 'role_label' => 'Dư Khí'],
        ], // Tuất: Mậu, Tân, Đinh
        11 => [
            ['stem_index' => 8, 'role' => 'ban_khi', 'role_label' => 'Bản Khí'],
            ['stem_index' => 0, 'role' => 'trung_khi', 'role_label' => 'Trung Khí'],
        ], // Hợi: Nhâm, Giáp
    ];

    /** @var array<string, array{code: string, name: string, short: string, hanzi: string, group: string, desc: string}> */
    public const TEN_GODS = [
        'ty_kien' => ['code' => 'ty_kien', 'name' => 'Tỷ Kiên', 'short' => 'Tỷ', 'hanzi' => '比肩', 'group' => 'Tỷ Kiếp (Đồng Hành)', 'desc' => 'Tinh thần tự lập, chính kiến rõ ràng, đề cao sự bình đẳng và đồng hành.'],
        'kiep_tai' => ['code' => 'kiep_tai', 'name' => 'Kiếp Tài', 'short' => 'Kiếp', 'hanzi' => '劫財', 'group' => 'Tỷ Kiếp (Đồng Hành)', 'desc' => 'Khí chất cạnh tranh, giao thiệp rộng, hành động dứt khoát và cần quản trị nguồn lực tỉnh táo.'],
        'thuc_than' => ['code' => 'thuc_than', 'name' => 'Thực Thần', 'short' => 'Thực', 'hanzi' => '食神', 'group' => 'Thực Thương (Tiết Tú)', 'desc' => 'Sự ung dung, óc thẩm mỹ, năng lực sáng tạo bền bỉ và khả năng nuôi dưỡng giá trị.'],
        'thuong_quan' => ['code' => 'thuong_quan', 'name' => 'Thương Quan', 'short' => 'Thương', 'hanzi' => '傷官', 'group' => 'Thực Thương (Tiết Tú)', 'desc' => 'Tư duy sắc bén, phá cách, biểu đạt mạnh mẽ và khao khát khẳng định dấu ấn riêng.'],
        'thien_tai' => ['code' => 'thien_tai', 'name' => 'Thiên Tài', 'short' => 'T.Tài', 'hanzi' => '偏財', 'group' => 'Tài Tinh (Khắc Xuất)', 'desc' => 'Tư duy cơ hội, linh hoạt xoay chuyển nguồn lực, rộng rãi trong kết nối xã hội.'],
        'chinh_tai' => ['code' => 'chinh_tai', 'name' => 'Chính Tài', 'short' => 'C.Tài', 'hanzi' => '正財', 'group' => 'Tài Tinh (Khắc Xuất)', 'desc' => 'Thực tế, kỷ luật tài chính, tích lũy từng bước và coi trọng sự bền vững.'],
        'that_sat' => ['code' => 'that_sat', 'name' => 'Thất Sát', 'short' => 'Sát', 'hanzi' => '七殺', 'group' => 'Quan Sát (Khắc Nhập)', 'desc' => 'Bản lĩnh trước áp lực, quyết liệt vượt thử thách, cần điều tiết để giữ sự cân bằng.'],
        'chinh_quan' => ['code' => 'chinh_quan', 'name' => 'Chính Quan', 'short' => 'Quan', 'hanzi' => '正官', 'group' => 'Quan Sát (Khắc Nhập)', 'desc' => 'Tinh thần trách nhiệm, tôn trọng chuẩn mực, uy tín cá nhân và năng lực tổ chức.'],
        'thien_an' => ['code' => 'thien_an', 'name' => 'Thiên Ấn', 'short' => 'Kiêu', 'hanzi' => '偏印', 'group' => 'Ấn Tinh (Sinh Nhập)', 'desc' => 'Trực giác sâu, khả năng nghiên cứu chuyên biệt, tư duy độc lập khác lối mòn.'],
        'chinh_an' => ['code' => 'chinh_an', 'name' => 'Chính Ấn', 'short' => 'Ấn', 'hanzi' => '正印', 'group' => 'Ấn Tinh (Sinh Nhập)', 'desc' => 'Hiếu học, nhân hậu, trọng nền tảng tri thức, gia phong và sự che chở vững vàng.'],
    ];

    /** @var list<string> 12 trạng thái Vòng Trường Sinh */
    public const TWELVE_STAGES = [
        'Trường Sinh', 'Mộc Dục', 'Quan Đới', 'Lâm Quan', 'Đế Vượng', 'Suy',
        'Bệnh', 'Tử', 'Mộ', 'Tuyệt', 'Thai', 'Dưỡng',
    ];

    /**
     * Vị trí Địa Chi khởi Trường Sinh của 10 Thiên Can theo Tử Bình chuẩn (Dương thuận, Âm nghịch).
     *
     * @var array<int, array{start: int, dir: int}>
     */
    public const STAGE_START_BY_STEM = [
        0 => ['start' => 11, 'dir' => 1],  // Giáp khởi Hợi, thuận
        1 => ['start' => 6, 'dir' => -1],  // Ất khởi Ngọ, nghịch
        2 => ['start' => 2, 'dir' => 1],   // Bính khởi Dần, thuận
        3 => ['start' => 9, 'dir' => -1],  // Đinh khởi Dậu, nghịch
        4 => ['start' => 2, 'dir' => 1],   // Mậu khởi Dần, thuận
        5 => ['start' => 9, 'dir' => -1],  // Kỷ khởi Dậu, nghịch
        6 => ['start' => 5, 'dir' => 1],   // Canh khởi Tỵ, thuận
        7 => ['start' => 0, 'dir' => -1],  // Tân khởi Tý, nghịch
        8 => ['start' => 8, 'dir' => 1],   // Nhâm khởi Thân, thuận
        9 => ['start' => 3, 'dir' => -1],  // Quý khởi Mão, nghịch
    ];

    /** @var array<string, array{color: string, soft_bg: string, generates: string, controls: string, generated_by: string, controlled_by: string}> */
    public const FIVE_ELEMENTS = [
        'Mộc' => ['color' => '#1E4D3B', 'soft_bg' => 'rgba(30,77,59,0.10)', 'generates' => 'Hỏa', 'controls' => 'Thổ', 'generated_by' => 'Thủy', 'controlled_by' => 'Kim'],
        'Hỏa' => ['color' => '#85182A', 'soft_bg' => 'rgba(133,24,42,0.10)', 'generates' => 'Thổ', 'controls' => 'Kim', 'generated_by' => 'Mộc', 'controlled_by' => 'Thủy'],
        'Thổ' => ['color' => '#9A7228', 'soft_bg' => 'rgba(154,114,40,0.12)', 'generates' => 'Kim', 'controls' => 'Thủy', 'generated_by' => 'Hỏa', 'controlled_by' => 'Mộc'],
        'Kim' => ['color' => '#52606D', 'soft_bg' => 'rgba(82,96,109,0.12)', 'generates' => 'Thủy', 'controls' => 'Mộc', 'generated_by' => 'Thổ', 'controlled_by' => 'Hỏa'],
        'Thủy' => ['color' => '#1B4965', 'soft_bg' => 'rgba(27,73,101,0.11)', 'generates' => 'Mộc', 'controls' => 'Hỏa', 'generated_by' => 'Kim', 'controlled_by' => 'Thổ'],
    ];

    /** @var list<array{stems: array{0: int, 1: int}, target_element: string, rule_id: string, label: string}> */
    public const STEM_COMBINATIONS = [
        ['stems' => [0, 5], 'target_element' => 'Thổ', 'rule_id' => 'stem_comb_giap_ky', 'label' => 'Giáp – Kỷ Hợp (xu hướng hóa Thổ)'],
        ['stems' => [1, 6], 'target_element' => 'Kim', 'rule_id' => 'stem_comb_at_canh', 'label' => 'Ất – Canh Hợp (xu hướng hóa Kim)'],
        ['stems' => [2, 7], 'target_element' => 'Thủy', 'rule_id' => 'stem_comb_binh_tan', 'label' => 'Bính – Tân Hợp (xu hướng hóa Thủy)'],
        ['stems' => [3, 8], 'target_element' => 'Mộc', 'rule_id' => 'stem_comb_dinh_nham', 'label' => 'Đinh – Nhâm Hợp (xu hướng hóa Mộc)'],
        ['stems' => [4, 9], 'target_element' => 'Hỏa', 'rule_id' => 'stem_comb_mau_quy', 'label' => 'Mậu – Quý Hợp (xu hướng hóa Hỏa)'],
    ];

    /** @var list<array{branches: array{0: int, 1: int}, target_element: string, rule_id: string, label: string}> */
    public const BRANCH_SIX_COMBINATIONS = [
        ['branches' => [0, 1], 'target_element' => 'Thổ', 'rule_id' => 'br_hex_ty_suu', 'label' => 'Tý – Sửu Lục Hợp'],
        ['branches' => [2, 11], 'target_element' => 'Mộc', 'rule_id' => 'br_hex_dan_hoi', 'label' => 'Dần – Hợi Lục Hợp'],
        ['branches' => [3, 10], 'target_element' => 'Hỏa', 'rule_id' => 'br_hex_mao_tuat', 'label' => 'Mão – Tuất Lục Hợp'],
        ['branches' => [4, 9], 'target_element' => 'Kim', 'rule_id' => 'br_hex_thin_dau', 'label' => 'Thìn – Dậu Lục Hợp'],
        ['branches' => [5, 8], 'target_element' => 'Thủy', 'rule_id' => 'br_hex_ty_than', 'label' => 'Tỵ – Thân Lục Hợp'],
        ['branches' => [6, 7], 'target_element' => 'Hỏa', 'rule_id' => 'br_hex_ngo_mui', 'label' => 'Ngọ – Mùi Lục Hợp'],
    ];

    /** @var list<array{branches: array{0: int, 1: int, 2: int}, target_element: string, rule_id: string, label: string}> */
    public const BRANCH_THREE_HARMONIES = [
        ['branches' => [8, 0, 4], 'target_element' => 'Thủy', 'rule_id' => 'br_tri_thuy', 'label' => 'Thân – Tý – Thìn Tam Hợp Thủy cục'],
        ['branches' => [11, 3, 7], 'target_element' => 'Mộc', 'rule_id' => 'br_tri_moc', 'label' => 'Hợi – Mão – Mùi Tam Hợp Mộc cục'],
        ['branches' => [2, 6, 10], 'target_element' => 'Hỏa', 'rule_id' => 'br_tri_hoa', 'label' => 'Dần – Ngọ – Tuất Tam Hợp Hỏa cục'],
        ['branches' => [5, 9, 1], 'target_element' => 'Kim', 'rule_id' => 'br_tri_kim', 'label' => 'Tỵ – Dậu – Sửu Tam Hợp Kim cục'],
    ];

    /** @var list<array{branches: array{0: int, 1: int, 2: int}, target_element: string, rule_id: string, label: string}> */
    public const BRANCH_SEASONAL_MEETINGS = [
        ['branches' => [2, 3, 4], 'target_element' => 'Mộc', 'rule_id' => 'br_meet_moc', 'label' => 'Dần – Mão – Thìn Tam Hội Phương Đông (Mộc)'],
        ['branches' => [5, 6, 7], 'target_element' => 'Hỏa', 'rule_id' => 'br_meet_hoa', 'label' => 'Tỵ – Ngọ – Mùi Tam Hội Phương Nam (Hỏa)'],
        ['branches' => [8, 9, 10], 'target_element' => 'Kim', 'rule_id' => 'br_meet_kim', 'label' => 'Thân – Dậu – Tuất Tam Hội Phương Tây (Kim)'],
        ['branches' => [11, 0, 1], 'target_element' => 'Thủy', 'rule_id' => 'br_meet_thuy', 'label' => 'Hợi – Tý – Sửu Tam Hội Phương Bắc (Thủy)'],
    ];

    /** @var list<array{branches: array{0: int, 1: int}, rule_id: string, label: string}> */
    public const BRANCH_CLASHES = [
        ['branches' => [0, 6], 'rule_id' => 'br_clash_ty_ngo', 'label' => 'Tý – Ngọ Lục Xung'],
        ['branches' => [1, 7], 'rule_id' => 'br_clash_suu_mui', 'label' => 'Sửu – Mùi Lục Xung'],
        ['branches' => [2, 8], 'rule_id' => 'br_clash_dan_than', 'label' => 'Dần – Thân Lục Xung'],
        ['branches' => [3, 9], 'rule_id' => 'br_clash_mao_dau', 'label' => 'Mão – Dậu Lục Xung'],
        ['branches' => [4, 10], 'rule_id' => 'br_clash_thin_tuat', 'label' => 'Thìn – Tuất Lục Xung'],
        ['branches' => [5, 11], 'rule_id' => 'br_clash_ty_hoi', 'label' => 'Tỵ – Hợi Lục Xung'],
    ];

    /** @var list<array{branches: array{0: int, 1: int}, rule_id: string, label: string}> */
    public const BRANCH_HARMS = [
        ['branches' => [0, 7], 'rule_id' => 'br_harm_ty_mui', 'label' => 'Tý – Mùi Lục Hại'],
        ['branches' => [1, 6], 'rule_id' => 'br_harm_suu_ngo', 'label' => 'Sửu – Ngọ Lục Hại'],
        ['branches' => [2, 5], 'rule_id' => 'br_harm_dan_ty', 'label' => 'Dần – Tỵ Lục Hại'],
        ['branches' => [3, 4], 'rule_id' => 'br_harm_mao_thin', 'label' => 'Mão – Thìn Lục Hại'],
        ['branches' => [8, 11], 'rule_id' => 'br_harm_than_hoi', 'label' => 'Thân – Hợi Lục Hại'],
        ['branches' => [9, 10], 'rule_id' => 'br_harm_dau_tuat', 'label' => 'Dậu – Tuất Lục Hại'],
    ];

    /** @var list<array{branches: array{0: int, 1: int}, rule_id: string, label: string}> */
    public const BRANCH_DESTRUCTIONS = [
        ['branches' => [0, 9], 'rule_id' => 'br_dest_ty_dau', 'label' => 'Tý – Dậu Tương Phá'],
        ['branches' => [1, 4], 'rule_id' => 'br_dest_suu_thin', 'label' => 'Sửu – Thìn Tương Phá'],
        ['branches' => [2, 11], 'rule_id' => 'br_dest_dan_hoi', 'label' => 'Dần – Hợi Tương Phá'],
        ['branches' => [3, 6], 'rule_id' => 'br_dest_mao_ngo', 'label' => 'Mão – Ngọ Tương Phá'],
        ['branches' => [5, 8], 'rule_id' => 'br_dest_ty_than', 'label' => 'Tỵ – Thân Tương Phá'],
        ['branches' => [7, 10], 'rule_id' => 'br_dest_mui_tuat', 'label' => 'Mùi – Tuất Tương Phá'],
    ];

    /** @var array<string, array{id: string, name: string, version: string, year_boundary: string, month_boundary: string, day_boundary: string, solar_time_mode: string, desc: string, sources: list<string>}> */
    public const METHOD_PROFILES = [
        'tu_binh_default_v1' => [
            'id' => 'tu_binh_default_v1',
            'name' => 'Tử Bình Chuẩn (Lập Xuân · 12 Tiết · Ngày đổi 23:00 · Giờ Dân Dụng)',
            'version' => '1.0',
            'year_boundary' => 'lap_xuan',
            'month_boundary' => 'twelve_jie_terms',
            'day_boundary' => '23:00',
            'solar_time_mode' => 'civil',
            'desc' => 'Quy ước phổ biến tại Việt Nam và Đông Á: Trụ Năm đổi tại thời điểm Lập Xuân, Trụ Tháng theo 12 mốc Tiết, ngày mới tính từ đầu giờ Tý (23:00), dùng giờ dân dụng tại nơi sinh.',
            'sources' => ['Tử Bình Chân Thuyên', 'Uyên Hải Tử Bình', 'Tam Mệnh Thông Hội'],
        ],
        'tu_binh_midnight_v1' => [
            'id' => 'tu_binh_midnight_v1',
            'name' => 'Tử Bình Đổi Ngày 00:00 (Tý Đêm Giữ Trụ Ngày Cũ)',
            'version' => '1.0',
            'year_boundary' => 'lap_xuan',
            'month_boundary' => 'twelve_jie_terms',
            'day_boundary' => '00:00',
            'solar_time_mode' => 'civil',
            'desc' => 'Phân tách Tý Sớm (00:00–00:59) và Tý Muộn (23:00–23:59): Trụ Ngày đổi tại 00:00, riêng giờ Tý muộn 23:00–23:59 lấy Can giờ theo ngày kế tiếp (Ngũ Thử Độn).',
            'sources' => ['Tam Mệnh Thông Hội', 'Quy ước Tử Bình hiện đại'],
        ],
        'tu_binh_true_solar_v1' => [
            'id' => 'tu_binh_true_solar_v1',
            'name' => 'Tử Bình Giờ Mặt Trời Thực (True Solar Time · Kinh Độ + EoT)',
            'version' => '1.0',
            'year_boundary' => 'lap_xuan',
            'month_boundary' => 'twelve_jie_terms',
            'day_boundary' => '23:00',
            'solar_time_mode' => 'true_solar',
            'desc' => 'Hiệu chỉnh giờ sinh theo kinh độ địa phương và Phương trình thời gian (Equation of Time) để xác định Địa Chi giờ; thời điểm giao Tiết vẫn so sánh trên cùng thang thời gian tuyệt đối UTC.',
            'sources' => ['Astronomical Algorithms (Jean Meeus)', 'Tử Bình Chân Thuyên'],
        ],
        'tu_binh_lmst_v1' => [
            'id' => 'tu_binh_lmst_v1',
            'name' => 'Tử Bình Giờ Mặt Trời Trung Bình (LMST · Hiệu Chỉnh Kinh Độ)',
            'version' => '1.0',
            'year_boundary' => 'lap_xuan',
            'month_boundary' => 'twelve_jie_terms',
            'day_boundary' => '23:00',
            'solar_time_mode' => 'lmst',
            'desc' => 'Hiệu chỉnh độ lệch kinh độ địa phương so với kinh tuyến múi giờ (+4 phút / 1° Đông), chưa cộng Phương trình thời gian.',
            'sources' => ['Thiên văn tính toán địa phương', 'Tử Bình Chân Thuyên'],
        ],
    ];

    /** @var array<string, array{name: string, country: string, tz: string, lat: float, lng: float}> */
    public const BIRTH_LOCATIONS = [
        'ha-noi' => ['name' => 'Hà Nội', 'country' => 'Việt Nam', 'tz' => 'Asia/Ho_Chi_Minh', 'lat' => 21.0285, 'lng' => 105.8542],
        'tp-hcm' => ['name' => 'TP. Hồ Chí Minh', 'country' => 'Việt Nam', 'tz' => 'Asia/Ho_Chi_Minh', 'lat' => 10.8231, 'lng' => 106.6297],
        'da-nang' => ['name' => 'Đà Nẵng', 'country' => 'Việt Nam', 'tz' => 'Asia/Ho_Chi_Minh', 'lat' => 16.0544, 'lng' => 108.2022],
        'hai-phong' => ['name' => 'Hải Phòng', 'country' => 'Việt Nam', 'tz' => 'Asia/Ho_Chi_Minh', 'lat' => 20.8449, 'lng' => 106.6881],
        'can-tho' => ['name' => 'Cần Thơ', 'country' => 'Việt Nam', 'tz' => 'Asia/Ho_Chi_Minh', 'lat' => 10.0452, 'lng' => 105.7469],
        'hue' => ['name' => 'Thừa Thiên Huế', 'country' => 'Việt Nam', 'tz' => 'Asia/Ho_Chi_Minh', 'lat' => 16.4637, 'lng' => 107.5909],
        'nghe-an' => ['name' => 'Nghệ An (Vinh)', 'country' => 'Việt Nam', 'tz' => 'Asia/Ho_Chi_Minh', 'lat' => 18.6796, 'lng' => 105.6813],
        'thanh-hoa' => ['name' => 'Thanh Hóa', 'country' => 'Việt Nam', 'tz' => 'Asia/Ho_Chi_Minh', 'lat' => 19.8067, 'lng' => 105.7852],
        'quang-ninh' => ['name' => 'Quảng Ninh (Hạ Long)', 'country' => 'Việt Nam', 'tz' => 'Asia/Ho_Chi_Minh', 'lat' => 20.9505, 'lng' => 107.0734],
        'nha-trang' => ['name' => 'Khánh Hòa (Nha Trang)', 'country' => 'Việt Nam', 'tz' => 'Asia/Ho_Chi_Minh', 'lat' => 12.2388, 'lng' => 109.1967],
        'da-lat' => ['name' => 'Lâm Đồng (Đà Lạt)', 'country' => 'Việt Nam', 'tz' => 'Asia/Ho_Chi_Minh', 'lat' => 11.9404, 'lng' => 108.4583],
        'vung-tau' => ['name' => 'Bà Rịa – Vũng Tàu', 'country' => 'Việt Nam', 'tz' => 'Asia/Ho_Chi_Minh', 'lat' => 10.3460, 'lng' => 107.0843],
        'ca-mau' => ['name' => 'Cà Mau', 'country' => 'Việt Nam', 'tz' => 'Asia/Ho_Chi_Minh', 'lat' => 9.1768, 'lng' => 105.1524],
        'dien-bien' => ['name' => 'Điện Biên', 'country' => 'Việt Nam', 'tz' => 'Asia/Ho_Chi_Minh', 'lat' => 21.3860, 'lng' => 103.0230],
        'singapore' => ['name' => 'Singapore', 'country' => 'Singapore', 'tz' => 'Asia/Singapore', 'lat' => 1.3521, 'lng' => 103.8198],
        'tokyo' => ['name' => 'Tokyo', 'country' => 'Nhật Bản', 'tz' => 'Asia/Tokyo', 'lat' => 35.6762, 'lng' => 139.6503],
        'seoul' => ['name' => 'Seoul', 'country' => 'Hàn Quốc', 'tz' => 'Asia/Seoul', 'lat' => 37.5665, 'lng' => 126.9780],
        'sydney' => ['name' => 'Sydney', 'country' => 'Úc', 'tz' => 'Australia/Sydney', 'lat' => -33.8688, 'lng' => 151.2093],
        'paris' => ['name' => 'Paris', 'country' => 'Pháp', 'tz' => 'Europe/Paris', 'lat' => 48.8566, 'lng' => 2.3522],
        'london' => ['name' => 'London', 'country' => 'Anh', 'tz' => 'Europe/London', 'lat' => 51.5074, 'lng' => -0.1278],
        'new-york' => ['name' => 'New York', 'country' => 'Hoa Kỳ', 'tz' => 'America/New_York', 'lat' => 40.7128, 'lng' => -74.0060],
        'los-angeles' => ['name' => 'Los Angeles', 'country' => 'Hoa Kỳ', 'tz' => 'America/Los_Angeles', 'lat' => 34.0522, 'lng' => -118.2437],
    ];

    /**
     * Tra cứu Thập Thần từ Nhật Chủ ($dayStemIdx 0..9) tới một Thiên Can bất kỳ ($targetStemIdx 0..9).
     * Ma trận 10×10 = 100 trường hợp xác định tuyệt đối.
     *
     * @return array{code: string, name: string, short: string, hanzi: string, group: string, desc: string}
     */
    public static function getTenGod(int $dayStemIdx, int $targetStemIdx): array
    {
        $day = self::STEMS[$dayStemIdx];
        $target = self::STEMS[$targetStemIdx];
        $samePolarity = $day['yin_yang'] === $target['yin_yang'];
        $el = $day['element'];
        $tEl = $target['element'];
        $meta = self::FIVE_ELEMENTS[$el];

        if ($tEl === $el) {
            return $samePolarity ? self::TEN_GODS['ty_kien'] : self::TEN_GODS['kiep_tai'];
        }
        if ($meta['generates'] === $tEl) {
            return $samePolarity ? self::TEN_GODS['thuc_than'] : self::TEN_GODS['thuong_quan'];
        }
        if ($meta['controls'] === $tEl) {
            return $samePolarity ? self::TEN_GODS['thien_tai'] : self::TEN_GODS['chinh_tai'];
        }
        if ($meta['controlled_by'] === $tEl) {
            return $samePolarity ? self::TEN_GODS['that_sat'] : self::TEN_GODS['chinh_quan'];
        }

        return $samePolarity ? self::TEN_GODS['thien_an'] : self::TEN_GODS['chinh_an'];
    }

    /**
     * Tra cứu trạng thái Vòng Trường Sinh của một Thiên Can tại một Địa Chi.
     */
    public static function getLifeStage(int $stemIdx, int $branchIdx): string
    {
        $cfg = self::STAGE_START_BY_STEM[$stemIdx];
        $offset = (($branchIdx - $cfg['start']) * $cfg['dir'] % 12 + 12) % 12;

        return self::TWELVE_STAGES[$offset];
    }

    /**
     * Tính chỉ số 60 Hoa Giáp (0..59) từ Stem (0..9) và Branch (0..11) cùng tính chẵn lẻ.
     */
    public static function getSexagenaryIndex(int $stemIdx, int $branchIdx): int
    {
        for ($i = 0; $i < 60; $i++) {
            if ($i % 10 === $stemIdx && $i % 12 === $branchIdx) {
                return $i;
            }
        }

        return 0;
    }

    /**
     * Tính Tuần Không Vong (2 Địa Chi không vong) cho một trụ Can Chi.
     *
     * @return array{branches: array{0: int, 1: int}, label: string}
     */
    public static function getKongWang(int $stemIdx, int $branchIdx): array
    {
        $idx = self::getSexagenaryIndex($stemIdx, $branchIdx);
        $xunStart = intdiv($idx, 10) * 10;
        $startBranch = $xunStart % 12;
        $kw1 = ($startBranch + 10) % 12;
        $kw2 = ($startBranch + 11) % 12;

        return [
            'branches' => [$kw1, $kw2],
            'label' => self::BRANCHES[$kw1]['name'].' – '.self::BRANCHES[$kw2]['name'],
        ];
    }

    /**
     * Lấy trọn bộ 60 Hoa Giáp kèm Nạp Âm (tái sử dụng VietnameseCalendarEngine::getNapAm).
     *
     * @return list<array<string, mixed>>
     */
    public static function getSixtyPillars(VietnameseCalendarEngine $calendar): array
    {
        $out = [];
        for ($i = 0; $i < 60; $i++) {
            $s = $i % 10;
            $b = $i % 12;
            $label = self::STEMS[$s]['name'].' '.self::BRANCHES[$b]['name'];
            $out[] = [
                'index' => $i,
                'stem_index' => $s,
                'branch_index' => $b,
                'label' => $label,
                'hanzi' => self::STEMS[$s]['hanzi'].self::BRANCHES[$b]['hanzi'],
                'nap_am' => $calendar->getNapAm($label),
                'kong_wang' => self::getKongWang($s, $b)['label'],
            ];
        }

        return $out;
    }

    /**
     * Xác định trạng thái Vượng – Tướng – Hưu – Tù – Tử của Ngũ Hành Nhật Chủ theo Địa Chi tháng sinh (Nguyệt Lệnh).
     *
     * @return array{state: string, is_dac_lenh: bool, month_element: string, note: string}
     */
    public static function getSeasonalState(string $dayElement, int $monthBranchIdx): array
    {
        $monthEl = self::BRANCHES[$monthBranchIdx]['element'];
        $meta = self::FIVE_ELEMENTS[$monthEl];

        if ($dayElement === $monthEl) {
            return ['state' => 'Vượng', 'is_dac_lenh' => true, 'month_element' => $monthEl, 'note' => 'Nhật Chủ đồng hành với Nguyệt Lệnh (Đắc Lệnh, khí đương vượng).'];
        }
        if ($meta['generates'] === $dayElement) {
            return ['state' => 'Tướng', 'is_dac_lenh' => true, 'month_element' => $monthEl, 'note' => 'Nguyệt Lệnh sinh trợ cho Nhật Chủ (Đắc Lệnh, khí tiến tới).'];
        }
        if ($meta['generated_by'] === $dayElement) {
            return ['state' => 'Hưu', 'is_dac_lenh' => false, 'month_element' => $monthEl, 'note' => 'Nhật Chủ sinh xuất cho Nguyệt Lệnh (Thất Lệnh, khí tiết nghỉ).'];
        }
        if ($meta['controlled_by'] === $dayElement) {
            return ['state' => 'Tù', 'is_dac_lenh' => false, 'month_element' => $monthEl, 'note' => 'Nhật Chủ khắc Nguyệt Lệnh đang vượng (Thất Lệnh, khí bị hao lực).'];
        }

        return ['state' => 'Tử', 'is_dac_lenh' => false, 'month_element' => $monthEl, 'note' => 'Nguyệt Lệnh khắc Nhật Chủ (Thất Lệnh; thuật ngữ "Tử" chỉ trạng thái khí theo mùa, không liên quan tuổi thọ).'];
    }
}
