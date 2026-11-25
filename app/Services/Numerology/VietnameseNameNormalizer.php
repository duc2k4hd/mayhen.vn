<?php

declare(strict_types=1);

namespace App\Services\Numerology;

use InvalidArgumentException;
use Normalizer;

/**
 * Chuẩn hóa họ tên tiếng Việt & quốc tế cho Thần Số Học Pythagorean.
 *
 * - Đảm bảo NFC & NFD cho kết quả giống nhau 100%.
 * - Bỏ dấu thanh/mũ, ánh xạ Đ/đ → D (quy ước bản địa hóa minh bạch Mây Hẹn).
 * - Chặn ký tự ngoài hệ Latin (không tự ý xóa bỏ).
 * - Phân nhóm Họ / Tên đệm / Tên chính (hỗ trợ tùy chỉnh họ ghép, tên ghép).
 * - Phân loại nguyên âm/phụ âm và quy ước chữ Y (`vn_phonetic_y_v1` / `en_phonetic_y_v1` + override từng vị trí).
 */
class VietnameseNameNormalizer
{
    public const Y_MODE_VN = 'vn_phonetic_y_v1';

    public const Y_MODE_EN = 'en_phonetic_y_v1';

    /** @var list<string> */
    protected const BASE_VOWELS = ['A', 'E', 'I', 'O', 'U'];

    /** @var array<string, string> Bảng dự phòng khi không có intl Normalizer */
    protected const VN_CHAR_MAP = [
        'À' => 'A', 'Á' => 'A', 'Ả' => 'A', 'Ã' => 'A', 'Ạ' => 'A',
        'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A', 'Ặ' => 'A',
        'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A', 'Ậ' => 'A',
        'à' => 'A', 'á' => 'A', 'ả' => 'A', 'ã' => 'A', 'ạ' => 'A',
        'ă' => 'A', 'ằ' => 'A', 'ắ' => 'A', 'ẳ' => 'A', 'ẵ' => 'A', 'ặ' => 'A',
        'â' => 'A', 'ầ' => 'A', 'ấ' => 'A', 'ẩ' => 'A', 'ẫ' => 'A', 'ậ' => 'A',
        'È' => 'E', 'É' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E', 'Ẹ' => 'E',
        'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ể' => 'E', 'Ễ' => 'E', 'Ệ' => 'E',
        'è' => 'E', 'é' => 'E', 'ẻ' => 'E', 'ẽ' => 'E', 'ẹ' => 'E',
        'ê' => 'E', 'ề' => 'E', 'ế' => 'E', 'ể' => 'E', 'ễ' => 'E', 'ệ' => 'E',
        'Ì' => 'I', 'Í' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I', 'Ị' => 'I',
        'ì' => 'I', 'í' => 'I', 'ỉ' => 'I', 'ĩ' => 'I', 'ị' => 'I',
        'Ò' => 'O', 'Ó' => 'O', 'Ỏ' => 'O', 'Õ' => 'O', 'Ọ' => 'O',
        'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O', 'Ộ' => 'O',
        'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O', 'Ợ' => 'O',
        'ò' => 'O', 'ó' => 'O', 'ỏ' => 'O', 'õ' => 'O', 'ọ' => 'O',
        'ô' => 'O', 'ồ' => 'O', 'ố' => 'O', 'ổ' => 'O', 'ỗ' => 'O', 'ộ' => 'O',
        'ơ' => 'O', 'ờ' => 'O', 'ớ' => 'O', 'ở' => 'O', 'ỡ' => 'O', 'ợ' => 'O',
        'Ù' => 'U', 'Ú' => 'U', 'Ủ' => 'U', 'Ũ' => 'U', 'Ụ' => 'U',
        'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ử' => 'U', 'Ữ' => 'U', 'Ự' => 'U',
        'ù' => 'U', 'ú' => 'U', 'ủ' => 'U', 'ũ' => 'U', 'ụ' => 'U',
        'ư' => 'U', 'ừ' => 'U', 'ứ' => 'U', 'ử' => 'U', 'ữ' => 'U', 'ự' => 'U',
        'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y', 'Ỵ' => 'Y',
        'ỳ' => 'Y', 'ý' => 'Y', 'ỷ' => 'Y', 'ỹ' => 'Y', 'ỵ' => 'Y',
        'Đ' => 'D', 'đ' => 'D',
    ];

