<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Generate dynamic and cached XML sitemap for SEO.
     */
    public function index(): Response
    {
        $xml = Cache::remember('mayhen_sitemap_xml', 86400, function () {
            $now = now()->toIso8601String();

            $pages = [
                ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily', 'lastmod' => $now],
                ['loc' => route('templates.index'), 'priority' => '0.9', 'changefreq' => 'daily', 'lastmod' => $now],
                ['loc' => route('pricing'), 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('services'), 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('how-it-works'), 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $now],
                ['loc' => route('about'), 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $now],
                ['loc' => route('contact'), 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $now],
                ['loc' => route('faq'), 'priority' => '0.7', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('terms'), 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => $now],
                ['loc' => route('privacy'), 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => $now],
            ];

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
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=43200',
        ]);
    }
}
