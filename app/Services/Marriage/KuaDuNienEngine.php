<?php

declare(strict_types=1);

namespace App\Services\Marriage;

/**
 * Engine Cung Phi Bát Trạch & Ma Trận Du Niên 8×8 (64 tổ hợp có thứ tự).
 *
 * - Công thức chuẩn dùng tổng rút gọn 4 chữ số năm sinh (áp dụng chính xác cho cả trước & sau năm 2000, phạm vi 1900–2100):
 *   r = digitalRoot(year)
 *   Nam: k = digitalRoot(11 - r), nếu k == 5 -> Khôn (2)
 *   Nữ:  k = digitalRoot(4 + r),  nếu k == 5 -> Cấn (8)
 * - 8 Quẻ vẽ đúng 3 hào Âm/Dương từ dưới lên trên (hào 1 -> hào 3).
 * - Ma trận Du Niên 8×8 đối xứng chuẩn Bát Trạch: đường chéo = Phục Vị; Khảm–Tốn = Sinh Khí; Khảm–Ly = Diên Niên.
 */
class KuaDuNienEngine
{
    public const VERSION = 'bat_trach_mayhen_v1.0';

    /**
     * 8 Quẻ Bát Quái (lines: [hào dưới, hào giữa, hào trên], 1 = Dương liền, 0 = Âm đứt).
     *
     * @var array<int, array{number: int, code: string, name: string, hanzi: string, trigram: string, element: string, group: string, lines: array{0: int, 1: int, 2: int}}>
     */
    public const TRIGRAMS = [
        1 => ['number' => 1, 'code' => 'kham', 'name' => 'Khảm', 'hanzi' => '坎', 'trigram' => '☵', 'element' => 'Thủy', 'group' => 'Đông Tứ Mệnh', 'lines' => [0, 1, 0]],
        2 => ['number' => 2, 'code' => 'khon', 'name' => 'Khôn', 'hanzi' => '坤', 'trigram' => '☷', 'element' => 'Thổ', 'group' => 'Tây Tứ Mệnh', 'lines' => [0, 0, 0]],
        3 => ['number' => 3, 'code' => 'chan', 'name' => 'Chấn', 'hanzi' => '震', 'trigram' => '☳', 'element' => 'Mộc', 'group' => 'Đông Tứ Mệnh', 'lines' => [1, 0, 0]],
        4 => ['number' => 4, 'code' => 'ton', 'name' => 'Tốn', 'hanzi' => '巽', 'trigram' => '☴', 'element' => 'Mộc', 'group' => 'Đông Tứ Mệnh', 'lines' => [0, 1, 1]],
        6 => ['number' => 6, 'code' => 'can', 'name' => 'Càn', 'hanzi' => '乾', 'trigram' => '☰', 'element' => 'Kim', 'group' => 'Tây Tứ Mệnh', 'lines' => [1, 1, 1]],
        7 => ['number' => 7, 'code' => 'doai', 'name' => 'Đoài', 'hanzi' => '兌', 'trigram' => '☱', 'element' => 'Kim', 'group' => 'Tây Tứ Mệnh', 'lines' => [1, 1, 0]],
        8 => ['number' => 8, 'code' => 'can_nui', 'name' => 'Cấn', 'hanzi' => '艮', 'trigram' => '☶', 'element' => 'Thổ', 'group' => 'Tây Tứ Mệnh', 'lines' => [0, 0, 1]],
        9 => ['number' => 9, 'code' => 'ly', 'name' => 'Ly', 'hanzi' => '離', 'trigram' => '☲', 'element' => 'Hỏa', 'group' => 'Đông Tứ Mệnh', 'lines' => [1, 0, 1]],
    ];

    /** @var array<string, array{code: string, name: string, group: string, modern_note: string}> */
    public const DU_NIEN_META = [
        'sinh_khi' => ['code' => 'sinh_khi', 'name' => 'Sinh Khí', 'group' => 'Cát (Bát Trạch)', 'modern_note' => 'Tượng trưng cho sinh lực dồi dào, tinh thần cầu tiến và sự nâng đỡ nhau phát triển.'],
        'thien_y' => ['code' => 'thien_y', 'name' => 'Thiên Y', 'group' => 'Cát (Bát Trạch)', 'modern_note' => 'Tượng trưng cho sự chăm sóc, an hòa và tinh thần quan tâm lẫn nhau trong đời sống hằng ngày.'],
        'dien_nien' => ['code' => 'dien_nien', 'name' => 'Diên Niên (Phúc Đức)', 'group' => 'Cát (Bát Trạch)', 'modern_note' => 'Tượng trưng cho sự ổn định, bền bỉ và hòa thuận trong quan hệ gia đình hai bên.'],
        'phuc_vi' => ['code' => 'phuc_vi', 'name' => 'Phục Vị', 'group' => 'Cát (Bát Trạch)', 'modern_note' => 'Tượng trưng cho sự bình yên, đồng điệu nếp sống và vững tâm khi bên nhau.'],
        'hoa_hai' => ['code' => 'hoa_hai', 'name' => 'Họa Hại', 'group' => 'Cần lưu tâm (Bát Trạch)', 'modern_note' => 'Thuật ngữ Bát Trạch nhắc giữ sự khéo léo trong lời nói và tránh những hiểu lầm nhỏ tích tụ.'],
        'luc_sat' => ['code' => 'luc_sat', 'name' => 'Lục Sát', 'group' => 'Cần lưu tâm (Bát Trạch)', 'modern_note' => 'Thuật ngữ Bát Trạch nhắc giữ ranh giới lành mạnh và minh bạch trong các mối quan hệ xung quanh (khác với Lục Tuyệt Mạng của Cao Ly).'],
        'ngu_quy' => ['code' => 'ngu_quy', 'name' => 'Ngũ Quỷ', 'group' => 'Cần lưu tâm (Bát Trạch)', 'modern_note' => 'Thuật ngữ Bát Trạch nhắc kiểm soát nóng nảy và tránh tranh cãi cảm tính khi bất đồng quan điểm.'],
        'tuyet_menh' => ['code' => 'tuyet_menh', 'name' => 'Tuyệt Mệnh', 'group' => 'Cần lưu tâm (Bát Trạch)', 'modern_note' => 'Thuật ngữ cổ của phép Bát Trạch biểu thị sự khác biệt mạnh về nhịp sống — hoàn toàn không phải kết luận về sức khỏe hay tuổi thọ.'],
    ];

