<?php

declare(strict_types=1);

namespace App\Services\Tarot;

/**
 * Xây dựng 78 bài viết chuẩn SEO độc lập cho từng lá bài Rider–Waite–Smith (/tarot/la-bai/{slug}).
 *
 * - Mỗi lá bài có tiêu đề (Title & H1), Meta Description, lời mở đầu, 8 mục H2 chuyên sâu,
 *   hướng dẫn Có/Không có điều kiện, FAQ riêng và JSON-LD (Article + BreadcrumbList + FAQPage).
 * - Tham chiếu trực tiếp kho dữ liệu đã thẩm định từ The Pictorial Key to the Tarot (A.E. Waite, 1910).
 */
class TarotSeoArticleService
{
    public function __construct(protected TarotDeckRegistry $registry) {}

    /**
     * @param  array<string, mixed>  $card
     * @return array<string, mixed>
     */
    public function buildArticle(array $card): array
    {
        $nameEn = (string) ($card['name_en'] ?? '');
        $nameVi = (string) ($card['name_vi'] ?? '');
        $roman = (string) ($card['roman'] ?? '');
        $slug = (string) ($card['slug'] ?? '');
        $arcana = (string) ($card['arcana'] ?? 'minor');
        $suit = (string) ($card['suit'] ?? 'major');
        $suitVi = (string) ($card['suit_label_vi'] ?? 'Ẩn Chính');
        $element = (string) ($card['element'] ?? 'Khí');
        $seq = (int) ($card['sequence_index'] ?? 0);

        $upKw = array_values((array) ($card['keywords_upright'] ?? []));
        $revKw = array_values((array) ($card['keywords_reversed'] ?? []));
        $symbols = array_values((array) ($card['symbols'] ?? []));
        $domains = (array) ($card['domains'] ?? []);

        $seoTitle = $this->buildUniqueSeoTitle($nameEn, $nameVi, $roman, $arcana, $seq, $upKw);
        $h1 = $seoTitle;
        $metaDesc = $this->buildUniqueMetaDescription($nameEn, $nameVi, $suitVi, $element, $upKw, $revKw, $seq);
        $yesNo = $this->buildConditionalYesNo($card);
        $relatedCards = $this->findRelatedCards($card);
        $faqs = $this->buildCardFaqs($card, $yesNo);

        $imageRel = "assets/client/images/tarot/rws/{$slug}.jpg";
        $hasLocalImg = is_file(public_path($imageRel));
        $imageUrl = $hasLocalImg ? asset($imageRel) : null;

        $toc = [
            ['id' => 'tong-quan', 'label' => "{$nameEn} là gì? Tổng quan & biểu tượng RWS"],
            ['id' => 'y-nghia-xuoi', 'label' => "Ý nghĩa lá bài {$nameEn} Xuôi (Upright)"],
            ['id' => 'y-nghia-nguoc', 'label' => "Ý nghĩa lá bài {$nameEn} Ngược (Reversed)"],
            ['id' => 'tinh-yeu-hon-nhan', 'label' => "{$nameEn} trong Tình yêu, Hôn nhân & Gia đình"],
            ['id' => 'cong-viec-tai-chinh', 'label' => "{$nameEn} trong Công việc, Tài chính & Bản thân"],
            ['id' => 'co-hay-khong', 'label' => "{$nameEn} trong trải bài Có / Không (Yes / No)"],
            ['id' => 'hieu-lam-tu-van', 'label' => 'Hiểu lầm thường gặp & Câu hỏi tự vấn'],
            ['id' => 'faq-la-bai', 'label' => "Câu hỏi thường gặp về {$nameEn}"],
        ];

        $introLead = $this->buildEditorialIntro($card);
        $suitContext = $this->buildSuitElementContext($arcana, $suit, $suitVi, $element);
        $reversedGuide = $this->buildReversedReadingGuide($card);

        $schemas = $this->buildJsonLdSchemas($card, $seoTitle, $metaDesc, $imageUrl, $faqs);

        return [
            'seo_title' => $seoTitle,
            'h1' => $h1,
            'meta_description' => $metaDesc,
            'image_url' => $imageUrl,
            'image_alt' => "Lá bài Tarot {$nameEn} ({$nameVi}) chuẩn bộ Rider–Waite–Smith gốc 1909",
            'image_caption' => "Minh họa gốc lá bài {$nameEn} ({$nameVi}) trong bộ Rider–Waite–Smith (1909) của Pamela Colman Smith & Arthur Edward Waite — thuộc phạm vi Công cộng (Public Domain).",
            'toc' => $toc,
            'intro_lead' => $introLead,
            'suit_context' => $suitContext,
            'reversed_guide' => $reversedGuide,
            'yes_no' => $yesNo,
            'related_cards' => $relatedCards,
            'faqs' => $faqs,
            'schemas' => $schemas,
            'reading_minutes' => 5,
            'updated_label' => 'Cập nhật chuẩn Rider–Waite–Smith · Mây Hẹn',
        ];
    }

