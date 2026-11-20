<?php

declare(strict_types=1);

namespace App\Services\Intelligence\V4;

use App\Models\AiKnowledgeRelation;
use App\Models\AiResearchClaim;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Typed Relational Knowledge Graph & Atomic Claim Retrieval Service V4.0:
 * - Xây dựng mạng quan hệ có kiểu chặt chẽ (13 kiểu quan hệ chuẩn trong OntologyAndClassificationService).
 * - Phân tách tuyệt đối cạnh sản phẩm thật (`supports_feature`) với cạnh gợi ý cải tiến (`suggests_improvement`).
 * - Hỗ trợ truy xuất trực tiếp Atomic Knowledge Claims (đã duyệt / tham khảo) kết hợp Typed Graph cho Chatbot!
 */
class TypedKnowledgeGraphService
{
    public const ATOMIC_CACHE_KEY = 'mayhen_ai_atomic_claims_live_v4';

    /**
     * Đồng bộ mạng tri thức mẫu có kiểu đầy đủ cho Ngành Cưới Việt Nam & Mây Hẹn (đúng kiến trúc Mục VIII).
     */
    public function syncTypedWeddingOntologyGraph(?int $userId = null): int
    {
        if (! Schema::hasTable('ai_knowledge_relations')) {
            return 0;
        }

        $typedEdges = [
            // Cây quan hệ Lễ ăn hỏi (Mục VIII)
            [
                'from_type' => 'ceremony', 'from_key' => 'le_an_hoi', 'from_label' => 'Lễ ăn hỏi',
                'relation_type' => 'is_a',
                'to_type' => 'domain', 'to_key' => 'nghi_le_cuoi_vn', 'to_label' => 'Nghi lễ cưới Việt Nam',
                'description' => 'Lễ ăn hỏi là nghi thức đính ước chính thức giữa hai bên gia đình trong chuỗi nghi lễ cưới hỏi truyền thống Việt Nam.',
                'evidence_refs' => ['ontology:vietnamese_wedding_culture'],
            ],
            [
                'from_type' => 'ceremony', 'from_key' => 'le_an_hoi', 'from_label' => 'Lễ ăn hỏi',
                'relation_type' => 'differs_by_region',
                'to_type' => 'culture', 'to_key' => 'phong_tuc_vung_mien', 'to_label' => 'Phong tục 3 miền Bắc - Trung - Nam',
                'description' => 'Số lượng tráp lễ (lẻ ở miền Bắc, chẵn ở miền Nam), lễ vật và trình tự đón lễ khác nhau tùy vùng miền và điều kiện gia đình.',
                'evidence_refs' => ['ontology:vietnamese_wedding_culture'],
            ],
            [
                'from_type' => 'ceremony', 'from_key' => 'le_dam_ngo', 'from_label' => 'Lễ dạm ngõ',
                'relation_type' => 'precedes',
                'to_type' => 'ceremony', 'to_key' => 'le_an_hoi', 'to_label' => 'Lễ ăn hỏi',
                'description' => 'Lễ dạm ngõ diễn ra trước lễ ăn hỏi để hai gia đình gặp mặt thân mật và thống nhất ngày lành tháng tốt.',
                'evidence_refs' => ['ontology:vietnamese_wedding_culture'],
            ],
            [
                'from_type' => 'ceremony', 'from_key' => 'le_an_hoi', 'from_label' => 'Lễ ăn hỏi',
                'relation_type' => 'requires',
                'to_type' => 'concept', 'to_key' => 'thanh_phan_tham_du', 'to_label' => 'Thành phần tham dự hai họ',
                'description' => 'Lễ ăn hỏi cần sự thống nhất thành phần đại diện gia đình nhà trai, nhà gái và đội ngũ bưng/đỡ tráp.',
                'evidence_refs' => ['ontology:vietnamese_wedding_culture'],
            ],
            [
                'from_type' => 'ceremony', 'from_key' => 'le_an_hoi', 'from_label' => 'Lễ ăn hỏi',
                'relation_type' => 'related_to',
                'to_type' => 'workflow', 'to_key' => 'quan_ly_khach_moi_timeline', 'to_label' => 'Lời mời, Khách mời & Timeline ngày cưới',
                'description' => 'Việc phân bổ thời gian lễ ăn hỏi và tiệc cưới liên kết chặt với lịch trình (Timeline) và cách ghi thông tin trên thiệp mời.',
                'evidence_refs' => ['ontology:wedding_planning_rsvp'],
            ],
            [
                'from_type' => 'ceremony', 'from_key' => 'le_hang_thuan', 'from_label' => 'Lễ Hằng thuận',
                'relation_type' => 'is_a',
                'to_type' => 'domain', 'to_key' => 'nghi_le_cuoi_phat_giao', 'to_label' => 'Nghi lễ cưới Phật giáo tại Việt Nam',
                'description' => 'Lễ Hằng thuận là nghi lễ cưới tổ chức tại chùa dưới sự chứng minh của chư Tăng, thuộc không gian văn hóa/tôn giáo (không thuộc tính năng RSVP kỹ thuật).',
                'evidence_refs' => ['ontology:vietnamese_wedding_culture'],
            ],
            // Phân tách rõ cạnh sản phẩm thật (supports_feature) vs ý tưởng đề xuất (suggests_improvement)
            [
                'from_type' => 'feature', 'from_key' => 'mayhen_rsvp', 'from_label' => 'Xác nhận tham dự (RSVP) Mây Hẹn',
                'relation_type' => 'supports_feature',
                'to_type' => 'feature', 'to_key' => 'mayhen_seating_qr', 'to_label' => 'Sắp xếp bàn tiệc & Check-in QR Mây Hẹn',
                'description' => 'Dữ liệu khách xác nhận RSVP (số người đi cùng, yêu cầu thực đơn/xe) kết nối trực tiếp vào sơ đồ bàn tiệc và mã QR Check-in trên Mây Hẹn.',
                'evidence_refs' => ['code:GuestController', 'table:guest_responses', 'table:wedding_tables'],
            ],
            [
                'from_type' => 'concept', 'from_key' => 'phan_nhom_khach_le_tiec', 'from_label' => 'Phân nhóm khách dự nghi lễ & dự tiệc riêng',
                'relation_type' => 'suggests_improvement',
                'to_type' => 'feature', 'to_key' => 'mayhen_rsvp', 'to_label' => 'Thiệp cưới & RSVP Mây Hẹn',
                'description' => 'Ý tưởng cải tiến sản phẩm (chưa mặc định thành cam kết tính năng): cho phép cấu hình lịch mời riêng cho nhóm dự lễ gia tiên và nhóm dự tiệc.',
                'evidence_refs' => ['space:product_innovation'],
            ],
        ];

        $synced = 0;
        foreach ($typedEdges as $edge) {
            if (! in_array($edge['relation_type'], OntologyAndClassificationService::ALLOWED_RELATION_TYPES, true)) {
                continue;
            }
            $edgeHash = AiKnowledgeRelation::computeEdgeHash(
                $edge['from_type'],
                $edge['from_key'],
                $edge['relation_type'],
                $edge['to_type'],
                $edge['to_key']
            );

            AiKnowledgeRelation::query()->updateOrCreate(
                ['edge_hash' => $edgeHash],
                [
                    'from_type' => $edge['from_type'],
                    'from_key' => $edge['from_key'],
                    'relation_type' => $edge['relation_type'],
                    'to_type' => $edge['to_type'],
                    'to_key' => $edge['to_key'],
                    'from_label' => $edge['from_label'],
                    'to_label' => $edge['to_label'],
                    'description' => $edge['description'],
                    'domain_level' => 2,
                    'evidence_refs' => $edge['evidence_refs'],
                    'source_type' => 'verified_internal',
                    'status' => AiKnowledgeRelation::STATUS_APPROVED,
                    'is_active' => true,
                    'confidence_score' => 92,
                    'created_by_user_id' => $userId,
                ]
            );
            $synced++;
        }

        AiKnowledgeRelation::clearRelationCache();

        return $synced;
    }

