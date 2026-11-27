<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

/**
 * Chuyển đổi Markdown nhẹ từ AI / Knowledge Engine sang HTML sạch & chống XSS tuyệt đối (Zero-Trust).
 *
 * Nguyên tắc bảo mật:
 * - Mọi ký tự HTML thô (<script>, <img onerror=...>, &...) đều được escape trước khi sinh thẻ HTML an toàn.
 * - Chỉ cho phép đường dẫn nội bộ tương đối (/...) hoặc tên miền mayhen.vn, chặn triệt để javascript:/data:.
 * - Giữ định dạng biên tập Quiet Luxury: đoạn văn, tiêu đề h4/h5, danh sách ul/ol, dòng phụ chú (↳), trích dẫn & mã inline.
 */
final class SafeAiMarkdownFormatter
{
    private const MAX_INLINE_DEPTH = 3;

    /**
     * Chuyển đổi toàn bộ văn bản Markdown sang HTML có cấu trúc khối (<p>, <h4>, <h5>, <ul>, <ol>, <blockquote>, <pre>).
     */
    public static function toHtml(?string $rawText): string
    {
        $normalized = self::normalize((string) ($rawText ?? ''));
        if ($normalized === '') {
            return '';
        }

        $lines = explode("\n", $normalized);
        $lineCount = count($lines);
        $blocks = [];
        $i = 0;

        while ($i < $lineCount) {
            $trimmed = trim($lines[$i]);

            if ($trimmed === '') {
                $i++;

                continue;
            }

            // 1. Code fence ```...```
            if (preg_match('/^```/u', $trimmed) === 1) {
                $i++;
                $codeLines = [];
                while ($i < $lineCount && preg_match('/^```\s*$/u', trim($lines[$i])) !== 1) {
                    $codeLines[] = $lines[$i];
                    $i++;
                }
                if ($i < $lineCount) {
                    $i++;
                }
                $blocks[] = '<pre class="mayhen_md_pre"><code>'.self::escape(implode("\n", $codeLines)).'</code></pre>';

                continue;
            }

            // 2. Đường kẻ phân tách --- / *** / ___
            if (preg_match('/^(?:[-*_]){3,}\s*$/u', $trimmed) === 1) {
                $blocks[] = '<hr class="mayhen_md_hr">';
                $i++;

                continue;
            }

            // 3. Tiêu đề #, ##, ### -> h4 | ####, #####, ###### -> h5
            if (preg_match('/^(#{1,6})\s+(.+)$/u', $trimmed, $headingMatch) === 1) {
                $level = strlen($headingMatch[1]);
                $tag = $level <= 3 ? 'h4' : 'h5';
                $cls = $level <= 3 ? 'mayhen_md_heading' : 'mayhen_md_subheading';
                $blocks[] = "<{$tag} class=\"{$cls}\">".self::parseInline(trim($headingMatch[2]))."</{$tag}>";
                $i++;

                continue;
            }

            // 4. Trích dẫn > ...
            if (preg_match('/^>\s?(.*)$/u', $trimmed, $quoteMatch) === 1) {
                $quoteLines = [trim($quoteMatch[1])];
                $i++;
                while ($i < $lineCount && preg_match('/^>\s?(.*)$/u', trim($lines[$i]), $nextQuote) === 1) {
                    $quoteLines[] = trim($nextQuote[1]);
                    $i++;
                }
                $renderedLines = array_map(
                    static fn (string $line): string => self::parseInline($line),
                    array_values(array_filter($quoteLines, static fn (string $line): bool => $line !== ''))
                );
                if ($renderedLines !== []) {
                    $blocks[] = '<blockquote class="mayhen_md_quote">'.implode('<br>', $renderedLines).'</blockquote>';
                }

                continue;
            }

            // 5. Danh sách không thứ tự (-, *, +, •, ◦, ▪) hoặc có thứ tự (1., 2))
            $isUnordered = self::matchUnorderedListItem($lines[$i]) !== null;
            $isOrdered = ! $isUnordered && self::matchOrderedListItem($lines[$i]) !== null;

            if ($isUnordered || $isOrdered) {
                $itemsHtml = [];
                $listType = $isUnordered ? 'ul' : 'ol';

                while ($i < $lineCount) {
                    $itemText = $isUnordered
                        ? self::matchUnorderedListItem($lines[$i])
                        : self::matchOrderedListItem($lines[$i]);

                    if ($itemText === null) {
                        // Cho phép gộp nếu chỉ cách 1 dòng trống tới mục cùng loại tiếp theo
                        if (
                            trim($lines[$i]) === ''
                            && isset($lines[$i + 1])
                            && (
                                ($isUnordered && self::matchUnorderedListItem($lines[$i + 1]) !== null)
                                || ($isOrdered && self::matchOrderedListItem($lines[$i + 1]) !== null)
                            )
                        ) {
                            $i++;

                            continue;
                        }

                        break;
                    }

                    $i++;
                    $subHtml = '';

                    // Thu nhận các dòng phụ chú "↳ ..." nối ngay dưới gạch đầu dòng
                    while ($i < $lineCount && preg_match('/^[ \t]*↳\s*(.+)$/u', $lines[$i], $subMatch) === 1) {
                        $subHtml .= '<div class="mayhen_md_subline"><span class="mayhen_md_sub_arrow" aria-hidden="true">↳</span><span>'.self::parseInline(trim($subMatch[1])).'</span></div>';
                        $i++;
                    }

                    $itemsHtml[] = '<li>'.self::parseInline(trim($itemText)).$subHtml.'</li>';
                }

                $listClass = $listType === 'ol'
                    ? 'mayhen_md_list mayhen_md_list--ordered'
                    : 'mayhen_md_list';
                $blocks[] = "<{$listType} class=\"{$listClass}\">".implode('', $itemsHtml)."</{$listType}>";

                continue;
            }

            // 6. Dòng phụ chú đứng độc lập "↳ ..."
            if (preg_match('/^[ \t]*↳\s*(.+)$/u', $lines[$i], $standaloneSub) === 1) {
                $blocks[] = '<div class="mayhen_md_subline"><span class="mayhen_md_sub_arrow" aria-hidden="true">↳</span><span>'.self::parseInline(trim($standaloneSub[1])).'</span></div>';
                $i++;

                continue;
            }

            // 7. Đoạn văn thường: gom các dòng liên tiếp không thuộc block đặc biệt
            $paraLines = [];
            while ($i < $lineCount) {
                $curr = $lines[$i];
                $currTrim = trim($curr);
                if (
                    $currTrim === ''
                    || preg_match('/^```/u', $currTrim) === 1
                    || preg_match('/^(?:[-*_]){3,}\s*$/u', $currTrim) === 1
                    || preg_match('/^(#{1,6})\s+(.+)$/u', $currTrim) === 1
                    || preg_match('/^>\s?(.*)$/u', $currTrim) === 1
                    || preg_match('/^[ \t]*↳\s*(.+)$/u', $curr) === 1
                    || self::matchUnorderedListItem($curr) !== null
                    || self::matchOrderedListItem($curr) !== null
                ) {
                    break;
                }
                $paraLines[] = self::parseInline($currTrim);
                $i++;
            }

            if ($paraLines !== []) {
                $blocks[] = '<p class="mayhen_md_p">'.implode('<br>', $paraLines).'</p>';
            }
        }

        return implode('', $blocks);
    }

