<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mở rộng không phá hủy cho Mây Hẹn Intelligence Engine:
     * - Bổ sung versioning, trust score, bằng chứng, kết quả phản biện AI và thời hạn hiệu lực vào ai_knowledge_entries.
     * - Bổ sung phân tầng rủi ro, trạng thái Reviewer AI, metadata truy vết và cờ hợp lệ feedback vào ai_chatbot_logs.
     * - Tạo mới 3 bảng độc lập: ai_knowledge_versions, ai_customer_memories, ai_learning_runs.
     */
    public function up(): void
    {
        if (Schema::hasTable('ai_knowledge_entries')) {
            Schema::table('ai_knowledge_entries', function (Blueprint $table) {
                if (! Schema::hasColumn('ai_knowledge_entries', 'version')) {
                    $table->unsignedInteger('version')->default(1)->after('priority');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'trust_score')) {
                    $table->unsignedTinyInteger('trust_score')->default(100)->after('version');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'evidence_refs')) {
                    $table->json('evidence_refs')->nullable()->after('trust_score');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'review_summary')) {
                    $table->json('review_summary')->nullable()->after('evidence_refs');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'effective_from')) {
                    $table->timestamp('effective_from')->nullable()->after('approved_at');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'effective_until')) {
                    $table->timestamp('effective_until')->nullable()->after('effective_from');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'is_outdated')) {
                    $table->boolean('is_outdated')->default(false)->index()->after('is_active');
                }
            });
        }

        if (Schema::hasTable('ai_chatbot_logs')) {
            Schema::table('ai_chatbot_logs', function (Blueprint $table) {
                if (! Schema::hasColumn('ai_chatbot_logs', 'risk_level')) {
                    $table->string('risk_level', 20)->default('simple')->index()->after('intent');
                }
                if (! Schema::hasColumn('ai_chatbot_logs', 'review_status')) {
                    $table->string('review_status', 30)->nullable()->index()->after('response_mode');
                }
                if (! Schema::hasColumn('ai_chatbot_logs', 'trace_metadata')) {
                    $table->json('trace_metadata')->nullable()->after('security_flags');
                }
                if (! Schema::hasColumn('ai_chatbot_logs', 'feedback_valid')) {
                    $table->boolean('feedback_valid')->default(true)->index()->after('feedback');
                }
            });
        }

        if (! Schema::hasTable('ai_knowledge_versions')) {
            Schema::create('ai_knowledge_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('knowledge_entry_id')
                    ->constrained('ai_knowledge_entries')
                    ->cascadeOnDelete();
                $table->unsignedInteger('version');
                $table->string('category', 40);
                $table->string('title', 191);
                $table->text('keywords')->nullable();
                $table->text('answer');
                $table->string('template_slug', 120)->nullable();
                $table->string('doc_url', 191)->nullable();
                $table->unsignedTinyInteger('trust_score')->default(100);
                $table->json('evidence_refs')->nullable();
                $table->string('change_reason', 191)->nullable();
                $table->unsignedBigInteger('changed_by_user_id')->nullable()->index();
                $table->timestamps();

                $table->index(['knowledge_entry_id', 'version'], 'ai_knowledge_versions_entry_ver_idx');
            });
        }

        if (! Schema::hasTable('ai_customer_memories')) {
            Schema::create('ai_customer_memories', function (Blueprint $table) {
                $table->id();
                $table->string('scope_key', 100)->unique();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('session_hash', 64)->nullable()->index();
                $table->json('preferred_styles')->nullable();
                $table->string('budget_tier', 30)->nullable();
                $table->json('recent_topics')->nullable();
                $table->string('last_intent', 40)->nullable();
                $table->unsignedInteger('interaction_count')->default(0);
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ai_learning_runs')) {
            Schema::create('ai_learning_runs', function (Blueprint $table) {
                $table->id();
                $table->string('run_type', 30)->default('self_train')->index();
                $table->string('status', 20)->default('running')->index();
                $table->unsignedBigInteger('triggered_by_user_id')->nullable()->index();
                $table->json('topics')->nullable();
                $table->unsignedInteger('signals_analyzed')->default(0);
                $table->unsignedInteger('candidates_proposed')->default(0);
                $table->unsignedInteger('candidates_passed')->default(0);
                $table->unsignedInteger('candidates_rejected')->default(0);
                $table->json('metrics')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_learning_runs');
        Schema::dropIfExists('ai_customer_memories');
        Schema::dropIfExists('ai_knowledge_versions');

        if (Schema::hasTable('ai_chatbot_logs')) {
            Schema::table('ai_chatbot_logs', function (Blueprint $table) {
                $cols = array_values(array_filter(
                    ['risk_level', 'review_status', 'trace_metadata', 'feedback_valid'],
                    fn (string $c) => Schema::hasColumn('ai_chatbot_logs', $c)
                ));
                if ($cols !== []) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('ai_knowledge_entries')) {
            Schema::table('ai_knowledge_entries', function (Blueprint $table) {
                $cols = array_values(array_filter(
                    ['version', 'trust_score', 'evidence_refs', 'review_summary', 'effective_from', 'effective_until', 'is_outdated'],
                    fn (string $c) => Schema::hasColumn('ai_knowledge_entries', $c)
                ));
                if ($cols !== []) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
