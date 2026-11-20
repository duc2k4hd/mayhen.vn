<?php

declare(strict_types=1);

namespace App\Services\Intelligence\V4;

use App\Models\AiKnowledgeEntry;
use App\Models\AiResearchClaim;
use App\Models\AiResearchGoal;
use App\Models\AiResearchTopic;
use Illuminate\Support\Str;

/**
 * Taxonomy, Ontology, Semantic Classification & Relevance Gate V4.0:
 * - Giải quyết tận gốc lỗi phân loại sai (Lễ Hằng thuận -> rsvp, Google Sites -> templates),
 *   lỗi học lan man (Shinzen kekkon Nhật Bản, Wix/Squarespace/Gmail/Website sơ đẳng) và chấm điểm ảo 1 nguồn.
 */
class OntologyAndClassificationService
{
    public const ONTOLOGY_DOMAINS = [
        'mayhen_product' => 'Sản phẩm & Chính sách Mây Hẹn',
        'vietnamese_wedding_culture' => 'Văn hóa & Nghi lễ Cưới Việt Nam',
        'digital_invitations' => 'Thiệp cưới Điện tử & Trải nghiệm Mời cưới',
        'wedding_planning_rsvp' => 'Quản lý Khách mời, RSVP, Bàn tiệc & Điều phối',
        'wedding_customer_experience' => 'Trải nghiệm & Hành trình Khách hàng Cưới',
        'wedding_product_innovation' => 'Nghiên cứu Công cụ & Ý tưởng Sản phẩm',
    ];

    public const KNOWLEDGE_SPACES = [
        AiResearchClaim::SPACE_OFFICIAL_MAYHEN => 'Chính thức Mây Hẹn',
        AiResearchClaim::SPACE_INDUSTRY_CULTURAL => 'Ngành cưới & Văn hóa VN',
        AiResearchClaim::SPACE_PRODUCT_INNOVATION => 'Nghiên cứu & Ý tưởng Sản phẩm',
        AiResearchClaim::SPACE_CUSTOMER_INTELLIGENCE => 'Tín hiệu Nhu cầu Khách hàng',
    ];

    /**
     * 13 kiểu quan hệ có cấu trúc chuẩn của Typed Knowledge Graph (Mục VIII).
     *
     * @var list<string>
     */
    public const ALLOWED_RELATION_TYPES = [
        'is_a',
        'part_of',
        'requires',
        'related_to',
        'used_for',
        'precedes',
        'depends_on',
        'differs_by_region',
        'supports_feature',
        'answers_intent',
        'contradicts',
        'supersedes',
        'suggests_improvement',
    ];

    /**
     * Phân cấp nguồn theo Mục 7.1 — Wikipedia chỉ là nguồn khám phá ban đầu (seed), không tự đạt tin cậy tuyệt đối.
     *
     * @return array{tier: string, label: string, max_reliability: int}
     */
    public function classifySourceTier(string $domain, string $discoveryMethod = ''): array
    {
        $d = strtolower(trim($domain));

        if ($d === 'mayhen.vn' || str_ends_with($d, '.mayhen.vn') || $discoveryMethod === 'internal_official') {
            return ['tier' => 'authority_official', 'label' => 'Nguồn chính thức nội bộ Mây Hẹn', 'max_reliability' => 98];
        }

        if (str_ends_with($d, '.gov.vn') || str_ends_with($d, '.edu.vn')) {
            return ['tier' => 'authority_official', 'label' => 'Cơ quan / Tổ chức chuyên môn', 'max_reliability' => 88];
        }

        if (str_ends_with($d, 'wikipedia.org') || $discoveryMethod === 'wikipedia_api') {
            return ['tier' => 'wikipedia_seed', 'label' => 'Wikipedia (Nguồn khám phá ban đầu)', 'max_reliability' => 65];
        }

        $industrySpecialists = ['marry.vn', 'vnexpress.net', 'tuoitre.vn', 'thanhnien.vn', 'elle.vn', 'vogue.com', 'theknot.com', 'brides.com', 'weddingbook.vn', 'blissvn.com'];
        foreach ($industrySpecialists as $spec) {
            if ($d === $spec || str_ends_with($d, '.'.$spec)) {
                return ['tier' => 'industry_media', 'label' => 'Chuyên trang / Báo chí uy tín', 'max_reliability' => 78];
            }
        }

        if (str_contains($d, 'researchgate') || str_contains($d, 'scholar') || str_contains($d, 'jstor')) {
            return ['tier' => 'research_paper', 'label' => 'Tài liệu nghiên cứu', 'max_reliability' => 84];
        }

        return ['tier' => 'community_experience', 'label' => 'Website cộng đồng / Tham khảo', 'max_reliability' => 58];
    }

