<?php

declare(strict_types=1);

namespace App\Services\Calendar;

/**
 * Traditional Almanac & Auspicious Date Rule Engine (Ngọc Hạp Thông Thư / Hiệp Kỷ Biện Phương Thư).
 *
 * - Tách bạch hoàn toàn với dữ liệu thiên văn; mỗi quy tắc đều ghi rõ nguồn & xử lý xung đột minh bạch.
 * - Không sinh tỷ lệ % may mắn giả (như 98% đại cát) và không dọa nạt mê tín.
 */
class TraditionalAlmanacEngine
{
    public const RULE_VERSION = 'ngoc_hap_mayhen_v1.0';

    /** @var list<array{name: string, type: 'hoang_dao'|'hac_dao', desc: string}> */
    public const TWELVE_GODS = [
        ['name' => 'Thanh Long', 'type' => 'hoang_dao', 'desc' => 'Cát thần chủ sự hanh thông, thuận khởi đầu.'],
        ['name' => 'Minh Đường', 'type' => 'hoang_dao', 'desc' => 'Cát thần chủ sự sáng tỏ, gặp quý nhân.'],
        ['name' => 'Thiên Hình', 'type' => 'hac_dao', 'desc' => 'Hắc đạo thần nhắc thận trọng giấy tờ, tranh chấp.'],
        ['name' => 'Chu Tước', 'type' => 'hac_dao', 'desc' => 'Hắc đạo thần nhắc giữ lời ăn tiếng nói hòa nhã.'],
        ['name' => 'Kim Quỹ', 'type' => 'hoang_dao', 'desc' => 'Cát thần thuận cưới hỏi, tích lũy và ký kết.'],
        ['name' => 'Thiên Đức', 'type' => 'hoang_dao', 'desc' => 'Cát thần che chở, tốt cho việc thiện và giao dịch.'],
        ['name' => 'Bạch Hổ', 'type' => 'hac_dao', 'desc' => 'Hắc đạo thần nhắc tránh nóng vội khi khởi công.'],
        ['name' => 'Ngọc Đường', 'type' => 'hoang_dao', 'desc' => 'Cát thần thuận cưới hỏi, học hành và khai trương.'],
        ['name' => 'Thiên Lao', 'type' => 'hac_dao', 'desc' => 'Hắc đạo thần dễ gặp trì trệ nếu thiếu chuẩn bị.'],
        ['name' => 'Huyền Vũ', 'type' => 'hac_dao', 'desc' => 'Hắc đạo thần nhắc giữ cẩn trọng tài sản và cam kết.'],
        ['name' => 'Tư Mệnh', 'type' => 'hoang_dao', 'desc' => 'Cát thần thuận việc gia đình, cầu phúc và tổ chức lễ.'],
        ['name' => 'Câu Trần', 'type' => 'hac_dao', 'desc' => 'Hắc đạo thần nhắc tránh quyết định cảm tính.'],
    ];

