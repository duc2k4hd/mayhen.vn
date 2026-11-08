<?php

namespace App\Services;

use InvalidArgumentException;

class CuratedInvitationService
{
    /**
     * Danh mục 4 phong cách định sẵn của Mây Hẹn (Mục 2).
     * Tuyệt đối không cho người dùng nhập phong cách tùy ý ngoài danh mục này.
     *
     * @var array<string, array<string, mixed>>
     */
    protected const STYLES = [
        'romantic' => [
            'id' => 'romantic',
            'name' => 'Lãng Mạn & Bay Bổng',
            'english_name' => 'Romantic & Ethereal',
            'description' => 'Đường nét mềm mại, font chữ serif uyển chuyển, hoa văn tinh tế gợi nhớ những câu chuyện tình thơ mộng.',
            'badge' => 'Phổ Biến Nhất',
            'recommended_palette' => 'dusty-rose',
            'default_variants' => [
                'hero' => 'variant_a',
                'story' => 'variant_a',
                'parents' => 'variant_a',
                'countdown' => 'variant_a',
                'events' => 'variant_b',
                'timeline' => 'variant_a',
                'dresscode' => 'variant_a',
            ],
        ],
        'minimal' => [
            'id' => 'minimal',
            'name' => 'Tối Giản & Hiện Đại',
            'english_name' => 'Minimal & Contemporary',
            'description' => 'Khoảng trắng thoáng đãng, bố cục typography sắc sảo, tôn vinh vẻ đẹp nguyên bản và thanh lịch vượt thời gian.',
            'badge' => 'Thanh Lịch',
            'recommended_palette' => 'sage-green',
            'default_variants' => [
                'hero' => 'variant_b',
                'story' => 'variant_b',
                'parents' => 'variant_b',
                'countdown' => 'variant_b',
                'events' => 'variant_a',
                'timeline' => 'variant_b',
                'dresscode' => 'variant_b',
            ],
        ],
        'editorial' => [
            'id' => 'editorial',
            'name' => 'Tạp Chí & Thời Thượng',
            'english_name' => 'Editorial & Vogue',
            'description' => 'Bố cục phá cách lấy cảm hứng từ trang bìa tạp chí cao cấp, tiêu đề lớn phóng khoáng và điểm nhấn nghệ thuật.',
            'badge' => 'Độc Đáo',
            'recommended_palette' => 'warm-amber',
            'default_variants' => [
                'hero' => 'variant_c',
                'story' => 'variant_c',
                'parents' => 'variant_b',
                'countdown' => 'variant_c',
                'events' => 'variant_c',
                'timeline' => 'variant_a',
                'dresscode' => 'variant_c',
            ],
        ],
        'traditional' => [
            'id' => 'traditional',
            'name' => 'Truyền Thống & Chuẩn Mực',
            'english_name' => 'Heritage & Classic',
            'description' => 'Trang trọng, tôn nghiêm theo chuẩn mực lễ nghi cưới hỏi hai họ, họa tiết Song Hỷ và tem niêm phong sáp cổ điển.',
            'badge' => 'Trang Trọng',
            'recommended_palette' => 'ruby',
            'default_variants' => [
                'hero' => 'variant_a',
                'story' => 'variant_b',
                'parents' => 'variant_c',
                'countdown' => 'variant_a',
                'events' => 'variant_b',
                'timeline' => 'variant_c',
                'dresscode' => 'variant_a',
            ],
        ],
    ];

