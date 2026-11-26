<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\NewsPost;
use App\Models\PlatformSetting;
use App\Services\Tarot\TarotDeckRegistry;
use Illuminate\Support\Facades\File;

class SitemapGeneratorService
{
    public const SETTING_INCLUDE_PAGES = 'seo_sitemap_include_pages';

    public const SETTING_INCLUDE_BLOG = 'seo_sitemap_include_blog';

    public const SETTING_INCLUDE_NEWS = 'seo_sitemap_include_news';

    public const SETTING_INCLUDE_DOCS = 'seo_sitemap_include_docs';

    public function __construct(
        protected DocumentationService $docsService
    ) {}

    /**
     * Đọc cấu hình bật/tắt từng nhóm Sitemap từ Cài đặt Admin (mặc định bật tất cả).
     *
     * @return array{pages: bool, blog: bool, news: bool, docs: bool}
     */
    public static function getSettings(): array
    {
        return [
            'pages' => self::readBoolSetting(self::SETTING_INCLUDE_PAGES, true),
            'blog' => self::readBoolSetting(self::SETTING_INCLUDE_BLOG, true),
            'news' => self::readBoolSetting(self::SETTING_INCLUDE_NEWS, true),
            'docs' => self::readBoolSetting(self::SETTING_INCLUDE_DOCS, true),
        ];
    }

    /**
     * Lưu cấu hình cài đặt Sitemap từ Admin.
     *
     * @param  array{pages: bool, blog: bool, news: bool, docs: bool}  $settings
     */
    public static function saveSettings(array $settings): void
    {
        $map = [
            self::SETTING_INCLUDE_PAGES => [
                'value' => $settings['pages'] ? '1' : '0',
                'label' => 'Sitemap Trang Chính & Thiệp Sự Kiện',
            ],
            self::SETTING_INCLUDE_BLOG => [
                'value' => $settings['blog'] ? '1' : '0',
                'label' => 'Sitemap Bài Viết Cẩm Nang (Blog)',
            ],
            self::SETTING_INCLUDE_NEWS => [
                'value' => $settings['news'] ? '1' : '0',
                'label' => 'Sitemap Tin Tức & Ra Mắt Mẫu Thiệp',
            ],
            self::SETTING_INCLUDE_DOCS => [
                'value' => $settings['docs'] ? '1' : '0',
                'label' => 'Sitemap Trung Tâm Hướng Dẫn (Docs)',
            ],
        ];

        foreach ($map as $key => $meta) {
            PlatformSetting::updateOrCreate(
                [
                    'group' => PlatformSetting::GROUP_BRAND_SEO,
                    'key' => $key,
                ],
                [
                    'value' => $meta['value'],
                    'label' => $meta['label'],
                    'is_secret' => false,
                    'updated_by' => auth()->id(),
                ]
            );
        }

        PlatformSetting::clearRuntimeCache();
    }

    /**
     * Xuất toàn bộ các tệp XML Sitemap tĩnh trực tiếp vào thư mục /public để web server phục vụ tức thì.
     *
     * @return array{files: array<string, array{filename: string, url: string, count: int, bytes: int}>, total_urls: int}
     */
    public function generateAndWriteAll(): array
    {
        $settings = self::getSettings();
        $files = [];
        $totalUrls = 0;

        $subConfigs = [
            'pages' => ['filename' => 'sitemap-pages.xml', 'builder' => fn (): array => $this->buildPagesXml()],
            'blog' => ['filename' => 'sitemap-blog.xml', 'builder' => fn (): array => $this->buildBlogXml()],
            'news' => ['filename' => 'sitemap-news.xml', 'builder' => fn (): array => $this->buildNewsXml()],
            'docs' => ['filename' => 'sitemap-docs.xml', 'builder' => fn (): array => $this->buildDocsXml()],
        ];

        foreach ($subConfigs as $key => $cfg) {
            $absPath = public_path($cfg['filename']);
            if (! $settings[$key]) {
                if (File::exists($absPath)) {
                    File::delete($absPath);
                }

                continue;
            }

            $built = ($cfg['builder'])();
            $this->atomicWrite($absPath, $built['xml']);
            $totalUrls += $built['count'];

            $files[$key] = [
                'filename' => $cfg['filename'],
                'url' => url('/'.$cfg['filename']),
                'count' => $built['count'],
                'bytes' => strlen($built['xml']),
            ];
        }

        $indexXml = $this->buildIndexXml($settings);
        $this->atomicWrite(public_path('sitemap.xml'), $indexXml);
        $files['index'] = [
            'filename' => 'sitemap.xml',
            'url' => url('/sitemap.xml'),
            'count' => count($files),
            'bytes' => strlen($indexXml),
        ];

        return [
            'files' => $files,
            'total_urls' => $totalUrls,
        ];
    }

