<?php

namespace App\Services;

use App\Models\Invitation;
use Illuminate\Http\UploadedFile;
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

    /**
     * Danh mục 8 bản nhạc Quiet Luxury chuẩn hôn lễ hoàng gia, acoustic & không lời (Royalty-free).
     *
     * @return array<int, array{id: string, title: string, artist: string, genre: string, duration: string, preview_url: string, description: string, tag: string}>
     */
    public static function getCuratedTracks(): array
    {
        return [
            [
                'id' => 'canon_in_d_acoustic',
                'title' => 'Canon in D (Acoustic & Cello Suite)',
                'artist' => 'Mây Hẹn Chamber Ensemble',
                'genre' => 'Classical / Acoustic',
                'duration' => '03:45',
                'preview_url' => asset('assets/client/audio/canon-in-d.mp3'),
                'description' => 'Giai điệu kinh điển ngân vang với tiếng đàn guitar mộc và cello êm đềm, tạo không gian trang trọng, sâu lắng.',
                'tag' => 'Kinh Điển',
            ],
            [
                'id' => 'river_flows_in_you_piano',
                'title' => 'River Flows in You (Soft Piano & Ambient)',
                'artist' => 'Yiruma Romance Tribute',
                'genre' => 'Piano Solo',
                'duration' => '03:10',
                'preview_url' => asset('assets/client/audio/river-flows-in-you.mp3'),
                'description' => 'Những phím dương cầm trong veo như dòng nước mùa thu, hoàn hảo cho những lời thề nguyện ngọt ngào.',
                'tag' => 'Lãng Mạn',
            ],
            [
                'id' => 'marry_me_acoustic',
                'title' => 'Marry Me (Gentle Fingerstyle Guitar)',
                'artist' => 'Warm Strings Duo',
                'genre' => 'Acoustic Fingerstyle',
                'duration' => '03:22',
                'preview_url' => asset('assets/client/audio/marry-me.mp3'),
                'description' => 'Bản phối acoustic mộc mạc, gần gũi, gợi lên những khoảnh khắc đầu tiên khi hai ánh mắt chạm nhau.',
                'tag' => 'Ấm Áp',
            ],
            [
                'id' => 'a_thousand_years_cello',
                'title' => 'A Thousand Years (Solo Cello & Harp)',
                'artist' => 'Imperial Chamber Guild',
                'genre' => 'Cello & Harp',
                'duration' => '04:15',
                'preview_url' => asset('assets/client/audio/a-thousand-years.mp3'),
                'description' => 'Giai điệu bất hủ về tình yêu ngàn năm với tiếng đàn hạc và cello da diết, sang trọng và quý phái.',
                'tag' => 'Hoàng Gia',
            ],
            [
                'id' => 'until_i_found_you_vintage',
                'title' => 'Until I Found You (Vintage 1950s Vinyl Vibe)',
                'artist' => 'Retro Velvet Symphony',
                'genre' => 'Vintage Romance',
                'duration' => '02:58',
                'preview_url' => asset('assets/client/audio/until-i-found-you.mp3'),
                'description' => 'Âm hưởng cổ điển thập niên 1950 kết hợp tiếng đĩa than xước nhẹ đặc trưng, tinh tế và đầy hoài niệm.',
                'tag' => 'Cổ Điển',
            ],
            [
                'id' => 'cant_help_falling_in_love_strings',
                'title' => "Can't Help Falling in Love (String Quartet)",
                'artist' => 'Vienna Strings Ensemble',
                'genre' => 'Orchestral Strings',
                'duration' => '03:30',
                'preview_url' => asset('assets/client/audio/cant-help-falling-in-love.mp3'),
                'description' => 'Tứ tấu đàn dây uyển chuyển, tôn vinh tình yêu định mệnh một cách thanh lịch và thuần khiết.',
                'tag' => 'Bất Hủ',
            ],
            [
                'id' => 'golden_hour_harp',
                'title' => 'Golden Hour (Harp & Ambient Strings)',
                'artist' => 'Celestial Sunset Orchestra',
                'genre' => 'Contemporary Classical',
                'duration' => '03:20',
                'preview_url' => asset('assets/client/audio/golden-hour.mp3'),
                'description' => 'Khúc nhạc hoàng hôn nhiệm màu như ánh nắng cuối chiều rọi lên tà váy cưới cô dâu.',
                'tag' => 'Hiện Đại',
            ],
            [
                'id' => 'beautiful_in_white_piano',
                'title' => 'Beautiful in White (Romantic Piano Sonata)',
                'artist' => 'Pure White Chamber',
                'genre' => 'Piano & Strings',
                'duration' => '03:52',
                'preview_url' => asset('assets/client/audio/beautiful-in-white.mp3'),
                'description' => 'Bản thánh ca tình yêu dành cho cô dâu bước vào lễ đường, lay động trái tim mọi quan khách.',
                'tag' => 'Thánh Ca',
            ],
        ];
    }

    /**
     * Phân giải cấu hình nhạc nền đầy đủ cho thiệp cưới.
     *
     * @return array{
     *     enabled: bool,
     *     track_id: string,
     *     title: string,
     *     artist: string,
     *     url: string,
     *     volume: float,
     *     autoplay: bool,
     *     loop: bool,
     *     fade_in: bool,
     *     fade_duration: int,
     *     pause_on_video: bool,
     *     floating_dock: bool,
     *     is_custom_upload: bool
     * }
     */
    public static function resolveMusicConfig(?Invitation $invitation = null, ?array $customConfig = null): array
    {
        $musicData = [];

        if ($invitation && is_array($invitation->custom_config) && isset($invitation->custom_config['music'])) {
            $musicData = $invitation->custom_config['music'];
        } elseif (is_array($customConfig) && isset($customConfig['music'])) {
            $musicData = $customConfig['music'];
        }

        $curated = collect(self::getCuratedTracks())->keyBy('id');
        $defaultTrack = $curated->first();

        $trackId = $musicData['track_id'] ?? ($defaultTrack['id'] ?? 'canon_in_d_acoustic');
        $isCustom = (bool) ($musicData['is_custom_upload'] ?? false);

        $selectedCurated = $curated->get($trackId);

        $title = $musicData['title'] ?? ($selectedCurated['title'] ?? 'Canon in D (Acoustic & Cello Suite)');
        $artist = $musicData['artist'] ?? ($selectedCurated['artist'] ?? 'Mây Hẹn Chamber Ensemble');
        $url = $musicData['url'] ?? ($selectedCurated['preview_url'] ?? asset('assets/client/audio/canon-in-d.mp3'));

        $volume = isset($musicData['volume']) ? (float) $musicData['volume'] : 0.7;
        if ($volume < 0.1) {
            $volume = 0.1;
        } elseif ($volume > 1.0) {
            $volume = 1.0;
        }

        return [
            'enabled' => array_key_exists('enabled', $musicData) ? (bool) $musicData['enabled'] : true,
            'track_id' => $trackId,
            'title' => $title,
            'artist' => $artist,
            'url' => $url,
            'volume' => round($volume, 2),
            'autoplay' => array_key_exists('autoplay', $musicData) ? (bool) $musicData['autoplay'] : true,
            'loop' => array_key_exists('loop', $musicData) ? (bool) $musicData['loop'] : true,
            'fade_in' => array_key_exists('fade_in', $musicData) ? (bool) $musicData['fade_in'] : true,
            'fade_duration' => (int) ($musicData['fade_duration'] ?? 2500), // ms
            'pause_on_video' => array_key_exists('pause_on_video', $musicData) ? (bool) $musicData['pause_on_video'] : true,
            'floating_dock' => array_key_exists('floating_dock', $musicData) ? (bool) $musicData['floating_dock'] : true,
            'is_custom_upload' => $isCustom,
        ];
    }

    /**
     * Tải lên tệp âm thanh riêng do cặp đôi cung cấp (mp3, m4a, wav, aac, ogg tối đa 15MB).
     */
    public static function uploadCustomAudio(Invitation $invitation, UploadedFile $file, ?string $customTitle = null, ?string $customArtist = null): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['mp3', 'm4a', 'wav', 'aac', 'ogg'];

        if (! in_array($extension, $allowedExtensions, true)) {
            throw new \InvalidArgumentException('Định dạng tệp âm thanh không được hỗ trợ. Vui lòng tải lên file MP3, M4A, WAV, AAC hoặc OGG.');
        }

        // Tối đa 15MB (15 * 1024 * 1024 bytes)
        if ($file->getSize() > 15 * 1024 * 1024) {
            throw new \InvalidArgumentException('Dung lượng tệp âm thanh vượt quá giới hạn 15MB. Vui lòng nén hoặc chọn tệp nhỏ hơn.');
        }

        $filename = 'wedding_audio_'.Str::random(12).'.'.$extension;
        $relativeDir = "invitations/{$invitation->id}/audio";
        $targetDir = storage_path("app/public/{$relativeDir}");

        if (! File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $file->move($targetDir, $filename);
        $publicUrl = asset("storage/{$relativeDir}/{$filename}");

        $title = ! empty(trim($customTitle ?? '')) ? trim($customTitle) : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $artist = ! empty(trim($customArtist ?? '')) ? trim($customArtist) : ($invitation->groom_name.' & '.$invitation->bride_name);

        $musicConfig = self::resolveMusicConfig($invitation);
        $musicConfig['enabled'] = true;
        $musicConfig['track_id'] = 'custom_'.Str::slug($title);
        $musicConfig['title'] = $title;
        $musicConfig['artist'] = $artist;
        $musicConfig['url'] = $publicUrl;
        $musicConfig['is_custom_upload'] = true;

        $customConfig = is_array($invitation->custom_config) ? $invitation->custom_config : [];
        $customConfig['music'] = $musicConfig;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        AuditLogger::log(
            'INVITATION_MUSIC_UPLOAD',
            'Invitation',
            $invitation->id,
            "Tải lên tệp âm thanh hôn lễ riêng thành công: {$title} ({$filename})",
            $musicConfig
        );

        return $musicConfig;
    }

    /**
     * Cập nhật cấu hình âm nhạc hôn lễ.
     */
    public static function updateMusicSettings(Invitation $invitation, array $data): array
    {
        $currentConfig = self::resolveMusicConfig($invitation);

        $updated = [
            'enabled' => isset($data['enabled']) ? filter_var($data['enabled'], FILTER_VALIDATE_BOOLEAN) : $currentConfig['enabled'],
            'track_id' => $data['track_id'] ?? $currentConfig['track_id'],
            'title' => $data['title'] ?? $currentConfig['title'],
            'artist' => $data['artist'] ?? $currentConfig['artist'],
            'url' => $data['url'] ?? $currentConfig['url'],
            'volume' => isset($data['volume']) ? max(0.1, min(1.0, (float) $data['volume'])) : $currentConfig['volume'],
            'autoplay' => isset($data['autoplay']) ? filter_var($data['autoplay'], FILTER_VALIDATE_BOOLEAN) : $currentConfig['autoplay'],
            'loop' => isset($data['loop']) ? filter_var($data['loop'], FILTER_VALIDATE_BOOLEAN) : $currentConfig['loop'],
            'fade_in' => isset($data['fade_in']) ? filter_var($data['fade_in'], FILTER_VALIDATE_BOOLEAN) : $currentConfig['fade_in'],
            'fade_duration' => isset($data['fade_duration']) ? (int) $data['fade_duration'] : $currentConfig['fade_duration'],
            'pause_on_video' => isset($data['pause_on_video']) ? filter_var($data['pause_on_video'], FILTER_VALIDATE_BOOLEAN) : $currentConfig['pause_on_video'],
            'floating_dock' => isset($data['floating_dock']) ? filter_var($data['floating_dock'], FILTER_VALIDATE_BOOLEAN) : $currentConfig['floating_dock'],
            'is_custom_upload' => isset($data['is_custom_upload']) ? filter_var($data['is_custom_upload'], FILTER_VALIDATE_BOOLEAN) : $currentConfig['is_custom_upload'],
        ];

        // Nếu người dùng chọn bài hát từ thư viện curated
        if (! $updated['is_custom_upload']) {
            $curated = collect(self::getCuratedTracks())->keyBy('id');
            if ($curated->has($updated['track_id'])) {
                $track = $curated->get($updated['track_id']);
                $updated['title'] = $track['title'];
                $updated['artist'] = $track['artist'];
                $updated['url'] = $track['preview_url'];
            }
        }

        $customConfig = is_array($invitation->custom_config) ? $invitation->custom_config : [];
        $customConfig['music'] = $updated;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        AuditLogger::log(
            'INVITATION_MUSIC_UPDATE',
            'Invitation',
            $invitation->id,
            "Cập nhật cấu hình âm nhạc thiệp cưới: {$updated['title']} - Âm lượng: {$updated['volume']}",
            $updated
        );

        return $updated;
    }
}