    /** @var list<array{name: string, good_for: list<string>, caution_for: list<string>, desc: string}> */
    public const TWELVE_TRUC = [
        ['name' => 'Kiến', 'good_for' => ['xuất hành', 'khai trương', 'khởi động dự án'], 'caution_for' => ['động thổ'], 'desc' => 'Khởi phát sinh khí mới, thuận mở đầu công việc.'],
        ['name' => 'Trừ', 'good_for' => ['dọn dẹp', 'sửa nhà', 'khởi đầu mới'], 'caution_for' => ['ký kết lớn'], 'desc' => 'Tống cựu nghinh tân, thuận dọn dẹp và tháo gỡ cũ kỹ.'],
        ['name' => 'Mãn', 'good_for' => ['cưới hỏi', 'ăn hỏi', 'khai trương', 'nhập trạch'], 'caution_for' => ['kiện tụng'], 'desc' => 'Đủ đầy viên mãn, thuận hỷ sự và khai trương.'],
        ['name' => 'Bình', 'good_for' => ['họp mặt', 'sửa sang', 'giao dịch thường nhật'], 'caution_for' => ['khởi công lớn'], 'desc' => 'Bình hòa ổn định, thuận công việc điều hòa.'],
        ['name' => 'Định', 'good_for' => ['cưới hỏi', 'đăng ký kết hôn', 'ký kết', 'nhập trạch'], 'caution_for' => ['tranh chấp'], 'desc' => 'An định bền chặt, rất hợp ký kết và hỷ sự.'],
        ['name' => 'Chấp', 'good_for' => ['động thổ', 'xây dựng', 'lập kế hoạch'], 'caution_for' => ['xuất hành xa'], 'desc' => 'Gìn giữ và kiến tạo nền móng vững chắc.'],
        ['name' => 'Phá', 'good_for' => ['tháo dỡ cũ', 'sửa chữa'], 'caution_for' => ['cưới hỏi', 'khai trương', 'ký kết'], 'desc' => 'Thích hợp dỡ bỏ cái cũ, thường tránh dùng cho hỷ sự.'],
        ['name' => 'Nguy', 'good_for' => ['cúng lễ bình an', 'nghỉ ngơi'], 'caution_for' => ['cưới hỏi', 'động thổ', 'xuất hành xa'], 'desc' => 'Nhắc sự cẩn trọng, tránh mạo hiểm.'],
        ['name' => 'Thành', 'good_for' => ['cưới hỏi', 'đón dâu', 'khai trương', 'nhập trạch', 'ký kết'], 'caution_for' => ['tranh tụng'], 'desc' => 'Thành tựu vẹn toàn, thuận hầu hết các đại sự.'],
        ['name' => 'Thu', 'good_for' => ['thu hoạch', 'tích lũy', 'họp mặt gia đình'], 'caution_for' => ['khai trương mới'], 'desc' => 'Thu gom thành quả, thuận củng cố nội lực.'],
        ['name' => 'Khai', 'good_for' => ['cưới hỏi', 'khai trương', 'nhập trạch', 'động thổ', 'xuất hành'], 'caution_for' => ['an táng'], 'desc' => 'Khai mở hanh thông, rất thuận cho khởi sự và lễ cưới.'],
        ['name' => 'Bế', 'good_for' => ['tu bổ', 'đóng gói', 'tĩnh tâm'], 'caution_for' => ['khai trương', 'cưới hỏi'], 'desc' => 'Thu liễm khép kín, không thiên về khai mở mới.'],
    ];

