<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tuvi_profiles')) {
            Schema::create('tuvi_profiles', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('slug', 120)->unique();
                $table->string('nickname', 80)->nullable();
                $table->string('relationship_label', 32)->default('self');
                $table->string('gender_yin_yang', 16)->default('female');
                $table->string('calendar_type', 16)->default('solar');
                $table->unsignedSmallInteger('birth_year');
                $table->unsignedTinyInteger('birth_month');
                $table->unsignedTinyInteger('birth_day');
                $table->boolean('is_leap_month')->default(false);
                $table->string('birth_time_mode', 24)->default('exact_time');
                $table->unsignedTinyInteger('birth_hour')->nullable();
                $table->unsignedTinyInteger('birth_minute')->nullable();
                $table->unsignedTinyInteger('birth_branch_index')->nullable();
                $table->string('birth_time_window', 32)->nullable();
                $table->string('birth_timezone', 64)->default('Asia/Ho_Chi_Minh');
                $table->string('school_code', 40)->default('vn_nam_phai_v1');
                $table->string('school_version', 32)->default('vn_nam_phai_v1.0.0');
                $table->string('leap_month_rule', 24)->default('split_15');
                $table->string('late_rat_rule', 24)->default('next_lunar_day');
                $table->boolean('is_primary_self')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['user_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('tuvi_charts')) {
            Schema::create('tuvi_charts', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('profile_id')->constrained('tuvi_profiles')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('slug', 120)->unique();
                $table->unsignedSmallInteger('version_number')->default(1);
                $table->string('input_hash', 64)->index();
                $table->string('school_code', 40)->default('vn_nam_phai_v1');
                $table->string('rule_version', 40)->default('vn_nam_phai_v1.0.0');
                $table->string('kb_version', 32)->default('1.0.0');
                $table->unsignedSmallInteger('viewed_year');
                $table->string('focus_topic', 64)->default('toan_bo');
                $table->json('chart_payload_json');
                $table->json('patterns_json')->nullable();
                $table->json('interpretation_json')->nullable();
                $table->boolean('ai_powered')->default(false);
                $table->string('ai_model', 120)->nullable();
                $table->string('ai_status', 24)->default('fallback');
                $table->string('privacy_mode', 24)->default('private');
                $table->string('share_token', 64)->nullable()->unique();
                $table->timestamp('share_revoked_at')->nullable();
                $table->boolean('share_hide_birth_info')->default(true);
                $table->string('idempotency_key', 80)->nullable()->unique();
                $table->boolean('is_favorite')->default(false);
                $table->string('feedback_rating', 32)->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
                $table->index(['profile_id', 'version_number']);
            });
        }

        if (! Schema::hasTable('tuvi_chart_followups')) {
            Schema::create('tuvi_chart_followups', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('chart_id')->constrained('tuvi_charts')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('palace_code', 32)->nullable();
                $table->text('question');
                $table->text('answer');
                $table->boolean('ai_powered')->default(false);
                $table->string('ai_model', 120)->nullable();
                $table->timestamps();

                $table->index(['chart_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('tuvi_chart_journals')) {
            Schema::create('tuvi_chart_journals', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('chart_id')->constrained('tuvi_charts')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedSmallInteger('milestone_days')->default(30);
                $table->text('focus_note')->nullable();
                $table->text('actions_taken')->nullable();
                $table->text('lessons_learned')->nullable();
                $table->timestamps();

                $table->index(['chart_id', 'milestone_days']);
            });
        }

        if (! Schema::hasTable('tuvi_synastry_reports')) {
            Schema::create('tuvi_synastry_reports', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('slug', 120)->unique();
                $table->foreignId('chart_a_id')->constrained('tuvi_charts')->cascadeOnDelete();
                $table->foreignId('chart_b_id')->constrained('tuvi_charts')->cascadeOnDelete();
                $table->boolean('partner_consent_confirmed')->default(false);
                $table->json('couple_answers_json')->nullable();
                $table->json('comparison_payload_json');
                $table->json('interpretation_json')->nullable();
                $table->boolean('ai_powered')->default(false);
                $table->string('ai_model', 120)->nullable();
                $table->string('privacy_mode', 24)->default('private');
                $table->string('share_token', 64)->nullable()->unique();
                $table->timestamp('share_revoked_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tuvi_synastry_reports');
        Schema::dropIfExists('tuvi_chart_journals');
        Schema::dropIfExists('tuvi_chart_followups');
        Schema::dropIfExists('tuvi_charts');
        Schema::dropIfExists('tuvi_profiles');
    }
};