    /**
     * Bộ lọc độ liên quan chặt ngay từ tầng Web Discovery (chặn Wix/Squarespace/Google Sites/Gmail/Website sơ đẳng/ngoại lai lạc đề).
     *
     * @return array{eligible: bool, relevance_score: int, reason: string}
     */
    public function evaluateDiscoveryRelevance(
        string $title,
        string $snippet,
        string $canonicalUrl,
        string $domain,
        AiResearchTopic $topic,
        ?AiResearchGoal $goal = null
    ): array {
        $d = strtolower(trim($domain));
        $urlLower = strtolower(urldecode($canonicalUrl));
        $blobAscii = Str::ascii(mb_strtolower($title.' '.$snippet.' '.urldecode($canonicalUrl), 'UTF-8'));

        $isWeddingGoal = $this->isWeddingRelatedGoal($topic, $goal);

        // 1. Chặn dứt điểm các trang công cụ chung chung / trang tài liệu trợ giúp không chuyên ngành cưới khi đang học ngành cưới VN
        if ($isWeddingGoal) {
            $genericNoiseDomains = [
                'wix.com', 'squarespace.com', 'website.com', 'workspace.google.com', 'support.google.com',
                'godaddy.com', 'weebly.com', 'wordpress.com', 'shopify.com', 'ktcc.edu.vn', 'nghengu.vn',
                'tudientiengviet.org', 'tratu.soha.vn', 'quanpos.vn', 'fpt.vn',
            ];
            foreach ($genericNoiseDomains as $noise) {
                if ($d === $noise || str_ends_with($d, '.'.$noise)) {
                    return ['eligible' => false, 'relevance_score' => 10, 'reason' => "off_topic_generic_tool_or_dict:{$noise}"];
                }
            }

            // Chặn bài Wikipedia tiếng Anh/Việt quá sơ đẳng hoặc phong tục nước ngoài không thuộc mục tiêu cưới VN
            $noiseUrlNeedles = [
                '/wiki/website', '/wiki/shinzen_kekkon', '/wiki/am_thuc', '/wiki/%e1%ba%a9m_th%e1%bb%b1c',
                '/wiki/ham_nghi', '/wiki/h%c3%a0m_nghi', '/wiki/thanh_lam', '/wiki/the_odyssey',
                '/wiki/viet_nam', '/wiki/vi%e1%bb%87t_nam', '/wiki/da_nang', '/wiki/bac_ninh',
                'gui-hay-goi', 'goi-hay-gui', 'gmail',
            ];
            foreach ($noiseUrlNeedles as $needle) {
                if (str_contains($urlLower, $needle) || str_contains($blobAscii, $needle)) {
                    return ['eligible' => false, 'relevance_score' => 12, 'reason' => 'off_topic_trivial_or_foreign_source'];
                }
            }

            // Phải có ít nhất 1 cụm từ chuyên ngành cưới / sự kiện cưới thực thụ!
            $weddingAnchors = [
                'thiep cuoi', 'dam cuoi', 'le cuoi', 'le an hoi', 'le dam ngo', 'le gia tien', 'le vu quy',
                'le thanh hon', 'le tan hon', 'le hang thuan', 'tiec cuoi', 'co dau', 'chu re', 'rsvp',
                'khach moi', 'ban tiec', 'check in', 'bao hy', 'cuoi hoi', 'hon nhan', 'mung cuoi', 'wedding',
            ];
            $anchorHits = 0;
            foreach ($weddingAnchors as $anchor) {
                if (str_contains($blobAscii, $anchor)) {
                    $anchorHits++;
                }
            }
            if ($anchorHits === 0) {
                return ['eligible' => false, 'relevance_score' => 15, 'reason' => 'no_wedding_domain_anchor'];
            }
        }

        // 2. Tính điểm khớp chủ đề đa từ khóa (cấm chỉ khớp 1 từ đơn chung chung như "website" hay "gửi")
        $stopWords = [
            'la', 'gi', 'va', 'cua', 'cho', 'tai', 'trong', 've', 'cac', 'nhung', 'voi', 'khi',
            'cach', 'nhu', 'the', 'nao', 'viet', 'nam', 'chuan', 'chi', 'tiet', 'hien', 'dai', 'moi',
            'nhat', 'website', 'web', 'online', 'gui', 'goi', 'tao', 'lam', 'xem', 'tim',
        ];
        $topicText = Str::ascii(mb_strtolower($topic->title.' '.implode(' ', (array) ($topic->search_queries ?? [])), 'UTF-8'));
        $topicTokens = array_values(array_unique(array_filter(
            explode(' ', preg_replace('/[^a-z0-9\s]/', ' ', $topicText) ?? ''),
            fn (string $t) => strlen($t) >= 3 && ! in_array($t, $stopWords, true)
        )));

        $titleAscii = Str::ascii(mb_strtolower($title, 'UTF-8'));
        $matchedTokens = 0;
        $score = 0;
        foreach ($topicTokens as $tk) {
            if (str_contains($titleAscii, $tk)) {
                $matchedTokens++;
                $score += 24;
            } elseif (str_contains($blobAscii, $tk)) {
                $matchedTokens++;
                $score += 12;
            }
        }

        $score = min(100, $score);
        if ($matchedTokens < 2 || $score < 36) {
            return ['eligible' => false, 'relevance_score' => $score, 'reason' => 'insufficient_topic_relevance'];
        }

        return ['eligible' => true, 'relevance_score' => $score, 'reason' => 'ok'];
    }

