<?php

namespace App\Services;

use App\Models\InvitationTemplate;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SonghyTemplateIngestionService
{
    protected const BASE_URL = 'https://songhy.online';

    /**
     * Bản đồ phân loại mẫu thiệp Song Hỷ vào 4 phong cách định sẵn của Mây Hẹn.
     *
     * @var array<string, string>
     */
    protected const CATEGORY_KEYWORDS = [
        'traditional' => [
            'song-hy', 'long-phung', 'hac-sen', 'hoang-cung', 'song-long', 'hy',
            'co-dien', 'dam-hoi', 'vu-quy', 'gia-tien', 'truyen-thong',
        ],
        'editorial' => [
            'thuoc-phim', 'chuyen-bay', 'songhy-v4', 'florence', 'tap-chi', 'vogue',
            'passport', 'boarding', 'newspaper', 'polaroid',
        ],
        'minimal' => [
            'minimalism', 'giay-dap-noi', 'don-gian', 'toi-gian', 'trang',
            'xam', 'den', 'clean', 'modern',
        ],
        'romantic' => [
            'hoa', 'tulip', 'mau-don', 'ngoc-trai', 'thu-tinh', 'moc-thao',
            'hong', 'sen', 'tim', 'lang-man', 'pastel', 'rose',
        ],
    ];

    /**
     * Quét và lấy danh sách toàn bộ các mẫu thiệp có trên songhy.online/kho-mau-thiep.
     *
     * @return array<int, array{slug: string, name: string, category: string, demo_url: string, thumbnail_url: string}>
     */
    public function discoverCatalog(): array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ])->get(self::BASE_URL.'/kho-mau-thiep');

        if (! $response->successful()) {
            throw new \RuntimeException("Không thể kết nối đến kho mẫu songhy.online (HTTP {$response->status()})");
        }

        $html = $response->body();
        $pattern = '/<a[^>]+href="\/mau-thiep\/([^"]+)"[^>]*>([\s\S]*?)<\/a>/i';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

        $templates = [];
        $seenSlugs = [];

        foreach ($matches as $match) {
            $slug = trim($match[1]);
            if (isset($seenSlugs[$slug])) {
                continue;
            }
            $seenSlugs[$slug] = true;

            $innerHtml = $match[2];
            $cleanName = trim(strip_tags($innerHtml));
            $cleanName = preg_replace('/\s+/', ' ', $cleanName);
            if (empty($cleanName)) {
                $cleanName = Str::title(str_replace('-', ' ', $slug));
            }

            // Tìm ảnh thumbnail
            $thumbnail = '';
            if (preg_match('/<img[^>]+src="([^"]+)"/i', $innerHtml, $imgMatch)) {
                $thumbnail = $imgMatch[1];
                if (str_starts_with($thumbnail, '/')) {
                    $thumbnail = self::BASE_URL.$thumbnail;
                }
            }

            $category = $this->detectCategory($slug, $cleanName);

            $templates[] = [
                'slug' => $slug,
                'name' => $cleanName,
                'category' => $category,
                'demo_url' => self::BASE_URL.'/invite/'.$slug,
                'thumbnail_url' => $thumbnail,
            ];
        }

        return $templates;
    }

    /**
     * Nhận diện phong cách (category) phù hợp của Mây Hẹn dựa trên slug và tên mẫu.
     */
    public function detectCategory(string $slug, string $name): string
    {
        $slugLower = Str::lower($slug.' '.Str::slug($name));

        foreach (self::CATEGORY_KEYWORDS as $cat => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($slugLower, $kw)) {
                    return $cat;
                }
            }
        }

        return 'romantic'; // Mặc định cho thiệp cưới
    }

    /**
     * Clone, tải 100% tài nguyên và chuẩn hóa mẫu thiệp theo đúng 8 nguyên tắc Mây Hẹn.
     *
     * @return array{success: bool, slug: string, name: string, category: string, assets_count: int, message: string}
     */
    public function ingestTemplate(string $slug, ?string $overrideCategory = null): array
    {
        $demoUrl = self::BASE_URL.'/invite/'.$slug;
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ])->get($demoUrl);

        if (! $response->successful()) {
            throw new \RuntimeException("Không thể tải trang demo của mẫu thiệp [{$slug}] từ {$demoUrl} (HTTP {$response->status()})");
        }

        $rawHtml = $response->body();

        // 1. Chuẩn bị thư mục đích
        $targetDir = resource_path("views/client/invitations/{$slug}");
        $assetsDir = "{$targetDir}/assets";
        File::ensureDirectoryExists($assetsDir);

        // 2. Tìm kiếm và tải toàn bộ ảnh / asset cục bộ
        $downloadedAssets = [];
        $rawHtml = $this->downloadAndLocalizeAssets($rawHtml, $assetsDir, $slug, $downloadedAssets);

        // 3. Tải và tổng hợp CSS
        $stylesCss = $this->extractAndNormalizeCss($rawHtml, $slug);
        File::put("{$targetDir}/styles.css", $stylesCss);

        // 4. Chuẩn hóa HTML sang Blade theo chuẩn Mây Hẹn
        $templateBlade = $this->normalizeHtmlToBlade($rawHtml, $slug, $downloadedAssets);
        File::put("{$targetDir}/template.blade.php", $templateBlade);

        // 5. Sinh script.js thuần Native
        $scriptJs = $this->generateNativeScript($slug);
        File::put("{$targetDir}/script.js", $scriptJs);

        // 6. Sinh schema.json chuẩn Mây Hẹn Studio
        $category = $overrideCategory ?: $this->detectCategory($slug, $slug);
        $name = Str::title(str_replace('-', ' ', $slug));
        $schemaJson = $this->generateSchemaJson($slug, $name, $category);
        File::put("{$targetDir}/schema.json", json_encode($schemaJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        // 7. Lưu hoặc cập nhật vào bảng invitation_templates
        $thumbnailPath = "/mau-thiep/{$slug}/assets/cover.webp";
        if (! File::exists("{$assetsDir}/cover.webp") && count($downloadedAssets) > 0) {
            $firstImg = array_values($downloadedAssets)[0];
            $thumbnailPath = "/mau-thiep/{$slug}/assets/{$firstImg}";
        }

        $template = InvitationTemplate::firstOrNew(['slug' => $slug]);
        $template->name = $name;
        $template->category = $category;
        $template->thumbnail_url = $thumbnailPath;
        $template->demo_url = "/xem-thu/{$slug}";
        $template->default_config = [
            'tier' => 'standard',
            'complexity' => 'standard',
            'setup_time' => '5 phút',
            'accent_color' => '#85182A',
            'font_serif' => 'Cormorant Garamond, Playfair Display',
            'animation' => 'gentle-unroll',
            'style_layout' => 'songhy-normalized',
            'section_count' => 10,
            'source' => 'songhy_curated_import',
        ];
        $template->is_active = true;
        $template->is_featured = false;
        $template->save();

        InvitationTemplate::clearTemplateCaches();

        return [
            'success' => true,
            'slug' => $slug,
            'name' => $name,
            'category' => $category,
            'assets_count' => count($downloadedAssets),
            'message' => "Đã trích xuất và chuẩn hóa thành công mẫu [{$name}] với ".count($downloadedAssets).' assets nội bộ.',
        ];
    }

    /**
     * Quét và tải toàn bộ ảnh từ các thẻ <img>, background-image về assets/ cục bộ.
     *
     * @param  array<string, string>  &$downloadedAssets
     */
    protected function downloadAndLocalizeAssets(string $html, string $assetsDir, string $slug, array &$downloadedAssets): string
    {
        // 1. Quét thẻ <img>
        preg_match_all('/<img[^>]+src="([^">]+)"/i', $html, $imgMatches);
        $candidateUrls = $imgMatches[1] ?? [];

        // 2. Quét url(...) trong style
        preg_match_all('/url\(([^)]+)\)/i', $html, $bgMatches);
        foreach ($bgMatches[1] ?? [] as $bgUrl) {
            $candidateUrls[] = trim($bgUrl, '\'"&quot; ');
        }

        $candidateUrls = array_unique($candidateUrls);
        $assetBase = url("/mau-thiep/{$slug}/assets");

        $index = 1;
        foreach ($candidateUrls as $rawUrl) {
            $rawUrl = trim($rawUrl);
            if (empty($rawUrl) || str_starts_with($rawUrl, 'data:') || str_starts_with($rawUrl, 'blob:')) {
                continue;
            }

            $absoluteUrl = $rawUrl;
            if (str_starts_with($rawUrl, '/')) {
                $absoluteUrl = self::BASE_URL.$rawUrl;
            } elseif (! str_starts_with($rawUrl, 'http://') && ! str_starts_with($rawUrl, 'https://')) {
                $absoluteUrl = self::BASE_URL.'/'.ltrim($rawUrl, '/');
            }

            // Đặt tên file lưu trữ gọn gàng
            $ext = pathinfo(parse_url($absoluteUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION);
            if (empty($ext) || strlen($ext) > 5) {
                $ext = 'webp';
            }

            $localFilename = "asset_{$index}.{$ext}";
            if ($index === 1) {
                $localFilename = "cover.{$ext}";
            }

            $localPath = "{$assetsDir}/{$localFilename}";

            try {
                $imgResponse = Http::timeout(10)->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'Referer' => self::BASE_URL,
                ])->get($absoluteUrl);

                if ($imgResponse->successful()) {
                    File::put($localPath, $imgResponse->body());
                    $downloadedAssets[$rawUrl] = $localFilename;
                    $index++;
                }
            } catch (\Throwable $e) {
                Log::warning("Không thể tải asset [{$absoluteUrl}] của mẫu [{$slug}]: ".$e->getMessage());
            }
        }

        // Tải ảnh placeholder dự phòng nếu chưa có
        $fallbackPlaceholder = resource_path('views/client/invitations/mayhen-curated/assets/placeholder-photo.svg');
        if (File::exists($fallbackPlaceholder) && ! File::exists("{$assetsDir}/placeholder-photo.svg")) {
            File::copy($fallbackPlaceholder, "{$assetsDir}/placeholder-photo.svg");
        }

        return $html;
    }

    /**
     * Tải và chuyển đổi các stylesheet chia sẻ từ Song Hỷ sang Pure CSS Scoped Mây Hẹn.
     */
    protected function extractAndNormalizeCss(string $html, string $slug): string
    {
        preg_match_all('/<link[^>]+rel="stylesheet"[^>]+href="([^"]+)"/i', $html, $matches);
        $cssUrls = $matches[1] ?? [];

        $combinedCss = "/**\n * MÂY HẸN (MAYHEN.VN) — TEMPLATE: {$slug}\n * 100% Pure Vanilla Scoped CSS • Zero External Libraries\n */\n\n";

        foreach ($cssUrls as $cssPath) {
            $url = str_starts_with($cssPath, '/') ? self::BASE_URL.$cssPath : $cssPath;
            try {
                $res = Http::timeout(8)->get($url);
                if ($res->successful()) {
                    $cssBody = $res->body();
                    // Thay thế URL ảnh sang đường dẫn local asset
                    $cssBody = preg_replace_callback('/url\(([^)]+)\)/i', function ($m) use ($slug) {
                        $inner = trim($m[1], '\'" ');
                        $filename = basename(parse_url($inner, PHP_URL_PATH) ?? '');
                        if (! empty($filename)) {
                            return "url('/mau-thiep/{$slug}/assets/{$filename}')";
                        }

                        return $m[0];
                    }, $cssBody);

                    $combinedCss .= "/* --- Source: {$cssPath} --- */\n".$cssBody."\n\n";
                }
            } catch (\Throwable $e) {
                Log::warning("Lỗi tải CSS [{$url}]: ".$e->getMessage());
            }
        }

        // Bổ sung các rule bắt buộc chuẩn Mây Hẹn cho Chế độ Nhúng Studio ($isEmbed)
        $combinedCss .= '
/* ==========================================================================
   MÂY HẸN STUDIO EMBED DEFENSE & ZERO CONFLICT RULES
   ========================================================================== */
.mayhen_invitation_ladi_wraper.is-embed .mdd-intro-overlay,
.mayhen_invitation_ladi_wraper.is-embed [data-landing-screenshot-id="invite-envelope"],
.mayhen_invitation_ladi_wraper.is-embed .mayhen_invitation_opening {
    display: none !important;
}

.mayhen_invitation_ladi_wraper.is-embed {
    display: block !important;
}

/* Loại bỏ rác widget và liên kết bên thứ ba */
#miu-managed-wedding-features,
#miu-messenger-widget,
#miu-messenger-tooltip,
[aria-label*="Song Hỷ"],
[aria-label*="Hỗ trợ Song Hỷ"] {
    display: none !important;
}
';

        return $combinedCss;
    }

    /**
     * Chuẩn hóa cấu trúc HTML từ Song Hỷ sang Blade template chuẩn Mây Hẹn với hợp đồng Live Sync.
     */
    protected function normalizeHtmlToBlade(string $html, string $slug, array $downloadedAssets): string
    {
        // 1. Trích xuất phần thân bên trong <body>
        if (preg_match('/<body[^>]*>([\s\S]*?)<\/body>/i', $html, $bodyMatch)) {
            $content = $bodyMatch[1];
        } else {
            $content = $html;
        }

        // 2. Loại bỏ toàn bộ <script> rác, id và attributes của bên thứ ba
        $content = preg_replace('/<script[\s\S]*?<\/script>/i', '', $content);
        $content = preg_replace('/<div role="complementary"[\s\S]*?id="miu-messenger-widget"[\s\S]*?<\/div>/i', '', $content);
        $content = preg_replace('/<section id="miu-managed-wedding-features"[\s\S]*?<\/section>/i', '', $content);
        $content = preg_replace('/\s*id="songhy-[^"]*"/i', '', $content);
        $content = preg_replace('/\s*data-songhy-[a-zA-Z0-9_-]+="[^"]*"/i', '', $content);
        $content = preg_replace('/\s*data-landing-screenshot-id="[^"]*"/i', '', $content);
        $content = preg_replace('/\s*data-testid="[^"]*"/i', '', $content);

        // 3. Thay thế các URL ảnh nguồn sang tài nguyên nội bộ
        foreach ($downloadedAssets as $remoteUrl => $localFile) {
            $content = str_replace($remoteUrl, "{{ \$assetBase }}/{$localFile}", $content);
        }

        // 4. Gắn hợp đồng Live Sync data-sync-target chuẩn Mây Hẹn
        // Groom & Bride Name
        $content = preg_replace(
            '/(<span[^>]*class="[^"]*groom[^"]*"[^>]*>)(.*?)(<\/span>)/i',
            '$1<span data-sync-target="groom_name">{{ $groomName }}</span>$3',
            $content
        );
        $content = preg_replace(
            '/(<span[^>]*class="[^"]*bride[^"]*"[^>]*>)(.*?)(<\/span>)/i',
            '$1<span data-sync-target="bride_name">{{ $brideName }}</span>$3',
            $content
        );

        // Bọc vào cấu trúc Blade chuẩn Mây Hẹn
        $header = "@php\n"
            ."    \$assetBase = url('/mau-thiep/{$slug}/assets');\n"
            ."    \$isEmbed = request()->boolean('embed');\n"
            ."    \$isPreview = request()->routeIs('templates.preview') || (!isset(\$wedding) && isset(\$template));\n"
            ."    \$templateName = \$template->name ?? '".Str::title(str_replace('-', ' ', $slug))."';\n\n"
            ."    \$groomName = \$wedding->groom_name ?? 'Minh Tuấn';\n"
            ."    \$brideName = \$wedding->bride_name ?? 'Thu Hà';\n"
            ."    \$extraData = \$wedding->extra_data ?? [];\n"
            ."    \$weddingDate = isset(\$wedding->wedding_date) ? \\Carbon\\Carbon::parse(\$wedding->wedding_date) : \\Carbon\\Carbon::create(2026, 12, 20, 18, 0, 0);\n"
            ."    \$partyVenue = \$extraData['party_venue'] ?? (\$wedding->venue_name ?? 'Trung tâm Tiệc cưới Ngân Hà');\n"
            ."    \$partyAddress = \$extraData['event_party_address'] ?? (\$wedding->venue_address ?? 'Số 88 Trần Duy Hưng, Cầu Giấy, Hà Nội');\n"
            ."@endphp\n\n"
            ."@extends('client.layouts.invitation')\n\n"
            ."@section('title', \$groomName . ' & ' . \$brideName . ' — ' . \$templateName)\n\n"
            ."@push('styles')\n"
            ."    <link rel=\"stylesheet\" href=\"{{ route('templates.asset', ['slug' => '{$slug}', 'path' => 'styles.css']) }}?v={{ time() }}\">\n"
            ."@endpush\n\n"
            ."@section('content')\n"
            ."<div class=\"mayhen_invitation_ladi_wraper {{ \$isEmbed ? 'is-embed' : '' }}\" id=\"mayhenInvitationRoot\">\n";

        $footer = "\n</div>\n@endsection\n\n"
            ."@push('scripts')\n"
            ."    <script src=\"{{ route('templates.asset', ['slug' => '{$slug}', 'path' => 'script.js']) }}?v={{ time() }}\"></script>\n"
            ."@endpush\n";

        return $header.$content.$footer;
    }

    /**
     * Sinh script.js Native điều khiển mở thiệp, nhạc, countdown và RSVP chuẩn Mây Hẹn.
     */
    protected function generateNativeScript(string $slug): string
    {
        return <<<JS
/**
 * MÂY HẸN (MAYHEN.VN) — TEMPLATE JAVASCRIPT: {$slug}
 * Pure Vanilla Native JS • Zero External Libraries
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        initEnvelopeOpening();
        initCountdown();
    });

    function initEnvelopeOpening() {
        const envelope = document.querySelector('.mdd-intro-overlay, [data-landing-screenshot-id="invite-envelope"]');
        if (!envelope) return;

        const openBtns = envelope.querySelectorAll('button, a, .open-btn');
        openBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                envelope.classList.add('is-opened');
                setTimeout(() => {
                    envelope.style.display = 'none';
                }, 800);
            });
        });
    }

    function initCountdown() {
        const cdBoxes = document.querySelectorAll('[data-countdown]');
        cdBoxes.forEach(box => {
            const dateStr = box.getAttribute('data-countdown');
            if (!dateStr) return;
            const target = new Date(dateStr).getTime();
            if (isNaN(target)) return;

            function update() {
                const now = new Date().getTime();
                const dist = target - now;
                if (dist < 0) return;

                const days = Math.floor(dist / (1000 * 60 * 60 * 24));
                const hours = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const mins = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60));
                const secs = Math.floor((dist % (1000 * 60)) / 1000);

                const dEl = box.querySelector('.cd-days, .days');
                const hEl = box.querySelector('.cd-hours, .hours');
                const mEl = box.querySelector('.cd-mins, .minutes');
                const sEl = box.querySelector('.cd-secs, .seconds');

                if (dEl) dEl.textContent = days < 10 ? '0' + days : days;
                if (hEl) hEl.textContent = hours < 10 ? '0' + hours : hours;
                if (mEl) mEl.textContent = mins < 10 ? '0' + mins : mins;
                if (sEl) sEl.textContent = secs < 10 ? '0' + secs : secs;
            }

            update();
            setInterval(update, 1000);
        });
    }
})();
JS;
    }

    /**
     * Sinh schema.json chuẩn Mây Hẹn Studio cho mẫu thiệp.
     *
     * @return array<string, mixed>
     */
    protected function generateSchemaJson(string $slug, string $name, string $category): array
    {
        return [
            'slug' => $slug,
            'version' => 'v1',
            'name' => $name,
            'category' => $category,
            'tier' => 'standard',
            'complexity' => 'standard',
            'setup_time' => '5 phút',
            'supported_sections' => [
                'opening',
                'hero',
                'story',
                'parents',
                'countdown_calendar',
                'events',
                'timeline',
                'dresscode',
                'album',
                'gift',
                'interactive_ending',
            ],
            'sections' => [
                [
                    'id' => 'opening',
                    'title' => 'Màn Mở Đầu (Phong Bì)',
                    'short_title' => 'Phong Bì',
                    'icon' => 'mail',
                    'description' => 'Màn mở thiệp với phong bì hoàng gia và hiệu ứng hoa bay',
                    'fields' => [
                        [
                            'key' => 'wedding.groom_name',
                            'label' => 'Họ và tên Chú rể',
                            'type' => 'text',
                            'required' => true,
                            'default' => 'Minh Tuấn',
                            'max' => 30,
                        ],
                        [
                            'key' => 'wedding.bride_name',
                            'label' => 'Họ và tên Cô dâu',
                            'type' => 'text',
                            'required' => true,
                            'default' => 'Thu Hà',
                            'max' => 30,
                        ],
                    ],
                ],
                [
                    'id' => 'hero',
                    'title' => 'Trang Bìa Chính',
                    'short_title' => 'Trang Bìa',
                    'icon' => 'sparkles',
                    'description' => 'Tên cô dâu chú rể, loại lễ và ảnh đại diện bìa thiệp',
                    'fields' => [
                        [
                            'key' => 'wedding.extra_data.ceremony_type',
                            'label' => 'Loại hình lễ cưới',
                            'type' => 'text',
                            'required' => false,
                            'default' => 'Lễ Thành Hôn',
                            'max' => 50,
                        ],
                        [
                            'key' => 'wedding.groom_name',
                            'label' => 'Họ và tên Chú rể',
                            'type' => 'text',
                            'required' => true,
                            'default' => 'Minh Tuấn',
                            'max' => 30,
                        ],
                        [
                            'key' => 'wedding.bride_name',
                            'label' => 'Họ và tên Cô dâu',
                            'type' => 'text',
                            'required' => true,
                            'default' => 'Thu Hà',
                            'max' => 30,
                        ],
                        [
                            'key' => 'wedding.cover_photo_url',
                            'label' => 'Ảnh bìa thiệp cưới',
                            'type' => 'image',
                            'required' => false,
                            'default' => "/mau-thiep/{$slug}/assets/cover.webp",
                        ],
                    ],
                ],
                [
                    'id' => 'story',
                    'title' => 'Lời Ngỏ & Chuyện Tình Yêu',
                    'short_title' => 'Lời Ngỏ',
                    'icon' => 'heart',
                    'description' => 'Ảnh kỷ niệm và thông điệp tình yêu trân quý',
                    'fields' => [
                        [
                            'key' => 'wedding.extra_data.story_title',
                            'label' => 'Tiêu đề câu chuyện',
                            'type' => 'text',
                            'default' => 'Chuyện Tình Yêu',
                        ],
                        [
                            'key' => 'wedding.extra_data.couple_quote',
                            'label' => 'Câu trích dẫn tâm tình',
                            'type' => 'textarea',
                            'default' => 'Gặp gỡ là duyên, đồng hành là nợ, cùng nhau già đi là lời hứa trọn đời.',
                        ],
                    ],
                ],
                [
                    'id' => 'parents',
                    'title' => 'Song Thân Hai Họ',
                    'short_title' => 'Hai Họ',
                    'icon' => 'users',
                    'description' => 'Họ tên cha mẹ và địa chỉ tư gia hai bên',
                    'fields' => [
                        [
                            'key' => 'wedding.extra_data.groom_father',
                            'label' => 'Họ tên Cha Chú rể',
                            'type' => 'text',
                            'default' => 'Nguyễn Quốc Bình',
                        ],
                        [
                            'key' => 'wedding.extra_data.groom_mother',
                            'label' => 'Họ tên Mẹ Chú rể',
                            'type' => 'text',
                            'default' => 'Nguyễn Hoài Mai',
                        ],
                        [
                            'key' => 'wedding.extra_data.bride_father',
                            'label' => 'Họ tên Cha Cô dâu',
                            'type' => 'text',
                            'default' => 'Vũ Minh Hải',
                        ],
                        [
                            'key' => 'wedding.extra_data.bride_mother',
                            'label' => 'Họ tên Mẹ Cô dâu',
                            'type' => 'text',
                            'default' => 'Vũ Thanh Hiền',
                        ],
                    ],
                ],
                [
                    'id' => 'countdown_calendar',
                    'title' => 'Đếm Ngược & Lịch Cưới',
                    'short_title' => 'Lịch Cưới',
                    'icon' => 'calendar',
                    'description' => 'Đồng hồ đếm ngược và tờ lịch tháng cưới',
                    'fields' => [
                        [
                            'key' => 'wedding.wedding_date',
                            'label' => 'Ngày cưới dương lịch',
                            'type' => 'date',
                            'default' => '2026-01-03',
                        ],
                        [
                            'key' => 'wedding.extra_data.wedding_lunar_date',
                            'label' => 'Ngày cưới âm lịch',
                            'type' => 'text',
                            'default' => 'Nhằm ngày 15 tháng 11 năm Ất Tỵ',
                        ],
                    ],
                ],
                [
                    'id' => 'events',
                    'title' => 'Tiệc Cưới & Bản Đồ',
                    'short_title' => 'Tiệc Cưới',
                    'icon' => 'map-pin',
                    'description' => 'Thời gian tiệc và địa điểm tổ chức',
                    'fields' => [
                        [
                            'key' => 'wedding.extra_data.party_time',
                            'label' => 'Giờ khai tiệc',
                            'type' => 'text',
                            'default' => '18:00',
                        ],
                        [
                            'key' => 'wedding.venue_name',
                            'label' => 'Tên trung tâm tiệc cưới',
                            'type' => 'text',
                            'default' => 'Trung Tâm Hội Nghị White Palace',
                        ],
                        [
                            'key' => 'wedding.venue_address',
                            'label' => 'Địa chỉ tổ chức tiệc cưới',
                            'type' => 'text',
                            'default' => '194 Hoàng Văn Thụ, Phường 9, Quận Phú Nhuận, TP. Hồ Chí Minh',
                        ],
                    ],
                ],
                [
                    'id' => 'timeline',
                    'title' => 'Lịch Trình Hôn Lễ',
                    'short_title' => 'Lịch Trình',
                    'icon' => 'clock',
                    'description' => 'Các mốc thời gian ngày cưới',
                    'fields' => [
                        [
                            'key' => 'wedding.extra_data.timeline',
                            'label' => 'Danh sách mốc thời gian',
                            'type' => 'timeline',
                        ],
                    ],
                ],
                [
                    'id' => 'dresscode',
                    'title' => 'Gợi Ý Trang Phục',
                    'short_title' => 'Dresscode',
                    'icon' => 'tag',
                    'description' => 'Bảng màu trang phục khách mời',
                    'fields' => [
                        [
                            'key' => 'wedding.extra_data.dresscode_colors',
                            'label' => 'Bảng màu trang phục',
                            'type' => 'dresscode',
                        ],
                    ],
                ],
                [
                    'id' => 'album',
                    'title' => 'Album 10 Ảnh Cưới',
                    'short_title' => 'Album Ảnh',
                    'icon' => 'image',
                    'description' => 'Khoảnh khắc hạnh phúc của cặp đôi',
                    'fields' => [
                        [
                            'key' => 'wedding.extra_data.gallery',
                            'label' => 'Danh sách ảnh cưới',
                            'type' => 'gallery',
                        ],
                    ],
                ],
                [
                    'id' => 'gift',
                    'title' => 'Hộp Mừng Cưới',
                    'short_title' => 'Mừng Cưới',
                    'icon' => 'gift',
                    'description' => 'Thông tin mừng cưới VietQR Nhà Trai & Nhà Gái',
                    'fields' => [
                        [
                            'key' => 'wedding.extra_data.groom_bank_account',
                            'label' => 'Số tài khoản Chú rể',
                            'type' => 'text',
                            'default' => '0123 456 789',
                        ],
                        [
                            'key' => 'wedding.extra_data.bride_bank_account',
                            'label' => 'Số tài khoản Cô dâu',
                            'type' => 'text',
                            'default' => '1903 5555 8888',
                        ],
                    ],
                ],
                [
                    'id' => 'interactive_ending',
                    'title' => 'Ký Tên, RSVP & Lời Cảm Ơn',
                    'short_title' => 'Tương Tác',
                    'icon' => 'edit-3',
                    'description' => 'Sổ ký tên, form RSVP và lời cảm ơn',
                    'fields' => [
                        [
                            'key' => 'wedding.extra_data.ending_thank_you',
                            'label' => 'Lời cảm ơn',
                            'type' => 'textarea',
                            'default' => 'Sự hiện diện của Quý khách là niềm vinh hạnh to lớn cho gia đình chúng tôi!',
                        ],
                    ],
                ],
            ],
        ];
    }
}