    /** @var list<array{name: string, element: string, nature: string}> 28 Tú chuẩn Hán-Việt */
    public const TWENTY_EIGHT_MANSIONS = [
        ['name' => 'Giác', 'element' => 'Mộc', 'nature' => 'Thuận cưới hỏi, khởi công'],
        ['name' => 'Cang', 'element' => 'Kim', 'nature' => 'Thận trọng việc lớn'],
        ['name' => 'Đê', 'element' => 'Thổ', 'nature' => 'Nên giữ nhịp bình tĩnh'],
        ['name' => 'Phòng', 'element' => 'Nhật', 'nature' => 'Thuận hỷ sự, nhập trạch'],
        ['name' => 'Tâm', 'element' => 'Nguyệt', 'nature' => 'Tránh vội vàng tranh cãi'],
        ['name' => 'Vĩ', 'element' => 'Hỏa', 'nature' => 'Thuận cưới hỏi, khai trương'],
        ['name' => 'Cơ', 'element' => 'Thủy', 'nature' => 'Thuận xây dựng, mở mang'],
        ['name' => 'Đẩu', 'element' => 'Mộc', 'nature' => 'Thuận học hành, khởi sự'],
        ['name' => 'Ngưu', 'element' => 'Kim', 'nature' => 'Hợp việc nhỏ, tránh đại sự'],
        ['name' => 'Nữ', 'element' => 'Thổ', 'nature' => 'Cẩn trọng lời nói'],
        ['name' => 'Hư', 'element' => 'Nhật', 'nature' => 'Nên tĩnh tâm, tu dưỡng'],
        ['name' => 'Nguy', 'element' => 'Nguyệt', 'nature' => 'Thận trọng khi di chuyển xa'],
        ['name' => 'Thất', 'element' => 'Hỏa', 'nature' => 'Thuận cưới hỏi, xây nhà'],
        ['name' => 'Bích', 'element' => 'Thủy', 'nature' => 'Thuận hỷ sự, khai trương, văn chương'],
        ['name' => 'Khuê', 'element' => 'Mộc', 'nature' => 'Thuận học tập, xuất hành'],
        ['name' => 'Lâu', 'element' => 'Kim', 'nature' => 'Thuận cưới hỏi, họp mặt'],
        ['name' => 'Vị', 'element' => 'Thổ', 'nature' => 'Thuận khai trương, xây dựng'],
        ['name' => 'Mão', 'element' => 'Nhật', 'nature' => 'Giữ sự điềm đạm'],
        ['name' => 'Tất', 'element' => 'Nguyệt', 'nature' => 'Thuận cưới hỏi, động thổ'],
        ['name' => 'Chủy', 'element' => 'Hỏa', 'nature' => 'Tránh hấp tấp'],
        ['name' => 'Sâm', 'element' => 'Thủy', 'nature' => 'Thuận dựng nhà, khai trương'],
        ['name' => 'Tỉnh', 'element' => 'Mộc', 'nature' => 'Thuận gieo trồng, học hỏi'],
        ['name' => 'Quỷ', 'element' => 'Kim', 'nature' => 'Thận trọng ký kết'],
        ['name' => 'Liễu', 'element' => 'Thổ', 'nature' => 'Hợp việc thường nhật'],
        ['name' => 'Tinh', 'element' => 'Nhật', 'nature' => 'Thuận xây dựng nhỏ'],
        ['name' => 'Trương', 'element' => 'Nguyệt', 'nature' => 'Thuận cưới hỏi, khai trương, tiệc mừng'],
        ['name' => 'Dực', 'element' => 'Hỏa', 'nature' => 'Nên thận trọng khi đổi mới'],
        ['name' => 'Chẩn', 'element' => 'Thủy', 'nature' => 'Thuận xuất hành, cưới hỏi, nhập trạch'],
    ];

    /** @var array<string, array{label: string, group: string, preferred_truc: list<string>, avoid_truc: list<string>, check_tam_nuong: bool}> */
    public const ACTIVITY_PROFILES = [
        'cuoi_hoi' => ['label' => 'Cưới hỏi / Lễ thành hôn', 'group' => 'Cưới hỏi', 'preferred_truc' => ['Thành', 'Khai', 'Định', 'Mãn'], 'avoid_truc' => ['Phá', 'Nguy', 'Bế'], 'check_tam_nuong' => true],
        'an_hoi' => ['label' => 'Dạm ngõ / Ăn hỏi', 'group' => 'Cưới hỏi', 'preferred_truc' => ['Định', 'Thành', 'Mãn', 'Khai'], 'avoid_truc' => ['Phá', 'Nguy'], 'check_tam_nuong' => true],
        'dang_ky_ket_hon' => ['label' => 'Đăng ký kết hôn', 'group' => 'Cưới hỏi', 'preferred_truc' => ['Định', 'Thành', 'Khai', 'Bình'], 'avoid_truc' => ['Phá'], 'check_tam_nuong' => false],
        'khai_truong' => ['label' => 'Khai trương / Ký kết', 'group' => 'Công việc', 'preferred_truc' => ['Khai', 'Thành', 'Mãn', 'Kiến'], 'avoid_truc' => ['Phá', 'Bế'], 'check_tam_nuong' => true],
        'nhap_trach' => ['label' => 'Nhập trạch / Chuyển nhà', 'group' => 'Nhà cửa', 'preferred_truc' => ['Thành', 'Khai', 'Định', 'Mãn'], 'avoid_truc' => ['Phá', 'Nguy'], 'check_tam_nuong' => true],
        'dong_tho' => ['label' => 'Động thổ / Sửa nhà', 'group' => 'Nhà cửa', 'preferred_truc' => ['Khai', 'Thành', 'Chấp', 'Định'], 'avoid_truc' => ['Kiến', 'Nguy'], 'check_tam_nuong' => true],
        'gia_dinh' => ['label' => 'Họp mặt / Lễ kỷ niệm gia đình', 'group' => 'Gia đình', 'preferred_truc' => ['Mãn', 'Bình', 'Định', 'Thành', 'Thu'], 'avoid_truc' => [], 'check_tam_nuong' => false],
    ];