    /**
     * Ma trận phối cung 8×8 (64 tổ hợp chuẩn Bát Trạch Minh Cảnh, đối xứng hoàn toàn).
     *
     * @var array<int, array<int, string>>
     */
    public const MATRIX = [
        1 => [1 => 'phuc_vi', 2 => 'tuyet_menh', 3 => 'thien_y', 4 => 'sinh_khi', 6 => 'luc_sat', 7 => 'hoa_hai', 8 => 'ngu_quy', 9 => 'dien_nien'],
        2 => [1 => 'tuyet_menh', 2 => 'phuc_vi', 3 => 'hoa_hai', 4 => 'ngu_quy', 6 => 'dien_nien', 7 => 'thien_y', 8 => 'sinh_khi', 9 => 'luc_sat'],
        3 => [1 => 'thien_y', 2 => 'hoa_hai', 3 => 'phuc_vi', 4 => 'dien_nien', 6 => 'ngu_quy', 7 => 'tuyet_menh', 8 => 'luc_sat', 9 => 'sinh_khi'],
        4 => [1 => 'sinh_khi', 2 => 'ngu_quy', 3 => 'dien_nien', 4 => 'phuc_vi', 6 => 'hoa_hai', 7 => 'luc_sat', 8 => 'tuyet_menh', 9 => 'thien_y'],
        6 => [1 => 'luc_sat', 2 => 'dien_nien', 3 => 'ngu_quy', 4 => 'hoa_hai', 6 => 'phuc_vi', 7 => 'sinh_khi', 8 => 'thien_y', 9 => 'tuyet_menh'],
        7 => [1 => 'hoa_hai', 2 => 'thien_y', 3 => 'tuyet_menh', 4 => 'luc_sat', 6 => 'sinh_khi', 7 => 'phuc_vi', 8 => 'dien_nien', 9 => 'ngu_quy'],
        8 => [1 => 'ngu_quy', 2 => 'sinh_khi', 3 => 'luc_sat', 4 => 'tuyet_menh', 6 => 'thien_y', 7 => 'dien_nien', 8 => 'phuc_vi', 9 => 'hoa_hai'],
        9 => [1 => 'dien_nien', 2 => 'luc_sat', 3 => 'sinh_khi', 4 => 'thien_y', 6 => 'tuyet_menh', 7 => 'ngu_quy', 8 => 'hoa_hai', 9 => 'phuc_vi'],
    ];

    /**
     * Tính Cung Phi theo năm Âm lịch & giới tính (chuẩn 4 chữ số năm sinh 1900–2100).
     *
     * @return array{number: int, code: string, name: string, hanzi: string, trigram: string, element: string, group: string, lines: array{0: int, 1: int, 2: int}, converted_from_5: bool}
     */
    public function calculateKua(int $lunarYear, string $gender): array
    {
        $r = $this->digitalRoot($lunarYear);
        $raw = $gender === 'female' ? $this->digitalRoot(4 + $r) : $this->digitalRoot(11 - $r);
        $convertedFrom5 = false;

        if ($raw === 5) {
            $raw = $gender === 'female' ? 8 : 2; // Nam 5 -> Khôn (2), Nữ 5 -> Cấn (8)
            $convertedFrom5 = true;
        }

        return array_merge(self::TRIGRAMS[$raw], ['converted_from_5' => $convertedFrom5]);
    }

    /**
     * Tra cứu Du Niên từ 2 số Cung Phi (1,2,3,4,6,7,8,9).
     *
     * @return array{code: string, name: string, group: string, modern_note: string, kua_a: array<string, mixed>, kua_b: array<string, mixed>, same_group: bool}
     */
    public function lookupDuNien(int $kuaA, int $kuaB): array
    {
        $duNienCode = self::MATRIX[$kuaA][$kuaB] ?? 'phuc_vi';
        $meta = self::DU_NIEN_META[$duNienCode];
        $trA = self::TRIGRAMS[$kuaA];
        $trB = self::TRIGRAMS[$kuaB];

        return array_merge($meta, [
            'kua_a' => $trA,
            'kua_b' => $trB,
            'same_group' => $trA['group'] === $trB['group'],
        ]);
    }

    protected function digitalRoot(int $n): int
    {
        $n = abs($n);
        if ($n === 0) {
            return 9;
        }
        $mod = $n % 9;

        return $mod === 0 ? 9 : $mod;
    }
}
