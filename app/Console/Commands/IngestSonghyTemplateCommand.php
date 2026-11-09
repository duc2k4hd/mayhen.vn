<?php

namespace App\Console\Commands;

use App\Services\SonghyTemplateIngestionService;
use Illuminate\Console\Command;

class IngestSonghyTemplateCommand extends Command
{
    /**
     * Tên và cú pháp của lệnh console.
     *
     * @var string
     */
    protected $signature = 'mayhen:ingest-songhy
                            {slug? : Slug của mẫu thiệp trên songhy.online/invite/{slug}}
                            {--list : Liệt kê toàn bộ 114 mẫu thiệp kèm phong cách}
                            {--category= : Lọc theo phong cách (romantic, minimal, editorial, traditional)}
                            {--pilot : Cào lô thí điểm 12 mẫu (mỗi phong cách 3 mẫu xuất sắc nhất)}
                            {--all : Cào và chuẩn hóa toàn bộ kho mẫu}
                            {--limit= : Giới hạn số lượng mẫu cần cào}';

    /**
     * Mô tả của lệnh console.
     *
     * @var string
     */
    protected $description = 'Trích xuất, tải 100% tài nguyên nội bộ và chuẩn hóa mẫu thiệp từ songhy.online theo quy chuẩn Mây Hẹn';

    /**
     * Thực thi lệnh console.
     */
    public function handle(SonghyTemplateIngestionService $ingestionService): int
    {
        $this->info('================================================================');
        $this->info('✦ MÂY HẸN (MAYHEN.VN) — SONGHY TEMPLATE INGESTION ENGINE ✦');
        $this->info('================================================================');

        $slug = $this->argument('slug');
        $isList = $this->option('list');
        $isPilot = $this->option('pilot');
        $isAll = $this->option('all');
        $categoryFilter = $this->option('category');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        // 1. Nếu người dùng yêu cầu xem danh sách hoặc cào hàng loạt -> quét catalog
        if ($isList || $isPilot || $isAll || $categoryFilter || empty($slug)) {
            $this->info('Đang quét danh mục từ https://songhy.online/kho-mau-thiep...');
            try {
                $catalog = $ingestionService->discoverCatalog();
            } catch (\Throwable $e) {
                $this->error('Lỗi quét danh mục: '.$e->getMessage());

                return self::FAILURE;
            }

            $this->info('Đã tìm thấy tổng cộng: '.count($catalog).' mẫu thiệp.');

            if ($isList) {
                $headers = ['STT', 'Slug', 'Tên Mẫu Gốc', 'Phong Cách Mây Hẹn', 'Demo URL'];
                $rows = [];
                $i = 1;
                foreach ($catalog as $item) {
                    if ($categoryFilter && $item['category'] !== $categoryFilter) {
                        continue;
                    }
                    $rows[] = [$i++, $item['slug'], $item['name'], $item['category'], $item['demo_url']];
                }
                $this->table($headers, $rows);

                return self::SUCCESS;
            }

            // Xử lý Lô Thí Điểm (Pilot Batch 12 Mẫu: mỗi phong cách 3 mẫu)
            if ($isPilot) {
                $this->info('Bắt đầu xử lý Lô Thí Điểm 12 mẫu (3 mẫu cho mỗi phong cách)...');
                $pilotSlugs = [
                    // Romantic (3 mẫu)
                    'hoa-tulip-xanh-reu' => 'romantic',
                    'ngoc-trai-be' => 'romantic',
                    'mau-don-do' => 'romantic',
                    // Minimal (3 mẫu)
                    'minimalism-do-dam' => 'minimal',
                    'minimalism-nau' => 'minimal',
                    'giay-dap-noi-nau' => 'minimal',
                    // Editorial (3 mẫu)
                    'thuoc-phim-be' => 'editorial',
                    'chuyen-bay-nau' => 'editorial',
                    'songhy-v4-do-burgundy' => 'editorial',
                    // Traditional (3 mẫu)
                    'hac-sen-do' => 'traditional',
                    'song-hy-xanh' => 'traditional',
                    'song-long-do' => 'traditional',
                ];

                $count = 0;
                foreach ($pilotSlugs as $pilotSlug => $cat) {
                    $this->line("\n[{$count}/12] Đang xử lý: {$pilotSlug} ({$cat})...");
                    try {
                        $res = $ingestionService->ingestTemplate($pilotSlug, $cat);
                        $this->info("✓ {$res['message']}");
                        $count++;
                    } catch (\Throwable $e) {
                        $this->error("✗ Lỗi mẫu [{$pilotSlug}]: ".$e->getMessage());
                    }
                }

                $this->info("\nHoàn tất Lô Thí Điểm: Đã chuẩn hóa {$count}/12 mẫu thiệp thành công!");

                return self::SUCCESS;
            }

            // Xử lý cào hàng loạt
            $targets = $catalog;
            if ($categoryFilter) {
                $targets = array_filter($targets, fn ($t) => $t['category'] === $categoryFilter);
            }
            if ($limit) {
                $targets = array_slice($targets, 0, $limit);
            }

            $this->info('Bắt đầu cào và chuẩn hóa '.count($targets).' mẫu thiệp...');
            $successCount = 0;
            foreach ($targets as $idx => $item) {
                $num = $idx + 1;
                $this->line("\n[{$num}/".count($targets)."] Đang xử lý: {$item['slug']} ({$item['category']})...");
                try {
                    $res = $ingestionService->ingestTemplate($item['slug'], $item['category']);
                    $this->info("✓ {$res['message']}");
                    $successCount++;
                } catch (\Throwable $e) {
                    $this->error("✗ Lỗi mẫu [{$item['slug']}]: ".$e->getMessage());
                }
            }

            $this->info("\nHoàn tất xử lý: {$successCount}/".count($targets).' mẫu thiệp thành công!');

            return self::SUCCESS;
        }

        // 2. Nếu người dùng chỉ định cụ thể 1 slug
        $this->info("Bắt đầu trích xuất và chuẩn hóa mẫu đơn lẻ: {$slug}...");
        try {
            $res = $ingestionService->ingestTemplate($slug, $categoryFilter);
            $this->info("✓ {$res['message']}");
            $this->info("Phong cách phân bổ: {$res['category']}");
            $this->info("Số assets nội bộ đã tải về: {$res['assets_count']}");
        } catch (\Throwable $e) {
            $this->error('Lỗi trích xuất: '.$e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