    /**
     * Liên kết có kiểm chứng tối đa 2 cạnh có kiểu từ một Atomic Claim đạt chuẩn vào Knowledge Graph.
     *
     * @param  list<array{from_label?: string, relation_type?: string, to_label?: string, description?: string}>  $suggestedEdges
     */
    public function linkVerifiedClaimEdges(AiResearchClaim $claim, array $suggestedEdges): int
    {
        if (! Schema::hasTable('ai_knowledge_relations')) {
            return 0;
        }

        $linked = 0;
        foreach (array_slice($suggestedEdges, 0, 2) as $edge) {
            $fromLabel = trim((string) ($edge['from_label'] ?? ''));
            $toLabel = trim((string) ($edge['to_label'] ?? ''));
            $relType = strtolower(trim((string) ($edge['relation_type'] ?? 'related_to')));
            $desc = trim((string) ($edge['description'] ?? ''));

            if ($fromLabel === '' || $toLabel === '' || mb_strlen($desc, 'UTF-8') < 15) {
                continue;
            }
            if (! in_array($relType, OntologyAndClassificationService::ALLOWED_RELATION_TYPES, true)) {
                $relType = 'related_to';
            }
            // Cấm nguồn nghiên cứu bên ngoài tự tạo cạnh `supports_feature` cho Mây Hẹn!
            if ($relType === 'supports_feature' && $claim->knowledge_space !== AiResearchClaim::SPACE_OFFICIAL_MAYHEN) {
                $relType = 'suggests_improvement';
            }

            $fromKey = Str::limit(Str::slug($fromLabel, '_') ?: 'node_a', 75, '');
            $toKey = Str::limit(Str::slug($toLabel, '_') ?: 'node_b', 75, '');
            $edgeHash = AiKnowledgeRelation::computeEdgeHash('concept', $fromKey, $relType, 'concept', $toKey);

            AiKnowledgeRelation::query()->updateOrCreate(
                ['edge_hash' => $edgeHash],
                [
                    'from_type' => 'concept',
                    'from_key' => $fromKey,
                    'relation_type' => $relType,
                    'to_type' => 'concept',
                    'to_key' => $toKey,
                    'from_label' => Str::limit($fromLabel, 145, ''),
                    'to_label' => Str::limit($toLabel, 145, ''),
                    'description' => Str::limit($desc, 450, ''),
                    'domain_level' => 3,
                    'evidence_refs' => array_values(array_filter([
                        'claim:#'.$claim->id,
                        $claim->source?->canonical_url ? 'url:'.$claim->source->canonical_url : null,
                    ])),
                    'source_type' => $claim->knowledge_space === AiResearchClaim::SPACE_OFFICIAL_MAYHEN ? 'verified_internal' : 'web_research',
                    'status' => AiKnowledgeRelation::STATUS_APPROVED,
                    'is_active' => true,
                    'confidence_score' => min(88, max(50, (int) $claim->confidence_score)),
                ]
            );
            $linked++;
        }

        if ($linked > 0) {
            AiKnowledgeRelation::clearRelationCache();
        }

        return $linked;
    }

