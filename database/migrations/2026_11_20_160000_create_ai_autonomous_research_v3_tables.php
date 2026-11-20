<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nâng cấp không phá hủy cho Mây Hẹn Autonomous Research & Intelligence Engine V3:
     * - KHÔNG chạm bất kỳ bảng nghiệp vụ cốt lõi nào.
     * - Bổ sung phân lớp tri thức (knowledge_layer: official_mayhen | research_general | applied_insight),
     *   research_topic_id, source_url và cultural_context vào ai_knowledge_entries.
     * - Tạo mới 4 bảng Research & Curriculum độc lập (mọi unique index đều <= 400 bytes, an toàn 100% MySQL utf8mb4):
     *   1. ai_research_goals   : Mục tiêu nghiên cứu do Admin hoặc hệ thống định nghĩa.
     *   2. ai_research_topics  : Chương trình học động (Dynamic Curriculum L1 -> L7) dạng cây.
     *   3. ai_research_sources : Sổ đăng ký nguồn web, provenance, content_hash & trạng thái crawl.
     *   4. ai_research_claims  : Luận điểm & bằng chứng trích xuất, kiểm chứng chéo đa nguồn độc lập.
     */
    public function up(): void
    {
        if (Schema::hasTable('ai_knowledge_entries')) {
            Schema::table('ai_knowledge_entries', function (Blueprint $table) {
                if (! Schema::hasColumn('ai_knowledge_entries', 'knowledge_layer')) {
                    $table->string('knowledge_layer', 30)->default('official_mayhen')->index()->after('category');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'research_topic_id')) {
                    $table->unsignedBigInteger('research_topic_id')->nullable()->index()->after('domain_level');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'source_url')) {
                    $table->string('source_url', 500)->nullable()->after('source_id');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'cultural_context')) {
                    $table->string('cultural_context', 60)->nullable()->index()->after('security_classification');
                }
            });
        }

        if (! Schema::hasTable('ai_research_goals')) {
            Schema::create('ai_research_goals', function (Blueprint $table) {
                $table->id();
                $table->string('slug', 100)->unique();
                $table->string('title', 190);
                $table->string('domain', 60)->index();
                $table->text('description')->nullable();
                $table->unsignedTinyInteger('priority')->default(8)->index();
                $table->string('country', 10)->default('VN');
                $table->string('language', 10)->default('vi');
                $table->unsignedTinyInteger('max_depth')->default(7);
                $table->string('autonomy_level', 20)->default('supervised');
                $table->json('allowed_domains')->nullable();
                $table->json('disallowed_domains')->nullable();
                $table->unsignedInteger('daily_url_limit')->default(20);
                $table->boolean('allow_auto_expand')->default(true);
                $table->string('status', 20)->default('active')->index();
                $table->unsignedBigInteger('created_by_user_id')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ai_research_topics')) {
            Schema::create('ai_research_topics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('goal_id')->constrained('ai_research_goals')->cascadeOnDelete();
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->string('slug', 120)->unique();
                $table->string('title', 190);
                $table->string('domain', 60)->index();
                $table->string('category', 40)->default('general')->index();
                $table->unsignedTinyInteger('priority')->default(7)->index();
                $table->unsignedTinyInteger('learning_stage')->default(1)->index();
                $table->json('learning_objectives')->nullable();
                $table->json('prerequisites')->nullable();
                $table->json('known_concepts')->nullable();
                $table->json('unknown_concepts')->nullable();
                $table->json('search_queries')->nullable();
                $table->unsignedInteger('source_coverage')->default(0);
                $table->unsignedInteger('evidence_count')->default(0);
                $table->unsignedTinyInteger('quality_score')->default(0);
                $table->string('status', 30)->default('pending')->index();
                $table->string('review_state', 30)->default('unreviewed')->index();
                $table->timestamp('last_researched_at')->nullable();
                $table->timestamp('next_research_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ai_research_sources')) {
            Schema::create('ai_research_sources', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('topic_id')->nullable()->index();
                $table->string('url_hash', 64)->unique();
                $table->string('canonical_url', 500);
                $table->string('domain', 120)->index();
                $table->string('title', 255)->nullable();
                $table->string('discovery_method', 30)->default('web_search')->index();
                $table->string('discovery_query', 190)->nullable();
                $table->string('status', 30)->default('discovered')->index();
                $table->string('rejection_reason', 190)->nullable();
                $table->unsignedSmallInteger('http_status')->nullable();
                $table->string('content_type', 80)->nullable();
                $table->string('content_hash', 64)->nullable()->index();
                $table->unsignedInteger('content_length')->default(0);
                $table->text('excerpt')->nullable();
                $table->string('published_at_detected', 40)->nullable();
                $table->unsignedTinyInteger('trust_score')->default(50);
                $table->unsignedTinyInteger('relevance_score')->default(50);
                $table->unsignedInteger('claims_count')->default(0);
                $table->json('security_flags')->nullable();
                $table->timestamp('fetched_at')->nullable();
                $table->timestamp('recheck_after_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ai_research_claims')) {
            Schema::create('ai_research_claims', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('topic_id')->nullable()->index();
                $table->unsignedBigInteger('source_id')->nullable()->index();
                $table->string('claim_hash', 64)->unique();
                $table->text('claim_text');
                $table->text('evidence_snippet')->nullable();
                $table->string('knowledge_layer', 30)->default('research_general')->index();
                $table->string('cultural_region', 40)->nullable()->index();
                $table->string('verification_status', 30)->default('hypothesis')->index();
                $table->unsignedInteger('independent_sources_count')->default(1);
                $table->json('source_domains')->nullable();
                $table->unsignedTinyInteger('confidence_score')->default(50);
                $table->text('critic_notes')->nullable();
                $table->unsignedBigInteger('promoted_entry_id')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_research_claims');
        Schema::dropIfExists('ai_research_sources');
        Schema::dropIfExists('ai_research_topics');
        Schema::dropIfExists('ai_research_goals');

        if (Schema::hasTable('ai_knowledge_entries')) {
            Schema::table('ai_knowledge_entries', function (Blueprint $table) {
                $cols = array_values(array_filter(
                    ['knowledge_layer', 'research_topic_id', 'source_url', 'cultural_context'],
                    fn (string $c) => Schema::hasColumn('ai_knowledge_entries', $c)
                ));
                if ($cols !== []) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
