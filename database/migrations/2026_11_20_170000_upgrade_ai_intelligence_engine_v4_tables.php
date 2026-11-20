<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nâng cấp không phá hủy Mây Hẹn Intelligence Engine V4.0:
     * - KHÔNG chạm bất kỳ bảng nghiệp vụ cốt lõi nào.
     * - Nâng ai_research_claims thành đơn vị tri thức trung tâm Atomic Knowledge Claim (4 không gian, 12 trạng thái, 9 chỉ số chất lượng đa chiều, versioning).
     * - Bổ sung knowledge_space, ontology_domain, quality_metrics, source_claim_ids vào ai_knowledge_entries.
     * - Bổ sung source_tier và is_blocked (Emergency Block Source) vào ai_research_sources.
     * - Bổ sung business_relevance, completion_criteria vào ai_research_goals.
     * - Tạo mới bảng ai_benchmark_runs theo dõi hiệu năng học tập Trước/Sau và phát hiện Regression.
     */
    public function up(): void
    {
        if (Schema::hasTable('ai_research_claims')) {
            Schema::table('ai_research_claims', function (Blueprint $table) {
                if (! Schema::hasColumn('ai_research_claims', 'canonical_statement')) {
                    $table->text('canonical_statement')->nullable()->after('claim_text');
                }
                if (! Schema::hasColumn('ai_research_claims', 'claim_type')) {
                    $table->string('claim_type', 30)->default('fact')->index()->after('canonical_statement');
                }
                if (! Schema::hasColumn('ai_research_claims', 'knowledge_space')) {
                    $table->string('knowledge_space', 30)->default('industry_cultural')->index()->after('knowledge_layer');
                }
                if (! Schema::hasColumn('ai_research_claims', 'ontology_domain')) {
                    $table->string('ontology_domain', 50)->nullable()->index()->after('knowledge_space');
                }
                if (! Schema::hasColumn('ai_research_claims', 'quality_metrics')) {
                    $table->json('quality_metrics')->nullable()->after('confidence_score');
                }
                if (! Schema::hasColumn('ai_research_claims', 'contradiction_notes')) {
                    $table->text('contradiction_notes')->nullable()->after('critic_notes');
                }
                if (! Schema::hasColumn('ai_research_claims', 'version')) {
                    $table->unsignedInteger('version')->default(1)->after('confidence_score');
                }
                if (! Schema::hasColumn('ai_research_claims', 'approval_status')) {
                    $table->string('approval_status', 30)->default('needs_review')->index()->after('verification_status');
                }
                if (! Schema::hasColumn('ai_research_claims', 'security_classification')) {
                    $table->string('security_classification', 30)->default('public_customer_safe')->index()->after('approval_status');
                }
                if (! Schema::hasColumn('ai_research_claims', 'effective_from')) {
                    $table->timestamp('effective_from')->nullable()->after('security_classification');
                }
                if (! Schema::hasColumn('ai_research_claims', 'effective_until')) {
                    $table->timestamp('effective_until')->nullable()->after('effective_from');
                }
                if (! Schema::hasColumn('ai_research_claims', 'approved_by_user_id')) {
                    $table->unsignedBigInteger('approved_by_user_id')->nullable()->index()->after('promoted_entry_id');
                }
                if (! Schema::hasColumn('ai_research_claims', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by_user_id');
                }
            });
        }

        if (Schema::hasTable('ai_knowledge_entries')) {
            Schema::table('ai_knowledge_entries', function (Blueprint $table) {
                if (! Schema::hasColumn('ai_knowledge_entries', 'knowledge_space')) {
                    $table->string('knowledge_space', 30)->default('official_mayhen')->index()->after('knowledge_layer');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'ontology_domain')) {
                    $table->string('ontology_domain', 50)->nullable()->index()->after('knowledge_space');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'quality_metrics')) {
                    $table->json('quality_metrics')->nullable()->after('review_summary');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'source_claim_ids')) {
                    $table->json('source_claim_ids')->nullable()->after('evidence_refs');
                }
            });
        }

        if (Schema::hasTable('ai_research_sources')) {
            Schema::table('ai_research_sources', function (Blueprint $table) {
                if (! Schema::hasColumn('ai_research_sources', 'source_tier')) {
                    $table->string('source_tier', 30)->default('community_experience')->index()->after('discovery_method');
                }
                if (! Schema::hasColumn('ai_research_sources', 'is_blocked')) {
                    $table->boolean('is_blocked')->default(false)->index()->after('status');
                }
            });
        }

        if (Schema::hasTable('ai_research_goals')) {
            Schema::table('ai_research_goals', function (Blueprint $table) {
                if (! Schema::hasColumn('ai_research_goals', 'business_relevance')) {
                    $table->text('business_relevance')->nullable()->after('description');
                }
                if (! Schema::hasColumn('ai_research_goals', 'completion_criteria')) {
                    $table->json('completion_criteria')->nullable()->after('max_depth');
                }
            });
        }

        if (! Schema::hasTable('ai_benchmark_runs')) {
            Schema::create('ai_benchmark_runs', function (Blueprint $table) {
                $table->id();
                $table->string('domain', 50)->default('all')->index();
                $table->string('benchmark_phase', 30)->default('after_learning')->index();
                $table->unsignedBigInteger('triggered_by_user_id')->nullable()->index();
                $table->unsignedInteger('total_cases')->default(0);
                $table->unsignedInteger('passed_cases')->default(0);
                $table->decimal('grounded_score', 5, 2)->default(0);
                $table->decimal('coverage_score', 5, 2)->default(0);
                $table->decimal('hallucination_rate', 5, 2)->default(0);
                $table->unsignedInteger('avg_latency_ms')->default(0);
                $table->decimal('delta_vs_baseline', 6, 2)->nullable();
                $table->boolean('regression_detected')->default(false)->index();
                $table->json('case_results')->nullable();
                $table->json('knowledge_snapshot')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_benchmark_runs');

        if (Schema::hasTable('ai_research_goals')) {
            Schema::table('ai_research_goals', function (Blueprint $table) {
                $cols = array_values(array_filter(
                    ['business_relevance', 'completion_criteria'],
                    fn (string $c) => Schema::hasColumn('ai_research_goals', $c)
                ));
                if ($cols !== []) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('ai_research_sources')) {
            Schema::table('ai_research_sources', function (Blueprint $table) {
                $cols = array_values(array_filter(
                    ['source_tier', 'is_blocked'],
                    fn (string $c) => Schema::hasColumn('ai_research_sources', $c)
                ));
                if ($cols !== []) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('ai_knowledge_entries')) {
            Schema::table('ai_knowledge_entries', function (Blueprint $table) {
                $cols = array_values(array_filter(
                    ['knowledge_space', 'ontology_domain', 'quality_metrics', 'source_claim_ids'],
                    fn (string $c) => Schema::hasColumn('ai_knowledge_entries', $c)
                ));
                if ($cols !== []) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('ai_research_claims')) {
            Schema::table('ai_research_claims', function (Blueprint $table) {
                $cols = array_values(array_filter(
                    [
                        'canonical_statement', 'claim_type', 'knowledge_space', 'ontology_domain',
                        'quality_metrics', 'contradiction_notes', 'version', 'approval_status',
                        'security_classification', 'effective_from', 'effective_until',
                        'approved_by_user_id', 'approved_at',
                    ],
                    fn (string $c) => Schema::hasColumn('ai_research_claims', $c)
                ));
                if ($cols !== []) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