    /**
     * Truy xuất trực tiếp Atomic Knowledge Claims đã được phép phục vụ khách (0 DB query khi cache ấm).
     *
     * @param  list<string>  $tokens
     * @return list<array{id: int, statement: string, space: string, domain: string, region: string, verification_status: string, source_url: ?string}>
     */
    public function matchRetrievableAtomicClaims(string $normalizedQuery, array $tokens): array
    {
        if (! Schema::hasTable('ai_research_claims')) {
            return [];
        }

        $claims = Cache::remember(self::ATOMIC_CACHE_KEY, 600, function (): array {
            return AiResearchClaim::query()
                ->with('source:id,canonical_url,domain')
                ->customerRetrievable()
                ->orderByDesc('independent_sources_count')
                ->orderByDesc('confidence_score')
                ->limit(300)
                ->get()
                ->map(fn (AiResearchClaim $c): array => [
                    'id' => (int) $c->id,
                    'statement' => (string) ($c->canonical_statement ?: $c->claim_text),
                    'space' => (string) ($c->knowledge_space ?: AiResearchClaim::SPACE_INDUSTRY_CULTURAL),
                    'domain' => (string) ($c->ontology_domain ?: 'vietnamese_wedding_culture'),
                    'region' => (string) ($c->cultural_region ?: 'vietnam_general'),
                    'verification_status' => (string) $c->verification_status,
                    'source_url' => $c->source?->canonical_url ? (string) $c->source->canonical_url : null,
                    'search_blob' => Str::ascii(mb_strtolower(($c->canonical_statement ?: $c->claim_text).' '.($c->ontology_domain ?? ''), 'UTF-8')),
                ])
                ->all();
        });

        if (! is_array($claims) || $claims === []) {
            return [];
        }

        $scored = [];
        foreach ($claims as $c) {
            $blob = (string) ($c['search_blob'] ?? '');
            $score = 0;
            foreach ($tokens as $tk) {
                if (str_contains($blob, $tk)) {
                    $score += 8;
                }
            }
            if ($score >= 16) {
                $scored[] = array_merge($c, ['score' => $score]);
            }
        }

        usort($scored, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        return array_values(array_map(fn (array $item) => [
            'id' => $item['id'],
            'statement' => $item['statement'],
            'space' => $item['space'],
            'domain' => $item['domain'],
            'region' => $item['region'],
            'verification_status' => $item['verification_status'],
            'source_url' => $item['source_url'],
        ], array_slice($scored, 0, 4)));
    }
}