    /**
     * Danh mục 5 bảng màu Quiet Luxury chuẩn Mây Hẹn (Mục 3).
     * Bảng màu do người dùng lựa chọn; phối lại layout tuyệt đối KHÔNG đổi màu.
     *
     * @var array<string, array<string, mixed>>
     */
    protected const PALETTES = [
        'ruby' => [
            'id' => 'ruby',
            'name' => 'Đỏ Rượu Vang Hoàng Gia & Vàng Antique',
            'short_name' => 'Đỏ Ruby',
            'primary' => '#85182A',
            'secondary' => '#5E0E1C',
            'accent' => '#CFA860',
            'background' => '#FAF7F2',
            'surface' => '#FFFFFF',
            'text' => '#1F1D1A',
            'text_muted' => '#6B6357',
            'border' => '#E5DCCD',
            'tag' => 'Hỷ Sự & Quý Phái',
        ],
        'dusty-rose' => [
            'id' => 'dusty-rose',
            'name' => 'Hồng Tro & Champagne Lụa',
            'short_name' => 'Hồng Tro',
            'primary' => '#A26B6B',
            'secondary' => '#7D4E4E',
            'accent' => '#C5A880',
            'background' => '#FFFDF9',
            'surface' => '#FFFFFF',
            'text' => '#2C2824',
            'text_muted' => '#7A7265',
            'border' => '#EFE8DD',
            'tag' => 'Thơ Mộng & Ngọt Ngào',
        ],
        'sage-green' => [
            'id' => 'sage-green',
            'name' => 'Xanh Xô Thơm & Kem Olive',
            'short_name' => 'Xanh Sage',
            'primary' => '#4A635D',
            'secondary' => '#2E423D',
            'accent' => '#9AA899',
            'background' => '#FAF8F5',
            'surface' => '#FFFFFF',
            'text' => '#1F1D1A',
            'text_muted' => '#666157',
            'border' => '#E5DFD5',
            'tag' => 'Thuần Khiết & Thiên Nhiên',
        ],
        'royal-navy' => [
            'id' => 'royal-navy',
            'name' => 'Xanh Hoàng Gia & Ánh Bạc',
            'short_name' => 'Xanh Navy',
            'primary' => '#1B2A4A',
            'secondary' => '#0F1A30',
            'accent' => '#A0AEC0',
            'background' => '#F8FAFC',
            'surface' => '#FFFFFF',
            'text' => '#0F172A',
            'text_muted' => '#64748B',
            'border' => '#E2E8F0',
            'tag' => 'Cổ Điển Châu Âu',
        ],
        'warm-amber' => [
            'id' => 'warm-amber',
            'name' => 'Nâu Hổ Phách & Hoàng Hôn Ấm',
            'short_name' => 'Hổ Phách',
            'primary' => '#8C4724',
            'secondary' => '#5C2D15',
            'accent' => '#D4A373',
            'background' => '#FFFBF5',
            'surface' => '#FFFFFF',
            'text' => '#2C221E',
            'text_muted' => '#7C6F66',
            'border' => '#EFE4DA',
            'tag' => 'Ấm Cúng & Điện Ảnh',
        ],
    ];

