<?php

declare(strict_types=1);

namespace App\Services\Personality;

/**
 * Pillar SEO Content & JSON-LD Structured Data Service (Google Search Central 2025–2026)
 * cho toàn bộ các chuyên trang công khai của Mây Hẹn – The Art of Personality:
 * - /trac-nghiem-tinh-cach
 * - /trac-nghiem-tinh-cach/{intj..esfp} (16 trang mã nhóm độc lập)
 * - /trac-nghiem-tinh-cach/16-nhom
 * - /trac-nghiem-tinh-cach/tinh-duyen
 * - /trac-nghiem-tinh-cach/chuc-nang-nhan-thuc
 * - /trac-nghiem-tinh-cach/huong-noi-huong-ngoai
 * - /trac-nghiem-tinh-cach/phuong-phap
 * - /big-five
 *
 * Nguyên tắc:
 * - Tiêu đề tự nhiên, dễ đọc, đúng search intent tiếng Việt, không nhồi từ khóa.
 * - Nội dung giàu chiều sâu E-E-A-T, minh bạch khoa học (không hứa hẹn 100%, không tạo % hợp nhau giả, không gán -A/-T).
 */
class PersonalitySeoContentService
{
    public function __construct(
        protected PersonalityTypeRegistry $typeRegistry,
        protected PersonalityMethodRegistry $methods
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function landingPillar(): array
    {
        $url = route('personality.index');
        $faqs = [
            [
                'q' => 'Trắc nghiệm 16 nhóm tính cách trên Mây Hẹn có miễn phí hoàn toàn không?',
                'a' => 'Có. Bạn có thể làm trọn bộ bài test 32 câu, 64 câu, 96 câu và Big Five 50 câu hoàn toàn miễn phí, không bắt buộc đăng nhập, xem ngay mã nhóm, biểu đồ 4 chiều, nút Xem căn cứ và đối chiếu nhóm gần kề (Best-Fit).',
            ],
            [
                'q' => 'Bài trắc nghiệm của Mây Hẹn khác gì với bài MBTI chính thức và 16Personalities?',
                'a' => 'MBTI® chính thức là công cụ có bản quyền của The Myers-Briggs Company. Bộ câu hỏi trên Mây Hẹn được biên soạn 100% độc lập cho bối cảnh người Việt dựa trên 4 cặp xu hướng Jung–Myers, không sao chép câu hỏi bên ngoài và không gắn hậu tố -A/-T (NERIS) để tránh gây nhầm lẫn.',
            ],
            [
                'q' => 'Nên chọn bài test 32 câu, 64 câu hay 96 câu để có kết quả sát thực tế nhất?',
                'a' => 'Nếu lần đầu làm bài, bạn nên chọn Bản Tiêu Chuẩn 64 câu (10–15 phút, 16 câu/chiều cân bằng thuận–đảo). Bản Nhanh 32 câu phù hợp khi bạn cần góc nhìn sơ bộ trong 5–7 phút, còn Bản Chuyên Sâu 96 câu cho nhiều dẫn chứng nhất ở nút Xem căn cứ.',
            ],
            [
                'q' => 'Nếu điểm số của tôi nằm ở giữa (45–55) hoặc đúng 50/50 thì sao?',
                'a' => 'Đó là điều hoàn toàn bình thường vì tính cách con người nằm trên thang liên tục chứ không chia đôi tuyệt đối. Khi điểm bằng đúng 50/50, Mây Hẹn đánh dấu chưa nghiêng rõ và liệt kê tất cả nhóm khả dĩ để bạn tự đối chiếu trong mục Best-Fit thay vì chọn ngẫu nhiên.',
            ],
            [
                'q' => 'Vì sao Mây Hẹn không hiển thị phần trăm hợp nhau 95%–98% cho cặp đôi?',
                'a' => 'Các nghiên cứu tâm lý học hiện đại cho thấy không tồn tại công thức phần trăm cố định quyết định hạnh phúc hôn nhân chỉ từ 4 chữ cái. Vì vậy, tính năng Tính Cách Cặp Đôi của Mây Hẹn tập trung chỉ ra điểm giống/khác thực tế trên 4 chiều và 6 câu hỏi thấu hiểu để hai bạn giao tiếp tốt hơn.',
            ],
            [
                'q' => 'Tại sao làm lại bài test sau vài tháng lại ra nhóm tính cách khác?',
                'a' => 'Khi một hoặc nhiều chiều của bạn nằm gần điểm giữa (45–55), chỉ cần thay đổi hoàn cảnh sống, áp lực công việc hoặc vài câu trả lời là kết quả có thể nghiêng nhẹ sang phía đối diện. Bạn nên xem biểu đồ điểm số của từng chiều thay vì chỉ nhìn 4 chữ cái.',
            ],
            [
                'q' => 'Có nên dùng kết quả trắc nghiệm tính cách để tuyển dụng hoặc chẩn đoán tâm lý không?',
                'a' => 'Tuyệt đối không. Trắc nghiệm tính cách không đo IQ, năng lực chuyên môn hay bệnh lý tâm thần. Đây là công cụ tự chiêm nghiệm, cải thiện giao tiếp cá nhân và vun đắp mối quan hệ.',
            ],
        ];

        $sections = [
            [
                'id' => 'khai-niem-16-nhom',
                'heading' => 'Trắc nghiệm tính cách 16 nhóm là gì và đo lường điều gì?',
                'paragraphs' => [
                    'Trắc nghiệm 16 nhóm tính cách là phương pháp tự báo cáo giúp bạn quan sát cách mình thường nạp năng lượng, tiếp nhận thông tin, cân nhắc quyết định và tổ chức đời sống hằng ngày. Nền tảng lý thuyết bắt nguồn từ công trình Psychological Types (1921) của bác sĩ tâm thần học Carl Gustav Jung và sau đó được Isabel Briggs Myers phát triển thành 4 cặp xu hướng trực quan: Hướng ngoại – Hướng nội (E/I), Giác quan – Trực giác (S/N), Lý trí – Cảm xúc (T/F) và Nguyên tắc – Linh hoạt (J/P).',
                    'Điểm quan trọng nhất khi tiếp cận trắc nghiệm tính cách là hiểu rằng 4 chữ cái không phải chiếc hộp đóng khung con người suốt đời. Mọi người đều sử dụng cả hai phía của mỗi chiều tùy bối cảnh — bài trắc nghiệm chỉ giúp bạn nhận ra phía nào mang lại cảm giác tự nhiên, ít tốn sức hơn khi bạn được là chính mình.',
                ],
            ],
            [
                'id' => 'cach-lam-bai-chinh-xac',
                'heading' => '4 nguyên tắc giúp bạn làm bài trắc nghiệm sát với bản thân nhất',
                'bullets' => [
                    'Trả lời theo con người thật đời thường, không phải hình mẫu lý tưởng ở nơi làm việc hay điều bạn mong muốn trở thành.',
                    'Ưu tiên phản hồi tự nhiên đầu tiên thay vì phân tích quá lâu xem lựa chọn nào "nghe có vẻ tốt hơn" — trên Mây Hẹn không có chiều nào tốt hơn chiều còn lại.',
                    'Sử dụng tùy chọn "Không chắc / Không áp dụng" khi tình huống thực sự không rõ ràng; hệ thống sẽ không ép câu đó thành điểm trung bình làm sai lệch chỉ số.',
                    'Sau khi có kết quả, hãy bấm nút "Xem căn cứ" ở từng chiều và đọc thêm nhóm gần kề (Best-Fit) nếu có chiều nằm trong vùng ranh giới 45–55.',
                ],
            ],
            [
                'id' => 'so-sanh-16-nhom-va-big-five',
                'heading' => 'So sánh 16 nhóm tính cách và mô hình Big Five (OCEAN)',
                'paragraphs' => [
                    'Trong tâm lý học nghiên cứu hiện đại (đặc biệt là công trình đối chiếu nổi tiếng của Robert McCrae và Paul Costa năm 1989), mô hình 5 yếu tố Big Five (Openness, Conscientiousness, Extraversion, Agreeableness, Neuroticism) được đánh giá rất cao về độ tin cậy đo lường trên thang liên tục. Trong khi đó, khung 16 nhóm tính cách lại rất dễ nhớ, giàu hình ảnh và thuận tiện để hai người cùng trò chuyện về thói quen giao tiếp.',
                    'Vì thế, Mây Hẹn triển khai song song cả hai hệ thống hoàn toàn độc lập: bạn có thể khám phá 16 nhóm tại đây để hiểu phong cách tương tác, và làm thêm bài Big Five IPIP 50 câu (Public Domain) để có thêm một góc nhìn khoa học bổ sung về mức độ cởi mở, tận tâm và ổn định cảm xúc.',
                ],
            ],
            [
                'id' => 'ung-dung-tinh-yeu-hon-nhan',
                'heading' => 'Ứng dụng hiểu biết tính cách vào tình yêu, giao tiếp và chuẩn bị hôn nhân',
                'paragraphs' => [
                    'Rất nhiều tranh cãi của các cặp đôi không xuất phát từ việc thiếu yêu thương, mà đến từ sự khác biệt âm thầm trong cách nạp năng lượng (E/I), cách bàn kế hoạch cưới hỏi (J/P) hay kỳ vọng khi tâm sự (T muốn tháo gỡ vấn đề ngay, F muốn được lắng nghe cảm xúc trước).',
                    'Khi hai người cùng làm bài độc lập trên Mây Hẹn Personality for Couples và đối chiếu 6 câu hỏi thực tế về nhịp sinh hoạt, tài chính và giải quyết bất đồng, hai bạn sẽ có một bản đồ giao tiếp rõ ràng để đồng hành nhẹ nhàng từ giai đoạn hẹn hò, chuẩn bị đám cưới cho đến đời sống gia đình lâu dài.',
                ],
            ],
        ];

        return [
            'breadcrumbs' => [
                ['name' => 'Trang chủ', 'url' => url('/')],
                ['name' => 'Trắc nghiệm tính cách', 'url' => $url],
            ],
            'meta' => [
                'author' => 'Ban Biên Tập Tâm Lý & Sản Phẩm Mây Hẹn',
                'updated' => '27/11/2026',
                'reading_time' => '7 phút đọc',
                'badge' => 'Phương pháp minh bạch • Pilot Beta v1.0',
            ],
            'sections' => $sections,
            'faqs' => $faqs,
            'schemas' => [
                $this->buildBreadcrumbSchema([
                    ['name' => 'Trang chủ', 'url' => url('/')],
                    ['name' => 'Trắc nghiệm tính cách', 'url' => $url],
                ]),
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebApplication',
                    'name' => 'Trắc Nghiệm MBTI Online, Test Tính Cách Miễn Phí — Mây Hẹn',
                    'url' => $url,
                    'applicationCategory' => 'LifestyleApplication',
                    'operatingSystem' => 'All',
                    'inLanguage' => 'vi',
                    'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'VND'],
                    'description' => 'Hệ thống trắc nghiệm 16 nhóm tính cách và Big Five OCEAN miễn phí theo thuật toán chấm điểm xác định, minh bạch vùng ranh giới và đối chiếu cặp đôi.',
                ],
                $this->buildFaqSchema($faqs),
            ],
        ];
    }

