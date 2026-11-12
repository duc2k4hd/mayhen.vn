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
        $cacheKey = "docs_categories_{$space}";

        return Cache::remember($cacheKey, 3600, function () use ($space) {
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
        });
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
        $html = '';
        $inList = false;
        $inOrderedList = false;
        $inCodeBlock = false;
        $codeLang = '';

        foreach ($lines as $line) {
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

                // Ghi vào TOC cho H2 và H3
                if ($level === 2 || $level === 3) {
                    $toc[] = [
                        'id' => $headingId,
                        'text' => $headingText,
                        'level' => $level,
                    ];
                }

                $inlineHtml = $this->renderInlineMarkdown($headingText);
                $html .= "<h{$level} id=\"{$headingId}\" class=\"mayhen_docs_h{$level}\">{$inlineHtml} <a href=\"#{$headingId}\" class=\"mayhen_docs_anchor\" aria-hidden=\"true\">#</a></h{$level}>\n";

                continue;
            }

            // Xử lý Callouts / Blockquotes > [!TIP], > [!NOTE], > [!WARNING], > [!CAUTION]
            if (str_starts_with($trimmed, '>')) {
                $quoteContent = trim(substr($trimmed, 1));
                $calloutClass = 'mayhen_docs_callout_note';
                $calloutTitle = 'LƯU Ý';

                if (str_starts_with($quoteContent, '[!TIP]')) {
                    $calloutClass = 'mayhen_docs_callout_tip';
                    $calloutTitle = 'MẸO NHỎ';
                    $quoteContent = trim(substr($quoteContent, 6));
                } elseif (str_starts_with($quoteContent, '[!NOTE]')) {
                    $calloutClass = 'mayhen_docs_callout_note';
                    $calloutTitle = 'LƯU Ý';
                    $quoteContent = trim(substr($quoteContent, 7));
                } elseif (str_starts_with($quoteContent, '[!WARNING]')) {
                    $calloutClass = 'mayhen_docs_callout_warning';
                    $calloutTitle = 'CẢNH BÁO';
                    $quoteContent = trim(substr($quoteContent, 10));
                } elseif (str_starts_with($quoteContent, '[!CAUTION]')) {
                    $calloutClass = 'mayhen_docs_callout_danger';
                    $calloutTitle = 'NGUY HIỂM / BẢO MẬT';
                    $quoteContent = trim(substr($quoteContent, 10));
                }

                $inlineHtml = $this->renderInlineMarkdown($quoteContent);
                $html .= "<div class=\"mayhen_docs_callout {$calloutClass}\"><div class=\"mayhen_docs_callout_badge\">{$calloutTitle}</div><div class=\"mayhen_docs_callout_text\">{$inlineHtml}</div></div>\n";

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
