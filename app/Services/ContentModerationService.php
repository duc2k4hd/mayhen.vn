<?php

namespace App\Services;

class ContentModerationService
{
    /**
     * Danh sách từ khóa thô tục, xúc phạm, chửi bậy, tang tóc và trù ẻo tiếng Việt.
     * Bao gồm cả từ có dấu, không dấu, viết tắt, và từ lóng mạng xã hội.
     */
    protected static array $badWords = [
        // 1. Tục tĩu, bộ phận sinh dục, hành vi tình dục
        'buồi', 'buoi', 'bòi', 'cặc', 'cac', 'kặc', 'kac', 'dái', 'hòn dái', 'hon dai',
        'lồn', 'lon', 'lồz', 'loz', 'l0n', 'bướm', 'hột le', 'vú', 'bóp vú',
        'địt', 'dit', 'd1t', 'đ1t', 'đụ', 'đụ má', 'du ma', 'đụ mẹ', 'du me', 'đụ cụ', 'du cu',
        'chịch', 'chich', 'nện', 'xoạc', 'vét máng', 'vet mang', 'bú cu', 'bu cu', 'liếm lồn', 'liem lon',
        'sóc lọ', 'soc lo', 'sục cặc', 'suc cac', 'thủ dâm', 'thu dam', 'thông đít', 'thong dit',
        'giao cấu', 'giao cau', 'hiếp dâm', 'hấp diêm', 'hap diem', 'cưỡng bức', 'quấy rối tình dục',

        // 2. Chửi thề, viết tắt tiếng lóng Gen Z & MXH
        'đm', 'dm', 'đkm', 'dkm', 'đcm', 'dcm', 'đmm', 'dmm',
        'vcl', 'vkl', 'vcc', 'vl', 'vlon', 'vloz',
        'clgt', 'clgv', 'cmn', 'cmnr', 'cc', 'dbrr', 'đbrr', 'dcmm', 'đcmm',
        'đéo', 'deo', 'đếch', 'dech', 'méo',

        // 3. Xúc phạm cha mẹ, tổ tiên, nhân phẩm gia đình
        'mẹ mày', 'me may', 'mẹ m', 'me m', 'bố mày', 'bo may', 'bố m', 'bo m',
        'bà già mày', 'ba gia may', 'ông già mày', 'ong gia may',
        'cha mày', 'cha may', 'tổ cha', 'to cha', 'tổ mẹ', 'to me',
        'mả cha', 'ma cha', 'mả mẹ', 'ma me', 'tiên sư', 'tien su', 'ông nội mày', 'bà nội mày',

        // 4. Miệt thị phụ nữ, nghề nghiệp xúc phạm
        'đĩ', 'con đĩ', 'con di', 'đĩ mẹ', 'di me', 'đĩ chó', 'di cho', 'đĩ thõa', 'thằng đĩ', 'thang di',
        'điếm', 'diem', 'gái bao', 'gai bao', 'gái gọi', 'gai goi', 'phò', 'pho', 'cave',
        'bán dâm', 'ban dam', 'mại dâm', 'mai dam',

        // 5. Chửi thú vật, miệt thị trí tuệ
        'chó đẻ', 'cho de', 'chó chết', 'cho chet', 'đồ chó', 'do cho', 'thằng chó', 'thang cho', 'con chó', 'con cho',
        'chó má', 'cho ma', 'súc vật', 'suc vat', 'súc sinh', 'suc sinh', 'xúc vật', 'xuc vat',
        'đồ lợn', 'do lon', 'con lợn', 'con lon', 'đồ heo', 'con heo',
        'đồ ngu', 'do ngu', 'ngu lồn', 'ngu lon', 'ngu dốt', 'ngu si', 'óc chó', 'oc cho',
        'óc lợn', 'oc lon', 'óc bò', 'oc bo', 'đần độn', 'dan don', 'đồ đần', 'do dan',
        'đồ khùng', 'do khung', 'bị khùng', 'bi khung', 'thằng khùng', 'thang khung', 'con khùng', 'con khung',
        'đồ điên', 'do dien', 'bị điên', 'bi dien', 'thằng điên', 'thang dien', 'con điên', 'con dien',
        'thiểu năng', 'thieu nang', 'bại não', 'bai nao', 'thần kinh', 'than kinh', 'tâm thần', 'tam than',

        // 6. Trù ẻo đám cưới, tang tóc, ly tán, phá hoại hạnh phúc (Đặc thù thiệp cưới)
        'chết mẹ', 'chet me', 'chết cha', 'chet cha', 'đi chết', 'di chet',
        'chết tiệt', 'chet tiet', 'đoản mệnh', 'doan menh', 'tai nạn', 'tai nan',
        'đám ma', 'dam ma', 'tang gia', 'ma chay', 'quan tài', 'quan tai', 'khăn tang', 'khan tang',
        'bát hương', 'bat huong', 'vòng hoa tang', 'chia buồn tang tóc',
        'ly dị', 'ly di', 'ly hôn', 'ly hon', 'đứt gánh', 'dut ganh',
        'cắm sừng', 'cam sung', 'mọc sừng', 'moc sung', 'tan vỡ', 'tan vo', 'bỏ nhau', 'bo nhau',
        'ngoại tình', 'ngoai tinh', 'phản bội', 'phan boi', 'tiểu tam', 'tieu tam', 'trà xanh', 'tra xanh',
        'con giáp thứ 13', 'con giap thu 13', 'giật chồng', 'giat chong', 'giật vợ', 'giat vo',
        'vô sinh', 'vo sinh', 'tuyệt tự', 'tuyet tu', 'đẻ không có lỗ đít', 'quả báo', 'qua bao',
    ];