    /** @var array<int, string> Hướng Hỷ Thần theo Thiên Can ngày (0 Giáp..9 Quý) */
    protected const HY_THAN_BY_STEM = [
        0 => 'Đông Bắc', 1 => 'Tây Bắc', 2 => 'Tây Nam', 3 => 'Chính Nam', 4 => 'Đông Nam',
        5 => 'Đông Bắc', 6 => 'Tây Bắc', 7 => 'Tây Nam', 8 => 'Chính Nam', 9 => 'Đông Nam',
    ];

    /** @var array<int, string> Hướng Tài Thần theo Thiên Can ngày */
    protected const TAI_THAN_BY_STEM = [
        0 => 'Đông Nam', 1 => 'Đông Nam', 2 => 'Chính Đông', 3 => 'Chính Đông', 4 => 'Chính Bắc',
        5 => 'Chính Nam', 6 => 'Tây Nam', 7 => 'Tây Nam', 8 => 'Chính Tây', 9 => 'Tây Bắc',
    ];

    /** @var list<array{name: string, desc: string}> Lục Diệu dân gian (Khổng Minh Lục Diệu) */
    protected const LUC_DIEU = [
        ['name' => 'Đại An', 'desc' => 'Bình an, thuận việc ổn định và giữ gìn.'],
        ['name' => 'Lưu Niên', 'desc' => 'Nhịp việc chậm rãi, nên kiên nhẫn kiểm tra kỹ.'],
        ['name' => 'Tốc Hỷ', 'desc' => 'Tin vui đến nhanh, thuận việc giao tiếp và hỷ sự.'],
        ['name' => 'Xích Khẩu', 'desc' => 'Nhắc giữ lời nói hòa nhã, tránh hiểu lầm.'],
        ['name' => 'Tiểu Cát', 'desc' => 'Hanh thông nhẹ nhàng, thuận gặp gỡ và hợp tác.'],
        ['name' => 'Không Vong', 'desc' => 'Nên rà soát kỹ kế hoạch trước khi chốt việc lớn.'],
    ];