    /**
     * Tạo 78 tiêu đề SEO tự nhiên, đa dạng cấu trúc (không lặp y hệt nhau).
     *
     * @param  list<string>  $upKw
     */
    protected function buildUniqueSeoTitle(
        string $nameEn,
        string $nameVi,
        string $roman,
        string $arcana,
        int $seq,
        array $upKw
    ): string {
        $kw1 = $upKw[0] ?? 'Thông điệp cốt lõi';

        // 8 mẫu tiêu đề SEO tự nhiên, biến thiên theo nhóm bài để 78 bài đều có sắc thái riêng
        $patterns = [
            "{$nameEn} Là Gì? Ý Nghĩa Của Lá Bài {$nameEn} Trong Tarot",
            "Ý Nghĩa Lá Bài {$nameEn} ({$nameVi}) Trong Tarot: Xuôi, Ngược & Tình Yêu",
            "Giải Mã {$nameEn} Trong Tarot: Biểu Tượng, {$kw1} & Ứng Dụng Thực Tế",
            "{$nameEn} Trong Tarot Có Ý Nghĩa Gì? Luận Giải Chi Tiết Chuẩn Rider–Waite–Smith",
            "Lá Bài {$nameEn} ({$nameVi}): Thông Điệp Tình Yêu, Công Việc & Lời Khuyên",
            "Tìm Hiểu {$nameEn} Trong Tarot — Ý Nghĩa Chiều Xuôi, Ngược & Trải Bài Có/Không",
            "{$nameEn} Là Lá Bài Gì? Giải Nghĩa Chuẩn Học Thuật Về {$kw1} Trong Tarot",
            "Ý Nghĩa {$nameEn} ({$nameVi}) Khi Rút Bài Tarot: Biểu Tượng & Định Hướng",
        ];

        if ($arcana === 'major' && $roman !== '' && ($seq % 3 === 1)) {
            return "{$nameEn} ({$roman}) Là Gì? Ý Nghĩa Lá Bài {$nameVi} Trong Tarot Chuẩn RWS";
        }

        return $patterns[$seq % count($patterns)];
    }

    /**
     * @param  list<string>  $upKw
     * @param  list<string>  $revKw
     */
    protected function buildUniqueMetaDescription(
        string $nameEn,
        string $nameVi,
        string $suitVi,
        string $element,
        array $upKw,
        array $revKw,
        int $seq
    ): string {
        $uText = implode(', ', array_slice($upKw, 0, 3));
        $rText = implode(', ', array_slice($revKw, 0, 2));

        $templates = [
            "Khám phá {$nameEn} ({$nameVi}) là gì trong bộ Tarot Rider–Waite–Smith: giải mã biểu tượng gốc, ý nghĩa Xuôi ({$uText}), Ngược ({$rText}), tình yêu, hôn nhân và công việc.",
            "Luận giải đầy đủ lá bài {$nameEn} ({$nameVi} — {$suitVi}, nguyên tố {$element}): từ khóa cốt lõi {$uText}, cách đọc chiều ngược, trải bài Có/Không và lời khuyên thực tế.",
            "Tìm hiểu ý nghĩa lá bài {$nameEn} trong Tarot chuẩn Rider–Waite–Smith: thông điệp tình yêu, gia đình, sự nghiệp, tài chính, hiểu lầm cần tránh và câu hỏi tự vấn.",
        ];

        return $templates[$seq % count($templates)];
    }