    /**
     * Kiểm tra trạng thái các file .xml vật lý đang có trong /public.
     *
     * @return array<string, array{label: string, filename: string, url: string, exists: bool, size_kb: float, updated_at: ?string}>
     */
    public static function getPublicFilesStatus(): array
    {
        $list = [
            'index' => ['label' => 'Sitemap Index (Tổng hợp)', 'filename' => 'sitemap.xml'],
            'pages' => ['label' => 'Trang chính & 10 Danh mục Thiệp Sự Kiện', 'filename' => 'sitemap-pages.xml'],
            'blog' => ['label' => 'Bài viết Cẩm Nang Cưới (Blog Posts)', 'filename' => 'sitemap-blog.xml'],
            'news' => ['label' => 'Tin Tức & Ra Mắt Mẫu Thiệp (News)', 'filename' => 'sitemap-news.xml'],
            'docs' => ['label' => 'Trung Tâm Hướng Dẫn (Docs)', 'filename' => 'sitemap-docs.xml'],
        ];

        $result = [];
        foreach ($list as $key => $info) {
            $path = public_path($info['filename']);
            $exists = File::exists($path);
            $result[$key] = [
                'label' => $info['label'],
                'filename' => $info['filename'],
                'url' => url('/'.$info['filename']),
                'exists' => $exists,
                'size_kb' => $exists ? round(File::size($path) / 1024, 2) : 0.0,
                'updated_at' => $exists ? date('d/m/Y H:i:s', File::lastModified($path)) : null,
            ];
        }

        return $result;
    }

    /**
     * Xây dựng XML Sitemap Index (/sitemap.xml).
     *
     * @param  array{pages: bool, blog: bool, news: bool, docs: bool}|null  $settings
     */
    public function buildIndexXml(?array $settings = null): string
    {
        $settings ??= self::getSettings();
        $now = now()->toIso8601String();

        $blogLastmod = $now;
        try {
            $latestBlog = BlogPost::published()
                ->select(['updated_at', 'published_at'])
                ->orderByDesc('updated_at')
                ->first();
            if ($latestBlog) {
                $blogLastmod = ($latestBlog->updated_at ?? $latestBlog->published_at ?? now())->toIso8601String();
            }
        } catch (\Throwable $e) {
            // Bỏ qua nếu chưa migrate
        }

        $newsLastmod = $now;
        try {
            $latestNews = NewsPost::published()
                ->select(['updated_at', 'published_at'])
                ->orderByDesc('updated_at')
                ->first();
            if ($latestNews) {
                $newsLastmod = ($latestNews->updated_at ?? $latestNews->published_at ?? now())->toIso8601String();
            }
        } catch (\Throwable $e) {
            // Bỏ qua nếu chưa migrate
        }

        $sitemaps = [];
        if ($settings['pages']) {
            $sitemaps[] = ['loc' => route('sitemap.pages'), 'lastmod' => $now];
        }
        if ($settings['blog']) {
            $sitemaps[] = ['loc' => route('sitemap.blog'), 'lastmod' => $blogLastmod];
        }
        if ($settings['news']) {
            $sitemaps[] = ['loc' => route('sitemap.news'), 'lastmod' => $newsLastmod];
        }
        if ($settings['docs']) {
            $sitemaps[] = ['loc' => route('sitemap.docs'), 'lastmod' => $now];
        }

        $xmlOutput = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xmlOutput .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($sitemaps as $sitemap) {
            $xmlOutput .= "  <sitemap>\n";
            $xmlOutput .= '    <loc>'.htmlspecialchars($sitemap['loc'], ENT_XML1, 'UTF-8')."</loc>\n";
            $xmlOutput .= "    <lastmod>{$sitemap['lastmod']}</lastmod>\n";
            $xmlOutput .= "  </sitemap>\n";
        }

        $xmlOutput .= '</sitemapindex>';

        return $xmlOutput;
    }

