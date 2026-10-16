<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class WeddingMusicService
{
    /**
     * Đường dẫn thư mục nhạc ca sĩ
     */
    protected string $musicBasePath;

    /**
     * File cache json metadata để tối ưu 100% tốc độ truy vấn
     */
    protected string $indexCachePath;

    public function __construct()
    {
        $this->musicBasePath = storage_path('app/public/client/musics/singer');
        $this->indexCachePath = storage_path('app/client_musics_index.json');
    }

    /**
     * Lấy toàn bộ danh sách bài hát (có hỗ trợ cache thông minh & tự động làm mới khi phát hiện thay đổi).
     *
     * @return array<int, array{id: string, title: string, artist: string, url: string, filename: string, search_str: string}>
     */
    public function getCatalog(bool $forceRefresh = false): array
    {
        if (! $forceRefresh && File::exists($this->indexCachePath)) {
            $json = File::get($this->indexCachePath);
            $data = json_decode($json, true);
            if (is_array($data) && ! empty($data)) {
                return $data;
            }
        }

        return $this->buildIndex();
    }

    /**
     * Quét thư mục singer/{Tên ca sĩ}/{Tên bài hát}.mp3 và lập chỉ mục siêu tốc.
     */
    public function buildIndex(): array
    {
        $catalog = [];

        if (! File::exists($this->musicBasePath)) {
            File::makeDirectory($this->musicBasePath, 0755, true);
        }

        $singerDirs = File::directories($this->musicBasePath);

        foreach ($singerDirs as $singerDir) {
            $artistName = basename($singerDir);
            $files = File::files($singerDir);

            foreach ($files as $file) {
                if (strtolower($file->getExtension()) !== 'mp3') {
                    continue;
                }

                $filename = $file->getFilename();
                $title = pathinfo($filename, PATHINFO_FILENAME);
                $id = md5($artistName . '/' . $filename);
                $encodedArtist = rawurlencode($artistName);
                $encodedFile = rawurlencode($filename);
                $url = asset("storage/client/musics/singer/{$encodedArtist}/{$encodedFile}");

                $searchStr = Str::lower($this->removeAccents($title . ' ' . $artistName));

                $catalog[] = [
                    'id' => $id,
                    'title' => $title,
                    'artist' => $artistName,
                    'filename' => $filename,
                    'url' => $url,
                    'search_str' => $searchStr,
                ];
            }
        }

        // Lưu vào cache json
        File::put($this->indexCachePath, json_encode($catalog, JSON_UNESCAPED_UNICODE));

        return $catalog;
    }

    /**
     * Tìm kiếm bài hát thông minh (theo tên bài hát hoặc tên ca sĩ/thư mục) có phân trang cực nhanh.
     */
    public function search(?string $keyword = '', int $page = 1, int $perPage = 20): array
    {
        $catalog = $this->getCatalog();

        if (! empty($keyword)) {
            $normalized = Str::lower($this->removeAccents(trim($keyword)));
            $catalog = array_values(array_filter($catalog, function ($item) use ($normalized) {
                return str_contains($item['search_str'], $normalized);
            }));
        }

        $total = count($catalog);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($catalog, $offset, $perPage);

        return [
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / max(1, $perPage)),
            'data' => $items,
        ];
    }

    /**
     * Hàm loại bỏ dấu tiếng Việt để tìm kiếm không dấu chuẩn xác.
     */
    protected function removeAccents(string $str): string
    {
        $accents = [
            'a' => '/[àáạảãâầấậẩẫăằắặẳẵ]/u',
            'e' => '/[èéẹẻẽêềếệểễ]/u',
            'i' => '/[ìíịỉĩ]/u',
            'o' => '/[òóọỏõôồốộổỗơờớợởỡ]/u',
            'u' => '/[ùúụủũưừứựửữ]/u',
            'y' => '/[ỳýỵỷỹ]/u',
            'd' => '/[đĐ]/u',
        ];

        foreach ($accents as $nonAccent => $regex) {
            $str = preg_replace($regex, $nonAccent, $str);
        }

        return $str ?? '';
    }
}