    /**
     * Danh mục đúng 7 section tương ứng 1, 2, 3, 4, 5, 6 và 8 của SKILL.md (Mục 5).
     * Tuyệt đối không thêm bất kỳ section nào khác.
     * Mỗi section có 3 biến thể bố cục (Variant A, B, C) được Art-Direct riêng biệt.
     *
     * @var array<string, array<string, mixed>>
     */
    protected const SECTIONS = [
        'hero' => [
            'key' => 'hero',
            'skill_section_index' => 1,
            'title' => 'Trang Bìa Chính (Hero Cover)',
            'description' => 'Màn mở thiệp, loại hôn lễ, tên uyên ương và ngày cưới dương/âm lịch',
            'variants' => [
                'variant_a' => [
                    'id' => 'variant_a',
                    'name' => 'Cổng Vòm Cổ Điển (Classic Arch)',
                    'description' => 'Khung ảnh vòm cung nghệ thuật, monogram đỉnh đầu, typography serif mềm mại.',
                    'preview_thumbnail' => 'hero-arch.svg',
                ],
                'variant_b' => [
                    'id' => 'variant_b',
                    'name' => 'Tối Giản Hiện Đại (Minimal Elegance)',
                    'description' => 'Ảnh tràn viền tinh tế, số ngày cưới lớn mạ vàng, bố cục cân đối thanh thoát.',
                    'preview_thumbnail' => 'hero-minimal.svg',
                ],
                'variant_c' => [
                    'id' => 'variant_c',
                    'name' => 'Trang Bìa Tạp Chí (Editorial Vogue)',
                    'description' => 'Chữ tên cặp đôi phóng khoáng đè nhẹ lên ảnh, tagline cảm xúc phá cách.',
                    'preview_thumbnail' => 'hero-editorial.svg',
                ],
            ],
        ],
        'story' => [
            'key' => 'story',
            'skill_section_index' => 2,
            'title' => 'Lời Ngỏ & Chuyện Tình Yêu (Love Story & Quote)',
            'description' => 'Câu trích dẫn tâm tình, ảnh kỷ niệm và lời ngỏ chân thành gửi quan khách',
            'variants' => [
                'variant_a' => [
                    'id' => 'variant_a',
                    'name' => 'Trích Dẫn & Khung Vòm (Poetic Arch)',
                    'description' => 'Khung ảnh vòm bo cong nghệ thuật kết hợp trích dẫn nghiêng phong cách thơ.',
                    'preview_thumbnail' => 'story-arch.svg',
                ],
                'variant_b' => [
                    'id' => 'variant_b',
                    'name' => 'Tự Sự Lắng Đọng (Intimate Story)',
                    'description' => 'Đoạn văn tâm tình trung tâm ấm áp, huy hiệu tình yêu và chữ ký lồng nhau.',
                    'preview_thumbnail' => 'story-centered.svg',
                ],
                'variant_c' => [
                    'id' => 'variant_c',
                    'name' => 'Tạp Chí Đối Xứng (Magazine Split)',
                    'description' => 'Bố cục 2 cột so le hiện đại: trích dẫn lớn bên trái, hình kỷ niệm bên phải.',
                    'preview_thumbnail' => 'story-magazine.svg',
                ],
            ],
        ],
        'parents' => [
            'key' => 'parents',
            'skill_section_index' => 3,
            'title' => 'Song Thân & Đôi Uyên Ương (Family & Couple)',
            'description' => 'Họ tên phụ mẫu hai bên nhà trai, nhà gái và lời tự bạch đôi bạn trẻ',
            'variants' => [
                'variant_a' => [
                    'id' => 'variant_a',
                    'name' => 'Đối Xứng Hai Họ (Symmetric Heritage)',
                    'description' => 'Hai cột nhà trai nhà gái chuẩn mực truyền thống, huy hiệu Hỷ mạ vàng ở giữa.',
                    'preview_thumbnail' => 'parents-symmetric.svg',
                ],
                'variant_b' => [
                    'id' => 'variant_b',
                    'name' => 'Thẻ Giấy Mỹ Thuật (Parchment Cards)',
                    'description' => 'Hai thẻ card bo góc thanh lịch trên nền giấy vân nhẹ, phân biệt rõ tư gia hai họ.',
                    'preview_thumbnail' => 'parents-cards.svg',
                ],
                'variant_c' => [
                    'id' => 'variant_c',
                    'name' => 'Gia Phong Chuẩn Mực (Formal Honor)',
                    'description' => 'Khung viền chỉ đôi trang trọng, thông điệp kính báo hiếu nghĩa từ song thân.',
                    'preview_thumbnail' => 'parents-formal.svg',
                ],
            ],
        ],
        'countdown' => [
            'key' => 'countdown',
            'skill_section_index' => 4,
            'title' => 'Đếm Ngược & Lịch Tháng (Countdown & Calendar)',
            'description' => 'Đồng hồ đếm ngược thời gian thực và tờ lịch Save The Date khoanh tròn ngày cưới',
            'variants' => [
                'variant_a' => [
                    'id' => 'variant_a',
                    'name' => 'Tờ Lịch Cổ Điển (Classic Tear-off)',
                    'description' => 'Tờ lịch tháng với ô ngày cưới tỏa sáng kèm bộ 4 số đếm ngược trang nhã.',
                    'preview_thumbnail' => 'calendar-classic.svg',
                ],
                'variant_b' => [
                    'id' => 'variant_b',
                    'name' => 'Hộp Số Tối Giản (Minimalist Digits)',
                    'description' => 'Đồng hồ số dạng pill card sang trọng, lịch tháng tối giản tập trung ngày chung đôi.',
                    'preview_thumbnail' => 'calendar-minimal.svg',
                ],
                'variant_c' => [
                    'id' => 'variant_c',
                    'name' => 'Vòng Tròn Thời Gian (Golden Circle)',
                    'description' => 'Vòng tròn mạ vàng bao bọc ngày cưới, hiệu ứng đếm ngược nhịp tim lãng mạn.',
                    'preview_thumbnail' => 'calendar-gold.svg',
                ],
            ],
        ],
        'events' => [
            'key' => 'events',
            'skill_section_index' => 5,
            'title' => 'Thông Tin Sự Kiện & Bản Đồ (Events & Maps)',
            'description' => 'Thư mời tiệc cưới chính, giờ đón khách, sảnh tiệc, địa chỉ và Google Maps',
            'variants' => [
                'variant_a' => [
                    'id' => 'variant_a',
                    'name' => 'Vé Mời Danh Dự (Boarding Pass Luxury)',
                    'description' => 'Thiết kế thẻ tiệc phong cách boarding pass độc bản, đường răng cưa nghệ thuật.',
                    'preview_thumbnail' => 'event-ticket.svg',
                ],
                'variant_b' => [
                    'id' => 'variant_b',
                    'name' => 'Thư Mời Niêm Phong (Wax Seal Letter)',
                    'description' => 'Thư mời mở nắp phong bì kèm tem sáp hoàng gia và nút hé mở Google Maps tiện lợi.',
                    'preview_thumbnail' => 'event-wax.svg',
                ],
                'variant_c' => [
                    'id' => 'variant_c',
                    'name' => 'Cột Lễ & Tiệc Đôi (Dual Ceremony Split)',
                    'description' => 'Hiển thị song song thông tin Lễ Gia Tiên và Tiệc Cưới chính cùng bản đồ dẫn đường.',
                    'preview_thumbnail' => 'event-split.svg',
                ],
            ],
        ],
        'timeline' => [
            'key' => 'timeline',
            'skill_section_index' => 6,
            'title' => 'Lịch Trình Hôn Lễ (Wedding Timeline)',
            'description' => 'Các mốc thời gian đón khách, làm lễ, khai tiệc và chụp ảnh kỷ niệm',
            'variants' => [
                'variant_a' => [
                    'id' => 'variant_a',
                    'name' => 'Trục Chỉ Vàng (Golden Thread)',
                    'description' => 'Trục dọc kết nối các mốc thời gian với hạt châu tỏa sáng và icon minh họa.',
                    'preview_thumbnail' => 'timeline-thread.svg',
                ],
                'variant_b' => [
                    'id' => 'variant_b',
                    'name' => 'Thẻ Mốc So Le (Alternating Cards)',
                    'description' => 'Các mốc thời gian bố trí ziczac so le hai bên trái phải sinh động.',
                    'preview_thumbnail' => 'timeline-cards.svg',
                ],
                'variant_c' => [
                    'id' => 'variant_c',
                    'name' => 'Tối Giản Tinh Tế (Minimal Steps)',
                    'description' => 'Dòng thời gian nằm ngang hoặc dọc tối giản, chữ số giờ lớn thanh lịch.',
                    'preview_thumbnail' => 'timeline-minimal.svg',
                ],
            ],
        ],
        'dresscode' => [
            'key' => 'dresscode',
            'skill_section_index' => 8,
            'title' => 'Quy Định Trang Phục (Dresscode)',
            'description' => 'Gợi ý bảng màu trang phục dự tiệc và lời nhắn gửi tinh tế đến quan khách',
            'variants' => [
                'variant_a' => [
                    'id' => 'variant_a',
                    'name' => 'Vòng Tròn Sắc Màu (Palette Circles)',
                    'description' => 'Các vòng tròn mẫu màu kèm nhãn tên thanh lịch, lời dặn trang phục mềm mại.',
                    'preview_thumbnail' => 'dresscode-circles.svg',
                ],
                'variant_b' => [
                    'id' => 'variant_b',
                    'name' => 'Mẫu Vải Nghệ Thuật (Fabric Swatches)',
                    'description' => 'Khối mẫu màu phong cách bảng vải thời trang, gợi ý tone trang phục hòa hợp tiệc cưới.',
                    'preview_thumbnail' => 'dresscode-swatches.svg',
                ],
                'variant_c' => [
                    'id' => 'variant_c',
                    'name' => 'Ký Họa Thời Trang (Vogue Palette)',
                    'description' => 'Phối hợp icon trang phục sketch tối giản và dải màu gợi ý Quiet Luxury.',
                    'preview_thumbnail' => 'dresscode-vogue.svg',
                ],
            ],
        ],
    ];

