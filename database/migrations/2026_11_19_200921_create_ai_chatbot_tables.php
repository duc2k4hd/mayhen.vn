<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_knowledge_entries')) {
            Schema::create('ai_knowledge_entries', function (Blueprint $table) {
                $table->id();
                $table->string('category', 40)->default('general')->index();
                $table->string('title', 191);
                $table->text('keywords')->nullable();
                $table->text('answer');
                $table->string('template_slug', 120)->nullable()->index();
                $table->string('doc_url', 191)->nullable();
                $table->string('source', 20)->default('admin')->index();
                $table->string('status', 20)->default('approved')->index();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedTinyInteger('priority')->default(5);
                $table->json('security_flags')->nullable();
                $table->unsignedBigInteger('submitted_by_user_id')->nullable()->index();
                $table->string('submitted_session_hash', 64)->nullable();
                $table->unsignedBigInteger('approved_by_user_id')->nullable()->index();
                $table->timestamp('approved_at')->nullable();
                $table->string('rejection_reason', 191)->nullable();
                $table->unsignedInteger('usage_count')->default(0);
                $table->timestamps();

                $table->index(['status', 'is_active', 'category'], 'ai_knowledge_live_lookup_idx');
            });
        }

        if (! Schema::hasTable('ai_chatbot_logs')) {
            Schema::create('ai_chatbot_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('session_hash', 64)->index();
                $table->string('ip_hash', 64)->nullable();
                $table->text('user_message');
                $table->text('bot_reply');
                $table->string('intent', 40)->default('general')->index();
                $table->string('response_mode', 40)->default('fallback_grounded')->index();
                $table->json('matched_knowledge_ids')->nullable();
                $table->json('matched_template_slugs')->nullable();
                $table->json('matched_doc_urls')->nullable();
                $table->json('security_flags')->nullable();
                $table->string('provider_status', 60)->nullable();
                $table->unsignedInteger('latency_ms')->default(0);
                $table->string('feedback', 20)->nullable()->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chatbot_logs');
        Schema::dropIfExists('ai_knowledge_entries');
    }
};
