<?php

namespace App\Services;

class ContentModerationService
{
    /**
     * Danh sách từ khóa thô tục, xúc phạm, chửi bậy, tang tóc và trù ẻo tiếng Việt & Quốc tế.
     * Tích hợp toàn diện từ 2 kho mã nguồn mở:
     * - https://github.com/blue-eyes-vn/vietnamese-offensive-words
     * - https://github.com/zautumnz/profane-words
     */
    protected static ?array $badWords = null;

    protected static ?array $singleExactSet = null;

    protected static ?array $singleAsciiSet = null;

    protected static ?array $multiChunksExact = null;

    protected static ?array $multiChunksAscii = null;

    protected static ?array $asciiAmbiguous = null;

    protected static ?array $separatedPatterns = null;

    /**
     * Khởi tạo cấu trúc tra cứu từ điển siêu tốc (O(1) Set + Regex Phân Khúc).
     */
    protected static function initDictionary(): void
    {
        if (self::$badWords !== null) {
            return;
        }

        if (function_exists('config') && app()->bound('config')) {
            self::$badWords = config('moderation.bad_words', []);
            self::$asciiAmbiguous = config('moderation.ascii_ambiguous', []);
            self::$separatedPatterns = config('moderation.separated_patterns', []);
        } else {
            $configPath = dirname(__DIR__, 2).'/config/moderation.php';
            $loadedConfig = file_exists($configPath) ? (require $configPath) : [];
            self::$badWords = $loadedConfig['bad_words'] ?? [];
            self::$asciiAmbiguous = $loadedConfig['ascii_ambiguous'] ?? [];
            self::$separatedPatterns = $loadedConfig['separated_patterns'] ?? [];
        }

        self::$singleExactSet = [];
        self::$singleAsciiSet = [];
        $multiPhrases = [];

        foreach (self::$badWords as $word) {
            $wLower = mb_strtolower(trim($word), 'UTF-8');
            if (empty($wLower)) {
                continue;
            }

            if (str_contains($wLower, ' ') || str_contains($wLower, '-') || str_contains($wLower, '_') || str_contains($wLower, '.')) {
                $multiPhrases[] = $wLower;
            } else {
                self::$singleExactSet[$wLower] = true;
                $wAscii = self::normalizeToAscii($wLower);
                if (! in_array($wAscii, self::$asciiAmbiguous, true)) {
                    self::$singleAsciiSet[$wAscii] = true;
                }
            }
        }

        // Biên dịch regex phân khúc cho các cụm từ ghép (Chunked Regex)
        self::$multiChunksExact = [];
        self::$multiChunksAscii = [];

        foreach (array_chunk($multiPhrases, 150) as $chunk) {
            $quotedExact = array_map(fn ($p) => preg_quote($p, '/'), $chunk);
            self::$multiChunksExact[] = '/(?<=^|[^\p{L}\p{N}])('.implode('|', $quotedExact).')(?=$|[^\p{L}\p{N}])/ui';

            $quotedAscii = [];
            foreach ($chunk as $p) {
                $pAscii = self::normalizeToAscii($p);
                if (! in_array($pAscii, self::$asciiAmbiguous, true)) {
                    $quotedAscii[] = preg_quote($pAscii, '/');
                }
            }
            if (! empty($quotedAscii)) {
                self::$multiChunksAscii[] = '/(?<=^|[^\p{L}\p{N}])('.implode('|', array_unique($quotedAscii)).')(?=$|[^\p{L}\p{N}])/ui';
            }
        }
    }

    /**
     * Kiểm tra xem chuỗi có chứa từ ngữ vi phạm không (Hiệu năng cao: < 2ms).
     */
    public static function containsProfanity(?string $text): bool
    {
        if (empty($text) || trim($text) === '') {
            return false;
        }

        self::initDictionary();

        $rawLower = mb_strtolower(trim($text), 'UTF-8');
        $normalizedAscii = self::normalizeToAscii($rawLower);

        // 1. Kiểm tra từ đơn nguyên bản có dấu (Exact Set O(1))
        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $rawLower, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($tokens as $token) {
            if (isset(self::$singleExactSet[$token])) {
                return true;
            }
        }

        // 2. Kiểm tra từ đơn không dấu ASCII (ASCII Set O(1))
        $asciiTokens = preg_split('/[^\p{L}\p{N}]+/u', $normalizedAscii, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($asciiTokens as $token) {
            if (isset(self::$singleAsciiSet[$token])) {
                return true;
            }
        }

        // 3. Kiểm tra cụm từ ghép nguyên bản (Multiword Regex Chunks)
        foreach (self::$multiChunksExact as $regex) {
            if (preg_match($regex, $rawLower)) {
                return true;
            }
        }

        // 4. Kiểm tra cụm từ ghép không dấu ASCII (Multiword Regex Chunks)
        foreach (self::$multiChunksAscii as $regex) {
            if (preg_match($regex, $normalizedAscii)) {
                return true;
            }
        }

        // 5. Bắt kẻ cố tình lách chữ bằng ký tự ngắt (d.m, c a c, v.c.l, ...)
        foreach (self::$separatedPatterns as $patternWord) {
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
        $pattern = '/(?<=^|[^a-z0-9])'.implode('[\s\._\*\-\+~]+', array_map('preg_quote', $letters)).'(?=$|[^a-z0-9])/i';

        return (bool) preg_match($pattern, $haystack);
    }

    /**
     * Tìm từ vi phạm trong câu (phục vụ log hoặc thông báo cụ thể).
     */
    public static function findProfanity(?string $text): ?string
    {
        if (empty($text) || trim($text) === '') {
            return null;
        }

        self::initDictionary();

        $rawLower = mb_strtolower(trim($text), 'UTF-8');
        $normalizedAscii = self::normalizeToAscii($rawLower);

        // 1. Single exact
        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $rawLower, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($tokens as $token) {
            if (isset(self::$singleExactSet[$token])) {
                return $token;
            }
        }

        // 2. Single ascii
        $asciiTokens = preg_split('/[^\p{L}\p{N}]+/u', $normalizedAscii, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($asciiTokens as $token) {
            if (isset(self::$singleAsciiSet[$token])) {
                return $token;
            }
        }

        // 3. Multiword exact
        foreach (self::$multiChunksExact as $regex) {
            if (preg_match($regex, $rawLower, $matches)) {
                return $matches[0];
            }
        }

        // 4. Multiword ascii
        foreach (self::$multiChunksAscii as $regex) {
            if (preg_match($regex, $normalizedAscii, $matches)) {
                return $matches[0];
            }
        }

        // 5. Separated patterns
        foreach (self::$separatedPatterns as $patternWord) {
            if (self::containsSeparatedWord($normalizedAscii, $patternWord)) {
                return $patternWord;
            }
        }

        return null;
    }

    /**
     * Lấy toàn bộ danh sách từ cấm phục vụ xuất sang JS Frontend.
     */
    public static function getBadWordsList(): array
    {
        self::initDictionary();

        return self::$badWords ?? [];
    }

    /**
     * Kiểm tra ranh giới từ để tránh bắt nhầm từ ghép vô hại.
     */
    protected static function containsWordBoundary(string $haystack, string $needle): bool
    {
        $pattern = '/(?<=^|[^\p{L}\p{N}])'.preg_quote($needle, '/').'(?=$|[^\p{L}\p{N}])/u';

        return (bool) preg_match($pattern, $haystack);
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