    /**
     * Pillar SEO Content độc lập, giàu chiều sâu cho TỪNG mã nhóm /trac-nghiem-tinh-cach/{intj..esfp}.
     *
     * @param  array<string, mixed>  $typeInfo
     * @return array<string, mixed>
     */
    public function typeDetailPillar(array $typeInfo): array
    {
        $code = (string) $typeInfo['code'];
        $slug = strtolower($code);
        $url = route('personality.type', ['typeCode' => $slug]);
        $vnName = (string) $typeInfo['vietnamese_name'];
        $artTitle = (string) $typeInfo['art_title'];
        $stack = (array) $typeInfo['function_stack'];
        $cogFns = $this->typeRegistry->cognitiveFunctions();
        $dims = $this->typeRegistry->dimensions16();

        $letterBreakdown = [
            $code[0] => $code[0] === 'E' ? $dims['EI']['positive_name'] : $dims['EI']['negative_name'],
            $code[1] => $code[1] === 'N' ? $dims['SN']['positive_name'] : $dims['SN']['negative_name'],
            $code[2] => $code[2] === 'T' ? $dims['TF']['positive_name'] : $dims['TF']['negative_name'],
            $code[3] => $code[3] === 'J' ? $dims['JP']['positive_name'] : $dims['JP']['negative_name'],
        ];

        $dom = $cogFns[$stack[0] ?? ''] ?? null;
        $aux = $cogFns[$stack[1] ?? ''] ?? null;

        $sections = [
            [
                'id' => 'giai-ma-4-chu-cai',
                'heading' => "{$code} là gì? Giải mã 4 xu hướng cốt lõi của {$vnName}",
                'paragraphs' => [
                    "Trong hệ thống 16 nhóm tính cách, {$code} ({$vnName} — hình tượng nghệ thuật {$artTitle} tại Mây Hẹn) thuộc nhóm {$typeInfo['group']}. {$typeInfo['theory_summary']}",
                    "Bốn chữ cái trong mã {$code} phản ánh tổ hợp xu hướng: ".implode(', ', array_map(fn (string $k, string $v): string => "{$k} ({$v})", array_keys($letterBreakdown), $letterBreakdown)).'. Tuy nhiên, hai người cùng nhận kết quả '.$code.' vẫn có thể rất khác nhau về trải nghiệm sống, mức độ nghiêng ở từng chiều và hoàn cảnh trưởng thành.',
                ],
            ],
            [
                'id' => 'chuc-nang-nhan-thuc-doi-song',
                'heading' => 'Cách chuỗi chức năng nhận thức '.implode(' → ', $stack)." vận hành ở nhóm {$code}",
                'paragraphs' => [
                    'Theo quy ước lý thuyết tham chiếu từ Carl Gustav Jung và Isabel Myers, hai chức năng dẫn dắt chính của '.$code.' là '.($dom ? "{$dom['code']} — {$dom['name']}: {$dom['definition']}" : ($stack[0] ?? '')).' kết hợp cùng chức năng phụ trợ '.($aux ? "{$aux['code']} — {$aux['name']}: {$aux['definition']}" : ($stack[1] ?? '')).'.',
                    'Lưu ý khoa học: chuỗi chức năng nhận thức suy ra từ mã '.$code.' là khung lý thuyết giúp bạn tự quan sát thói quen tư duy, không phải chỉ số đo trực tiếp từ vùng não và cũng không có tỷ lệ phần trăm cố định.',
                ],
            ],
            [
                'id' => 'tinh-yeu-hon-nhan-giao-tiep',
                'heading' => "Nhóm {$code} trong tình yêu, hôn nhân và cách giao tiếp để thấu hiểu nhau",
                'paragraphs' => [
                    "Về phong cách giao tiếp: {$typeInfo['communication_style']}",
                    "Trong tình yêu và đời sống gia đình: {$typeInfo['love_theme']} {$typeInfo['family_theme']} Khi đồng hành cùng người thuộc nhóm {$code}, việc trao đổi thẳng thắn về nhu cầu không gian riêng, cách lắng nghe và nhịp lên kế hoạch sẽ giúp mối quan hệ bền vững hơn rất nhiều so với việc áp đặt khuôn mẫu.",
                ],
            ],
            [
                'id' => 'cong-viec-phat-trien-ban-than',
                'heading' => "Phong cách làm việc, học tập và gợi ý phát triển bản thân cho {$code}",
                'paragraphs' => [
                    "{$typeInfo['work_learning_style']} Lưu ý rằng mã {$code} không bao giờ giới hạn ngành nghề bạn có thể theo đuổi — người thuộc bất kỳ nhóm tính cách nào cũng có thể thành công ở nhiều lĩnh vực khác nhau nếu tìm được cách làm việc phù hợp.",
                ],
                'bullets' => array_merge(
                    (array) $typeInfo['growth_prompts'],
                    ['Nếu một chiều trong bài test của bạn nằm trong vùng 45–55, hãy đối chiếu thêm các nhóm gần kề: '.implode(', ', (array) $typeInfo['adjacent_types']).'.']
                ),
            ],
        ];

        $faqs = [
            [
                'q' => "Nhóm tính cách {$code} hợp với nhóm nào nhất trong tình yêu?",
                'a' => "Không có cặp nhóm nào mặc định hợp hay khắc tuyệt đối. Người thuộc nhóm {$code} có thể xây dựng mối quan hệ hạnh phúc với bất kỳ nhóm nào trong 16 nhóm nếu cả hai tôn trọng khác biệt giao tiếp, lắng nghe nhau và chia sẻ giá trị sống chung.",
            ],
            [
                'q' => "Điểm khác nhau giữa {$code} và các nhóm gần kề (".implode(', ', (array) $typeInfo['adjacent_types']).') là gì?',
                'a' => "Mỗi nhóm gần kề chỉ khác {$code} đúng 1 trong 4 chiều (E/I, S/N, T/F hoặc J/P). Nếu điểm số của bạn nằm gần ranh giới 45–55, bạn nên đọc cả mô tả nhóm gần kề và dùng tính năng Best-Fit sau khi làm bài test.",
            ],
            [
                'q' => "Có tồn tại biến thể {$code}-A và {$code}-T trong bài test này không?",
                'a' => 'Không. Hậu tố -A (Assertive) và -T (Turbulent) là phần mở rộng riêng của mô hình NERIS (16Personalities), không thuộc 4 cặp xu hướng gốc. Nếu muốn đo chiều ổn định cảm xúc một cách độc lập, bạn có thể làm bài Big Five (OCEAN) trên Mây Hẹn.',
            ],
            [
                'q' => "Làm sao để biết mình có thực sự thuộc nhóm {$code} hay không?",
                'a' => "Hãy làm Bản Tiêu Chuẩn 64 câu hoặc Chuyên Sâu 96 câu trên Mây Hẹn, sau đó mở nút 'Xem căn cứ' ở từng chiều để kiểm tra lại chính các câu trả lời của mình và đối chiếu với trải nghiệm đời sống thực tế.",
            ],
        ];

        return [
            'seo_title' => "{$code} Là Gì? Tính Cách {$vnName} ({$artTitle}) Trong Tình Yêu & Công Việc",
            'seo_description' => "Giải mã trọn vẹn nhóm tính cách {$code} ({$vnName} — {$artTitle}): 4 chiều xu hướng, Function Stack ".implode('-', $stack).', điểm mạnh, tình yêu, giao tiếp, công việc và các nhóm gần kề.',
            'breadcrumbs' => [
                ['name' => 'Trang chủ', 'url' => url('/')],
                ['name' => 'Trắc nghiệm tính cách', 'url' => route('personality.index')],
                ['name' => '16 nhóm tính cách', 'url' => route('personality.gallery')],
                ['name' => $code.' — '.$artTitle, 'url' => $url],
            ],
            'meta' => [
                'author' => 'Ban Biên Tập Tâm Lý Mây Hẹn',
                'updated' => '27/11/2026',
                'reading_time' => '6 phút đọc',
                'badge' => "Hồ sơ chuyên sâu {$code} • {$typeInfo['group']}",
            ],
            'sections' => $sections,
            'faqs' => $faqs,
            'schemas' => [
                $this->buildBreadcrumbSchema([
                    ['name' => 'Trang chủ', 'url' => url('/')],
                    ['name' => 'Trắc nghiệm tính cách', 'url' => route('personality.index')],
                    ['name' => '16 nhóm tính cách', 'url' => route('personality.gallery')],
                    ['name' => $code.' — '.$artTitle, 'url' => $url],
                ]),
                $this->buildArticleSchema("{$code} Là Gì? Tính Cách {$vnName} ({$artTitle})", $typeInfo['theory_summary'], $url),
                $this->buildFaqSchema($faqs),
            ],
        ];
    }

