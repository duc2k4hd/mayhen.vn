<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nâng cấp không phá hủy cho Mây Hẹn Autonomous Intelligence Engine V2:
     * - KHÔNG chạm vào bất kỳ bảng nghiệp vụ cốt lõi nào (users, weddings, invitations, guests, orders, plans...).
     * - Bổ sung cột provenance, content_hash, domain_level (L0-L4) và candidate expiry cho ai_knowledge_entries.
     * - Tạo 4 bảng AI phụ trợ độc lập:
     *   1. ai_knowledge_relations  : Knowledge Graph quan hệ giữa dịch vụ, tính năng, quy trình và gói dịch vụ.
     *   2. ai_knowledge_gaps       : Ghi nhận khoảng trống tri thức có bằng chứng thay vì bịa câu trả lời.
     *   3. ai_learning_checkpoints : Theo dõi incremental discovery (cursor, content_hash) tránh quét toàn DB.
     *   4. ai_learning_tasks       : Hàng đợi học nền tách biệt hoàn toàn khỏi queue nghiệp vụ (idempotency + fencing).
     */
    public function up(): void
    {
        if (Schema::hasTable('ai_knowledge_entries')) {
            Schema::table('ai_knowledge_entries', function (Blueprint $table) {
                if (! Schema::hasColumn('ai_knowledge_entries', 'content_hash')) {
                    $table->string('content_hash', 64)->nullable()->index()->after('title');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'domain_level')) {
                    $table->unsignedTinyInteger('domain_level')->default(0)->index()->after('category');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'source_id')) {
                    $table->string('source_id', 120)->nullable()->index()->after('source');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'source_type')) {
                    $table->string('source_type', 40)->default('verified_internal')->index()->after('source_id');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'ownership')) {
                    $table->string('ownership', 60)->default('mayhen_internal')->after('source_type');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'security_classification')) {
                    $table->string('security_classification', 30)->default('public_customer_safe')->index()->after('ownership');
                }
                if (! Schema::hasColumn('ai_knowledge_entries', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable()->index()->after('effective_until');
                }
            });
        }

        if (! Schema::hasTable('ai_knowledge_relations')) {
            Schema::create('ai_knowledge_relations', function (Blueprint $table) {
                $table->id();
                $table->string('edge_hash', 64)->unique();
                $table->string('from_type', 30);
                $table->string('from_key', 80);
                $table->string('relation_type', 40);
                $table->string('to_type', 30);
                $table->string('to_key', 80);
                $table->string('from_label', 150);
                $table->string('to_label', 150);
                $table->text('description')->nullable();
                $table->unsignedTinyInteger('domain_level')->default(2)->index();
                $table->json('evidence_refs')->nullable();
                $table->string('source_type', 40)->default('verified_internal');
                $table->string('status', 20)->default('approved')->index();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedTinyInteger('confidence_score')->default(100);
                $table->unsignedBigInteger('created_by_user_id')->nullable()->index();
                $table->timestamps();

                $table->index(['from_type', 'from_key', 'is_active'], 'ai_kr_from_active_idx');
            });
        } elseif (! Schema::hasColumn('ai_knowledge_relations', 'edge_hash')) {
            Schema::table('ai_knowledge_relations', function (Blueprint $table) {
                $table->string('edge_hash', 64)->unique()->after('id');
            });
        }

        if (! Schema::hasTable('ai_knowledge_gaps')) {
            Schema::create('ai_knowledge_gaps', function (Blueprint $table) {
                $table->id();
                $table->string('gap_hash', 64)->unique();
                $table->string('topic', 40)->default('general')->index();
                $table->unsignedTinyInteger('domain_level')->default(3)->index();
                $table->string('sample_question', 191);
                $table->unsignedInteger('occurrence_count')->default(1);
                $table->string('status', 20)->default('open')->index();
                $table->unsignedBigInteger('resolved_entry_id')->nullable()->index();
                $table->json('evidence_meta')->nullable();
                $table->timestamp('last_seen_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ai_learning_checkpoints')) {
            Schema::create('ai_learning_checkpoints', function (Blueprint $table) {
                $table->id();
                $table->string('source_key', 80)->unique();
                $table->unsignedTinyInteger('domain_level')->default(0)->index();
                $table->unsignedBigInteger('last_cursor')->default(0);
                $table->timestamp('last_source_updated_at')->nullable();
                $table->string('content_hash', 64)->nullable();
                $table->unsignedInteger('records_scanned')->default(0);
                $table->timestamp('last_run_at')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ai_learning_tasks')) {
            Schema::create('ai_learning_tasks', function (Blueprint $table) {
                $table->id();
                $table->string('idempotency_key', 100)->unique();
                $table->unsignedTinyInteger('domain_level')->default(0)->index();
                $table->string('task_type', 40)->index();
                $table->string('topic', 40)->default('general')->index();
                $table->unsignedTinyInteger('priority')->default(5)->index();
                $table->string('status', 20)->default('queued')->index();
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->unsignedTinyInteger('max_attempts')->default(3);
                $table->timestamp('next_retry_at')->nullable()->index();
                $table->string('locked_by', 80)->nullable()->index();
                $table->timestamp('locked_at')->nullable()->index();
                $table->unsignedBigInteger('fencing_token')->default(0);
                $table->json('payload')->nullable();
                $table->json('result')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'domain_level', 'priority', 'next_retry_at'], 'ai_lt_sched_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_learning_tasks');
        Schema::dropIfExists('ai_learning_checkpoints');
        Schema::dropIfExists('ai_knowledge_gaps');
        Schema::dropIfExists('ai_knowledge_relations');

        if (Schema::hasTable('ai_knowledge_entries')) {
            Schema::table('ai_knowledge_entries', function (Blueprint $table) {
                $cols = array_values(array_filter(
                    ['content_hash', 'domain_level', 'source_id', 'source_type', 'ownership', 'security_classification', 'expires_at'],
                    fn (string $c) => Schema::hasColumn('ai_knowledge_entries', $c)
                ));
                if ($cols !== []) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