    /**
     * Phân loại ngữ nghĩa & ontology chính xác cho 1 bản ghi/claim — sửa dứt điểm lỗi gán sai category.
     *
     * @return array{
     *     ontology_domain: string,
     *     category: string,
     *     knowledge_space: string,
     *     knowledge_layer: string,
     *     claim_type: string,
     *     applicable_region: string,
     *     business_relevance: int,
     *     cultural_applicability: int,
     *     is_trivial_or_off_topic: bool,
     *     classification_note: string
     * }
     */
    public function classifyKnowledgeRecord(
        string $title,
        string $body,
        ?string $sourceUrl = null,
        ?string $sourceDomain = null,
        string $sourceOrigin = 'web_research'
    ): array {
        $text = Str::ascii(mb_strtolower($title.' '.$body, 'UTF-8'));
        $urlLower = strtolower(urldecode((string) ($sourceUrl ?? '')));
        $domainLower = strtolower((string) ($sourceDomain ?? ''));

        // 1. Nguồn chính thức nội bộ Mây Hẹn (Admin hoặc Self-train nội bộ có đối chiếu bảng giá/tính năng)
        if (in_array($sourceOrigin, [
            AiKnowledgeEntry::SOURCE_ADMIN,
            AiKnowledgeEntry::SOURCE_AI_SELF_TRAIN,
            'internal_official',
        ], true)) {
            $category = $this->inferInternalCategory($text);

            return [
                'ontology_domain' => 'mayhen_product',
                'category' => $category,
                'knowledge_space' => AiResearchClaim::SPACE_OFFICIAL_MAYHEN,
                'knowledge_layer' => AiKnowledgeEntry::LAYER_OFFICIAL_MAYHEN,
                'claim_type' => $category === 'pricing' ? 'policy' : 'fact',
                'applicable_region' => 'vietnam_general',
                'business_relevance' => 95,
                'cultural_applicability' => 90,
                'is_trivial_or_off_topic' => false,
                'classification_note' => 'Tri thức chính thức nội bộ Mây Hẹn.',
            ];
        }

        // 2. Phát hiện nội dung quá sơ đẳng / chính tả chung / Gmail hướng dẫn chung / ẩm thực không gắn cưới
        $trivialPatterns = [
            '/gui.{0,12}hay.{0,12}goi/i',
            '/goi.{0,12}hay.{0,12}gui/i',
            '/gmail.{0,40}(huy|bcc|email|nhieu nguoi)/i',
            '/website la tap hop cac trang web/i',
            '/khong phai moi website deu cong khai/i',
            '/website co the duoc truy cap bang trinh duyet/i',
            '/am thuc chung de xac dinh/i',
        ];
        foreach ($trivialPatterns as $pat) {
            if (preg_match($pat, $text) === 1 || str_contains($urlLower, 'support.google.com/mail') || str_contains($urlLower, '/wiki/website') || str_contains($urlLower, 'gui-hay-goi') || str_contains($urlLower, 'goi-hay-gui')) {
                return [
                    'ontology_domain' => 'wedding_product_innovation',
                    'category' => 'general',
                    'knowledge_space' => AiResearchClaim::SPACE_PRODUCT_INNOVATION,
                    'knowledge_layer' => AiKnowledgeEntry::LAYER_APPLIED_INSIGHT,
                    'claim_type' => 'ux_principle',
                    'applicable_region' => 'vietnam_general',
                    'business_relevance' => 18,
                    'cultural_applicability' => 25,
                    'is_trivial_or_off_topic' => true,
                    'classification_note' => 'Kiến thức quá cơ bản / chính tả / công cụ phổ thông không chuyên sâu ngành cưới.',
                ];
            }
        }

        // 3. Công cụ xây web bên ngoài (Google Sites, Squarespace, Wix) -> Product Research & Innovation, KHÔNG phải Mẫu Thiệp Cưới!
        if (str_contains($text, 'google sites') || str_contains($text, 'squarespace') || str_contains($text, 'wix')
            || in_array($domainLower, ['workspace.google.com', 'squarespace.com', 'wix.com'], true)) {
            return [
                'ontology_domain' => 'wedding_product_innovation',
                'category' => 'general',
                'knowledge_space' => AiResearchClaim::SPACE_PRODUCT_INNOVATION,
                'knowledge_layer' => AiKnowledgeEntry::LAYER_APPLIED_INSIGHT,
                'claim_type' => 'product_idea',
                'applicable_region' => 'vietnam_general',
                'business_relevance' => 42,
                'cultural_applicability' => 45,
                'is_trivial_or_off_topic' => false,
                'classification_note' => 'Nghiên cứu công cụ / gợi ý cải tiến UX — không phải tính năng hay mẫu thiệp của Mây Hẹn.',
            ];
        }

        // 4. Nghi lễ nước ngoài (Shinzen kekkon / Thần đạo Nhật Bản) -> Product Research nếu gợi ý ý tưởng, KHÔNG coi là phong tục VN hay tính năng RSVP!
        if (str_contains($urlLower, 'shinzen_kekkon') || str_contains($text, 'than dao') || str_contains($text, 'nhat ban')) {
            return [
                'ontology_domain' => 'wedding_product_innovation',
                'category' => 'general',
                'knowledge_space' => AiResearchClaim::SPACE_PRODUCT_INNOVATION,
                'knowledge_layer' => AiKnowledgeEntry::LAYER_APPLIED_INSIGHT,
                'claim_type' => 'product_idea',
                'applicable_region' => 'international_reference',
                'business_relevance' => 48,
                'cultural_applicability' => 25,
                'is_trivial_or_off_topic' => false,
                'classification_note' => 'Tham khảo quốc tế / ý tưởng sản phẩm — tách khỏi phong tục Việt Nam và tính năng hiện hữu.',
            ];
        }

        // 5. Văn hóa & Nghi lễ cưới hỏi Việt Nam (bao gồm Lễ Hằng thuận, Lễ ăn hỏi, Dạm ngõ, Gia tiên, Vu quy, Thành hôn, Thuật ngữ cưới)
        $cultureKeywords = [
            'hang thuan', 'le an hoi', 'dam ngo', 'gia tien', 'vu quy', 'thanh hon', 'tan hon',
            'phong tuc', 'nghi le', 'nghi thuc', 'thuat ngu cuoi', 'trap le', 'mam qua', 'lai mat',
            'don dau', 'ruoc dau', 'hon nhan xua', 'cung gioi', 'le len den', 'phat giao',
        ];
        foreach ($cultureKeywords as $ck) {
            if (str_contains($text, $ck)) {
                $region = $this->detectVietnamRegion($text);

                return [
                    'ontology_domain' => 'vietnamese_wedding_culture',
                    'category' => 'general',
                    'knowledge_space' => AiResearchClaim::SPACE_INDUSTRY_CULTURAL,
                    'knowledge_layer' => AiKnowledgeEntry::LAYER_RESEARCH_GENERAL,
                    'claim_type' => 'cultural_norm',
                    'applicable_region' => $region,
                    'business_relevance' => 76,
                    'cultural_applicability' => 88,
                    'is_trivial_or_off_topic' => false,
                    'classification_note' => 'Văn hóa & nghi lễ cưới hỏi Việt Nam (tách khỏi nhóm RSVP/kỹ thuật).',
                ];
            }
        }

        // 6. Quản lý Khách mời, RSVP, Bàn tiệc, Check-in & Timeline cưới
        $rsvpKeywords = ['rsvp', 'xac nhan tham du', 'khach moi', 'ban tiec', 'so do ban', 'check in', 'diem danh', 'timeline ngay cuoi', 'lap ke hoach dam cuoi'];
        foreach ($rsvpKeywords as $rk) {
            if (str_contains($text, $rk)) {
                $isIdea = str_contains($text, 'co nen') || str_contains($text, 'de xuat') || str_contains($text, 'tham khao cach');

                return [
                    'ontology_domain' => $isIdea ? 'wedding_product_innovation' : 'wedding_planning_rsvp',
                    'category' => 'rsvp',
                    'knowledge_space' => $isIdea ? AiResearchClaim::SPACE_PRODUCT_INNOVATION : AiResearchClaim::SPACE_INDUSTRY_CULTURAL,
                    'knowledge_layer' => $isIdea ? AiKnowledgeEntry::LAYER_APPLIED_INSIGHT : AiKnowledgeEntry::LAYER_RESEARCH_GENERAL,
                    'claim_type' => $isIdea ? 'product_idea' : 'process',
                    'applicable_region' => $this->detectVietnamRegion($text),
                    'business_relevance' => 86,
                    'cultural_applicability' => 82,
                    'is_trivial_or_off_topic' => false,
                    'classification_note' => $isIdea ? 'Ý tưởng cải tiến quy trình khách mời / RSVP.' : 'Kiến thức chuyên môn quản lý khách mời & sự kiện cưới.',
                ];
            }
        }

        // 7. Thiệp cưới điện tử, viết lời mời, trải nghiệm di động
        $inviteKeywords = ['thiep cuoi', 'thiep moi', 'loi moi', 'xung ho', 'bao hy', 'thiep dien tu', 'thiep online'];
        foreach ($inviteKeywords as $ik) {
            if (str_contains($text, $ik)) {
                $isUxIdea = str_contains($text, 'nen luu y') || str_contains($text, 'nen uu tien') || str_contains($text, 'de xuat');

                return [
                    'ontology_domain' => $isUxIdea ? 'wedding_customer_experience' : 'digital_invitations',
                    'category' => 'templates',
                    'knowledge_space' => $isUxIdea ? AiResearchClaim::SPACE_PRODUCT_INNOVATION : AiResearchClaim::SPACE_INDUSTRY_CULTURAL,
                    'knowledge_layer' => $isUxIdea ? AiKnowledgeEntry::LAYER_APPLIED_INSIGHT : AiKnowledgeEntry::LAYER_RESEARCH_GENERAL,
                    'claim_type' => $isUxIdea ? 'ux_principle' : 'fact',
                    'applicable_region' => $this->detectVietnamRegion($text),
                    'business_relevance' => 88,
                    'cultural_applicability' => 85,
                    'is_trivial_or_off_topic' => false,
                    'classification_note' => 'Chuyên môn thiệp cưới điện tử & nội dung lời mời.',
                ];
            }
        }

        return [
            'ontology_domain' => 'wedding_customer_experience',
            'category' => 'general',
            'knowledge_space' => AiResearchClaim::SPACE_INDUSTRY_CULTURAL,
            'knowledge_layer' => AiKnowledgeEntry::LAYER_RESEARCH_GENERAL,
            'claim_type' => 'fact',
            'applicable_region' => $this->detectVietnamRegion($text),
            'business_relevance' => 45,
            'cultural_applicability' => 60,
            'is_trivial_or_off_topic' => false,
            'classification_note' => 'Kiến thức ngành tham khảo tổng quát.',
        ];
    }