    public function __construct(
        protected ContentModerationService $moderationService
    ) {}

    /**
     * Lấy danh sách phong cách hợp lệ.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getStyles(): array
    {
        return self::STYLES;
    }

    /**
     * Lấy danh sách bảng màu hợp lệ.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getPalettes(): array
    {
        return self::PALETTES;
    }

    /**
     * Lấy định nghĩa 7 section tiêu chuẩn.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getSectionDefinitions(): array
    {
        return self::SECTIONS;
    }

    /**
     * Kiểm tra tính hợp lệ của phong cách.
     */
    public function isValidStyle(string $style): bool
    {
        return array_key_exists($style, self::STYLES);
    }

    /**
     * Kiểm tra tính hợp lệ của bảng màu.
     */
    public function isValidPalette(string $palette): bool
    {
        return array_key_exists($palette, self::PALETTES);
    }

    /**
     * Phối ngẫu nhiên bố cục từ các component section đã được kiểm duyệt (Mục 4).
     * Tuyệt đối không cho AI tạo HTML, CSS, JS.
     * Giữ nguyên màu sắc $palette do người dùng chọn (Mục 3).
     *
     * @param  array<string, string>|null  $pinnedSections  Các section muốn giữ cố định variant
     * @return array{
     *     style: string,
     *     palette: string,
     *     sections: array<string, string>,
     *     assembled_at: string,
     *     is_curated: bool
     * }
     */
    public function assembleCuratedLayout(string $style, string $palette, ?array $pinnedSections = null): array
    {
        if (! $this->isValidStyle($style)) {
            $style = 'romantic';
        }

        if (! $this->isValidPalette($palette)) {
            $palette = self::STYLES[$style]['recommended_palette'] ?? 'dusty-rose';
        }

        $styleConfig = self::STYLES[$style];
        $defaultVariants = $styleConfig['default_variants'] ?? [];
        $sections = [];

        foreach (self::SECTIONS as $sectionKey => $sectionDef) {
            // Nếu có chỉ định ghim variant cụ thể, giữ nguyên
            if ($pinnedSections && isset($pinnedSections[$sectionKey]) && in_array($pinnedSections[$sectionKey], ['variant_a', 'variant_b', 'variant_c'], true)) {
                $sections[$sectionKey] = $pinnedSections[$sectionKey];

                continue;
            }

            // Phối ngẫu nhiên trong 3 biến thể có sẵn của section đó
            $availableVariantKeys = array_keys($sectionDef['variants']);
            // Ưu tiên biến thể mặc định của style với xác suất cao hơn để giữ thẩm mỹ nhất quán
            $randChoice = mt_rand(1, 100);
            if ($randChoice <= 50 && isset($defaultVariants[$sectionKey])) {
                $sections[$sectionKey] = $defaultVariants[$sectionKey];
            } else {
                $sections[$sectionKey] = $availableVariantKeys[array_rand($availableVariantKeys)];
            }
        }

        return [
            'style' => $style,
            'palette' => $palette,
            'sections' => $sections,
            'assembled_at' => now()->toIso8601String(),
            'is_curated' => true,
        ];
    }