    /**
     * Kiểm tra xem chuỗi có chứa từ ngữ vi phạm không.
     */
    public static function containsProfanity(?string $text): bool
    {
        if (empty($text) || trim($text) === '') {
            return false;
        }

        $rawLower = mb_strtolower(trim($text), 'UTF-8');
        $normalizedAscii = self::normalizeToAscii($rawLower);

        // 1. Kiểm tra từ điển có word boundary (cả nguyên bản và ASCII chuẩn hóa)
        foreach (self::$badWords as $badWord) {
            $badWordLower = mb_strtolower($badWord, 'UTF-8');
            $badWordAscii = self::normalizeToAscii($badWordLower);

            if (self::containsWordBoundary($rawLower, $badWordLower)) {
                return true;
            }

            if (self::containsWordBoundary($normalizedAscii, $badWordAscii)) {
                return true;
            }
        }

        // 2. Bắt kẻ cố tình lách chữ bằng ký tự ngắt (dấu chấm, khoảng trắng, gạch, sao, cộng):
        // Ví dụ: d.m, d m, d-m, c.a.c, c a c, l.o.n, l o n, d.i.t, v.c.l, v l
        $separatedPatterns = [
            'dm', 'dkm', 'dcm', 'dmm', 'vcl', 'vkl', 'vl', 'clgt', 'cac', 'kac',
            'lon', 'dit', 'buoi', 'cmn', 'cmnr', 'dbrr', 'dcmm',
        ];

        foreach ($separatedPatterns as $patternWord) {
            if (self::containsSeparatedWord($normalizedAscii, $patternWord)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Kiểm tra từ có ký tự ngắt xen kẽ giữa các chữ cái nhưng được bao bởi boundary.
     * Ví dụ: 'd.m', 'd m', 'c.a.c', 'l * n'
     */
    protected static function containsSeparatedWord(string $haystack, string $shortWord): bool
    {
        $letters = str_split($shortWord);
        // Bắt buộc giữa các chữ cái phải có ít nhất một ký tự ngắt (khoảng trắng, dấu chấm, gạch ngang, sao, v.v.)
        $pattern = '/(?<=^|[^a-z0-9])'.implode('[\s\._\*\-\+~]+', array_map('preg_quote', $letters)).'(?=$|[^a-z0-9])/i';

        return (bool) preg_match($pattern, $haystack);
    }

    /**
     * Tìm từ vi phạm trong câu (phục vụ log hoặc thông báo cụ thể).
     */
    public static function findProfanity(?string $text): ?string
    {
        if (empty($text)) {
            return null;
        }

        foreach (self::$badWords as $badWord) {
            if (self::containsProfanityWord($text, $badWord)) {
                return $badWord;
            }
        }

        return null;
    }

    /**
     * Lấy toàn bộ danh sách từ cấm phục vụ xuất sang JS Frontend.
     */
    public static function getBadWordsList(): array
    {
        return self::$badWords;
    }

    /**
     * Kiểm tra ranh giới từ để tránh bắt nhầm từ ghép vô hại.
     * Ví dụ: từ 'cu' không nên bắt nhầm trong 'cung kính' hay 'tích cực'.
     */
    protected static function containsWordBoundary(string $haystack, string $needle): bool
    {
        // Chèn boundary regex
        $pattern = '/(?<=^|[^\p{L}\p{N}])'.preg_quote($needle, '/').'(?=$|[^\p{L}\p{N}])/u';

        return (bool) preg_match($pattern, $haystack);
    }

    /**
     * Kiểm tra các mẫu lách ngắn: d.m, d-m, d m, v.c.l
     */
    protected static function containsShortPattern(string $haystack, string $shortWord): bool
    {
        $letters = str_split($shortWord);
        $regex = '/(?<=^|[^a-z0-9])'.implode('[\s\._\*\-\+]*', array_map('preg_quote', $letters)).'(?=$|[^a-z0-9])/i';

        return (bool) preg_match($regex, $haystack);
    }

    protected static function containsProfanityWord(string $text, string $badWord): bool
    {
        $rawLower = mb_strtolower($text, 'UTF-8');
        $normalizedAscii = self::normalizeToAscii($rawLower);
        $badWordLower = mb_strtolower($badWord, 'UTF-8');
        $badWordAscii = self::normalizeToAscii($badWordLower);

        return self::containsWordBoundary($rawLower, $badWordLower)
            || self::containsWordBoundary($normalizedAscii, $badWordAscii);
    }

    /**
     * Chuyển đổi chuỗi tiếng Việt có dấu sang không dấu (ASCII chuẩn hóa).
     */
    public static function normalizeToAscii(string $str): string
    {
        $map = [
            'à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
            'đ' => 'd',
            'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
        ];

        $result = strtr($str, $map);

        // Chuyển ký tự số leetspeak phổ biến
        $leetspeak = [
            '0' => 'o',
            '1' => 'i',
            '3' => 'e',
            '4' => 'a',
            '5' => 's',
            '7' => 't',
            '@' => 'a',
        ];

        return strtr($result, $leetspeak);
    }
}