    /**
     * Chỉ parse định dạng inline (**bold**, *italic*, `code`, link an toàn) cho chuỗi ngắn / tiêu đề / <li>.
     */
    public static function toInlineHtml(?string $rawText): string
    {
        $normalized = self::normalize((string) ($rawText ?? ''));
        if ($normalized === '') {
            return '';
        }

        $lines = explode("\n", $normalized);
        $rendered = array_map(
            static fn (string $line): string => self::parseInline(trim($line)),
            $lines
        );

        return implode('<br>', $rendered);
    }

    private static function matchUnorderedListItem(string $line): ?string
    {
        if (preg_match('/^[ \t]*(?:[-*+•◦▪])\s+(.+)$/u', $line, $m) === 1) {
            return $m[1];
        }

        return null;
    }

    private static function matchOrderedListItem(string $line): ?string
    {
        if (preg_match('/^[ \t]*\d{1,3}[.)]\s+(.+)$/u', $line, $m) === 1) {
            return $m[1];
        }

        return null;
    }

    private static function parseInline(string $text, int $depth = 0): string
    {
        if ($text === '') {
            return '';
        }

        if ($depth >= self::MAX_INLINE_DEPTH) {
            return self::escape($text);
        }

        // Quét trái-sang-phải bằng OFFSET_CAPTURE để bảo toàn HTML escape trên mọi lát cắt ngoài token
        $tokenRegex = '/`([^`\n]+)`|\[([^\]\n]+)\]\(([^()\s]+(?:\([^()\s]*\)[^()\s]*)*)\)|\*\*\*([^\s*\n](?:[^\n]*?[^\s*\n])?)\*\*\*|\*\*([^\s*\n](?:[^\n]*?[^\s*\n])?)\*\*|__([^\s_\n](?:[^\n]*?[^\s_\n])?)__|(?<![\p{L}\p{N}])\*(?!\s)([^*\n]+?)(?<!\s)\*(?!\*)(?![\p{L}\p{N}])/u';

        if (preg_match_all($tokenRegex, $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) === false || $matches === []) {
            return self::escape($text);
        }

        $out = '';
        $cursor = 0;

        foreach ($matches as $m) {
            $fullMatch = $m[0][0];
            $offset = (int) $m[0][1];

            if ($offset > $cursor) {
                $out .= self::escape(substr($text, $cursor, $offset - $cursor));
            }

            if (isset($m[1][0]) && $m[1][1] !== -1 && $m[1][0] !== '') {
                $out .= '<code class="mayhen_md_code">'.self::escape($m[1][0]).'</code>';
            } elseif (isset($m[2][0]) && $m[2][1] !== -1 && isset($m[3][0]) && $m[3][1] !== -1) {
                $label = trim($m[2][0]);
                $href = trim($m[3][0]);
                if (self::isSafeUrl($href)) {
                    $out .= '<a class="mayhen_md_link" href="'.self::escape($href).'" target="_blank" rel="noopener noreferrer">'.self::parseInline($label, $depth + 1).'</a>';
                } else {
                    $out .= self::parseInline($label, $depth + 1);
                }
            } elseif (isset($m[4][0]) && $m[4][1] !== -1 && $m[4][0] !== '') {
                $out .= '<strong><em>'.self::parseInline($m[4][0], $depth + 1).'</em></strong>';
            } elseif (isset($m[5][0]) && $m[5][1] !== -1 && $m[5][0] !== '') {
                $out .= '<strong>'.self::parseInline($m[5][0], $depth + 1).'</strong>';
            } elseif (isset($m[6][0]) && $m[6][1] !== -1 && $m[6][0] !== '') {
                $out .= '<strong>'.self::parseInline($m[6][0], $depth + 1).'</strong>';
            } elseif (isset($m[7][0]) && $m[7][1] !== -1 && $m[7][0] !== '') {
                $out .= '<em>'.self::parseInline($m[7][0], $depth + 1).'</em>';
            } else {
                $out .= self::escape($fullMatch);
            }

            $cursor = $offset + strlen($fullMatch);
        }

        if ($cursor < strlen($text)) {
            $out .= self::escape(substr($text, $cursor));
        }

        return $out;
    }

    private static function isSafeUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        // Cho phép đường dẫn nội bộ bắt đầu bằng "/" (loại trừ protocol-relative "//" hoặc "/\")
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//') && ! str_starts_with($url, '/\\')) {
            return preg_match('/^\/[a-zA-Z0-9\-_\/?=&%#.]*$/', $url) === 1;
        }

        return preg_match('#^https://(?:www\.)?mayhen\.vn(?:/|$|\?|\#)#i', $url) === 1;
    }

    private static function normalize(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        return trim($text);
    }

    private static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