    /**
     * Đổi riêng biến thể của một section cụ thể (Mục 9).
     *
     * @param  array<string, mixed>  $currentLayout
     * @return array<string, mixed>
     */
    public function switchSectionVariant(array $currentLayout, string $sectionKey, string $direction = 'next'): array
    {
        if (! isset(self::SECTIONS[$sectionKey])) {
            throw new InvalidArgumentException("Mục thiệp [{$sectionKey}] không tồn tại trong khung 7 section.");
        }

        $availableVariants = array_keys(self::SECTIONS[$sectionKey]['variants']); // ['variant_a', 'variant_b', 'variant_c']
        $currentVariant = $currentLayout['sections'][$sectionKey] ?? $availableVariants[0];
        $currentIndex = array_search($currentVariant, $availableVariants, true);

        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        if ($direction === 'prev') {
            $nextIndex = ($currentIndex - 1 + count($availableVariants)) % count($availableVariants);
        } else {
            $nextIndex = ($currentIndex + 1) % count($availableVariants);
        }

        $currentLayout['sections'][$sectionKey] = $availableVariants[$nextIndex];
        $currentLayout['updated_at'] = now()->toIso8601String();

        return $currentLayout;
    }

    /**
     * AI sinh nội dung cảm xúc phù hợp với phong cách (Mục 7).
     * AI chỉ viết lời ngỏ, câu trích dẫn, tiêu đề cảm xúc.
     * Tuyệt đối không được bịa thông tin cá nhân, sự kiện hay ngày cưới.
     *
     * @param  array<string, mixed>  $weddingData
     * @return array{
     *     couple_quote: string,
     *     story_title: string,
     *     story_subtitle: string,
     *     story_content: string,
     *     dresscode_note: string,
     *     ceremony_type: string
     * }
     */
    public function generateCuratedContent(array $weddingData, string $style): array
    {
        if (! $this->isValidStyle($style)) {
            $style = 'romantic';
        }

        $groom = trim((string) ($weddingData['groom_name'] ?? 'Chú Rể'));
        $bride = trim((string) ($weddingData['bride_name'] ?? 'Cô Dâu'));

        // Làm sạch và lọc ngôn từ phản cảm
        $groom = htmlspecialchars(strip_tags($groom), ENT_QUOTES, 'UTF-8');
        $bride = htmlspecialchars(strip_tags($bride), ENT_QUOTES, 'UTF-8');

        return match ($style) {
            'minimal' => [
                'couple_quote' => 'Hạnh phúc không phải là điều gì đó xa xôi, mà là mỗi ngày thức dậy biết có một người luôn ở bên sẻ chia.',
                'story_title' => 'Lời Ngỏ Yêu Thương',
                'story_subtitle' => 'Khoảnh Khắc Bình Yên Giữa Đời Thường',
                'story_content' => "Không cần những lời hoa mỹ hay những lời hứa hẹn xa vời, tình yêu của {$groom} và {$bride} được dệt nên từ những thấu hiểu bình dị, những bữa cơm ấm áp và sự đồng hành trọn vẹn. Ngày hôm nay, trước sự chứng kiến của những người thân quý nhất, chúng mình chính thức nắm tay nhau bước vào một chặng đường mới.",
                'dresscode_note' => 'Trân trọng kính mời Quý khách diện trang phục trang nhã, ưu tiên tông màu be, trắng ngà hoặc xanh nhạt.',
                'ceremony_type' => 'Lễ Thành Hôn',
            ],
            'editorial' => [
                'couple_quote' => 'Tình yêu đẹp nhất là khi hai tâm hồn tự do tìm thấy nơi chốn bình yên sâu thẳm trong mắt nhau.',
                'story_title' => 'Bản Tình Ca Riêng',
                'story_subtitle' => 'Từng Trang Viết Cho Hạnh Phúc',
                'story_content' => "Giữa thế giới tấp nập với bao điều vội vã, định mệnh đã dẫn lối để {$groom} và {$bride} nhận ra nhau. Chúng mình nhận ra rằng, điều tuyệt vời nhất của tuổi trẻ không phải là đi được bao xa, mà là tìm thấy một người để cùng nhau dừng chân và xây đắp một mái ấm vĩnh cửu. Sự hiện diện của bạn là mảnh ghép hoàn hảo nhất trong ngày trọng đại này.",
                'dresscode_note' => 'Gợi ý trang phục: Tone màu ấm, sang trọng (Nâu đất, Vàng champagne, Đen thanh lịch).',
                'ceremony_type' => 'Lễ Thành Hôn',
            ],
            'traditional' => [
                'couple_quote' => 'Trăm năm kết tóc se duyên — Nghĩa tình vẹn tròn, một lòng son sắt.',
                'story_title' => 'Chuyện Trăm Năm',
                'story_subtitle' => 'Nên Duyên Vợ Chồng — Vẹn Tròn Hiếu Nghĩa',
                'story_content' => "Được sự tác thành và vun đắp của hai bên gia đình, tình yêu của {$groom} và {$bride} đã đơm hoa kết trái. Trong niềm hân hoan của ngày vui trăm năm, gia đình chúng tôi xin gửi lời tri ân sâu sắc tới ông bà, cô bác, họ hàng nội ngoại và bạn bè thân hữu đã luôn yêu thương, đồng hành và chúc phúc.",
                'dresscode_note' => 'Kính mời Quý khách diện trang phục lịch sự, trang nhã để buổi lễ thêm phần long trọng và ấm cúng.',
                'ceremony_type' => 'Lễ Thành Hôn',
            ],
            default => [ // 'romantic'
                'couple_quote' => 'Giữa muôn ngàn vì sao sáng trên bầu trời, em là ngôi sao duy nhất chiếu rọi và sưởi ấm trái tim anh.',
                'story_title' => 'Câu Chuyện Tình Yêu',
                'story_subtitle' => 'Hành Trình Gặp Gỡ & Kết Duyên',
                'story_content' => "Có những cuộc gặp gỡ như đã được định sẵn từ muôn kiếp trước. {$groom} và {$bride} đã cùng nhau đi qua những ngày mưa giông lẫn những chiều hoàng hôn rạng rỡ, để nhận ra rằng bàn tay này là dành để nắm chặt lấy nhau suốt cuộc đời. Chúng mình rất mong được chia sẻ niềm hạnh phúc thiêng liêng này cùng những người thân yêu nhất.",
                'dresscode_note' => 'Trang phục khuyến nghị: Gam màu pastel, hồng tro, be hoặc trắng để hòa cùng không gian lãng mạn.',
                'ceremony_type' => 'Lễ Thành Hôn',
            ],
        };
    }
}