    /**
     * Phân tích trọn bộ Lịch Vạn Niên truyền thống cho 1 ngày cụ thể.
     *
     * @param  array<string, mixed>  $dayData  Kết quả từ VietnameseCalendarEngine::convertSolarToLunar
     * @param  array<string, mixed>  $solarTermData  Kết quả từ CalendarAstronomyEngine::getSolarTermForDate
     * @return array<string, mixed>
     */
    public function analyzeDay(array $dayData, array $solarTermData): array
    {
        $jdn = (int) $dayData['jdn'];
        $lunarDay = (int) $dayData['lunar']['day'];
        $lunarMonth = (int) $dayData['lunar']['month'];
        $dayStemIdx = (int) $dayData['can_chi']['day']['stem_index'];
        $dayBranchIdx = (int) $dayData['can_chi']['day']['branch_index'];
        $solarMonthBranchIdx = (int) ($solarTermData['solar_month_branch_index'] ?? (($lunarMonth + 1) % 12));

        // 1. Ngày Hoàng Đạo / Hắc Đạo (Ngọc Hạp Thông Thư: khởi Thanh Long theo tháng Âm)
        $thanhLongDayStart = [1 => 0, 7 => 0, 2 => 2, 8 => 2, 3 => 4, 9 => 4, 4 => 6, 10 => 6, 5 => 8, 11 => 8, 6 => 10, 12 => 10][$lunarMonth] ?? 0;
        $godIdx = ($dayBranchIdx - $thanhLongDayStart + 12) % 12;
        $dayGod = self::TWELVE_GODS[$godIdx];

        // 2. 12 Giờ Hoàng Đạo / Hắc Đạo trong ngày (khởi Thanh Long theo Địa Chi ngày)
        $hourStartByDayBranch = [0 => 0, 6 => 0, 1 => 2, 7 => 2, 2 => 4, 8 => 4, 3 => 6, 9 => 6, 4 => 8, 10 => 8, 5 => 10, 11 => 10][$dayBranchIdx] ?? 0;
        $hoursDetail = [];
        $hoangDaoHours = [];
        foreach ($dayData['can_chi']['hours'] as $h) {
            $hGodIdx = ($h['branch_index'] - $hourStartByDayBranch + 12) % 12;
            $hGod = self::TWELVE_GODS[$hGodIdx];
            $item = array_merge($h, [
                'god_name' => $hGod['name'],
                'type' => $hGod['type'],
                'is_hoang_dao' => $hGod['type'] === 'hoang_dao',
            ]);
            $hoursDetail[] = $item;
            if ($item['is_hoang_dao']) {
                $hoangDaoHours[] = $item;
            }
        }

        // 3. Thập Nhị Kiến Trừ (12 Trực) tính theo tháng Tiết Khí (Lập Xuân = tháng Dần)
        $trucIdx = ($dayBranchIdx - $solarMonthBranchIdx + 12) % 12;
        $truc = self::TWELVE_TRUC[$trucIdx];

        // 4. Nhị Thập Bát Tú (chu kỳ 28 ngày dân gian, mốc JDN chuẩn 2451545 = 01/01/2000 là Tú Hư = index 10)
        $mansionIdx = (($jdn - 2451545) % 28 + 28 + 10) % 28;
        $mansion = self::TWENTY_EIGHT_MANSIONS[$mansionIdx];

        // 5. Lục Diệu (khởi từ Tháng 1 ngày 1 = Đại An)
        $lucDieuIdx = (($lunarMonth - 1) + ($lunarDay - 1)) % 6;
        $lucDieu = self::LUC_DIEU[$lucDieuIdx];

        // 6. Ngày kiêng kỵ dân gian (tách rõ nguồn & không gây hoang mang)
        $folkCautions = [];
        if (in_array($lunarDay, [3, 7, 13, 18, 22, 27], true)) {
            $folkCautions[] = [
                'rule_id' => 'folk_tam_nuong',
                'name' => 'Ngày Tam Nương',
                'source' => 'Quan niệm dân gian Việt Nam (mùng 3, 7, 13, 18, 22, 27 Âm lịch)',
                'note' => 'Dân gian thường hạn chế chọn cho lễ cưới hoặc khai trương; các sinh hoạt, khám bệnh hay công việc thiết yếu vẫn diễn ra bình thường.',
            ];
        }
        if (in_array($lunarDay, [5, 14, 23], true)) {
            $folkCautions[] = [
                'rule_id' => 'folk_nguyet_ky',
                'name' => 'Ngày Nguyệt Kỵ',
                'source' => 'Tục ngữ dân gian (“Mùng năm, mười bốn, hăm ba”)',
                'note' => 'Lưu ý mang tính văn hóa truyền thống khi xuất hành xa hoặc khởi sự lớn.',
            ];
        }

        // 7. Hướng xuất hành (Ngọc Hạp Thông Thư theo Thiên Can ngày)
        $directions = [
            'hy_than' => self::HY_THAN_BY_STEM[$dayStemIdx],
            'tai_than' => self::TAI_THAN_BY_STEM[$dayStemIdx],
            'source' => 'Ngọc Hạp Thông Thư (quy ước theo Thiên Can ngày)',
        ];

        // 8. Tuổi xung theo Địa Chi ngày (Lục Xung: cách 6 vị trí)
        $clashBranchIdx = ($dayBranchIdx + 6) % 12;
        $clashZodiac = VietnameseCalendarEngine::BRANCHES[$clashBranchIdx]['name'].' ('.VietnameseCalendarEngine::BRANCHES[$clashBranchIdx]['zodiac'].')';

        return [
            'rule_version' => self::RULE_VERSION,
            'day_quality' => [
                'type' => $dayGod['type'],
                'label' => $dayGod['type'] === 'hoang_dao' ? 'Ngày Hoàng Đạo' : 'Ngày Hắc Đạo',
                'god_name' => $dayGod['name'],
                'desc' => $dayGod['desc'],
                'source' => 'Ngọc Hạp Thông Thư (12 Thần Hoàng Đạo / Hắc Đạo)',
            ],
            'truc' => array_merge($truc, ['source' => 'Thập Nhị Kiến Trừ (theo tháng Tiết Khí)']),
            'mansion' => array_merge($mansion, ['source' => 'Nhị Thập Bát Tú (chu kỳ 28 ngày lịch dân gian, tách biệt tọa độ thiên văn)']),
            'luc_dieu' => array_merge($lucDieu, ['source' => 'Lục Diệu dân gian']),
            'hours' => $hoursDetail,
            'hoang_dao_hours' => $hoangDaoHours,
            'folk_cautions' => $folkCautions,
            'directions' => $directions,
            'clash_branch' => $clashZodiac,
        ];
    }

