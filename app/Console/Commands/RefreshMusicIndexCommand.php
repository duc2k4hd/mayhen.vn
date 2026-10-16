<?php

namespace App\Console\Commands;

use App\Services\WeddingMusicService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('musics:refresh')]
#[Description('Quét và cập nhật lại toàn bộ chỉ mục danh sách nhạc cưới vào storage/app/client_musics_index.json')]
class RefreshMusicIndexCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(WeddingMusicService $musicService): int
    {
        $this->info('🎵 Đang quét toàn bộ thư mục bài hát tại storage/app/public/client/musics ...');

        $catalog = $musicService->buildIndex();
        $total = count($catalog);

        $artists = array_unique(array_column($catalog, 'artist'));
        $totalArtists = count($artists);

        $this->info("✅ Đã cập nhật thành công {$total} bài hát ({$totalArtists} ca sĩ/bộ sưu tập) vào client_musics_index.json!");

        return Command::SUCCESS;
    }
}
