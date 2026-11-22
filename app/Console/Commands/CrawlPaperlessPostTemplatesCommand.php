<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class CrawlPaperlessPostTemplatesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'events:crawl-paperlesspost
                            {--per-category=6 : Số mẫu thiệp tối đa cào cho mỗi nguồn danh mục Paperless Post (1-12)}
                            {--fresh : Xóa catalog cũ trước khi cào mới}';

    /**
     * @var string
     */
    protected $description = 'Cào mẫu thiệp thực tế từ Paperless Post, tải 100% tài nguyên phong bì & phông nền về local và chuyển hóa thành mẫu thiệp Tiếng Việt chuẩn Mây Hẹn (loại trừ Thiệp Cưới)';

    /**
     * Ánh xạ danh mục gốc Paperless Post -> 10 danh mục văn hóa & mùa lễ hội Việt Nam trên Mây Hẹn.
     *
     * @var array<int, array{source: string, category_slug: string, vi_prefix: string, card_mode: string, motif: string, seal_icon: string, badge: string, host: string, sub: string, msg: string, show_venue: bool, gift_qr: bool}>
     */
    protected array $sourceMap = [
        // 1. Tết Nguyên Đán & Tân Niên
        [
            'source' => 'new-year-cards',
            'category_slug' => 'tet-nguyen-dan',
            'vi_prefix' => 'Chúc Tết Tân Xuân',
            'card_mode' => 'greeting',
            'motif' => 'tet-blossom',
            'seal_icon' => '🧧',
            'badge' => 'CUNG CHÚC TÂN XUÂN • AN KHANG THỊNH VƯỢNG',
            'host' => 'Gia Đình Minh Tuấn & Thu Hà',
            'sub' => 'Kính chúc Năm Mới Vạn Sự Như Ý • Tài Lộc Hanh Thông',
            'msg' => 'Kính chúc Ông Bà, Cô Chú và Quý thân hữu năm mới Sức Khỏe Dồi Dào, Gia Đạo Bình An, Vạn Sự Cát Tường!',
            'show_venue' => false,
            'gift_qr' => true,
        ],
        [
            'source' => 'new-years-eve-invitations',
            'category_slug' => 'tet-nguyen-dan',
            'vi_prefix' => 'Tiệc Tân Niên Khai Xuân',
            'card_mode' => 'invitation',
            'motif' => 'tet-swallows',
            'seal_icon' => '🌸',
            'badge' => 'TIỆC TÂN NIÊN KHAI XUÂN ĐẦU NĂM',
            'host' => 'Họp Mặt Khai Xuân Đầu Năm',
            'sub' => 'Nâng Ly Chúc Mừng Năm Mới Đại Thắng • Thuận Buồm Xuôi Gió',
            'msg' => 'Trân trọng kính mời Quý vị tới dự Tiệc Tân Niên Khai Xuân và nhận lộc may mắn đầu năm.',
            'show_venue' => true,
            'gift_qr' => false,
        ],

        // 2. Tết Trung Thu & Đêm Hội Trăng Rằm
        [
            'source' => 'fall-entertaining-invitations',
            'category_slug' => 'tet-trung-thu',
            'vi_prefix' => 'Trăng Rằm Đoàn Viên',
            'card_mode' => 'greeting',
            'motif' => 'moon-lotus',
            'seal_icon' => '🥮',
            'badge' => 'TẾT TRUNG THU • NGUYỆT VIÊN HOA HẢO',
            'host' => 'Tết Đoàn Viên Tháng Tám',
            'sub' => 'Kính chúc Quý Đối Tác & Gia Đình mùa Trăng Rằm Viên Mãn',
            'msg' => 'Gửi tới Quý vị lời chúc Tết Trung Thu ấm áp, sum vầy hạnh phúc dưới ánh trăng rằm tròn đầy.',
            'show_venue' => false,
            'gift_qr' => false,
        ],
        [
            'source' => 'tea-party-invitations',
            'category_slug' => 'tet-trung-thu',
            'vi_prefix' => 'Tiệc Trà Thưởng Nguyệt',
            'card_mode' => 'invitation',
            'motif' => 'moon-lantern',
            'seal_icon' => '🏮',
            'badge' => 'TIỆC TRÀ THƯỞNG NGUYỆT • RẰM THÁNG TÁM',
            'host' => 'Đêm Hội Trăng Rằm & Tiệc Trà',
            'sub' => 'Thưởng Trà Sen • Nếm Bánh Trung Thu • Rước Đèn Phá Cỗ',
            'msg' => 'Thân mời bạn ghé thưởng chén trà sen ấm, nếm bánh trung thu và ngắm trăng rằm đoàn viên.',
            'show_venue' => true,
            'gift_qr' => false,
        ],

        // 3. Giáng Sinh & Năm Mới
        [
            'source' => 'christmas-party-invitations',
            'category_slug' => 'giang-sinh-nam-moi',
            'vi_prefix' => 'Tiệc Giáng Sinh',
            'card_mode' => 'invitation',
            'motif' => 'christmas-tree',
            'seal_icon' => '🎄',
            'badge' => 'MERRY CHRISTMAS • ĐÊM TIỆC GIÁNG SINH',
            'host' => 'Đêm Tiệc Giáng Sinh An Lành',
            'sub' => 'Christmas Cheer • Holiday Cocktail & Dinner Party',
            'msg' => 'Cùng quây quần bên cây thông ấm áp, nâng ly rượu vang nóng và đón đêm Giáng Sinh an lành.',
            'show_venue' => true,
            'gift_qr' => false,
        ],
        [
            'source' => 'christmas-cards',
            'category_slug' => 'giang-sinh-nam-moi',
            'vi_prefix' => 'Chúc Mừng Giáng Sinh',
            'card_mode' => 'greeting',
            'motif' => 'holly-wreath',
            'seal_icon' => '🎀',
            'badge' => 'MERRY CHRISTMAS & HAPPY NEW YEAR',
            'host' => 'Giáng Sinh An Lành & Ấm Áp',
            'sub' => 'Gửi Trọn Yêu Thương Mùa Lễ Hội Cuối Năm',
            'msg' => 'Chúc bạn và gia đình một mùa Giáng Sinh bình an, ngập tràn tiếng cười và năm mới rực rỡ.',
            'show_venue' => false,
            'gift_qr' => false,
        ],

        // 4. Đầy Tháng & Thôi Nôi Em Bé
        [
            'source' => '100-day-celebration-invitations',
            'category_slug' => 'day-thang-thoi-noi',
            'vi_prefix' => 'Lễ Đầy Tháng Bé Yêu',
            'card_mode' => 'invitation',
            'motif' => 'baby-cradle',
            'seal_icon' => '👶',
            'badge' => 'LỄ MỪNG ĐẦY THÁNG THIÊN THẦN NHỎ',
            'host' => 'Bé Nguyễn Minh Khôi (Bé Bơ)',
            'sub' => 'Mừng Con Yêu Tròn Tháng Bình An • Con Của Ba Tuấn & Mẹ Hà',
            'msg' => 'Trân trọng kính mời Ông Bà, Cô Chú tới dự bữa cơm thân mật mừng lễ Đầy Tháng của bé.',
            'show_venue' => true,
            'gift_qr' => true,
        ],
        [
            'source' => 'first-birthday-invitations',
            'category_slug' => 'day-thang-thoi-noi',
            'vi_prefix' => 'Tiệc Thôi Nôi 1 Tuổi',
            'card_mode' => 'invitation',
            'motif' => 'woodland-animals',
            'seal_icon' => '🦌',
            'badge' => 'HAPPY 1ST BIRTHDAY • TIỆC MỪNG THÔI NÔI',
            'host' => 'Bé Lê Tuệ Nhi (Bé Cam)',
            'sub' => 'Mừng Thiên Thần Nhỏ Tròn 1 Tuổi • Bốc Đồ Chọn Nghề',
            'msg' => 'Cùng gia đình đón mừng cột mốc tròn 1 tuổi đầu đời và thổi cây nến đầu tiên của bé yêu.',
            'show_venue' => true,
            'gift_qr' => true,
        ],

        // 5. Sinh Nhật Trẻ Em
        [
            'source' => 'kids-birthday-invitations',
            'category_slug' => 'sinh-nhat-tre-em',
            'vi_prefix' => 'Sinh Nhật Bé Yêu',
            'card_mode' => 'invitation',
            'motif' => 'kids-pamper-party',
            'seal_icon' => '🎈',
            'badge' => 'KIDS’ BIRTHDAY PARTY • MỪNG TUỔI MỚI',
            'host' => 'Sinh Nhật 6 Tuổi Bé Khánh Ngân',
            'sub' => 'Bữa Tiệc Tuổi Thơ Ngọt Ngào Cùng Các Bạn Nhỏ',
            'msg' => 'Thân mời Cô Chú và các bạn nhỏ tới dự tiệc sinh nhật, thổi nến và chơi trò chơi vui nhộn!',
            'show_venue' => true,
            'gift_qr' => false,
        ],

        // 6. Sinh Nhật Người Lớn & Mừng Thọ
        [
            'source' => 'adult-birthday-invitations',
            'category_slug' => 'sinh-nhat-nguoi-lon',
            'vi_prefix' => 'Tiệc Sinh Nhật Sang Trọng',
            'card_mode' => 'invitation',
            'motif' => 'adult-martini-editorial',
            'seal_icon' => '🍸',
            'badge' => 'BIRTHDAY DINNER & COCKTAIL NIGHT',
            'host' => 'Trần Thu Hà • Turning 30!',
            'sub' => 'Đêm Tiệc Cocktail & Bữa Tối Ấm Cúng Bên Hội Bạn Thân',
            'msg' => 'Hẹn gặp những người bạn thương quý trong đêm tiệc sinh nhật đáng nhớ này nhé!',
            'show_venue' => true,
            'gift_qr' => false,
        ],
        [
            'source' => '80th-birthday-invitations',
            'category_slug' => 'sinh-nhat-nguoi-lon',
            'vi_prefix' => 'Đại Lễ Mừng Thọ',
            'card_mode' => 'invitation',
            'motif' => 'elder-longevity-crane',
            'seal_icon' => '🍑',
            'badge' => 'ĐẠI LỄ MỪNG THỌ • PHÚC NHƯ ĐÔNG HẢI',
            'host' => 'Mừng Thọ Cụ Ông Nguyễn Văn Phúc',
            'sub' => 'Đại Lễ Mừng Thọ 80 Tuổi • Con Cháu Thành Tâm Báo Hiếu',
            'msg' => 'Trân trọng kính mời Quý khách tới chung vui cùng đại gia đình trong Đại lễ Mừng Thọ Ông Bà.',
            'show_venue' => true,
            'gift_qr' => true,
        ],

        // 7. Tân Gia & Nhà Mới
        [
            'source' => 'housewarming-party-invitations',
            'category_slug' => 'tan-gia-nha-moi',
            'vi_prefix' => 'Tiệc Mừng Tân Gia',
            'card_mode' => 'invitation',
            'motif' => 'house-key',
            'seal_icon' => '🏡',
            'badge' => 'LỄ MỪNG TÂN GIA • AN CƯ LẠC NGHIỆP',
            'host' => 'Gia Đình Minh Tuấn & Thu Hà',
            'sub' => 'Housewarming Party • Mừng Hoàn Thiện Tổ Ấm Mới',
            'msg' => 'Hân hạnh đón tiếp Quý vị tới tham quan ngôi nhà mới và dùng bữa cơm thân mật cùng gia đình.',
            'show_venue' => true,
            'gift_qr' => true,
        ],

        // 8. Tất Niên Công Ty (YEP) & Khai Trương
        [
            'source' => 'business-holiday-invitations',
            'category_slug' => 'tat-nien-khai-truong',
            'vi_prefix' => 'Gala Tất Niên YEP',
            'card_mode' => 'invitation',
            'motif' => 'corp-gala-trophy',
            'seal_icon' => '🏆',
            'badge' => 'YEAR END PARTY & GALA DINNER',
            'host' => 'Tập Đoàn Công Nghệ Mây Hẹn',
            'sub' => 'Gala Tất Niên Cuối Năm • Vinh Danh & Bứt Phá Tương Lai',
            'msg' => 'Trân trọng kính mời Quý Đối Tác và CBNV tới tham dự đêm Gala Tất Niên tổng kết cuối năm.',
            'show_venue' => true,
            'gift_qr' => false,
        ],
        [
            'source' => 'launch-party-invitations',
            'category_slug' => 'tat-nien-khai-truong',
            'vi_prefix' => 'Lễ Khai Trương Hồng Phát',
            'card_mode' => 'invitation',
            'motif' => 'corp-gala-trophy',
            'seal_icon' => '🎊',
            'badge' => 'GRAND OPENING • LỄ KHAI TRƯƠNG HỒNG PHÁT',
            'host' => 'Showroom & Atelier Mây Hẹn',
            'sub' => 'Lễ Cắt Băng Khai Trương Cơ Sở Mới • Đại Cát Đại Lợi',
            'msg' => 'Trân trọng kính mời Quý khách và Đối tác tới dự lễ cắt băng khai trương và tiệc trà chúc mừng.',
            'show_venue' => true,
            'gift_qr' => true,
        ],

        // 9. Lễ Tốt Nghiệp & Vinh Danh
        [
            'source' => 'graduation-party-invitations',
            'category_slug' => 'tot-nghiep-thanh-tuu',
            'vi_prefix' => 'Lễ Tốt Nghiệp Đại Học',
            'card_mode' => 'invitation',
            'motif' => 'graduation-cap',
            'seal_icon' => '🎓',
            'badge' => 'GRADUATION CEREMONY • LỄ TỐT NGHIỆP',
            'host' => 'Tân Cử Nhân Nguyễn Minh Anh',
            'sub' => 'Lễ Trao Bằng Tốt Nghiệp & Chụp Ảnh Kỷ Niệm Thanh Xuân',
            'msg' => 'Thân mời gia đình và những người bạn yêu quý tới dự lễ trao bằng tốt nghiệp cùng mình!',
            'show_venue' => true,
            'gift_qr' => false,
        ],

        // 10. Ngày Lễ 8/3, 20/10, 20/11 & Kỷ Niệm
        [
            'source' => 'teacher-appreciation',
            'category_slug' => 'ngay-le-chuc-mung',
            'vi_prefix' => 'Tri Ân Thầy Cô 20/11',
            'card_mode' => 'greeting',
            'motif' => 'floral-bouquet',
            'seal_icon' => '📖',
            'badge' => 'CHÀO MỪNG NGÀY NHÀ GIÁO VIỆT NAM 20/11',
            'host' => 'Kính Tặng Thầy Cô Đáng Kính',
            'sub' => 'Lời Tri Ân Sâu Sắc Từ Tập Thể Học Trò',
            'msg' => 'Kính chúc Thầy Cô luôn mạnh khỏe, bình an và giữ mãi ngọn lửa đam mê trên bục giảng.',
            'show_venue' => false,
            'gift_qr' => false,
        ],
        [
            'source' => 'anniversary-party-invitations',
            'category_slug' => 'ngay-le-chuc-mung',
            'vi_prefix' => 'Kỷ Niệm Ngày Cưới',
            'card_mode' => 'invitation',
            'motif' => 'rings-anniversary',
            'seal_icon' => '💍',
            'badge' => 'WEDDING ANNIVERSARY • KỶ NIỆM NGÀY CHUNG ĐÔI',
            'host' => 'Minh Tuấn & Thu Hà',
            'sub' => 'Kỷ Niệm 10 Năm Hành Trình Yêu Thương Vàng Son',
            'msg' => 'Trân trọng mời những người thân thương nhất tới dự bữa tối kỷ niệm ngày cưới ấm cúng.',
            'show_venue' => true,
            'gift_qr' => false,
        ],
        [
            'source' => 'congratulations-cards',
            'category_slug' => 'ngay-le-chuc-mung',
            'vi_prefix' => 'Chúc Mừng 8/3 & 20/10',
            'card_mode' => 'greeting',
            'motif' => 'floral-bouquet',
            'seal_icon' => '💐',
            'badge' => 'HAPPY WOMEN’S DAY • TÔN VINH PHÁI ĐẸP',
            'host' => 'Gửi Người Phụ Nữ Tuyệt Vời',
            'sub' => 'Chúc Mừng Ngày Quốc Tế Phụ Nữ 8/3 & 20/10',
            'msg' => 'Chúc bạn luôn xinh đẹp rạng rỡ, kiêu hãnh và ngập tràn hạnh phúc mỗi ngày!',
            'show_venue' => false,
            'gift_qr' => true,
        ],
    ];

    public function handle(): int
    {
        $perCategory = max(1, min(12, (int) $this->option('per-category')));
        $catalogPath = storage_path('app/event-templates-catalog.json');
        $assetsBaseDir = public_path('assets/client/events/pp');

        if ($this->option('fresh') && File::exists($catalogPath)) {
            File::delete($catalogPath);
        }

        File::ensureDirectoryExists($assetsBaseDir);

        $existingCatalog = [];
        if (File::exists($catalogPath)) {
            $decoded = json_decode((string) File::get($catalogPath), true);
            if (is_array($decoded)) {
                $existingCatalog = $decoded;
            }
        }

        $importedCount = 0;
        $seenPackageIds = [];
        foreach ($existingCatalog as $item) {
            if (! empty($item['source_package_id'])) {
                $seenPackageIds[(string) $item['source_package_id']] = true;
            }
        }

        foreach ($this->sourceMap as $src) {
            $url = 'https://www.paperlesspost.com/cards/category/'.$src['source'];
            $this->info("→ Đang cào danh mục [{$src['source']}] -> [{$src['category_slug']}]...");

            $html = $this->fetchUrl($url);
            if ($html === '') {
                $this->warn("  ! Không tải được trang {$url}");

                continue;
            }

            $edges = $this->extractCardEdges($html);
            if (empty($edges)) {
                $this->warn("  ! Không tìm thấy dữ liệu mẫu thiệp trong {$src['source']}");

                continue;
            }

            $taken = 0;
            foreach ($edges as $edge) {
                if ($taken >= $perCategory) {
                    break;
                }

                $comp = $edge['node']['composition'] ?? null;
                if (! is_array($comp)) {
                    continue;
                }

                $packageId = (string) ($comp['packageId'] ?? '');
                if ($packageId === '' || isset($seenPackageIds[$packageId])) {
                    continue;
                }

                $rawTitle = trim((string) ($comp['family']['meta']['label'] ?? ''));
                if ($rawTitle === '' || str_contains(mb_strtolower($rawTitle), 'upload your own')) {
                    continue;
                }

                $slug = 'pp-'.$src['category_slug'].'-'.Str::slug($rawTitle).'-'.$packageId;
                $slug = Str::limit($slug, 90, '');

                $envUrl = $this->pickArtifactUrl($comp['artifacts']['envelopeBack'] ?? null, ['pngMedium', 'jpegMedium', 'pngSmall']);
                $bgUrl = $this->pickArtifactUrl($comp['artifacts']['backdropDesktop'] ?? null, ['jpegMedium', 'jpegSmall']);

                if ($envUrl === '') {
                    continue;
                }

                $tplAssetDir = $assetsBaseDir.DIRECTORY_SEPARATOR.$slug;
                File::ensureDirectoryExists($tplAssetDir);

                $localEnvRel = null;
                $envBytes = $this->fetchUrl($envUrl);
                if (strlen($envBytes) > 1000) {
                    $ext = str_contains($envUrl, 'png') ? 'png' : 'jpg';
                    File::put($tplAssetDir.DIRECTORY_SEPARATOR.'envelope-back.'.$ext, $envBytes);
                    $localEnvRel = 'assets/client/events/pp/'.$slug.'/envelope-back.'.$ext;
                }

                $localBgRel = null;
                if ($bgUrl !== '') {
                    $bgBytes = $this->fetchUrl($bgUrl);
                    if (strlen($bgBytes) > 1000) {
                        File::put($tplAssetDir.DIRECTORY_SEPARATOR.'backdrop.jpg', $bgBytes);
                        $localBgRel = 'assets/client/events/pp/'.$slug.'/backdrop.jpg';
                    }
                }

                if (! $localEnvRel) {
                    continue;
                }

                $dominantColor = $this->mapColorToMayHen($comp['colors'] ?? []);
                $cardBorder = $this->mapShapeToMayHenBorder((string) ($comp['shape']['name'] ?? 'tall'), $src['category_slug']);

                $existingCatalog[$slug] = [
                    'slug' => $slug,
                    'source_package_id' => $packageId,
                    'source_category' => $src['source'],
                    'category_slug' => $src['category_slug'],
                    'name' => $src['vi_prefix'].' — '.$rawTitle,
                    'tagline' => 'Thiết kế 1 trang chuyển hóa từ bộ sưu tập '.$rawTitle.' với phong bì mở nắp 3D và phông nền thực tế chuẩn Mây Hẹn',
                    'card_mode' => $src['card_mode'],
                    'default_palette' => $dominantColor['palette'],
                    'envelope_color' => $dominantColor['envelope_color'],
                    'envelope_liner' => $dominantColor['envelope_liner'],
                    'card_border' => $cardBorder,
                    'artwork_motif' => $src['motif'],
                    'backdrop' => $dominantColor['backdrop'],
                    'envelope_back_img' => $localEnvRel,
                    'backdrop_img' => $localBgRel,
                    'seal_icon' => $src['seal_icon'],
                    'seal_label' => 'Chạm Mở '.$src['vi_prefix'],
                    'host_name' => $src['host'],
                    'sub_host_name' => $src['sub'],
                    'event_badge' => $src['badge'],
                    'invitation_message' => $src['msg'],
                    'show_datetime_venue' => $src['show_venue'],
                    'enable_gift_qr' => $src['gift_qr'],
                ];

                $seenPackageIds[$packageId] = true;
                $taken++;
                $importedCount++;
            }

            $this->line("  ✓ Đã nhập {$taken} mẫu từ {$src['source']}");
        }

        File::put(
            $catalogPath,
            json_encode($existingCatalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $total = count($existingCatalog);
        $this->info("Hoàn tất! Đã cào mới {$importedCount} mẫu (Tổng kho chuyển hóa từ Paperless Post: {$total} mẫu) và lưu tại storage/app/event-templates-catalog.json.");

        return self::SUCCESS;
    }

    protected function fetchUrl(string $url): string
    {
        $process = new Process([
            'curl',
            '-sL',
            '--max-time',
            '20',
            '-A',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            $url,
        ]);
        $process->setTimeout(25);
        $process->run();

        if ($process->isSuccessful()) {
            return $process->getOutput();
        }

        return '';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function extractCardEdges(string $html): array
    {
        if (! preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $html, $m)) {
            return [];
        }

        $data = json_decode($m[1], true);
        $rq = $data['props']['pageProps']['_apolloState']['ROOT_QUERY'] ?? [];
        if (! is_array($rq)) {
            return [];
        }

        foreach ($rq as $k => $v) {
            if (is_string($k) && str_contains($k, '"productType":"card"') && is_array($v) && ! empty($v['edges'])) {
                return $v['edges'];
            }
        }

        return [];
    }

    /**
     * @param  array<string, mixed>|null  $artifact
     * @param  array<int, string>  $preferredKeys
     */
    protected function pickArtifactUrl(?array $artifact, array $preferredKeys): string
    {
        if (! is_array($artifact) || empty($artifact['asset']) || ! is_array($artifact['asset'])) {
            return '';
        }

        foreach ($artifact['asset'] as $rk => $rv) {
            if (is_string($rk) && str_starts_with($rk, 'renditions') && is_array($rv)) {
                foreach ($preferredKeys as $key) {
                    if (! empty($rv[$key]['url'])) {
                        return (string) $rv[$key]['url'];
                    }
                }
            }
        }

        return '';
    }

    /**
     * @param  array<int, array<string, mixed>>  $colors
     * @return array{palette: string, envelope_color: string, envelope_liner: string, backdrop: string}
     */
    protected function mapColorToMayHen(array $colors): array
    {
        $names = array_map(
            static fn (array $c): string => mb_strtolower((string) ($c['name'] ?? '')),
            $colors
        );

        if (in_array('green', $names, true)) {
            return ['palette' => 'sage-green', 'envelope_color' => 'forest-green', 'envelope_liner' => 'botanical-holly', 'backdrop' => 'silk-cream'];
        }
        if (in_array('red', $names, true) || in_array('burgundy', $names, true)) {
            return ['palette' => 'ruby', 'envelope_color' => 'crimson-red', 'envelope_liner' => 'gold-foil', 'backdrop' => 'silk-cream'];
        }
        if (in_array('pink', $names, true) || in_array('blush', $names, true)) {
            return ['palette' => 'dusty-rose', 'envelope_color' => 'blush-pink', 'envelope_liner' => 'berry-dots', 'backdrop' => 'blush-dream'];
        }
        if (in_array('purple', $names, true) || in_array('lavender', $names, true)) {
            return ['palette' => 'dusty-rose', 'envelope_color' => 'royal-violet', 'envelope_liner' => 'blush-stripes', 'backdrop' => 'blush-dream'];
        }
        if (in_array('blue', $names, true) || in_array('navy', $names, true) || in_array('black', $names, true)) {
            return ['palette' => 'royal-navy', 'envelope_color' => 'midnight-navy', 'envelope_liner' => 'starry-night', 'backdrop' => 'moonlight-navy'];
        }
        if (in_array('gold', $names, true) || in_array('yellow', $names, true) || in_array('orange', $names, true)) {
            return ['palette' => 'warm-amber', 'envelope_color' => 'imperial-gold', 'envelope_liner' => 'gold-foil', 'backdrop' => 'silk-cream'];
        }

        return ['palette' => 'warm-amber', 'envelope_color' => 'ivory-cream', 'envelope_liner' => 'gold-foil', 'backdrop' => 'silk-cream'];
    }

    protected function mapShapeToMayHenBorder(string $shape, string $categorySlug): string
    {
        return match (true) {
            str_contains($shape, 'oval') || str_contains($shape, 'scallop') => 'scalloped-wave',
            str_contains($shape, 'arch') => 'lantern-arch',
            $categorySlug === 'tet-nguyen-dan' => 'lunar-brocade',
            $categorySlug === 'giang-sinh-nam-moi' => 'candy-stripe',
            $categorySlug === 'day-thang-thoi-noi' || $categorySlug === 'sinh-nhat-tre-em' => 'scalloped-wave',
            $categorySlug === 'sinh-nhat-nguoi-lon' => 'minimal-editorial',
            default => 'gold-double',
        };
    }
}