    /**
     * Đánh giá minh bạch một ngày cho mục đích cụ thể (cưới hỏi, khai trương, nhập trạch...) kèm xử lý xung đột.
     *
     * @param  array<string, mixed>  $almanac
     * @return array{activity_key: string, activity_label: string, status: 'favorable'|'mixed'|'caution', status_label: string, favorable_rules: list<string>, caution_rules: list<string>, conflicts: list<string>}
     */
    public function evaluateDateForActivity(array $almanac, string $activityKey = 'cuoi_hoi'): array
    {
        $profile = self::ACTIVITY_PROFILES[$activityKey] ?? self::ACTIVITY_PROFILES['cuoi_hoi'];
        $favorable = [];
        $cautions = [];
        $conflicts = [];

        $isHoangDao = ($almanac['day_quality']['type'] ?? '') === 'hoang_dao';
        if ($isHoangDao) {
            $favorable[] = "Ngày Hoàng Đạo ({$almanac['day_quality']['god_name']}): {$almanac['day_quality']['desc']}";
        } else {
            $cautions[] = "Ngày Hắc Đạo ({$almanac['day_quality']['god_name']}): vẫn có 6 khung giờ Hoàng Đạo trong ngày nếu cần chọn giờ.";
        }

        $trucName = (string) ($almanac['truc']['name'] ?? '');
        if (in_array($trucName, $profile['preferred_truc'], true)) {
            $favorable[] = "Trực {$trucName}: thuộc nhóm Trực phù hợp với {$profile['label']}.";
        } elseif (in_array($trucName, $profile['avoid_truc'], true)) {
            $cautions[] = "Trực {$trucName}: theo Thập Nhị Kiến Trừ thường cân nhắc tránh cho {$profile['label']}.";
        }

        if ($profile['check_tam_nuong']) {
            foreach (($almanac['folk_cautions'] ?? []) as $fc) {
                $cautions[] = "{$fc['name']} ({$fc['source']}).";
            }
        }

        if ($favorable !== [] && $cautions !== []) {
            $conflicts[] = 'Ngày này có đồng thời yếu tố thuận lợi và yếu tố cần lưu tâm giữa các hệ quy tắc khác nhau; gia đình nên ưu tiên lịch rảnh thực tế, sự đồng thuận hai bên và chọn khung giờ Hoàng Đạo phù hợp.';
        }

        $status = 'mixed';
        $statusLabel = 'Cân nhắc theo điều kiện thực tế (Có yếu tố đan xen)';
        if ($cautions === [] && count($favorable) >= 2) {
            $status = 'favorable';
            $statusLabel = 'Thuận lợi theo quy tắc truyền thống';
        } elseif ($favorable === [] && $cautions !== []) {
            $status = 'caution';
            $statusLabel = 'Nên cân nhắc đổi ngày nếu lịch gia đình linh hoạt';
        }

        return [
            'activity_key' => $activityKey,
            'activity_label' => $profile['label'],
            'status' => $status,
            'status_label' => $statusLabel,
            'favorable_rules' => $favorable,
            'caution_rules' => $cautions,
            'conflicts' => $conflicts,
        ];
    }
}