    /**
     * @return array{xml: string, count: int}
     */
    public function buildPagesXml(): array
    {
        $now = now()->toIso8601String();

        $pages = [
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily', 'lastmod' => $now],
            ['loc' => route('templates.index'), 'priority' => '0.95', 'changefreq' => 'daily', 'lastmod' => $now],
            ['loc' => route('family_tree.index'), 'priority' => '0.9', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('iching.index'), 'priority' => '0.9', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('tuvi.index'), 'priority' => '0.9', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('tuvi.synastry.index'), 'priority' => '0.86', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('tuvi.palaces'), 'priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('tuvi.stars'), 'priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('numerology.index'), 'priority' => '0.9', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('numerology.love.index'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('numerology.library.life_path'), 'priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('numerology.library.indicators'), 'priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('numerology.library.birth_chart'), 'priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('numerology.library.personal_year'), 'priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('numerology.library.index'), 'priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('tarot.index'), 'priority' => '0.9', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('tarot.love'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('tarot.daily'), 'priority' => '0.86', 'changefreq' => 'daily', 'lastmod' => $now],
            ['loc' => route('tarot.celtic'), 'priority' => '0.85', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('tarot.library'), 'priority' => '0.88', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('calendar.index'), 'priority' => '0.95', 'changefreq' => 'daily', 'lastmod' => $now],
            ['loc' => route('calendar.converter'), 'priority' => '0.88', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('calendar.solar_terms'), 'priority' => '0.86', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('calendar.anniversary'), 'priority' => '0.86', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('calendar.year', ['year' => now('Asia/Ho_Chi_Minh')->year]), 'priority' => '0.88', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('calendar.year', ['year' => now('Asia/Ho_Chi_Minh')->year + 1]), 'priority' => '0.86', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('bazi.index'), 'priority' => '0.92', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('bazi.love'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('bazi.library'), 'priority' => '0.86', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('bazi.reverse'), 'priority' => '0.82', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('bazi.topic', ['topic' => 'dung-than']), 'priority' => '0.86', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('bazi.topic', ['topic' => 'nhat-chu']), 'priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('bazi.topic', ['topic' => 'thap-than']), 'priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('bazi.topic', ['topic' => 'ngu-hanh']), 'priority' => '0.84', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('bazi.topic', ['topic' => 'dai-van']), 'priority' => '0.84', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('bazi.topic', ['topic' => 'gio-mat-troi']), 'priority' => '0.84', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('marriage.caoly.index'), 'priority' => '0.92', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('marriage.caoly.matrix'), 'priority' => '0.88', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('marriage.caoly.scenes'), 'priority' => '0.86', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('marriage.compatibility.index'), 'priority' => '0.92', 'changefreq' => 'weekly', 'lastmod' => $now],
        ];

        // 78 bài viết chuẩn SEO độc lập cho từng lá bài Rider–Waite–Smith (/tarot/la-bai/{slug})
        foreach (app(TarotDeckRegistry::class)->getAllCards() as $tarotCard) {
            $pages[] = [
                'loc' => route('tarot.card_detail', $tarotCard['slug']),
                'priority' => '0.82',
                'changefreq' => 'monthly',
                'lastmod' => $now,
            ];
        }

        $pages = array_merge($pages, [
            ['loc' => route('tuvi.patterns'), 'priority' => '0.84', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('tuvi.zodiacs'), 'priority' => '0.84', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('tuvi.yearly'), 'priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('event_invitations.index'), 'priority' => '0.9', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('event_invitations.category', 'tet-nguyen-dan'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('event_invitations.category', 'tet-trung-thu'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('event_invitations.category', 'giang-sinh-nam-moi'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('event_invitations.category', 'day-thang-thoi-noi'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('event_invitations.category', 'sinh-nhat-tre-em'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('event_invitations.category', 'sinh-nhat-nguoi-lon'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('event_invitations.category', 'tan-gia-nha-moi'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('event_invitations.category', 'tat-nien-khai-truong'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('event_invitations.category', 'tot-nghiep-thanh-tuu'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('event_invitations.category', 'ngay-le-chuc-mung'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('blog.index'), 'priority' => '0.9', 'changefreq' => 'daily', 'lastmod' => $now],
            ['loc' => route('news.index'), 'priority' => '0.9', 'changefreq' => 'daily', 'lastmod' => $now],
            ['loc' => route('pricing'), 'priority' => '0.85', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('services'), 'priority' => '0.85', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('docs.index'), 'priority' => '0.85', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('pricing.advisor'), 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('about'), 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('contact'), 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('faq'), 'priority' => '0.7', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => route('terms'), 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('privacy'), 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('policy.complaints'), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('policy.pricing'), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('policy.payment'), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('policy.service_conditions'), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('policy.delivery_refund'), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('policy.support'), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $now],
        ]);

        return [
            'xml' => $this->renderUrlset($pages),
            'count' => count($pages),
        ];
    }

    /**
     * @return array{xml: string, count: int}
     */
    public function buildBlogXml(): array
    {
        $now = now()->toIso8601String();
        $pages = [];

        try {
            $posts = BlogPost::published()
                ->select(['id', 'slug', 'updated_at', 'published_at'])
                ->orderByDesc('published_at')
                ->get();

            foreach ($posts as $post) {
                $lastmod = ! empty($post->updated_at)
                    ? $post->updated_at->toIso8601String()
                    : (! empty($post->published_at) ? $post->published_at->toIso8601String() : $now);

                $pages[] = [
                    'loc' => route('blog.show', $post->slug),
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                    'lastmod' => $lastmod,
                ];
            }
        } catch (\Throwable $e) {
            // Phòng vệ an toàn nếu bảng chưa migrate
        }

        return [
            'xml' => $this->renderUrlset($pages),
            'count' => count($pages),
        ];
    }

    /**
     * @return array{xml: string, count: int}
     */
    public function buildNewsXml(): array
    {
        $now = now()->toIso8601String();
        $pages = [
            [
                'loc' => route('news.index'),
                'priority' => '0.9',
                'changefreq' => 'daily',
                'lastmod' => $now,
            ],
        ];

        try {
            $newsPosts = NewsPost::published()
                ->select(['id', 'slug', 'updated_at', 'published_at'])
                ->orderByDesc('published_at')
                ->get();

            foreach ($newsPosts as $newsPost) {
                $lastmod = ! empty($newsPost->updated_at)
                    ? $newsPost->updated_at->toIso8601String()
                    : (! empty($newsPost->published_at) ? $newsPost->published_at->toIso8601String() : $now);

                $pages[] = [
                    'loc' => route('news.show', $newsPost->slug),
                    'priority' => '0.85',
                    'changefreq' => 'weekly',
                    'lastmod' => $lastmod,
                ];
            }
        } catch (\Throwable $e) {
            // Phòng vệ an toàn nếu bảng chưa migrate
        }

        return [
            'xml' => $this->renderUrlset($pages),
            'count' => count($pages),
        ];
    }

    /**
     * @return array{xml: string, count: int}
     */
    public function buildDocsXml(): array
    {
        $now = now()->toIso8601String();
        $categories = $this->docsService->getCategories('client');

        $pages = [
            [
                'loc' => route('docs.index'),
                'priority' => '0.9',
                'changefreq' => 'weekly',
                'lastmod' => $now,
            ],
        ];

        foreach ($categories as $cat) {
            foreach ($cat['articles'] as $art) {
                $lastmod = ! empty($art['updated_at'])
                    ? date('c', strtotime((string) $art['updated_at']))
                    : $now;

                $pages[] = [
                    'loc' => $art['url'],
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                    'lastmod' => $lastmod,
                ];
            }
        }

        return [
            'xml' => $this->renderUrlset($pages),
            'count' => count($pages),
        ];
    }

    /**
     * @param  array<int, array{loc: string, lastmod: string, changefreq: string, priority: string}>  $pages
     */
    protected function renderUrlset(array $pages): string
    {
        $xmlOutput = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xmlOutput .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($pages as $page) {
            $xmlOutput .= "  <url>\n";
            $xmlOutput .= '    <loc>'.htmlspecialchars($page['loc'], ENT_XML1, 'UTF-8')."</loc>\n";
            $xmlOutput .= "    <lastmod>{$page['lastmod']}</lastmod>\n";
            $xmlOutput .= "    <changefreq>{$page['changefreq']}</changefreq>\n";
            $xmlOutput .= "    <priority>{$page['priority']}</priority>\n";
            $xmlOutput .= "  </url>\n";
        }

        $xmlOutput .= '</urlset>';

        return $xmlOutput;
    }

    protected function atomicWrite(string $absPath, string $content): void
    {
        $tmpPath = $absPath.'.tmp.'.uniqid('', true);
        File::put($tmpPath, $content);
        @rename($tmpPath, $absPath);
        if (File::exists($tmpPath)) {
            File::put($absPath, $content);
            @unlink($tmpPath);
        }
    }

    protected static function readBoolSetting(string $key, bool $default): bool
    {
        try {
            $val = PlatformSetting::getValue(PlatformSetting::GROUP_BRAND_SEO, $key);
            if ($val === null || $val === '') {
                return $default;
            }

            return in_array(strtolower((string) $val), ['1', 'true', 'yes', 'on'], true);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