    /**
     * Pillar SEO Content cho các chuyên trang con (gallery, couple, cognitive, ei, methodology, big-five).
     *
     * @return array<string, mixed>
     */
    public function subpagePillar(string $pageKey): array
    {
        return match ($pageKey) {
            'gallery' => $this->buildGenericPillar(
                url: route('personality.gallery'),
                crumbName: 'Triển lãm 16 nhóm tính cách',
                title: 'Tổng Quan 16 Nhóm Tính Cách & 4 Nhóm Khí Chất (NT, NF, SJ, SP)',
                description: 'Khám phá trọn bộ 16 nhóm tính cách qua triển lãm 16 hình tượng nghệ thuật 3D độc quyền Mây Hẹn, phân nhóm rõ ràng và trung lập.',
                sections: [
                    [
                        'id' => 'bon-nhom-khi-chat',
                        'heading' => 'Cách phân loại 16 nhóm thành 4 cụm xu hướng dễ nhớ',
                        'bullets' => [
                            'Nhóm Tư duy & Hệ thống (NT — INTJ, INTP, ENTJ, ENTP): Ưu tiên nguyên lý, mô hình dài hạn và phân tích khách quan.',
                            'Nhóm Lý tưởng & Giá trị (NF — INFJ, INFP, ENFJ, ENFP): Đề cao ý nghĩa sâu sắc, sự chân thành và kết nối nhân văn.',
                            'Nhóm Thực tế & Cấu trúc (SJ — ISTJ, ISFJ, ESTJ, ESFJ): Coi trọng độ tin cậy, trật tự, trách nhiệm và gìn giữ giá trị bền vững.',
                            'Nhóm Trải nghiệm & Linh hoạt (SP — ISTP, ISFP, ESTP, ESFP): Nhạy bén với hiện tại, linh hoạt ứng biến và giàu cảm thụ thực tế.',
                        ],
                    ],
                    [
                        'id' => 'y-nghia-16-hinh-tuong',
                        'heading' => 'Ý nghĩa bộ sưu tập 16 hình tượng Fine Art của Mây Hẹn',
                        'paragraphs' => [
                            'Thay vì sử dụng nhân vật hoạt hình dễ tạo định kiến về giới tính hay ngoại hình, Mây Hẹn thiết kế 16 tác phẩm điêu khắc biểu trưng từ đá cẩm thạch, đồng cổ, lăng kính pha lê, hoa sen và dải lụa. Bạn có thể kéo xoay trực tiếp từng tác phẩm ở chế độ 3D tương tác hoặc chuyển sang chế độ 2D Editorial tối giản.',
                        ],
                    ],
                ],
                faqs: [
                    ['q' => 'Trong 16 nhóm tính cách, có nhóm nào hiếm hơn hay giỏi hơn không?', 'a' => 'Không có nhóm nào cao cấp hay vượt trội hơn nhóm khác. Độ phổ biến thống kê khác nhau giữa các mẫu khảo sát và hoàn toàn không phản ánh giá trị hay năng lực của một cá nhân.'],
                    ['q' => 'Làm sao để so sánh hai nhóm tính cách gần nhau?', 'a' => 'Bạn có thể bấm vào bất kỳ tác phẩm nào trong triển lãm để mở trang chi tiết của nhóm đó và xem danh sách 4 nhóm gần kề (Best-Fit) ở cuối trang.'],
                ]
            ),
            'couple' => $this->buildGenericPillar(
                url: route('personality.love'),
                crumbName: 'Tính cách cặp đôi & Tình duyên',
                title: 'MBTI Tình Duyên & Cách Hai Nhóm Tính Cách Thấu Hiểu Nhau',
                description: 'Hướng dẫn đối chiếu tính cách cặp đôi 16×16 khách quan: hiểu khác biệt E/I, S/N, T/F, J/P trong tình yêu, giao tiếp và chuẩn bị hôn nhân — không tạo % hợp nhau giả.',
                sections: [
                    [
                        'id' => 'vi-sao-khong-dung-phan-tram-hop-nhau',
                        'heading' => 'Vì sao không nên tin vào bảng "tỷ lệ hợp nhau 98%" theo 4 chữ cái?',
                        'paragraphs' => [
                            'Nhiều trang web giải trí thường gắn một con số như "INTJ và ENFP hợp nhau 96%" hoặc "hai nhóm này khắc nhau không nên yêu". Trên thực tế, tâm lý học hôn nhân cho thấy chất lượng mối quan hệ phụ thuộc vào mức độ tôn trọng, kỹ năng giải quyết bất đồng và sự đồng thuận về giá trị sống — chứ không phải một mã chữ cố định.',
                            'Tại Mây Hẹn, hệ thống so sánh cặp đôi cho phép mỗi người làm bài độc lập, đối chiếu sự chênh lệch điểm thực tế trên 4 chiều và trả lời 6 câu hỏi đời sống (nhịp giải quyết bất đồng, ngôn ngữ quan tâm, không gian riêng, tài chính gia đình) để đưa ra gợi ý đối thoại thiết thực nhất.',
                        ],
                    ],
                    [
                        'id' => 'bon-khac-biet-pho-bien-cap-doi',
                        'heading' => '4 khác biệt tính cách phổ biến nhất ở các cặp đôi Việt Nam',
                        'bullets' => [
                            'Khác biệt E – I: Một người muốn tụ họp gia đình/bạn bè cuối tuần để vui hơn, người kia cần buổi tối yên tĩnh để hồi phục năng lượng.',
                            'Khác biệt S – N: Khi bàn chuyện cưới hỏi, người thiên S tập trung ngay vào ngân sách, mâm cỗ, giờ đón dâu; người thiên N thích bàn concept, câu chuyện tình yêu và cảm xúc tổng thể.',
                            'Khác biệt T – F: Khi có chuyện buồn, người thiên T hay đưa ngay phương án sửa lỗi còn người thiên F cần một cái ôm và sự lắng nghe trước.',
                            'Khác biệt J – P: Người thiên J thấy yên tâm khi chốt xong lịch trình trước nhiều tháng, còn người thiên P thích giữ sự thoải mái linh hoạt.',
                        ],
                    ],
                ],
                faqs: [
                    ['q' => 'Tôi có thể làm bài thay người yêu để xem kết quả cặp đôi không?', 'a' => 'Mây Hẹn khuyến khích mỗi người tự làm bài độc lập để phản ánh đúng suy nghĩ thật của từng người, tránh tình trạng góc nhìn chủ quan làm sai lệch kết quả đối chiếu.'],
                    ['q' => 'Hai người khác nhau cả 4 chiều có yêu và cưới nhau hạnh phúc được không?', 'a' => 'Hoàn toàn có thể. Khác biệt lớn mang lại khả năng bổ trợ rất tốt cho nhau nếu hai bạn hiểu rõ dấu hiệu cần lắng nghe và thống nhất các nguyên tắc giao tiếp cơ bản.'],
                ]
            ),
            'cognitive' => $this->buildGenericPillar(
                url: route('personality.cognitive_functions'),
                crumbName: '8 Chức năng Nhận thức',
                title: '8 Chức Năng Nhận Thức (Cognitive Functions) Trong Lý Thuyết Carl Jung',
                description: 'Giải thích dễ hiểu 8 chức năng nhận thức Se, Si, Ne, Ni, Te, Ti, Fe, Fi và cách đọc chuỗi Function Stack (Dominant, Auxiliary, Tertiary, Inferior) của 16 nhóm tính cách.',
                sections: [
                    [
                        'id' => 'cau-truc-4-tang-function-stack',
                        'heading' => '4 vị trí trong chuỗi Function Stack có ý nghĩa gì?',
                        'bullets' => [
                            'Dominant (Chức năng chủ đạo): Cách tiếp nhận hoặc đánh giá tự nhiên nhất, thường phát triển rõ từ sớm.',
                            'Auxiliary (Chức năng phụ trợ): Giúp cân bằng hướng nội/hướng ngoại với chức năng chủ đạo để ứng xử hài hòa với thế giới bên ngoài.',
                            'Tertiary (Chức năng thứ ba): Phát triển dần theo trải nghiệm trưởng thành, mang lại sự linh hoạt khi được rèn luyện.',
                            'Inferior (Chức năng kém phát triển hơn): Thường là điểm dễ mệt mỏi khi chịu áp lực kéo dài, nhưng cũng là nguồn cảm hứng trưởng thành quan trọng.',
                        ],
                    ],
                ],
                faqs: [
                    ['q' => 'Chức năng nhận thức có phải là vùng não đo bằng máy quét không?', 'a' => 'Không. Đây là khái niệm mô tả tâm lý học từ lý thuyết của Carl Gustav Jung, không phải chỉ số sinh học hay vùng giải phẫu thần kinh.'],
                ]
            ),
            'ei' => $this->buildGenericPillar(
                url: route('personality.ei_guide'),
                crumbName: 'Hướng nội & Hướng ngoại (E/I)',
                title: 'Hướng Nội (I) và Hướng Ngoại (E): Hiểu Đúng Bản Chất Năng Lượng Tâm Lý',
                description: 'Phân biệt chính xác hướng nội và hướng ngoại trong tâm lý học: xóa bỏ hiểu lầm hướng nội là nhút nhát, hướng ngoại là nói nhiều, và giải thích vùng ranh giới Ambivert.',
                sections: [
                    [
                        'id' => 'khac-biet-nang-luong-ei',
                        'heading' => 'Sự khác biệt cốt lõi giữa Hướng nội (I) và Hướng ngoại (E)',
                        'paragraphs' => [
                            'Trong tâm lý học tính cách, E và I không đo kỹ năng giao tiếp hay sự tự tin. Một người hướng nội hoàn toàn có thể thuyết trình xuất sắc trước hàng trăm người, nhưng sau buổi sự kiện đó họ cần một khoảng lặng riêng để hồi phục năng lượng. Ngược lại, một người hướng ngoại có thể vẫn ngại đám đông lạ, nhưng họ thường cảm thấy tỉnh táo và sáng rõ ý tưởng hơn khi được trao đổi trực tiếp cùng người khác.',
                            'Nếu chỉ số E/I của bạn nằm trong khoảng 45–55 trên Mây Hẹn, điều đó cho thấy bạn mang đặc điểm linh hoạt ở giữa (thường được gọi thân mật là hướng trung) — có thể thích nghi mượt mà giữa không gian sôi nổi và thời gian một mình.',
                        ],
                    ],
                ],
                faqs: [
                    ['q' => 'Hướng nội có phải là bệnh lo âu xã hội không?', 'a' => 'Hoàn toàn không. Hướng nội là xu hướng nạp năng lượng tự nhiên lành mạnh, còn lo âu xã hội là một tình trạng tâm lý gây đau khổ và né tránh cần chuyên gia hỗ trợ.'],
                ]
            ),
            'big_five' => $this->buildGenericPillar(
                url: route('personality.big_five'),
                crumbName: 'Trắc nghiệm Big Five (OCEAN)',
                title: 'Trắc Nghiệm Big Five (OCEAN) Là Gì? Cách Đọc 5 Yếu Tố Tính Cách IPIP-50',
                description: 'Tìm hiểu mô hình 5 yếu tố tính cách Big Five (Openness, Conscientiousness, Extraversion, Agreeableness, Neuroticism) dựa trên bộ câu hỏi IPIP Public Domain.',
                sections: [
                    [
                        'id' => 'vi-sao-big-five-duoc-tin-dung',
                        'heading' => 'Vì sao Big Five (OCEAN) là mô hình phổ biến nhất trong nghiên cứu tâm lý học?',
                        'paragraphs' => [
                            'Thay vì phân con người vào hai cực rời rạc, Big Five đo 5 đặc điểm cốt lõi trên thang liên tục từ 0 đến 100. Mô hình này được xây dựng từ phân tích nhân tố trên hàng chục nghìn mẫu dữ liệu đa văn hóa bởi các nhà nghiên cứu như Lewis Goldberg, Robert McCrae và Paul Costa.',
                            'Bộ câu hỏi 50 câu trên Mây Hẹn bám sát cấu trúc IPIP 50-Item Big-Five Factor Markers (Public Domain — ipip.ori.org), hiển thị rõ cả điểm Nhạy cảm cảm xúc (Neuroticism) lẫn điểm Ổn định cảm xúc (Emotional Stability = 100 - N) để người đọc tiếp cận trung lập, không kỳ thị.',
                        ],
                    ],
                ],
                faqs: [
                    ['q' => 'Điểm Neuroticism cao trong Big Five có nghĩa là bị bệnh tâm lý không?', 'a' => 'Tuyệt đối không. Neuroticism chỉ phản ánh mức độ nhạy cảm tự nhiên của hệ cảm xúc trước áp lực hằng ngày; không phải công cụ chẩn đoán y khoa hay tâm thần.'],
                    ['q' => 'Tại sao Mây Hẹn không hiển thị tôi thuộc Top 10% dân số ở mỗi yếu tố?', 'a' => 'Để tính Percentile trung thực cần một tập dữ liệu chuẩn hóa quy mô lớn của đúng dân số Việt Nam. Khi đang ở giai đoạn Pilot, Mây Hẹn chỉ hiển thị điểm trên thang nội bộ 0–100 để bảo đảm tính khoa học.'],
                ]
            ),
            default => $this->buildGenericPillar(
                url: route('personality.methodology'),
                crumbName: 'Phương pháp & Minh bạch',
                title: 'Báo Cáo Minh Bạch Phương Pháp Đo Lường Tính Cách Mây Hẹn',
                description: 'Công khai đầy đủ công thức chấm điểm, kiểm soát chất lượng câu trả lời, nguồn học thuật quốc tế và quy chuẩn đạo đức của Mây Hẹn – The Art of Personality.',
                sections: [
                    [
                        'id' => 'lo-trinh-kiem-dinh-psychometrics',
                        'heading' => 'Lộ trình kiểm định tâm lý học (Psychometrics) minh bạch',
                        'paragraphs' => [
                            'Mây Hẹn phân biệt rõ hai khái niệm: Kiểm thử phần mềm (Unit/Feature Test đảm bảo công thức toán học chính xác 100%) và Kiểm định tâm lý học (đo Cronbach\'s Alpha, McDonald\'s Omega, EFA/CFA và Test-Retest trên dữ liệu khảo sát người Việt thực tế). Chỉ khi hoàn tất đầy đủ nghiên cứu thực nghiệm độc lập, phương pháp mới chuyển từ trạng thái Pilot Beta sang Validated.',
                        ],
                    ],
                ],
                faqs: [
                    ['q' => 'AI có tham gia chấm điểm bài trắc nghiệm không?', 'a' => 'Không bao giờ. Toàn bộ điểm số, phân loại chiều và calculation_hash đều do Scoring Engine xác định xử lý 100% trước khi chuyển dữ liệu có cấu trúc cho AI biên tập báo cáo 12 tầng.'],
                ]
            ),
        };
    }

