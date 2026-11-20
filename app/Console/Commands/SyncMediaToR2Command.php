<?php

namespace App\Console\Commands;

use App\Services\CloudflareR2StorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncMediaToR2Command extends Command
{
    /**
     * Tên và chữ ký của lệnh Artisan.
     */
    protected $signature = 'mayhen:sync-to-r2 
                            {--folder=weddings/photos : Thư mục cần đồng bộ (ví dụ: weddings/photos, paper_scans)}
                            {--dry-run : Chỉ quét kiểm tra, không upload thật}';

    /**
     * Mô tả lệnh.
     */
    protected $description = 'Đồng bộ ảnh cưới và ảnh thiệp từ bộ nhớ cục bộ lên Cloudflare R2 Object Storage';

    public function handle(CloudflareR2StorageService $r2): int
    {
        if (! $r2->isConfigured()) {
            $this->error('Cloudflare R2 Object Storage chưa được cấu hình đầy đủ trong file .env.');

            return self::FAILURE;
        }

        $folder = trim((string) $this->option('folder'), '/');
        $isDryRun = (bool) $this->option('dry-run');
        $sourceDir = storage_path('app/public/'.$folder);

        if (! File::isDirectory($sourceDir)) {
            $this->warn("Thư mục [{$sourceDir}] không tồn tại.");

            return self::SUCCESS;
        }

        $files = File::allFiles($sourceDir);
        $totalFiles = count($files);

        if ($totalFiles === 0) {
            $this->info("Không có tệp nào trong thư mục [{$folder}].");

            return self::SUCCESS;
        }

        $this->info("Bắt đầu quét {$totalFiles} tệp trong [{$folder}] để đồng bộ lên Cloudflare R2 (Bucket: {$r2->getBucket()})...");

        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        $successCount = 0;
        $failCount = 0;

        foreach ($files as $file) {
            $relativePath = $folder.'/'.str_replace('\\', '/', $file->getRelativePathname());

            if ($isDryRun) {
                $this->line(" [DRY-RUN] Sẽ upload: {$relativePath}");
                $bar->advance();

                continue;
            }

            try {
                $content = (string) file_get_contents($file->getRealPath());
                $mimeType = mime_content_type($file->getRealPath()) ?: 'application/octet-stream';
                $uploaded = $r2->putObject($relativePath, $content, $mimeType);

                if ($uploaded) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            } catch (\Throwable) {
                $failCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Hoàn tất đồng bộ lên Cloudflare R2! Thành công: {$successCount} | Thất bại: {$failCount}.");

        return self::SUCCESS;
    }
}