    /**
     * Chuẩn hóa chuỗi họ tên sang Latin in hoa A–Z (giữ nguyên thứ tự từ, từ chối ký tự ngoài Latin).
     */
    public function normalizeToLatin(string $rawName): string
    {
        $trimmed = trim($rawName);
        if ($trimmed === '') {
            return '';
        }

        // Chuyển Đ/đ trước khi phân tách dấu Unicode
        $prepared = str_replace(['Đ', 'đ'], 'D', $trimmed);

        if (class_exists(Normalizer::class)) {
            $decomposed = Normalizer::normalize($prepared, Normalizer::FORM_D);
            if ($decomposed !== false) {
                $prepared = $decomposed;
            }
        }

        // Loại bỏ toàn bộ dấu thanh & dấu mũ tổ hợp (Unicode Combining Diacritical Marks)
        $stripped = (string) preg_replace('/\p{Mn}+/u', '', $prepared);
        $stripped = strtr($stripped, self::VN_CHAR_MAP);

        // Chặn chữ cái ngoài hệ Latin (Hán, Nhật, Hàn, Cyrillic, Ả Rập, Thái...) — không âm thầm bỏ qua
        if (preg_match('/[^\p{Latin}\s\-\'\.]/u', $stripped) === 1 && preg_match('/\p{L}/u', preg_replace('/[A-Za-z\s\-\'\.]/', '', $stripped) ?? '') === 1) {
            throw new InvalidArgumentException('Họ tên chứa ký tự ngoài bảng chữ cái Latin. Vui lòng nhập cách viết Latin (không dấu hoặc tiếng Việt) để đảm bảo phép tính chính xác.');
        }

        // Dấu gạch nối tách từ theo quy ước; dấu nháy đơn trong tên quốc tế (O'Connor) gộp liền ký tự
        $cleaned = str_replace(["'", '’', '.'], '', $stripped);
        $cleaned = (string) preg_replace('/[\-]+/u', ' ', $cleaned);
        $cleaned = (string) preg_replace('/[^A-Za-z\s]+/u', ' ', $cleaned);
        $cleaned = trim((string) preg_replace('/\s+/u', ' ', $cleaned));

        return strtoupper($cleaned);
    }

    /**
     * Phân tích đầy đủ họ tên thành các nhóm (Họ / Tên đệm / Tên chính) và từng ký tự kèm giá trị Pythagorean & loại nguyên âm/phụ âm.
     *
     * @param  array{last?: string, middle?: string, first?: string}|null  $customGroups
     * @param  array<int, string>  $yOverrides  Map [globalLetterIndex => 'vowel'|'consonant']
     * @return array<string, mixed>
     */
    public function analyze(
        string $rawName,
        string $yMode = self::Y_MODE_VN,
        ?array $customGroups = null,
        array $yOverrides = []
    ): array {
        $normalizedFull = $this->normalizeToLatin($rawName);
        if ($normalizedFull === '') {
            throw new InvalidArgumentException('Họ tên không chứa chữ cái Latin hợp lệ để tính chỉ số.');
        }

        $yRule = $yMode === self::Y_MODE_EN ? self::Y_MODE_EN : self::Y_MODE_VN;
        $groups = $this->resolveGroups($normalizedFull, $customGroups);

        $letterIndex = 0;
        $analyzedGroups = [];
        $allLetters = [];
        $rawAllSum = 0;
        $rawVowelSum = 0;
        $rawConsonantSum = 0;
        $yPositions = [];

        foreach ($groups as $group) {
            $words = array_values(array_filter(explode(' ', $group['normalized'])));
            $groupLetters = [];
            $groupAllSum = 0;
            $groupVowelSum = 0;
            $groupConsonantSum = 0;

            foreach ($words as $word) {
                $chars = str_split($word);
                $len = count($chars);
                for ($i = 0; $i < $len; $i++) {
                    $ch = $chars[$i];
                    $val = NumerologyMethodRegistry::PYTHAGOREAN_MAP[$ch] ?? 0;
                    if ($val === 0) {
                        continue;
                    }

                    $isVowel = $this->classifyLetterIsVowel($chars, $i, $letterIndex, $yRule, $yOverrides);
                    $letterRecord = [
                        'index' => $letterIndex,
                        'char' => $ch,
                        'word' => $word,
                        'group_key' => $group['key'],
                        'value' => $val,
                        'type' => $isVowel ? 'vowel' : 'consonant',
                        'is_y' => $ch === 'Y',
                    ];

                    if ($ch === 'Y') {
                        $yPositions[] = $letterRecord;
                    }

                    $groupLetters[] = $letterRecord;
                    $allLetters[] = $letterRecord;
                    $groupAllSum += $val;
                    $rawAllSum += $val;
                    if ($isVowel) {
                        $groupVowelSum += $val;
                        $rawVowelSum += $val;
                    } else {
                        $groupConsonantSum += $val;
                        $rawConsonantSum += $val;
                    }

                    $letterIndex++;
                }
            }

            if ($groupLetters !== []) {
                $analyzedGroups[] = [
                    'key' => $group['key'],
                    'label' => $group['label'],
                    'text' => $group['normalized'],
                    'words' => $words,
                    'letters' => $groupLetters,
                    'raw_all_sum' => $groupAllSum,
                    'raw_vowel_sum' => $groupVowelSum,
                    'raw_consonant_sum' => $groupConsonantSum,
                ];
            }
        }

        $canonicalOriginal = class_exists(Normalizer::class)
            ? (Normalizer::normalize(trim($rawName), Normalizer::FORM_C) ?: trim($rawName))
            : trim($rawName);

        return [
            'original_name' => $canonicalOriginal,
            'normalized_name' => $normalizedFull,
            'y_mode' => $yRule,
            'y_mode_label' => $yRule === self::Y_MODE_VN
                ? 'Ngữ âm tiếng Việt (Y là nguyên âm trong Yến, Vy, Mỹ, Duy, Nguyễn, Quỳnh)'
                : 'Ngữ âm tiếng Anh (Y là phụ âm khi mở đầu âm tiết trước nguyên âm)',
            'groups' => $analyzedGroups,
            'letters' => $allLetters,
            'y_positions' => $yPositions,
            'raw_all_sum' => $rawAllSum,
            'raw_vowel_sum' => $rawVowelSum,
            'raw_consonant_sum' => $rawConsonantSum,
            'integrity_ok' => ($rawVowelSum + $rawConsonantSum) === $rawAllSum,
            'first_name_group' => $this->extractPrimaryGivenNameGroup($analyzedGroups),
        ];
    }

