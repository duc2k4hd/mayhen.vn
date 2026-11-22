<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\InvitationTemplate;
use App\Models\PlatformSetting;
use App\Models\Review;
use App\Services\InvitationTemplateCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /**
     * Runtime In-Memory Static Memoization cho các worker PHP (FPM / Octane).
     */
    protected static ?Collection $memoizedReviews = null;

    protected static ?string $memoizedHeroBanner = null;

    protected static ?string $memoizedHeroBannerMobile = null;

    /**
     * Xóa sạch RAM memoization khi có cập nhật dữ liệu trang chủ.
     */
    public static function clearMemoization(): void
    {
        self::$memoizedReviews = null;
        self::$memoizedHeroBanner = null;
        self::$memoizedHeroBannerMobile = null;
    }

    /**
     * Hiển thị Trang chủ Mây Hẹn (Landing Page).
     */
    public function index(Request $request): Response|View
    {
        // 1 & 2. Lấy Mẫu thiệp nổi bật & Bộ sưu tập qua seam InvitationTemplateCatalog
        $featuredTemplates = InvitationTemplateCatalog::featured(8);
        $galleryTemplates = InvitationTemplateCatalog::gallery(50);

        // 3. Lấy đánh giá nổi bật (Featured Reviews) - Cache mảng thuần + Static RAM Memoization (0 DB Query trên cache ấm)
        if (self::$memoizedReviews === null) {
            $rawReviews = Cache::remember('mayhen_home_featured_reviews_v2', 3600, function (): array {
                return Review::with(['template:id,name,slug'])
                    ->featured()
                    ->take(6)
                    ->get()
                    ->map(fn (Review $r): array => [
                        'attributes' => $r->getAttributes(),
                        'template' => $r->template ? $r->template->getAttributes() : null,
                    ])
                    ->all();
            });

            $hydratedReviews = new Collection;
            if (is_array($rawReviews)) {
                foreach ($rawReviews as $row) {
                    if (! is_array($row) || ! isset($row['attributes']) || ! is_array($row['attributes'])) {
                        continue;
                    }
                    $review = (new Review)->newFromBuilder($row['attributes']);
                    if (! empty($row['template']) && is_array($row['template'])) {
                        $review->setRelation('template', (new InvitationTemplate)->newFromBuilder($row['template']));
                    }
                    $hydratedReviews->push($review);
                }
            }
            self::$memoizedReviews = $hydratedReviews;
        }
        $featuredReviews = self::$memoizedReviews;

        // 4. Lấy cấu hình ảnh nền Hero Trang Chủ (Desktop 16:9 & Mobile 9:16 TikTok — đã có sẵn cache nhóm brand_seo)
        if (self::$memoizedHeroBanner === null) {
            self::$memoizedHeroBanner = PlatformSetting::getValue(PlatformSetting::GROUP_BRAND_SEO, 'home_hero_banner', '/storage/images/client/banners/banner-home.webp') ?: '/storage/images/client/banners/banner-home.webp';
        }
        $heroBannerUrl = self::$memoizedHeroBanner;

        if (self::$memoizedHeroBannerMobile === null) {
            self::$memoizedHeroBannerMobile = PlatformSetting::getValue(PlatformSetting::GROUP_BRAND_SEO, 'home_hero_banner_mobile', '/storage/images/client/banners/banner-home-mobile.webp') ?: '/storage/images/client/banners/banner-home-mobile.webp';
        }
        $heroBannerMobileUrl = self::$memoizedHeroBannerMobile;

        // 5. Nếu người dùng đã đăng nhập, trả về view cá nhân hóa (không cache qua CDN)
        if (auth()->check()) {
            return view('client.pages.home', [
                'featuredTemplates' => $featuredTemplates,
                'galleryTemplates' => $galleryTemplates,
                'featuredReviews' => $featuredReviews,
                'heroBannerUrl' => $heroBannerUrl,
                'heroBannerMobileUrl' => $heroBannerMobileUrl,
            ]);
        }

        // 6. Tối ưu cho khách vãng lai: ETag & HTTP 304 Not Modified
        $etag = '"mayhen_home_'.md5(
            $featuredTemplates->pluck('id')->join(',').'_'.
            $galleryTemplates->count().'_'.
            $featuredReviews->pluck('id')->join(',').'_'.
            $heroBannerUrl.'_'.
            $heroBannerMobileUrl.'_'.
            PlatformSetting::getBrandName()
        ).'"';

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'public, max-age=1800, stale-while-revalidate=86400');
        }

        return response()
            ->view('client.pages.home', [
                'featuredTemplates' => $featuredTemplates,
                'galleryTemplates' => $galleryTemplates,
                'featuredReviews' => $featuredReviews,
                'heroBannerUrl' => $heroBannerUrl,
                'heroBannerMobileUrl' => $heroBannerMobileUrl,
            ])
            ->header('Cache-Control', 'public, max-age=1800, stale-while-revalidate=86400')
            ->header('ETag', $etag);
    }
}