    /**
     * @param  array<string, mixed>  $card
     */
    protected function buildEditorialIntro(array $card): string
    {
        $nameEn = (string) ($card['name_en'] ?? '');
        $nameVi = (string) ($card['name_vi'] ?? '');
        $roman = (string) ($card['roman'] ?? '');
        $arcana = (string) ($card['arcana'] ?? 'minor');
        $suitVi = (string) ($card['suit_label_vi'] ?? 'Ẩn Chính');
        $element = (string) ($card['element'] ?? 'Khí');
        $waite = (string) ($card['traditional_waite'] ?? '');

        if ($arcana === 'major') {
            return "{$nameEn} ({$nameVi}) là lá bài thứ {$roman} thuộc nhóm 22 Ẩn Chính (Major Arcana) trong hệ thống Tarot Rider–Waite–Smith, mang năng lượng nguyên tố {$element}. Khác với các lá Ẩn Phụ phản ánh tình huống thường nhật, sự xuất hiện của {$nameEn} thường đánh dấu một bài học nhận thức quan trọng hoặc bước chuyển đáng suy ngẫm trong hành trình trưởng thành. Theo nguyên bản The Pictorial Key to the Tarot (A.E. Waite, 1910): {$waite}";
        }

        return "{$nameEn} ({$nameVi}) thuộc nhóm {$suitVi} trong 56 lá Ẩn Phụ (Minor Arcana) của bộ bài Rider–Waite–Smith, gắn liền với nguyên tố {$element}. Khi xuất hiện trong một trải bài Tarot, {$nameEn} phản ánh trực tiếp nhịp điệu đời sống, trạng thái tâm lý và cách bạn đang tương tác với hoàn cảnh hiện tại. Theo diễn giải kinh điển của Arthur Edward Waite (1910): {$waite}";
    }

    protected function buildSuitElementContext(string $arcana, string $suit, string $suitVi, string $element): string
    {
        if ($arcana === 'major') {
            return "Nhóm Ẩn Chính (Major Arcana) đại diện cho các nguyên mẫu phổ quát của trải nghiệm con người. Kết hợp cùng nguyên tố {$element}, lá bài này khuyến khích bạn nhìn rộng hơn bề nổi sự việc để nhận diện giá trị cốt lõi đang dẫn dắt lựa chọn của mình.";
        }

        return match ($suit) {
            'wands' => 'Bộ Gậy (Wands — Nguyên tố Lửa) đại diện cho ý chí, cảm hứng, động lực hành động và sự chủ động. Các lá thuộc bộ Gậy cho thấy ngọn lửa nhiệt huyết của bạn đang bùng sáng, cần định hướng hay cần điều tiết để không kiệt sức.',
            'cups' => 'Bộ Cốc (Cups — Nguyên tố Nước) gắn liền với cảm xúc, tình yêu, sự đồng cảm, gắn kết gia đình và trực giác. Các lá thuộc bộ Cốc phản ánh thế giới nội tâm và cách bạn nuôi dưỡng các mối quan hệ thân thương.',
            'swords' => 'Bộ Kiếm (Swords — Nguyên tố Khí) đại diện cho tư duy lý trí, giao tiếp, sự minh bạch, quyết định và những rào cản tinh thần. Các lá thuộc bộ Kiếm nhắc bạn dùng sự tỉnh táo và đối thoại thẳng thắn để tháo gỡ khúc mắc.',
            'pentacles' => 'Bộ Tiền / Xu (Pentacles — Nguyên tố Đất) tượng trưng cho đời sống thực tế, công việc bền bỉ, quản trị tài chính, sức khỏe thể chất và nền tảng tổ ấm. Các lá thuộc bộ Xu đề cao sự kiên nhẫn và kết quả hữu hình.',
            default => "Nhóm {$suitVi} (Nguyên tố {$element}) mang sắc thái ứng dụng thực tế vào từng quyết định hằng ngày.",
        };
    }