    protected function detectVietnamRegion(string $asciiText): string
    {
        $north = str_contains($asciiText, 'mien bac') || str_contains($asciiText, 'ha noi');
        $central = str_contains($asciiText, 'mien trung') || str_contains($asciiText, 'hue');
        $south = str_contains($asciiText, 'mien nam') || str_contains($asciiText, 'sai gon') || str_contains($asciiText, 'nam bo');

        $count = ($north ? 1 : 0) + ($central ? 1 : 0) + ($south ? 1 : 0);
        if ($count > 1) {
            return 'vietnam_multi_region';
        }
        if ($north) {
            return 'vietnam_north';
        }
        if ($central) {
            return 'vietnam_central';
        }
        if ($south) {
            return 'vietnam_south';
        }

        return 'vietnam_general';
    }

    protected function inferInternalCategory(string $asciiText): string
    {
        if (str_contains($asciiText, 'gia') || str_contains($asciiText, 'goi') || str_contains($asciiText, 'vietqr') || str_contains($asciiText, 'thanh toan')) {
            return 'pricing';
        }
        if (str_contains($asciiText, 'rsvp') || str_contains($asciiText, 'khach moi') || str_contains($asciiText, 'ban tiec') || str_contains($asciiText, 'qr')) {
            return 'rsvp';
        }
        if (str_contains($asciiText, 'mau thiep') || str_contains($asciiText, 'doi mau') || str_contains($asciiText, 'phong cach')) {
            return 'templates';
        }
        if (str_contains($asciiText, 'huong dan') || str_contains($asciiText, 'nhac nen') || str_contains($asciiText, 'luu lich') || str_contains($asciiText, 'studio')) {
            return 'docs';
        }

        return 'general';
    }

    protected function isWeddingRelatedGoal(AiResearchTopic $topic, ?AiResearchGoal $goal): bool
    {
        $blob = Str::ascii(mb_strtolower(($goal?->domain ?? '').' '.($goal?->title ?? '').' '.$topic->domain.' '.$topic->title, 'UTF-8'));

        return str_contains($blob, 'cuoi') || str_contains($blob, 'wedding') || str_contains($blob, 'thiep') || str_contains($blob, 'rsvp') || str_contains($blob, 'an hoi') || str_contains($blob, 'dam ngo');
    }
}
