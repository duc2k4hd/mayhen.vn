<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('personality_sessions')) {
            Schema::create('personality_sessions', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('guest_token', 64)->nullable()->index();
                $table->string('session_token', 64)->unique();
                $table->string('slug', 48)->unique();
                $table->string('share_token', 64)->nullable()->unique();
                $table->string('model_code', 32)->index(); // sixteen_types | big_five
                $table->string('assessment_version', 48)->index(); // mayhen_16types_quick_v1 | standard | deep | bigfive_ipip50_v1
                $table->string('method_version', 32)->default('v1.0');
                $table->string('scoring_version', 32)->default('v1.0');
                $table->string('status', 24)->default('created')->index(); // created|in_progress|submitted|scored|report_ready|abandoned|expired
                $table->string('display_name', 120)->nullable();
                $table->unsignedSmallInteger('question_count')->default(32);
                $table->unsignedSmallInteger('answered_count')->default(0);
                $table->unsignedInteger('duration_seconds')->default(0);
                $table->json('question_order_json')->nullable();
                $table->json('responses_json')->nullable();
                $table->json('scores_json')->nullable();
                $table->string('suggested_type', 16)->nullable()->index(); // INTJ..ESFP hoặc UNDECIDED hoặc OCEAN
                $table->string('best_fit_type', 16)->nullable()->index();
                $table->json('possible_types_json')->nullable();
                $table->json('boundary_flags_json')->nullable();
                $table->json('quality_flags_json')->nullable();
                $table->string('calculation_hash', 64)->nullable()->index();
                $table->json('ai_report_json')->nullable();
                $table->string('ai_status', 24)->default('idle'); // idle|pending|ready|fallback|failed
                $table->json('growth_plan_json')->nullable();
                $table->text('user_notes')->nullable();
                $table->boolean('is_shared')->default(false)->index();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();

                $table->index(['user_id', 'model_code', 'created_at'], 'ps_user_model_created_idx');
            });
        }

        if (! Schema::hasTable('personality_couple_reports')) {
            Schema::create('personality_couple_reports', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->string('slug', 48)->unique();
                $table->string('invite_code', 32)->unique();
                $table->unsignedBigInteger('user_a_id')->nullable()->index();
                $table->unsignedBigInteger('user_b_id')->nullable()->index();
                $table->unsignedBigInteger('session_a_id')->index();
                $table->unsignedBigInteger('session_b_id')->nullable()->index();
                $table->string('partner_a_name', 120)->nullable();
                $table->string('partner_b_name', 120)->nullable();
                $table->string('type_a', 16)->nullable()->index();
                $table->string('type_b', 16)->nullable()->index();
                $table->string('status', 24)->default('waiting_partner')->index(); // waiting_partner|completed
                $table->boolean('share_consent_a')->default(true);
                $table->boolean('share_consent_b')->default(false);
                $table->json('couple_answers_a_json')->nullable();
                $table->json('couple_answers_b_json')->nullable();
                $table->json('comparison_json')->nullable();
                $table->json('ai_synthesis_json')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('personality_followups')) {
            Schema::create('personality_followups', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('session_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('topic_code', 48)->default('general')->index();
                $table->text('question');
                $table->longText('answer');
                $table->json('evidence_refs_json')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('personality_followups');
        Schema::dropIfExists('personality_couple_reports');
        Schema::dropIfExists('personality_sessions');
    }
};
