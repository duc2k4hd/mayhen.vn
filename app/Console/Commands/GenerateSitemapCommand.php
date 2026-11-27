<?php

namespace App\Console\Commands;

use App\Http\Controllers\Client\SitemapController;
use App\Services\SitemapGeneratorService;
use Illuminate\Console\Command;

class GenerateSitemapCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * @var string
     */
    protected $description = 'Xuất toàn bộ tệp XML Sitemap tĩnh (sitemap.xml, pages, blog, news, docs, tarot) trực tiếp vào thư mục /public để tải tức thì';

    public function handle(SitemapGeneratorService $generator): int
    {
        SitemapController::clearCache(regenerateStaticFiles: false);
        $result = $generator->generateAndWriteAll();

        foreach ($result['files'] as $file) {
            $kb = round($file['bytes'] / 1024, 2);
            $this->line("  ✓ /public/{$file['filename']} ({$file['count']} mục, {$kb} KB)");
        }

        $this->info("Hoàn tất xuất Sitemap tĩnh vào /public (Tổng cộng {$result['total_urls']} URL công khai).");

        return self::SUCCESS;
    }
}