    /**
     * @param  list<array{id: string, heading: string, paragraphs?: list<string>, bullets?: list<string>}>  $sections
     * @param  list<array{q: string, a: string}>  $faqs
     * @return array<string, mixed>
     */
    protected function buildGenericPillar(
        string $url,
        string $crumbName,
        string $title,
        string $description,
        array $sections,
        array $faqs
    ): array {
        $breadcrumbs = [
            ['name' => 'Trang chủ', 'url' => url('/')],
            ['name' => 'Trắc nghiệm tính cách', 'url' => route('personality.index')],
            ['name' => $crumbName, 'url' => $url],
        ];

        return [
            'breadcrumbs' => $breadcrumbs,
            'meta' => [
                'author' => 'Ban Biên Tập Tâm Lý Mây Hẹn',
                'updated' => '27/11/2026',
                'reading_time' => '5 phút đọc',
                'badge' => 'Chuẩn học thuật minh bạch',
            ],
            'sections' => $sections,
            'faqs' => $faqs,
            'schemas' => [
                $this->buildBreadcrumbSchema($breadcrumbs),
                $this->buildArticleSchema($title, $description, $url),
                $this->buildFaqSchema($faqs),
            ],
        ];
    }

    /**
     * @param  list<array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    protected function buildBreadcrumbSchema(array $items): array
    {
        $list = [];
        foreach ($items as $idx => $item) {
            $list[] = [
                '@type' => 'ListItem',
                'position' => $idx + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildArticleSchema(string $headline, string $description, string $url): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $headline,
            'description' => $description,
            'mainEntityOfPage' => $url,
            'inLanguage' => 'vi',
            'datePublished' => '2026-11-27',
            'dateModified' => '2026-11-27',
            'author' => ['@type' => 'Organization', 'name' => 'Mây Hẹn', 'url' => url('/')],
            'publisher' => ['@type' => 'Organization', 'name' => 'Mây Hẹn', 'url' => url('/')],
        ];
    }

    /**
     * @param  list<array{q: string, a: string}>  $faqs
     * @return array<string, mixed>
     */
    protected function buildFaqSchema(array $faqs): array
    {
        $entities = [];
        foreach ($faqs as $f) {
            $entities[] = [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $f['a'],
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entities,
        ];
    }
}
