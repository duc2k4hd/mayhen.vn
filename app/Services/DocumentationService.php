<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DocumentationService
{
    protected string $basePath;

    public function __construct()
    {
        $this->basePath = resource_path('docs');
    }

    /**
     * Lấy toàn bộ danh mục chuyên mục và bài viết theo space (client hoặc admin).
     *
     * @return array<string, array{name: string, slug: string, order: int, articles: array<int, array<string, mixed>>}>
     */
    public function getCategories(string $space = 'client'): array
    {
        $space = $this->sanitizeSpace($space);

        if (app()->isLocal()) {
            return $this->loadCategories($space);
        }

        $cacheKey = "docs_categories_{$space}";

        return Cache::remember($cacheKey, 3600, function () use ($space) {
            return $this->loadCategories($space);
        });
    }

    /**
     * Tải và phân giải danh mục và bài viết từ thư mục tài liệu.
     *
     * @return array<string, array{name: string, slug: string, order: int, articles: array<int, array<string, mixed>>}>
     */
    protected function loadCategories(string $space): array
    {
        $spaceDir = $this->basePath.DIRECTORY_SEPARATOR.$space;
        if (! is_dir($spaceDir)) {
            return [];
        }

        $categories = [];
        $dirs = glob($spaceDir.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR) ?: [];

        foreach ($dirs as $dir) {
            $dirName = basename($dir);
            $parts = explode('-', $dirName, 2);
            $order = isset($parts[0]) && is_numeric($parts[0]) ? (int) $parts[0] : 99;
            $catSlug = $parts[1] ?? $dirName;

            $articles = [];
            $files = glob($dir.DIRECTORY_SEPARATOR.'*.md') ?: [];

            foreach ($files as $file) {
                $parsed = $this->parseFile($file);
                if ($parsed !== null) {
                    $parsed['url'] = $this->buildArticleUrl($space, $catSlug, $parsed['id']);
                    $parsed['slug'] = $parsed['id'];
                    $articles[] = $parsed;
                }
            }

            // Sắp xếp bài viết theo order
            usort($articles, fn ($a, $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));

            $catName = ! empty($articles) && ! empty($articles[0]['category'])
                ? $articles[0]['category']
                : Str::headline($catSlug);

            $categories[$catSlug] = [
                'name' => $catName,
                'slug' => $catSlug,
                'order' => $order,
                'articles' => $articles,
            ];
        }

        uasort($categories, fn ($a, $b) => $a['order'] <=> $b['order']);

        return $categories;
    }

    /**
     * Lấy chi tiết một bài viết theo ID hoặc Slug trong space.
     *
     * @return array<string, mixed>|null
     */
    public function getArticle(string $space, string $category, string $slug): ?array
    {
        $space = $this->sanitizeSpace($space);
        $categories = $this->getCategories($space);

        if (! isset($categories[$category])) {
            return null;
        }

        foreach ($categories[$category]['articles'] as $art) {
            if ($art['id'] === $slug || Str::slug($art['id']) === $slug || Str::slug($art['title']) === $slug) {
                return $art;
            }
        }

        return null;
    }

    /**
     * Tìm kiếm bài viết theo từ khóa (tiếng Việt có dấu và không dấu).
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $space, string $keyword): array
    {
        $space = $this->sanitizeSpace($space);
        $keyword = trim($keyword);
        if ($keyword === '') {
            return [];
        }

        $normalizedKw = $this->removeVietnameseAccents(mb_strtolower($keyword));
        $categories = $this->getCategories($space);
        $results = [];

        foreach ($categories as $cat) {
            foreach ($cat['articles'] as $art) {
                $titleNorm = $this->removeVietnameseAccents(mb_strtolower($art['title'] ?? ''));
                $summaryNorm = $this->removeVietnameseAccents(mb_strtolower($art['summary'] ?? ''));
                $tagsNorm = $this->removeVietnameseAccents(mb_strtolower(implode(' ', $art['tags'] ?? [])));
                $contentNorm = $this->removeVietnameseAccents(mb_strtolower($art['raw_body'] ?? ''));

                $score = 0;
                if (str_contains($titleNorm, $normalizedKw)) {
                    $score += 50;
                }
                if (str_contains($tagsNorm, $normalizedKw)) {
                    $score += 30;
                }
                if (str_contains($summaryNorm, $normalizedKw)) {
                    $score += 20;
                }
                if (str_contains($contentNorm, $normalizedKw)) {
                    $score += 10;
                }

                if ($score > 0) {
                    $results[] = [
                        'id' => $art['id'],
                        'title' => $art['title'],
                        'category' => $cat['name'],
                        'summary' => $art['summary'] ?? '',
                        'url' => $art['url'],
                        'score' => $score,
                    ];
                }
            }
        }

        usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($results, 0, 15);
    }

    /**
     * Phân giải tệp Markdown chứa YAML Frontmatter.
     *
     * @return array<string, mixed>|null
     */
    protected function parseFile(string $filePath): ?array
    {
        if (! file_exists($filePath)) {
            return null;
        }

        $raw = file_get_contents($filePath);
        if ($raw === false) {
            return null;
        }

        $meta = [];
        $body = $raw;

        // Tách Frontmatter nếu có dạng ---\n...\n---
        if (preg_match('/^---\r?\n(.*?)\r?\n---\r?\n(.*)$/s', $raw, $matches)) {
            $frontmatter = $matches[1];
            $body = $matches[2];

            foreach (explode("\n", $frontmatter) as $line) {
                $line = trim($line);
                if (empty($line) || str_starts_with($line, '#')) {
                    continue;
                }
                if (str_contains($line, ':')) {
                    [$key, $val] = explode(':', $line, 2);
                    $key = trim($key);
                    $val = trim($val);

                    // Xử lý mảng [tag1, tag2]
                    if (str_starts_with($val, '[') && str_ends_with($val, ']')) {
                        $items = explode(',', trim($val, '[]'));
                        $meta[$key] = array_map('trim', $items);
                    } else {
                        $meta[$key] = trim($val, '"\'');
                    }
                }
            }
        }

        $title = $meta['title'] ?? basename($filePath, '.md');
        $id = $meta['id'] ?? basename($filePath, '.md');

        // Phân giải Markdown sang HTML an toàn và tạo TOC
        $toc = [];
        $html = $this->renderMarkdown($body, $toc);

        return [
            'id' => $id,
            'slug' => $id,
            'title' => $title,
            'category' => $meta['category'] ?? '',
            'summary' => $meta['summary'] ?? '',
            'reader' => $meta['reader'] ?? '',
            'order' => isset($meta['order']) ? (int) $meta['order'] : 99,
            'updated_at' => $meta['updated_at'] ?? date('Y-m-d'),
            'tags' => $meta['tags'] ?? [],
            'mapped_page' => $meta['mapped_page'] ?? '',
            'mapped_route' => $meta['mapped_route'] ?? '',
            'risk_level' => $meta['risk_level'] ?? 'LOW',
            'required_role' => $meta['required_role'] ?? 'all',
            'html_body' => $html,
            'raw_body' => $body,
            'toc' => $toc,
        ];
    }

    /**
     * Chuyển đổi Markdown cơ bản sang HTML ngữ nghĩa an toàn và sinh TOC.
     *
     * @param  array<int, array{id: string, text: string, level: int}>  $toc
     */
    protected function renderMarkdown(string $md, array &$toc): string
    {
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $md));
        $count = count($lines);
        $html = '';
        $inList = false;
        $inOrderedList = false;
        $inCodeBlock = false;
        $codeLang = '';

        for ($i = 0; $i < $count; $i++) {
            $line = $lines[$i];
            $trimmed = trim($line);

            // Xử lý Code block ```
            if (str_starts_with($trimmed, '```')) {
                if ($inCodeBlock) {
                    $html .= "</code></pre>\n";
                    $inCodeBlock = false;
                } else {
                    if ($inList) {
                        $html .= "</ul>\n";
                        $inList = false;
                    }
                    if ($inOrderedList) {
                        $html .= "</ol>\n";
                        $inOrderedList = false;
                    }
                    $codeLang = htmlspecialchars(trim(substr($trimmed, 3)));
                    $html .= "<pre class=\"mayhen_docs_code\"><code class=\"language-{$codeLang}\">";
                    $inCodeBlock = true;
                }

                continue;
            }

            if ($inCodeBlock) {
                $html .= htmlspecialchars($line)."\n";

                continue;
            }

            // Đóng danh sách nếu dòng trống
            if ($trimmed === '') {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                if ($inOrderedList) {
                    $html .= "</ol>\n";
                    $inOrderedList = false;
                }

                continue;
            }

            // Xử lý Thước kẻ phân cách ngang (--- hoặc ***)
            if (preg_match('/^(?:-{3,}|\*{3,})$/', $trimmed)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                if ($inOrderedList) {
                    $html .= "</ol>\n";
                    $inOrderedList = false;
                }

                $html .= "<hr class=\"mayhen_docs_hr\">\n";

                continue;
            }

            // Xử lý Headings
            if (preg_match('/^(#{1,6})\s+(.*)$/', $trimmed, $hm)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                if ($inOrderedList) {
                    $html .= "</ol>\n";
                    $inOrderedList = false;
                }

                $level = strlen($hm[1]);
                $headingText = trim($hm[2]);
                $headingId = Str::slug($headingText);

                // Tiêu đề H1 đã được hiển thị trang trọng tại mayhen_docs_detail_article_header trong Blade layout,
                // loại bỏ H1 trong body để tránh thừa thẻ h1 và tuân thủ chuẩn ngữ nghĩa SEO (chỉ có duy nhất 1 thẻ H1 trên trang).
                if ($level === 1) {
                    continue;
                }

                // Ghi vào TOC cho H2 và H3
                if ($level === 2 || $level === 3) {
                    $toc[] = [
                        'id' => $headingId,
                        'text' => $headingText,
                        'level' => $level,
                    ];
                }

                $inlineHtml = $this->renderInlineMarkdown($headingText);
                $html .= "<h{$level} id=\"{$headingId}\" class=\"mayhen_docs_h{$level}\"><span>{$inlineHtml}</span> <a href=\"#{$headingId}\" class=\"mayhen_docs_anchor\" aria-hidden=\"true\">#</a></h{$level}>\n";

                continue;
            }

            // Xử lý Callouts / Blockquotes liên tiếp: gom tất cả các dòng bắt đầu bằng `>`
            if (str_starts_with($trimmed, '>')) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                if ($inOrderedList) {
                    $html .= "</ol>\n";
                    $inOrderedList = false;
                }

                $quoteLines = [];
                while ($i < $count && str_starts_with(trim($lines[$i]), '>')) {
                    $quoteLines[] = trim(substr(trim($lines[$i]), 1));
                    $i++;
                }
                $i--; // Lùi 1 bước vì vòng for sẽ tăng $i

                $firstLine = $quoteLines[0] ?? '';
                if (preg_match('/^\[!(NOTE|TIP|WARNING|CAUTION|IMPORTANT)\]\s*(.*)$/i', $firstLine, $cm)) {
                    $tag = strtoupper($cm[1]);
                    $restOfFirstLine = trim($cm[2]);
                    if ($restOfFirstLine !== '') {
                        $quoteLines[0] = $restOfFirstLine;
                    } else {
                        array_shift($quoteLines);
                    }

                    switch ($tag) {
                        case 'TIP':
                            $calloutClass = 'mayhen_docs_callout_tip';
                            $badgeTitle = 'MẸO NHỎ';
                            $iconSvg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>';
                            break;
                        case 'WARNING':
                            $calloutClass = 'mayhen_docs_callout_warning';
                            $badgeTitle = 'CẢNH BÁO';
                            $iconSvg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
                            break;
                        case 'CAUTION':
                            $calloutClass = 'mayhen_docs_callout_danger';
                            $badgeTitle = 'NGUY HIỂM / BẢO MẬT';
                            $iconSvg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
                            break;
                        case 'IMPORTANT':
                            $calloutClass = 'mayhen_docs_callout_important';
                            $badgeTitle = 'QUAN TRỌNG';
                            $iconSvg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
                            break;
                        case 'NOTE':
                        default:
                            $calloutClass = 'mayhen_docs_callout_note';
                            $badgeTitle = 'LƯU Ý';
                            $iconSvg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';
                            break;
                    }

                    $quoteHtml = '';
                    foreach ($quoteLines as $ql) {
                        if ($ql === '') {
                            $quoteHtml .= "<br>\n";
                        } else {
                            $quoteHtml .= '<p class="mayhen_docs_callout_p">'.$this->renderInlineMarkdown($ql)."</p>\n";
                        }
                    }

                    $html .= "<div class=\"mayhen_docs_callout {$calloutClass}\">\n";
                    $html .= "  <div class=\"mayhen_docs_callout_header\">\n";
                    $html .= "    <span class=\"mayhen_docs_callout_icon\" aria-hidden=\"true\">{$iconSvg}</span>\n";
                    $html .= "    <span class=\"mayhen_docs_callout_badge\">{$badgeTitle}</span>\n";
                    $html .= "  </div>\n";
                    $html .= "  <div class=\"mayhen_docs_callout_text\">\n{$quoteHtml}  </div>\n";
                    $html .= "</div>\n";
                } else {
                    $quoteHtml = '';
                    foreach ($quoteLines as $ql) {
                        if ($ql === '') {
                            $quoteHtml .= "<br>\n";
                        } else {
                            $quoteHtml .= '<p>'.$this->renderInlineMarkdown($ql)."</p>\n";
                        }
                    }
                    $html .= "<blockquote class=\"mayhen_docs_blockquote\">\n{$quoteHtml}</blockquote>\n";
                }

                continue;
            }

            // Xử lý Danh sách không thứ tự (* hoặc -)
            if (preg_match('/^[\*\-]\s+(.*)$/', $trimmed, $lm)) {
                if ($inOrderedList) {
                    $html .= "</ol>\n";
                    $inOrderedList = false;
                }
                if (! $inList) {
                    $html .= "<ul class=\"mayhen_docs_list\">\n";
                    $inList = true;
                }
                $inlineHtml = $this->renderInlineMarkdown($lm[1]);
                $html .= "  <li>{$inlineHtml}</li>\n";

                continue;
            }

            // Xử lý Danh sách có thứ tự (1. 2. 3.)
            if (preg_match('/^(\d+)\.\s+(.*)$/', $trimmed, $olm)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                if (! $inOrderedList) {
                    $html .= "<ol class=\"mayhen_docs_ordered_list\">\n";
                    $inOrderedList = true;
                }
                $inlineHtml = $this->renderInlineMarkdown($olm[2]);
                $html .= "  <li>{$inlineHtml}</li>\n";

                continue;
            }

            // Đóng danh sách nếu đang mở
            if ($inList) {
                $html .= "</ul>\n";
                $inList = false;
            }
            if ($inOrderedList) {
                $html .= "</ol>\n";
                $inOrderedList = false;
            }

            // Xử lý Hình ảnh đứng riêng: ![alt](url)
            if (preg_match('/^!\[([^\]]*)\]\(([^)]+)\)$/', $trimmed, $imgM)) {
                $alt = htmlspecialchars(trim($imgM[1]), ENT_QUOTES, 'UTF-8');
                $url = trim($imgM[2]);
                $isSafeUrl = str_starts_with($url, '/assets/') || str_starts_with($url, 'https://') || str_starts_with($url, 'http://');

                if ($isSafeUrl && ! preg_match('/javascript:|data:|vbscript:/i', $url)) {
                    $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                    $html .= "<figure class=\"mayhen_docs_figure\"><img src=\"{$escapedUrl}\" alt=\"{$alt}\" class=\"mayhen_docs_img\" loading=\"lazy\"></figure>\n";
                }

                continue;
            }

            // Đoạn văn thông thường
            $inlineHtml = $this->renderInlineMarkdown($trimmed);
            $html .= "<p class=\"mayhen_docs_p\">{$inlineHtml}</p>\n";
        }

        if ($inList) {
            $html .= "</ul>\n";
        }
        if ($inOrderedList) {
            $html .= "</ol>\n";
        }
        if ($inCodeBlock) {
            $html .= "</code></pre>\n";
        }

        return $html;
    }

    /**
     * Phân giải định dạng văn bản nội dòng (Bold, Italic, Link, Inline code, Image).
     */
    protected function renderInlineMarkdown(string $text): string
    {
        // Escape HTML ban đầu để chống XSS
        $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');

        // Inline code `code`
        $text = preg_replace('/`([^`]+)`/', '<code class="mayhen_docs_inline_code">$1</code>', $text);

        // Bold **text**
        $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);

        // Italic *text*
        $text = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $text);

        // Images ![alt](url) - Bắt buộc xử lý TRƯỚC Links [text](url) để tránh nuốt cú pháp ảnh
        $text = preg_replace_callback('/!\[([^\]]*)\]\(([^)]+)\)/', function ($m) {
            $alt = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
            $url = $m[2];
            $isSafeUrl = str_starts_with($url, '/assets/') || str_starts_with($url, 'https://') || str_starts_with($url, 'http://');

            if (! $isSafeUrl || preg_match('/javascript:|data:|vbscript:/i', $url)) {
                return '';
            }

            $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

            return "<img src=\"{$escapedUrl}\" alt=\"{$alt}\" class=\"mayhen_docs_img\" loading=\"lazy\">";
        }, $text);

        // Links [text](url)
        $text = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function ($m) {
            $label = $m[1];
            $url = $m[2];
            $isExternal = str_starts_with($url, 'http://') || str_starts_with($url, 'https://');
            $rel = $isExternal ? ' rel="noopener noreferrer" target="_blank"' : '';

            return "<a href=\"{$url}\" class=\"mayhen_docs_link\"{$rel}>{$label}</a>";
        }, $text);

        return $text;
    }

    /**
     * Loại bỏ dấu tiếng Việt để tìm kiếm không dấu chính xác.
     */
    public function removeVietnameseAccents(string $str): string
    {
        $accents = [
            'a' => ['à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ'],
            'e' => ['è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ'],
            'i' => ['ì', 'í', 'ị', 'ỉ', 'ĩ'],
            'o' => ['ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ'],
            'u' => ['ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ'],
            'y' => ['ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ'],
            'd' => ['đ'],
            'A' => ['À', 'Á', 'Ạ', 'Ả', 'Ã', 'Â', 'Ầ', 'Ấ', 'Ậ', 'Ẩ', 'Ẫ', 'Ă', 'Ằ', 'Ắ', 'Ặ', 'Ẳ', 'Ẵ'],
            'E' => ['È', 'É', 'Ẹ', 'Ẻ', 'Ẽ', 'Ê', 'Ề', 'Ế', 'Ệ', 'Ể', 'Ễ'],
            'I' => ['Ì', 'Í', 'Ị', 'Ỉ', 'Ĩ'],
            'O' => ['Ò', 'Ó', 'Ọ', 'Ỏ', 'Õ', 'Ô', 'Ồ', 'Ố', 'Ộ', 'Ổ', 'Ỗ', 'Ơ', 'Ờ', 'Ớ', 'Ợ', 'Ở', 'Ỡ'],
            'U' => ['Ù', 'Ú', 'Ụ', 'Ủ', 'Ũ', 'Ư', 'Ừ', 'Ứ', 'Ự', 'Ử', 'Ữ'],
            'Y' => ['Ỳ', 'Ý', 'Ỵ', 'Ỷ', 'Ỹ'],
            'D' => ['Đ'],
        ];

        foreach ($accents as $nonAccent => $accentList) {
            $str = str_replace($accentList, $nonAccent, $str);
        }

        return $str;
    }

    protected function sanitizeSpace(string $space): string
    {
        return $space === 'admin' ? 'admin' : 'client';
    }

    protected function buildArticleUrl(string $space, string $catSlug, string $articleId): string
    {
        if ($space === 'admin') {
            return route('admin.docs.show', ['category' => $catSlug, 'slug' => $articleId]);
        }

        return route('docs.show', ['category' => $catSlug, 'slug' => $articleId]);
    }
}
