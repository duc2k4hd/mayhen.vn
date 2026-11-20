<?php

declare(strict_types=1);

namespace App\Services\Intelligence\V4;

use App\Models\AiKnowledgeEntry;
use App\Models\AiResearchClaim;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Schema;

/**
 * Safe Legacy Knowledge Audit & Reclassification Service (Mục XVIII):
 * - KHÔNG xóa bất kỳ bản ghi cũ nào; luôn hỗ trợ --dry-run xem trước plan.
 * - Khi --apply: ghi bản chụp AiKnowledgeVersion trước khi cập nhật, gán đúng ontology_domain,
 *   knowledge_space, category, hạ điểm ảo 100/100 cho bài 1 nguồn, và tắt (outdated/inactive)
 *   các FAQ sơ đẳng/lạc đề (Gmail, gửi hay gởi, Website cơ bản, Shinzen kekkon Nhật Bản...).
 */
class LegacyKnowledgeAuditService
{
    public function __construct(
        protected OntologyAndClassificationService $ontology,
        protected TypedKnowledgeGraphService $typedGraph
    ) {}

    /**
     * @return array{
     *     dry_run: bool,
     *     scanned: int,
     *     changes_planned: int,
     *     changes_applied: int,
     *     plan: list<array{id: int, title: string, old_category: string, new_category: string, old_space: string, new_space: string, action: string, reason: string}>
     * }
     */
    public function auditAndMigrate(bool $dryRun = true, ?int $adminUserId = null): array
    {
        if (! Schema::hasTable('ai_knowledge_entries')) {
            return ['dry_run' => $dryRun, 'scanned' => 0, 'changes_planned' => 0, 'changes_applied' => 0, 'plan' => []];
        }

        $entries = AiKnowledgeEntry::query()->orderBy('id')->get();
        $plan = [];
        $applied = 0;

        foreach ($entries as $entry) {
            $sourceDomain = $entry->source_url ? (string) (parse_url((string) $entry->source_url, PHP_URL_HOST) ?? '') : '';
            $cls = $this->ontology->classifyKnowledgeRecord(
                title: (string) $entry->title,
                body: (string) $entry->answer,
                sourceUrl: $entry->source_url,
                sourceDomain: $sourceDomain,
                sourceOrigin: (string) $entry->source
            );

            $oldCategory = (string) $entry->category;
            $oldSpace = (string) ($entry->knowledge_space ?: ($entry->knowledge_layer === AiKnowledgeEntry::LAYER_APPLIED_INSIGHT ? AiResearchClaim::SPACE_PRODUCT_INNOVATION : ($entry->knowledge_layer === AiKnowledgeEntry::LAYER_RESEARCH_GENERAL ? AiResearchClaim::SPACE_INDUSTRY_CULTURAL : AiResearchClaim::SPACE_OFFICIAL_MAYHEN)));
            $isInternalOfficial = in_array((string) $entry->source, [
                AiKnowledgeEntry::SOURCE_ADMIN,
                AiKnowledgeEntry::SOURCE_AI_SELF_TRAIN,
            ], true);
            $newCategory = ($isInternalOfficial && array_key_exists($oldCategory, AiKnowledgeEntry::CATEGORIES))
                ? $oldCategory
                : $cls['category'];
            $newSpace = $cls['knowledge_space'];
            $newLayer = $cls['knowledge_layer'];
            $newDomain = $cls['ontology_domain'];

            $action = 'keep_sync_metadata';
            $shouldDeactivate = false;
            $shouldMarkOutdated = false;
            $honestScore = (int) $entry->trust_score;

            if ($cls['is_trivial_or_off_topic']) {
                $action = 'archive_outdated_trivial';
                $shouldDeactivate = true;
                $shouldMarkOutdated = true;
                $honestScore = min(40, $honestScore);
            } elseif ($newSpace === AiResearchClaim::SPACE_PRODUCT_INNOVATION) {
                $action = 'move_to_product_innovation';
                $shouldDeactivate = true; // Ý tưởng sản phẩm không bật live ra chatbot khách hàng
                $honestScore = min(65, $honestScore);
            } elseif ($entry->source === AiKnowledgeEntry::SOURCE_WEB_RESEARCH) {
                $tier = $this->ontology->classifySourceTier($sourceDomain, '');
                // Hạ điểm ảo 100/100 cho bài nghiên cứu 1 nguồn Wikipedia/blog xuống mức trung thực theo source_tier
                $honestScore = min($tier['max_reliability'], max(50, $honestScore));
                if ($oldCategory !== $newCategory) {
                    $action = 'reclassify_domain_and_calibrate';
                } elseif ((int) $entry->trust_score > $tier['max_reliability']) {
                    $action = 'calibrate_single_source_score';
                }
            }

            $needsChange = $action !== 'keep_sync_metadata'
                || ($entry->ontology_domain !== $newDomain)
                || ($entry->knowledge_space !== $newSpace);

            if (! $needsChange) {
                continue;
            }

            $plan[] = [
                'id' => (int) $entry->id,
                'title' => (string) $entry->title,
                'old_category' => $oldCategory,
                'new_category' => $newCategory,
                'old_space' => $oldSpace,
                'new_space' => $newSpace,
                'action' => $action,
                'reason' => $cls['classification_note'],
            ];

            if (! $dryRun) {
                // Ghi snapshot phiên bản trước khi chuyển đổi để hỗ trợ Rollback 100%
                if ($entry->versions()->count() === 0) {
                    $entry->recordVersion($adminUserId, 'Bản gốc trước khi chuyển đổi Ontology V4.0');
                }

                $nextVersion = max((int) ($entry->version ?: 1), (int) $entry->versions()->max('version')) + 1;
                $update = [
                    'category' => $newCategory,
                    'knowledge_layer' => $newLayer,
                    'trust_score' => $honestScore,
                    'version' => $nextVersion,
                ];
                if (Schema::hasColumn('ai_knowledge_entries', 'knowledge_space')) {
                    $update['knowledge_space'] = $newSpace;
                    $update['ontology_domain'] = $newDomain;
                    $update['quality_metrics'] = [
                        'methodology' => 'heuristic_multi_gate_v4_uncalibrated',
                        'business_relevance' => $cls['business_relevance'],
                        'cultural_applicability' => $cls['cultural_applicability'],
                        'source_tier' => $this->ontology->classifySourceTier($sourceDomain, $entry->source === AiKnowledgeEntry::SOURCE_ADMIN ? 'internal_official' : '')['tier'],
                        'audit_action' => $action,
                        'audited_at' => now()->toIso8601String(),
                    ];
                }
                if ($shouldDeactivate) {
                    $update['is_active'] = false;
                }
                if ($shouldMarkOutdated) {
                    $update['is_outdated'] = true;
                }

                $entry->update($update);
                $entry->recordVersion($adminUserId, "V4 Ontology Audit ({$action}): {$cls['classification_note']}");
                $applied++;
            }
        }

        if (! $dryRun && $applied > 0) {
            $this->typedGraph->syncTypedWeddingOntologyGraph($adminUserId);
            AuditLogger::log(
                'AI_LEGACY_KNOWLEDGE_MIGRATED_V4',
                'AiKnowledgeEntry',
                $applied,
                "Kiểm toán & chuẩn hóa {$applied} bản ghi tri thức cũ sang Ontology & 4 Không gian V4.0 (có lưu Rollback)",
                ['changes_count' => $applied]
            );
        }

        return [
            'dry_run' => $dryRun,
            'scanned' => $entries->count(),
            'changes_planned' => count($plan),
            'changes_applied' => $applied,
            'plan' => $plan,
        ];
    }
}