    /**
     * @param  array<string, mixed>  $card
     */
    protected function buildReversedReadingGuide(array $card): string
    {
        $pattern = (string) ($card['reversed_pattern'] ?? 'nội tâm hóa / cần điều chỉnh cân bằng');
        $nameEn = (string) ($card['name_en'] ?? '');

        return "Trong trường phái Rider–Waite–Smith hiện đại, {$nameEn} Ngược không mang nghĩa \"điềm xấu\" hay đảo ngược hoàn toàn thành tiêu cực. Thay vào đó, lá ngược thường vận hành theo cơ chế [{$pattern}]: năng lượng của lá bài đang diễn ra âm thầm bên trong, bị trì hoãn tạm thời, hoặc biểu hiện quá mức/chưa tới ngưỡng cân bằng và cần bạn chủ động điều chỉnh.";
    }

    /**
     * Gợi ý Có / Không mang tính điều kiện (tuyệt đối không phán cứng 100%).
     *
     * @param  array<string, mixed>  $card
     * @return array{signal: string, upright_note: string, reversed_note: string}
     */
    protected function buildConditionalYesNo(array $card): array
    {
        $slug = (string) ($card['slug'] ?? '');
        $nameEn = (string) ($card['name_en'] ?? '');

        $positiveSlugs = [
            'the-sun', 'the-star', 'the-world', 'the-empress', 'the-lovers', 'the-magician', 'strength', 'the-chariot',
            'ace-of-cups', 'two-of-cups', 'three-of-cups', 'six-of-cups', 'nine-of-cups', 'ten-of-cups',
            'ace-of-wands', 'four-of-wands', 'six-of-wands', 'ace-of-pentacles', 'nine-of-pentacles', 'ten-of-pentacles',
        ];
        $cautionSlugs = [
            'the-tower', 'the-devil', 'the-moon', 'three-of-swords', 'five-of-swords', 'seven-of-swords',
            'eight-of-swords', 'nine-of-swords', 'ten-of-swords', 'five-of-cups', 'five-of-pentacles', 'ten-of-wands',
        ];

        if (in_array($slug, $positiveSlugs, true)) {
            return [
                'signal' => 'Nghiêng về CÓ (có điều kiện thuận lợi)',
                'upright_note' => "Khi {$nameEn} xuất hiện xuôi, tín hiệu nghiêng về hướng thuận lợi nếu bạn giữ đúng tinh thần chủ động và chân thành như lá bài gợi mở.",
                'reversed_note' => "Khi {$nameEn} xuất hiện ngược, kết quả chuyển sang \"Chưa vội / Cần điều chỉnh thêm\" — hãy rà lại điểm chưa cân bằng trước khi chốt quyết định.",
            ];
        }

        if (in_array($slug, $cautionSlugs, true)) {
            return [
                'signal' => 'Nghiêng về CHƯA NÊN / CẦN THẬN TRỌNG',
                'upright_note' => "{$nameEn} gợi ý nên tạm chậm lại để kiểm chứng dữ kiện, tháo gỡ áp lực hoặc nhìn thẳng vào rủi ro trước khi tiến hành.",
                'reversed_note' => 'Ở chiều ngược, áp lực có xu hướng dịu dần hoặc mở ra lối thoát — tuy nhiên vẫn cần bước đi thực tế từng phần thay vì vội vàng.',
            ];
        }

        return [
            'signal' => 'TRUNG TÍNH — Phụ thuộc vào lựa chọn chủ động của bạn',
            'upright_note' => "{$nameEn} xuôi nhấn mạnh rằng câu trả lời không cố định sẵn mà phụ thuộc vào cách bạn chuẩn bị, giao tiếp và giữ cân bằng trong bước tiếp theo.",
            'reversed_note' => "{$nameEn} ngược nhắc bạn chưa nên vội chốt Có hay Không ngay lúc này; hãy dành thêm thời gian làm rõ mục tiêu và điều kiện thực tế.",
        ];
    }

