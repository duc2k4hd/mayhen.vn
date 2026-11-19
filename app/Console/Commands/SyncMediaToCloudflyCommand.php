<?php

namespace App\Console\Commands;

use App\Services\CloudflyStorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncMediaToCloudflyCommand extends Command
{
    /**
     * Tên và chữ ký của lệnh Artisan.
     */
    protected $signature = 'mayhen:sync-to-cloudfly 
                            {--folder=weddings/photos : Thư mục cần đồng bộ (ví dụ: weddings/photos, paper_scans)}
                            {--dry-run : Chỉ quét kiểm tra, không upload thật}';

    /**
     * Mô tả lệnh.
     */
    protected $description = 'Đồng bộ ảnh cưới và ảnh thiệp từ bộ nhớ cục bộ lên Cloudfly Object Storage';

    public function handle(CloudflyStorageService $cloudfly): int
    {
        if (! $cloudfly->isConfigured()) {
            $this->error('Cloudfly Object Storage chưa được cấu hình đầy đủ trong file .env.');

            return self::FAILURE;
        }

        $folder = trim($this->option('folder'), '/');
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

        $this->info("Bắt đầu quét {$totalFiles} tệp trong [{$folder}] để đồng bộ lên Cloudfly (Bucket: {$cloudfly->getBucket()})...");

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
                $content = file_get_contents($file->getRealPath());
                $mimeType = mime_content_type($file->getRealPath()) ?: 'application/octet-stream';
                $uploaded = $cloudfly->putObject($relativePath, $content, $mimeType, 'public-read');

                if ($uploaded) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            } catch (\Throwable $e) {
                $failCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Hoàn tất đồng bộ: {$successCount} thành công, {$failCount} thất bại.");

        return self::SUCCESS;
    }
}
