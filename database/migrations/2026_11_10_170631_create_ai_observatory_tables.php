<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('wedding_id')->nullable()->constrained('weddings')->nullOnDelete();
            $table->string('feature_name', 50)->default('story_generator'); // story_generator, rsvp_wording, concierge_qa
            $table->string('provider', 50)->default('gemini-1.5-flash'); // gemini-1.5-flash, gemini-1.5-pro, heuristic_fallback
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);
            $table->decimal('cost_vnd', 10, 4)->default(0); // Chi phí thực tế quy đổi theo VND
            $table->unsignedInteger('latency_ms')->default(0);
            $table->string('status', 30)->default('success'); // success, fallback_applied, blocked_safety, failed
            $table->string('safety_rating', 50)->nullable()->default('passed'); // passed, flagged_profanity, flagged_hate, flagged_inappropriate
            $table->string('user_action', 30)->default('pending'); // accepted, edited, rejected, pending
            $table->text('prompt_preview')->nullable();
            $table->text('output_preview')->nullable();
            $table->string('error_message', 255)->nullable();
            $table->timestamps();

            $table->index(['feature_name', 'status']);
            $table->index(['provider', 'created_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};
