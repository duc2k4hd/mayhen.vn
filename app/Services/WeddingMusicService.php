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
     * Đường dẫn thư mục gốc nhạc client
     */
    protected string $musicRootPath;

    protected string $indexCachePath;

    public function __construct()
    {
        $this->musicRootPath = storage_path('app/public/client/musics');
        $this->musicBasePath = storage_path('app/public/client/musics/singer');
        $this->indexCachePath = storage_path('app/client_musics_index.json');
    }

    /**
     * Lấy toàn bộ danh sách bài hát (có cơ chế tự động phát hiện thay đổi file để làm mới cache siêu tốc).
     *
     * @return array<int, array{id: string, title: string, artist: string, url: string, filename: string, search_str: string}>
     */
    public function getCatalog(bool $forceRefresh = false): array
    {
        if (! $forceRefresh && ! $this->shouldRefreshIndex()) {
            $json = File::get($this->indexCachePath);
            $data = json_decode($json, true);
            if (is_array($data) && ! empty($data)) {
                return $data;
            }
        }

        return $this->buildIndex();
    }

    /**
     * Kiểm tra xem thư mục nhạc có bài hát hoặc thư mục mới hơn file cache index không.
     * Tận dụng mtime của inode (thời gian xử lý < 1ms, không load lại database hay ổ đĩa nặng).
     */
    public function shouldRefreshIndex(): bool
    {
        if (! File::exists($this->indexCachePath)) {
            return true;
        }

        $cacheMtime = File::lastModified($this->indexCachePath);

        // Kiểm tra thư mục gốc musics
        if (File::exists($this->musicRootPath) && File::lastModified($this->musicRootPath) > $cacheMtime) {
            return true;
        }

        // Kiểm tra thư mục ca sĩ singer
        if (File::exists($this->musicBasePath)) {
            if (File::lastModified($this->musicBasePath) > $cacheMtime) {
                return true;
            }

            // Kiểm tra từng thư mục ca sĩ con (nếu có file mới thêm vào, mtime thư mục ca sĩ đó sẽ thay đổi)
            $singerDirs = File::directories($this->musicBasePath);
            foreach ($singerDirs as $singerDir) {
                if (File::lastModified($singerDir) > $cacheMtime) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Quét thư mục musics/ và musics/singer/{Tên ca sĩ}/{Tên bài hát}.mp3 và lập chỉ mục siêu tốc.
     */
    public function buildIndex(): array
    {
        $catalog = [];

        if (! File::exists($this->musicBasePath)) {
            File::makeDirectory($this->musicBasePath, 0755, true);
        }

        // 1. Quét các file MP3 nằm trực tiếp trong thư mục musics/ (nếu có)
        if (File::exists($this->musicRootPath)) {
            $rootFiles = File::files($this->musicRootPath);
            foreach ($rootFiles as $file) {
                if (strtolower($file->getExtension()) !== 'mp3') {
                    continue;
                }

                $filename = $file->getFilename();
                $title = pathinfo($filename, PATHINFO_FILENAME);
                $artistName = 'Mây Hẹn Collection';

                // Nếu tên file có dạng "Ca sĩ - Tên bài hát"
                if (str_contains($title, ' - ')) {
                    [$guessedArtist, $guessedTitle] = explode(' - ', $title, 2);
                    if (! empty(trim($guessedArtist)) && ! empty(trim($guessedTitle))) {
                        $artistName = trim($guessedArtist);
                        $title = trim($guessedTitle);
                    }
                }

                $id = md5('root/'.$filename);
                $encodedFile = rawurlencode($filename);
                $url = asset("storage/client/musics/{$encodedFile}");
                $searchStr = Str::lower($this->removeAccents($title.' '.$artistName));

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

        // 2. Quét các file MP3 trong thư mục con singer/{Tên ca sĩ}/
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
                $id = md5($artistName.'/'.$filename);
                $encodedArtist = rawurlencode($artistName);
                $encodedFile = rawurlencode($filename);
                $url = asset("storage/client/musics/singer/{$encodedArtist}/{$encodedFile}");

                $searchStr = Str::lower($this->removeAccents($title.' '.$artistName));

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

        // Sắp xếp bài hát theo thứ tự bảng chữ cái tên ca sĩ và tiêu đề
        usort($catalog, function ($a, $b) {
            $artistCmp = strcasecmp($a['artist'], $b['artist']);

            return $artistCmp !== 0 ? $artistCmp : strcasecmp($a['title'], $b['title']);
        });

        // Lưu vào cache json với định dạng Unicode chuẩn
        File::put($this->indexCachePath, json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return $catalog;
    }

    /**
     * Tìm kiếm bài hát thông minh (theo tên bài hát hoặc tên ca sĩ/thư mục) có phân trang cực nhanh.
     */
    public function search(?string $keyword = '', int $page = 1, int $perPage = 20, bool $forceRefresh = false): array
    {
        $catalog = $this->getCatalog($forceRefresh);

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