    /**
     * @param  array{last?: string, middle?: string, first?: string}|null  $customGroups
     * @return list<array{key: string, label: string, normalized: string}>
     */
    protected function resolveGroups(string $normalizedFull, ?array $customGroups): array
    {
        if (is_array($customGroups)) {
            $built = [];
            $defs = [
                'last' => 'Họ',
                'middle' => 'Tên đệm',
                'first' => 'Tên chính',
            ];
            foreach ($defs as $k => $label) {
                $val = $this->normalizeToLatin((string) ($customGroups[$k] ?? ''));
                if ($val !== '') {
                    $built[] = ['key' => $k, 'label' => $label, 'normalized' => $val];
                }
            }
            if ($built !== []) {
                return $built;
            }
        }

        $words = array_values(array_filter(explode(' ', $normalizedFull)));
        $count = count($words);
        if ($count === 1) {
            return [['key' => 'first', 'label' => 'Tên chính', 'normalized' => $words[0]]];
        }
        if ($count === 2) {
            return [
                ['key' => 'last', 'label' => 'Họ', 'normalized' => $words[0]],
                ['key' => 'first', 'label' => 'Tên chính', 'normalized' => $words[1]],
            ];
        }

        return [
            ['key' => 'last', 'label' => 'Họ', 'normalized' => $words[0]],
            ['key' => 'middle', 'label' => 'Tên đệm', 'normalized' => implode(' ', array_slice($words, 1, $count - 2))],
            ['key' => 'first', 'label' => 'Tên chính', 'normalized' => $words[$count - 1]],
        ];
    }

    /**
     * @param  list<string>  $wordChars
     * @param  array<int, string>  $yOverrides
     */
    protected function classifyLetterIsVowel(
        array $wordChars,
        int $posInWord,
        int $globalIndex,
        string $yRule,
        array $yOverrides
    ): bool {
        $ch = $wordChars[$posInWord];
        if (in_array($ch, self::BASE_VOWELS, true)) {
            return true;
        }
        if ($ch !== 'Y') {
            return false;
        }

        // Cho phép người dùng kiểm soát thủ công từng chữ Y
        if (isset($yOverrides[$globalIndex])) {
            return $yOverrides[$globalIndex] === 'vowel';
        }

        // Quy ước bản địa hóa tiếng Việt: Y đóng vai trò nguyên âm trong tiếng Việt (Yến, Vy, Mỹ, Duy, Nguyễn, Quỳnh...)
        if ($yRule === self::Y_MODE_VN) {
            return true;
        }

        // Quy ước tiếng Anh / quốc tế: Y ở đầu từ đi liền trước nguyên âm A/E/I/O/U (Yuri, Yasmine, York) là phụ âm
        $next = $wordChars[$posInWord + 1] ?? null;
        if ($posInWord === 0 && $next !== null && in_array($next, self::BASE_VOWELS, true)) {
            return false;
        }

        return true;
    }

    /**
     * @param  list<array<string, mixed>>  $groups
     * @return array<string, mixed>|null
     */
    protected function extractPrimaryGivenNameGroup(array $groups): ?array
    {
        foreach ($groups as $g) {
            if ($g['key'] === 'first') {
                return $g;
            }
        }

        return $groups[count($groups) - 1] ?? null;
    }
}