    /**
     * @param  array<string, mixed>  $card
     * @return list<array<string, mixed>>
     */
    protected function findRelatedCards(array $card): array
    {
        $all = $this->registry->getAllCards();
        $sameSuit = array_values(array_filter(
            $all,
            fn (array $c): bool => $c['slug'] !== $card['slug'] && $c['suit'] === $card['suit']
        ));

        $seq = (int) ($card['sequence_index'] ?? 0);
        $count = count($sameSuit);
        if ($count === 0) {
            return [];
        }

        $picked = [];
        for ($i = 0; $i < min(4, $count); $i++) {
            $picked[] = $sameSuit[($seq + $i * 3) % $count];
        }

        return $picked;
    }

    /**
     * @param  array<string, mixed>  $card
     * @param  array{signal: string, upright_note: string, reversed_note: string}  $yesNo
     * @return list<array{q: string, a: string}>
     */
    protected function buildCardFaqs(array $card, array $yesNo): array
    {
        $nameEn = (string) ($card['name_en'] ?? '');
        $nameVi = (string) ($card['name_vi'] ?? '');
        $domains = (array) ($card['domains'] ?? []);
        $misconception = (string) ($card['common_misconception'] ?? '');

        return [
            [
                'q' => "Lá bài {$nameEn} ({$nameVi}) là gì trong Tarot?",
                'a' => (string) ($card['upright_meaning'] ?? ''),
            ],
            [
                'q' => "Rút trúng lá {$nameEn} trong tình yêu có ý nghĩa gì?",
                'a' => trim((string) ($domains['love'] ?? '').' '.(string) ($domains['marriage'] ?? '')),
            ],
            [
                'q' => "Lá bài {$nameEn} có nghĩa là Có hay Không (Yes/No)?",
                'a' => "{$yesNo['signal']}. {$yesNo['upright_note']}",
            ],
            [
                'q' => "Hiểu lầm nào cần tránh khi gặp {$nameEn} (đặc biệt là chiều ngược)?",
                'a' => $misconception !== '' ? $misconception : (string) ($card['reversed_meaning'] ?? ''),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $card
     * @param  list<array{q: string, a: string}>  $faqs
     * @return list<array<string, mixed>>
     */
    protected function buildJsonLdSchemas(
        array $card,
        string $seoTitle,
        string $metaDesc,
        ?string $imageUrl,
        array $faqs
    ): array {
        $url = route('tarot.card_detail', $card['slug']);
        $article = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $seoTitle,
            'description' => $metaDesc,
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
            'author' => ['@type' => 'Organization', 'name' => 'Mây Hẹn – The Art of Tarot', 'url' => route('home')],
            'publisher' => ['@type' => 'Organization', 'name' => 'Mây Hẹn', 'url' => route('home')],
            'inLanguage' => 'vi-VN',
        ];
        if ($imageUrl) {
            $article['image'] = [$imageUrl];
        }

        $breadcrumb = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Xem Tarot Online', 'item' => route('tarot.index')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Bách Khoa 78 Lá Bài', 'item' => route('tarot.library')],
                ['@type' => 'ListItem', 'position' => 4, 'name' => $card['name_en'].' ('.$card['name_vi'].')', 'item' => $url],
            ],
        ];

        $faqSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $f): array => [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ], $faqs),
        ];

        return [$article, $breadcrumb, $faqSchema];
    }
}
