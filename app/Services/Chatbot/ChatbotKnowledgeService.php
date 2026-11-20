<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Models\AiKnowledgeEntry;
use App\Models\InvitationTemplate;
use App\Models\Plan;
use App\Services\DocumentationService;
use App\Services\Intelligence\AiGovernanceService;
use App\Services\Intelligence\KnowledgeGraphService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ChatbotKnowledgeService
{
    /**
     * Từ khóa mở rộng giúp hiểu đúng nhu cầu chọn mẫu thiệp của cặp đôi.
     *
     * @var array<string, array<int, string>>
     */
    protected const TEMPLATE_STYLE_SYNONYMS = [
        'luxury' => ['sang trong', 'hoang gia', 'quy toc', 'luxury', 'cao cap', 'vang', 'nhung', 'vogue', 'sap', 'phong thu', 'quiet luxury'],
        'modern' => ['hien dai', 'modern', 'toi gian', 'minimal', 'tre trung', 'don gian', 'tinh gon', 'nhe nhang'],
        'floral' => ['hoa', 'hoa la', 'floral', 'lang man', 'vuon', 'co dien', 'hong', 'pastel', 'botanic'],
        'traditional' => ['truyen thong', 'do', 'son do', 'long phung', 'rong phuong', 'hy', 'song hy', 'gia tien', 'co truyen'],
        'curated' => ['tu phoi', 'curated', 'tu dong', 'nhanh', 'ai tao thiep'],
    ];

    protected const STOP_WORDS = [
        'la', 'gi', 'co', 'khong', 'cho', 'toi', 'minh', 'ban', 'em', 'anh', 'chi',
        'cua', 'va', 'hay', 'hoac', 'nhu', 'the', 'nao', 'duoc', 'voi', 'trong', 've',
        'muon', 'can', 'giup', 'hoi', 've', 'mot', 'nhung', 'cac', 'nay', 'kia', 'o', 'dau',
    ];

    public function __construct(
        protected DocumentationService $documentationService,
        protected AiGovernanceService $governance,
        protected KnowledgeGraphService $graphService
    ) {}

    /**
     * Truy xuất dữ liệu ĐÃ XÁC THỰC 100% từ nội bộ dự án (tuân thủ chính sách nguồn được phép từ AI Governance):
     * 1. Kho tri thức Admin đã duyệt & còn hiệu lực (AiKnowledgeEntry::liveApproved)
     * 2. Mẫu thiệp cưới đang hoạt động (InvitationTemplate)
     * 3. Tài liệu hướng dẫn khách hàng (DocumentationService client space)
     * 4. Bảng giá & Gói dịch vụ (Plan)
     *
     * @return array{
     *     intent: string,
     *     has_context: bool,
     *     knowledge: array<int, array<string, mixed>>,
     *     templates: array<int, array<string, mixed>>,
     *     docs: array<int, array<string, mixed>>,
     *     plans: array<int, array<string, mixed>>,
     *     evidence_refs: list<string>,
     *     verified_context_text: string
     * }
     */
    public function retrieve(string $query): array
    {
        $normalized = $this->normalizeText($query);
        $tokens = $this->extractTokens($normalized);

        $intent = $this->detectIntent($normalized);

        $knowledge = $this->governance->isSourceAllowed('approved_knowledge')
            ? $this->matchApprovedKnowledge($normalized, $tokens)
            : [];
        $templates = $this->governance->isSourceAllowed('templates')
            ? $this->matchTemplates($normalized, $tokens, $intent === 'templates')
            : [];
        $docs = $this->governance->isSourceAllowed('client_docs')
            ? $this->matchClientDocs($query, $normalized, $tokens, $intent === 'docs')
            : [];
        $plans = $this->governance->isSourceAllowed('plans')
            ? $this->matchPlans($normalized, $intent === 'pricing')
            : [];

        // Nếu tri thức Admin đã duyệt có gắn mẫu thiệp hoặc link docs cụ thể -> bổ sung ngay thẻ xác thực tương ứng
        foreach ($knowledge as $entry) {
            if (! empty($entry['template_slug']) && $this->governance->isSourceAllowed('templates')) {
                $templates = $this->prependTemplateBySlug($templates, (string) $entry['template_slug']);
            }
            if (! empty($entry['doc_url']) && $this->governance->isSourceAllowed('client_docs')) {
                $docs = $this->prependDocByUrl($docs, (string) $entry['doc_url']);
            }
        }

        $templates = array_slice($templates, 0, 3);
        $docs = array_slice($docs, 0, 3);
        $relations = $this->graphService->findRelatedContext($normalized, $intent);

        $hasContext = $knowledge !== [] || $templates !== [] || $docs !== [] || $plans !== [] || $relations !== [];

        $evidenceRefs = [];
        foreach ($knowledge as $k) {
            $evidenceRefs[] = 'knowledge:#'.$k['id'].'@v'.($k['version'] ?? 1);
        }
        foreach ($templates as $t) {
            $evidenceRefs[] = 'template:'.$t['slug'];
        }
        foreach ($docs as $d) {
            $evidenceRefs[] = 'doc:'.$d['url'];
        }
        foreach ($plans as $p) {
            $evidenceRefs[] = 'plan:'.$p['name'];
        }
        foreach ($relations as $rel) {
            $evidenceRefs[] = 'relation:#'.$rel['id'];
        }

        return [
            'intent' => $intent,
            'has_context' => $hasContext,
            'knowledge' => $knowledge,
            'templates' => $templates,
            'docs' => $docs,
            'plans' => $plans,
            'relations' => $relations,
            'evidence_refs' => array_values(array_unique($evidenceRefs)),
            'verified_context_text' => $this->compileVerifiedContextText($knowledge, $templates, $docs, $plans, $relations),
        ];
    }

    /**
     * Tóm tắt danh mục xác thực cốt lõi (dùng khi khách chào hỏi/hỏi mở để AI tư vấn tự nhiên mà không bịa).
     */
    public function buildCoreCatalogSummary(): string
    {
        $lines = [
            '[TỔNG QUAN NỀN TẢNG MÂY HẸN ĐÃ XÁC THỰC]',
            '- Mây Hẹn (mayhen.vn) là nền tảng tạo thiệp cưới online phong cách Quiet Luxury.',
            '- Đường dẫn nội bộ hợp lệ: [Kho Mẫu Thiệp](/mau-thiep), [Studio Tạo Thiệp](/tao-thiep), [Bảng Giá](/bang-gia), [Hướng Dẫn](/docs).',
        ];

        if ($this->governance->isSourceAllowed('templates')) {
            $featured = InvitationTemplate::query()
                ->where('is_active', true)
                ->orderByDesc('is_featured')
                ->limit(4)
                ->get(['name', 'slug', 'category', 'default_config']);

            if ($featured->isNotEmpty()) {
                $lines[] = '[MỘT SỐ MẪU THIỆP NỔI BẬT ĐANG HOẠT ĐỘNG]';
                foreach ($featured as $tpl) {
                    $cfg = is_array($tpl->default_config) ? $tpl->default_config : [];
                    $tier = strtolower((string) ($cfg['tier'] ?? 'free'));
                    $lines[] = "- {$tpl->name} (Phong cách: {$tpl->category}, Gói: {$tier}, Xem thử: /xem-thu/{$tpl->slug}, Tạo ngay: /tao-thiep?template={$tpl->slug})";
                }
            }
        }

        if ($this->governance->isSourceAllowed('plans')) {
            $plans = $this->matchPlans('bang gia', true);
            if ($plans !== []) {
                $lines[] = '[GÓI DỊCH VỤ HIỆN HÀNH]';
                foreach ($plans as $plan) {
                    $lines[] = "- Gói {$plan['name']}: {$plan['formatted_price']} ({$plan['duration_days']} ngày) — {$plan['description']}";
                }
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Trích xuất Toàn bộ Kho Sự Thật Gốc (Ground Truth Corpus) phục vụ Multi-Agent Learning Pipeline.
     *
     * @return array{
     *     templates: list<array<string, mixed>>,
     *     valid_template_slugs: list<string>,
     *     plans: list<array<string, mixed>>,
     *     valid_prices: list<string>,
     *     docs: list<array<string, mixed>>,
     *     valid_doc_urls: list<string>,
     *     existing_titles: list<string>,
     *     corpus_by_topic: array<string, string>
     * }
     */
    public function getGroundTruthCorpus(): array
    {
        $templates = InvitationTemplate::query()
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->get(['slug', 'name', 'category', 'default_config'])
            ->map(function (InvitationTemplate $tpl): array {
                $cfg = is_array($tpl->default_config) ? $tpl->default_config : [];

                return [
                    'slug' => (string) $tpl->slug,
                    'name' => (string) $tpl->name,
                    'category' => (string) ($tpl->category ?? 'luxury'),
                    'tier' => strtolower((string) ($cfg['tier'] ?? 'free')),
                    'description' => Str::limit((string) ($cfg['description'] ?? ''), 140),
                    'preview_url' => '/xem-thu/'.$tpl->slug,
                    'editor_url' => '/tao-thiep?template='.$tpl->slug,
                ];
            })
            ->values()
            ->all();

        $plans = $this->matchPlans('bang gia', true);

        $docs = [];
        $validDocUrls = [];
        foreach ($this->documentationService->getCategories('client') as $cat) {
            $catName = (string) ($cat['name'] ?? 'Hướng dẫn');
            foreach ($cat['articles'] ?? [] as $article) {
                $docPath = $this->normalizeDocPath((string) ($article['url'] ?? ''));
                if ($docPath === null) {
                    continue;
                }
                $validDocUrls[] = $docPath;
                $docs[] = [
                    'category' => $catName,
                    'title' => (string) ($article['title'] ?? ''),
                    'summary' => Str::limit((string) ($article['summary'] ?? ''), 180),
                    'url' => $docPath,
                ];
            }
        }

        $existingTitles = AiKnowledgeEntry::query()
            ->whereIn('status', [AiKnowledgeEntry::STATUS_APPROVED, AiKnowledgeEntry::STATUS_PENDING])
            ->pluck('title')
            ->map(fn ($t) => (string) $t)
            ->values()
            ->all();

        $tplText = implode("\n", array_map(
            fn (array $t) => "- {$t['name']} [slug={$t['slug']}] | Phong cách: {$t['category']} | Gói: {$t['tier']} | Mô tả: {$t['description']} | Xem thử: {$t['preview_url']} | Tạo ngay: {$t['editor_url']}",
            $templates
        ));

        $planText = implode("\n", array_map(
            fn (array $p) => "- Gói {$p['name']}: {$p['formatted_price']} ({$p['duration_days']} ngày) — {$p['description']}",
            $plans
        ));

        $rsvpDocs = array_values(array_filter($docs, function (array $d): bool {
            $n = $this->normalizeText($d['category'].' '.$d['title'].' '.$d['summary']);

            return str_contains($n, 'khach') || str_contains($n, 'rsvp') || str_contains($n, 'qr') || str_contains($n, 'ban tiec') || str_contains($n, 'diem danh');
        }));

        $generalDocs = array_slice($docs, 0, 22);
        $docsText = implode("\n", array_map(
            fn (array $d) => "- [{$d['category']}] {$d['title']} | URL: {$d['url']} | Tóm tắt: {$d['summary']}",
            $generalDocs
        ));

        $rsvpDocsText = implode("\n", array_map(
            fn (array $d) => "- [{$d['category']}] {$d['title']} | URL: {$d['url']} | Tóm tắt: {$d['summary']}",
            array_slice($rsvpDocs, 0, 15)
        ));

        $platformHeader = "Nền tảng Mây Hẹn (mayhen.vn) — Thiệp cưới online Quiet Luxury. Các trang nội bộ hợp lệ: /mau-thiep, /tao-thiep, /bang-gia, /docs, /lien-he.\n";

        return [
            'templates' => $templates,
            'valid_template_slugs' => array_values(array_map(fn (array $t) => $t['slug'], $templates)),
            'plans' => $plans,
            'valid_prices' => array_values(array_map(fn (array $p) => (string) $p['formatted_price'], $plans)),
            'docs' => $docs,
            'valid_doc_urls' => array_values(array_unique($validDocUrls)),
            'existing_titles' => $existingTitles,
            'corpus_by_topic' => [
                'templates' => $platformHeader."[MẪU THIỆP ĐANG HOẠT ĐỘNG]\n{$tplText}\n\n[GÓI DỊCH VỤ]\n{$planText}",
                'pricing' => $platformHeader."[BẢNG GIÁ GÓI DỊCH VỤ CHÍNH THỨC]\n{$planText}\n\n[MẪU THIỆP HIỆN CÓ]\n{$tplText}",
                'rsvp' => $platformHeader."[TÀI LIỆU KHÁCH MỜI, RSVP, QR & SẢNH TIỆC]\n{$rsvpDocsText}\n\n[GÓI DỊCH VỤ]\n{$planText}",
                'docs' => $platformHeader."[TÀI LIỆU HƯỚNG DẪN KHÁCH HÀNG (CLIENT DOCS)]\n{$docsText}",
                'general' => $platformHeader."[GÓI DỊCH VỤ]\n{$planText}\n\n[MẪU THIỆP]\n{$tplText}\n\n[TÀI LIỆU CHÍNH]\n".implode("\n", array_map(
                    fn (array $d) => "- {$d['title']} | URL: {$d['url']} | {$d['summary']}",
                    array_slice($docs, 0, 12)
                )),
            ],
        ];
    }

    protected function detectIntent(string $normalized): string
    {
        if (preg_match('/^(xin\s*chao|chao(\s+(ban|shop|admin|may\s*hen|em|anh|chi))?|hello|hi|alo|helo|hey)$/u', $normalized) === 1) {
            return 'greeting';
        }

        if (preg_match('/^(cam\s*on|thanks?|thank\s*you|ok|oke|okay|da\s*cam\s*on)$/u', $normalized) === 1) {
            return 'thanks';
        }

        $docTriggers = [
            'khong hieu', 'huong dan', 'cach lam', 'lam sao', 'lam the nao', 'tai lieu', 'docs',
            'loi', 'khong thay', 'sua', 'chinh', 'doi anh', 'cat anh', 'them nhac', 'xuat ban',
            'khach moi', 'rsvp', 'xep ban', 'so do ban', 'diem danh', 'quet qr', 'qr', 'vietqr',
            'ten mien', 'custom domain', 'luu but', 'doi mau', 'mat khau', 'dang nhap',
        ];
        foreach ($docTriggers as $kw) {
            if (str_contains($normalized, $kw)) {
                return 'docs';
            }
        }

        $templateTriggers = [
            'mau thiep', 'chon thiep', 'goi y thiep', 'thiep dep', 'phong cach', 'thiep cuoi',
            'sang trong', 'hien dai', 'toi gian', 'truyen thong', 'hoa la', 'hoang gia',
            'mau do', 'mau hong', 'mau trang', 'vom troi', 'curated', 'phong thu',
        ];
        foreach ($templateTriggers as $kw) {
            if (str_contains($normalized, $kw)) {
                return 'templates';
            }
        }

        $pricingTriggers = [
            'bang gia', 'gia bao nhieu', 'bao nhieu tien', 'chi phi', 'goi dich vu',
            'mien phi', 'free', 'plus', 'pro', 'premium', 'thanh toan', 'nang cap', '99k',
        ];
        foreach ($pricingTriggers as $kw) {
            if (str_contains($normalized, $kw)) {
                return 'pricing';
            }
        }

        return 'general';
    }

    /**
     * @param  array<int, string>  $tokens
     * @return array<int, array<string, mixed>>
     */
    protected function matchApprovedKnowledge(string $normalized, array $tokens): array
    {
        $entries = Cache::remember('mayhen_ai_approved_knowledge_entries_v3', 600, function (): array {
            $columns = [
                'id', 'category', 'title', 'keywords', 'answer',
                'template_slug', 'doc_url', 'priority',
            ];
            if (Schema::hasColumn('ai_knowledge_entries', 'version')) {
                $columns[] = 'version';
                $columns[] = 'trust_score';
            }

            return AiKnowledgeEntry::query()
                ->liveApproved()
                ->orderByDesc('priority')
                ->orderByDesc('usage_count')
                ->get($columns)
                ->map(fn (AiKnowledgeEntry $item): array => [
                    'id' => (int) $item->id,
                    'category' => (string) $item->category,
                    'title' => (string) $item->title,
                    'keywords' => (string) ($item->keywords ?? ''),
                    'answer' => (string) $item->answer,
                    'template_slug' => $item->template_slug ? (string) $item->template_slug : null,
                    'doc_url' => $item->doc_url ? (string) $item->doc_url : null,
                    'priority' => (int) $item->priority,
                    'version' => (int) ($item->version ?? 1),
                    'trust_score' => (int) ($item->trust_score ?? 100),
                ])
                ->all();
        });

        if (! is_array($entries)) {
            return [];
        }

        $scored = [];
        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $titleNorm = $this->normalizeText((string) ($entry['title'] ?? ''));
            $kwNorm = $this->normalizeText((string) ($entry['keywords'] ?? ''));
            $ansNorm = $this->normalizeText((string) ($entry['answer'] ?? ''));

            $score = 0;

            if ($normalized !== '' && (str_contains($titleNorm, $normalized) || str_contains($normalized, $titleNorm))) {
                $score += 35;
            }

            if ($kwNorm !== '') {
                $kwList = array_filter(array_map('trim', explode(',', $kwNorm)));
                foreach ($kwList as $kwPhrase) {
                    if ($kwPhrase !== '' && str_contains($normalized, $kwPhrase)) {
                        $score += 24;
                    }
                }
            }

            foreach ($tokens as $token) {
                if (str_contains($titleNorm, $token)) {
                    $score += 8;
                }
                if ($kwNorm !== '' && str_contains($kwNorm, $token)) {
                    $score += 10;
                }
                if (str_contains($ansNorm, $token)) {
                    $score += 3;
                }
            }

            if ($score >= 10) {
                $score += (int) ($entry['priority'] ?? 5);
                $scored[] = [
                    'id' => (int) ($entry['id'] ?? 0),
                    'category' => (string) ($entry['category'] ?? 'general'),
                    'title' => (string) ($entry['title'] ?? ''),
                    'answer' => (string) ($entry['answer'] ?? ''),
                    'template_slug' => ! empty($entry['template_slug']) ? (string) $entry['template_slug'] : null,
                    'doc_url' => ! empty($entry['doc_url']) ? (string) $entry['doc_url'] : null,
                    'version' => (int) ($entry['version'] ?? 1),
                    'trust_score' => (int) ($entry['trust_score'] ?? 100),
                    'score' => $score,
                ];
            }
        }

        usort($scored, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, 3);
    }

    /**
     * @param  array<int, string>  $tokens
     * @return array<int, array<string, mixed>>
     */
    protected function matchTemplates(string $normalized, array $tokens, bool $isTemplateIntent): array
    {
        $templates = InvitationTemplate::query()
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('id')
            ->get(['id', 'slug', 'name', 'category', 'thumbnail_url', 'default_config', 'is_featured']);

        $wantsFree = str_contains($normalized, 'mien phi') || str_contains($normalized, 'free') || str_contains($normalized, '0d');
        $wantsPaid = str_contains($normalized, 'plus') || str_contains($normalized, 'pro') || str_contains($normalized, 'vip') || str_contains($normalized, 'cao cap');

        $preferredCategories = [];
        foreach (self::TEMPLATE_STYLE_SYNONYMS as $cat => $phrases) {
            foreach ($phrases as $phrase) {
                if (str_contains($normalized, $phrase)) {
                    $preferredCategories[$cat] = true;
                }
            }
        }

        $scored = [];
        foreach ($templates as $tpl) {
            $nameNorm = $this->normalizeText((string) $tpl->name);
            $slugNorm = $this->normalizeText(str_replace('-', ' ', (string) $tpl->slug));
            $catNorm = $this->normalizeText((string) ($tpl->category ?? ''));
            $config = is_array($tpl->default_config) ? $tpl->default_config : [];
            $tier = strtolower((string) ($config['tier'] ?? 'free'));
            $descNorm = $this->normalizeText((string) ($config['description'] ?? ''));

            $score = 0;

            if ($normalized !== '' && (str_contains($nameNorm, $normalized) || str_contains($normalized, $nameNorm))) {
                $score += 40;
            }

            if (isset($preferredCategories[$catNorm])) {
                $score += 22;
            }

            if ($wantsFree && $tier === 'free') {
                $score += 18;
            }
            if ($wantsPaid && in_array($tier, ['plus', 'pro', 'premium'], true)) {
                $score += 18;
            }

            foreach ($tokens as $token) {
                if (str_contains($nameNorm, $token) || str_contains($slugNorm, $token)) {
                    $score += 12;
                } elseif (str_contains($catNorm, $token) || str_contains($descNorm, $token)) {
                    $score += 6;
                }
            }

            if ($score > 0 || $isTemplateIntent) {
                $scored[] = [
                    'slug' => (string) $tpl->slug,
                    'name' => (string) $tpl->name,
                    'category' => (string) ($tpl->category ?? 'luxury'),
                    'tier' => $tier,
                    'thumbnail_url' => $tpl->thumbnail_url ? (string) $tpl->thumbnail_url : '',
                    'preview_url' => route('templates.preview', ['slug' => $tpl->slug]),
                    'editor_url' => route('invitation.create', ['template' => $tpl->slug]),
                    'score' => $score + ($tpl->is_featured ? 3 : 0),
                ];
            }
        }

        usort($scored, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, 3);
    }

    /**
     * @param  array<int, string>  $tokens
     * @return array<int, array<string, mixed>>
     */
    protected function matchClientDocs(string $rawQuery, string $normalized, array $tokens, bool $isDocIntent): array
    {
        $categories = $this->documentationService->getCategories('client');
        $scored = [];

        foreach ($categories as $cat) {
            $catName = (string) ($cat['name'] ?? '');
            $catNorm = $this->normalizeText($catName.' '.($cat['slug'] ?? ''));

            foreach ($cat['articles'] ?? [] as $article) {
                $title = (string) ($article['title'] ?? '');
                $summary = (string) ($article['summary'] ?? $article['description'] ?? '');
                $url = $this->normalizeDocPath((string) ($article['url'] ?? ''));

                if ($title === '' || $url === null) {
                    continue;
                }

                $titleNorm = $this->normalizeText($title);
                $summaryNorm = $this->normalizeText($summary);
                $tagsNorm = $this->normalizeText(implode(' ', is_array($article['tags'] ?? null) ? $article['tags'] : []));

                $score = 0;

                if ($normalized !== '' && str_contains($titleNorm, $normalized)) {
                    $score += 30;
                }

                foreach ($tokens as $token) {
                    if (str_contains($titleNorm, $token)) {
                        $score += 11;
                    }
                    if ($tagsNorm !== '' && str_contains($tagsNorm, $token)) {
                        $score += 9;
                    }
                    if (str_contains($summaryNorm, $token)) {
                        $score += 5;
                    }
                    if (str_contains($catNorm, $token)) {
                        $score += 4;
                    }
                }

                if ($score >= 8) {
                    $scored[$url] = [
                        'title' => $title,
                        'category' => $catName,
                        'summary' => Str::limit($summary, 130),
                        'url' => $url,
                        'score' => $score,
                    ];
                }
            }
        }

        // Bổ sung tìm kiếm toàn văn qua DocumentationService::search nếu chưa đủ bài khớp
        if (count($scored) < 3 && mb_strlen(trim($rawQuery), 'UTF-8') >= 2) {
            foreach ($this->documentationService->search('client', $rawQuery) as $hit) {
                $url = $this->normalizeDocPath((string) ($hit['url'] ?? ''));
                if ($url === null || isset($scored[$url])) {
                    continue;
                }

                $scored[$url] = [
                    'title' => (string) ($hit['title'] ?? 'Hướng dẫn Mây Hẹn'),
                    'category' => (string) ($hit['category'] ?? 'Tài liệu khách hàng'),
                    'summary' => Str::limit((string) ($hit['excerpt'] ?? $hit['summary'] ?? ''), 130),
                    'url' => $url,
                    'score' => 12,
                ];
            }
        }

        $results = array_values($scored);
        usort($results, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        // Nếu khách đang hỏi cách sử dụng mà chưa khớp bài cụ thể -> gợi ý các bài nhập môn quan trọng nhất
        if ($results === [] && $isDocIntent) {
            foreach ($categories as $cat) {
                foreach ($cat['articles'] ?? [] as $article) {
                    $url = $this->normalizeDocPath((string) ($article['url'] ?? ''));
                    if ($url !== null) {
                        $results[] = [
                            'title' => (string) ($article['title'] ?? 'Hướng dẫn sử dụng'),
                            'category' => (string) ($cat['name'] ?? 'Hướng dẫn'),
                            'summary' => Str::limit((string) ($article['summary'] ?? ''), 130),
                            'url' => $url,
                            'score' => 5,
                        ];
                    }
                    if (count($results) >= 3) {
                        break 2;
                    }
                }
            }
        }

        return array_slice($results, 0, 3);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function matchPlans(string $normalized, bool $isPricingIntent): array
    {
        if (! $isPricingIntent && ! str_contains($normalized, 'goi') && ! str_contains($normalized, 'gia')) {
            return [];
        }

        return Plan::query()
            ->where('is_active', true)
            ->orderBy('price')
            ->get(['slug', 'name', 'price', 'duration_days', 'max_invitations', 'features'])
            ->map(fn (Plan $plan) => [
                'slug' => (string) $plan->slug,
                'name' => (string) $plan->name,
                'formatted_price' => (string) $plan->formatted_price,
                'duration_days' => (int) $plan->duration_days,
                'description' => (string) $plan->description,
            ])
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $templates
     * @return array<int, array<string, mixed>>
     */
    protected function prependTemplateBySlug(array $templates, string $slug): array
    {
        foreach ($templates as $tpl) {
            if (($tpl['slug'] ?? '') === $slug) {
                return $templates;
            }
        }

        $tpl = InvitationTemplate::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first(['slug', 'name', 'category', 'thumbnail_url', 'default_config']);

        if (! $tpl) {
            return $templates;
        }

        $config = is_array($tpl->default_config) ? $tpl->default_config : [];

        array_unshift($templates, [
            'slug' => (string) $tpl->slug,
            'name' => (string) $tpl->name,
            'category' => (string) ($tpl->category ?? 'luxury'),
            'tier' => strtolower((string) ($config['tier'] ?? 'free')),
            'thumbnail_url' => $tpl->thumbnail_url ? (string) $tpl->thumbnail_url : '',
            'preview_url' => route('templates.preview', ['slug' => $tpl->slug]),
            'editor_url' => route('invitation.create', ['template' => $tpl->slug]),
            'score' => 99,
        ]);

        return $templates;
    }

    /**
     * @param  array<int, array<string, mixed>>  $docs
     * @return array<int, array<string, mixed>>
     */
    protected function prependDocByUrl(array $docs, string $docUrl): array
    {
        $normalizedTarget = $this->normalizeDocPath($docUrl);
        if ($normalizedTarget === null) {
            return $docs;
        }

        foreach ($docs as $doc) {
            if (($doc['url'] ?? '') === $normalizedTarget) {
                return $docs;
            }
        }

        foreach ($this->documentationService->getCategories('client') as $cat) {
            foreach ($cat['articles'] ?? [] as $article) {
                if ($this->normalizeDocPath((string) ($article['url'] ?? '')) === $normalizedTarget) {
                    array_unshift($docs, [
                        'title' => (string) ($article['title'] ?? 'Hướng dẫn chi tiết'),
                        'category' => (string) ($cat['name'] ?? 'Tài liệu khách hàng'),
                        'summary' => Str::limit((string) ($article['summary'] ?? ''), 130),
                        'url' => $normalizedTarget,
                        'score' => 99,
                    ]);

                    return $docs;
                }
            }
        }

        return $docs;
    }

    protected function normalizeDocPath(string $rawUrl): ?string
    {
        $trimmed = trim($rawUrl);
        if ($trimmed === '') {
            return null;
        }

        $path = str_starts_with($trimmed, '/')
            ? $trimmed
            : (string) (parse_url($trimmed, PHP_URL_PATH) ?? '');

        if ($path === '' || (! str_starts_with($path, '/docs/') && $path !== '/docs')) {
            return null;
        }

        return $path;
    }

    /**
     * @param  array<int, array<string, mixed>>  $knowledge
     * @param  array<int, array<string, mixed>>  $templates
     * @param  array<int, array<string, mixed>>  $docs
     * @param  array<int, array<string, mixed>>  $plans
     * @param  array<int, array<string, mixed>>  $relations
     */
    protected function compileVerifiedContextText(array $knowledge, array $templates, array $docs, array $plans, array $relations = []): string
    {
        $lines = [];

        if ($knowledge !== []) {
            $lines[] = '[TRI THỨC ĐÃ ĐƯỢC ADMIN MÂY HẸN DUYỆT]';
            foreach ($knowledge as $item) {
                $lines[] = "- Hỏi: {$item['title']}\n  Đáp chuẩn: {$item['answer']}";
            }
        }

        if ($relations !== []) {
            $lines[] = '[MỐI QUAN HỆ TÍNH NĂNG LIÊN KẾT TRÊN MÂY HẸN]';
            foreach ($relations as $rel) {
                $lines[] = "- {$rel['edge']}: {$rel['description']}";
            }
        }

        if ($templates !== []) {
            $lines[] = '[MẪU THIỆP ĐANG HOẠT ĐỘNG TRÊN HỆ THỐNG]';
            foreach ($templates as $tpl) {
                $lines[] = "- {$tpl['name']} (Phong cách: {$tpl['category']}, Gói: {$tpl['tier']}, Xem thử: {$tpl['preview_url']}, Tạo ngay: {$tpl['editor_url']})";
            }
        }

        if ($docs !== []) {
            $lines[] = '[TÀI LIỆU HƯỚNG DẪN KHÁCH HÀNG (CLIENT DOCS)]';
            foreach ($docs as $doc) {
                $lines[] = "- {$doc['title']} ({$doc['category']}): {$doc['summary']} | Link: {$doc['url']}";
            }
        }

        if ($plans !== []) {
            $lines[] = '[BẢNG GIÁ GÓI DỊCH VỤ CHÍNH THỨC]';
            foreach ($plans as $plan) {
                $lines[] = "- Gói {$plan['name']}: {$plan['formatted_price']} ({$plan['duration_days']} ngày) — {$plan['description']}";
            }
        }

        return implode("\n", $lines);
    }

    public function normalizeText(string $text): string
    {
        $ascii = Str::ascii(mb_strtolower($text, 'UTF-8'));
        $ascii = preg_replace('/[^a-z0-9\s]/', ' ', $ascii) ?? $ascii;
        $ascii = preg_replace('/\s+/', ' ', $ascii) ?? $ascii;

        return trim($ascii);
    }

    /**
     * @return array<int, string>
     */
    protected function extractTokens(string $normalized): array
    {
        $rawTokens = array_filter(explode(' ', $normalized), fn (string $t) => strlen($t) >= 2 && ! in_array($t, self::STOP_WORDS, true));

        return array_values(array_unique($rawTokens));
    }
}
