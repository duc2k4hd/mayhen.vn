<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('numerology_profiles')) {
            Schema::create('numerology_profiles', function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('slug', 120)->unique();
                $table->string('display_name', 100);
                $table->string('full_birth_name', 160)->nullable();
                $table->string('everyday_name', 100)->nullable();
                $table->json('name_groups_json')->nullable();
                $table->string('y_mode', 32)->default('vn_phonetic_y_v1');
                $table->json('y_overrides_json')->nullable();
                $table->date('birth_date');
                $table->string('calendar_input_type', 16)->default('solar');
                $table->string('relationship_status', 40)->nullable();
                $table->string('method_id', 48)->default('pythagorean_decoz_v1');
                $table->string('method_version', 32)->default('1.0.0');
                $table->boolean('is_primary_self')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['user_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('numerology_readings')) {
            Schema::create('numerology_readings', function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('profile_id')->constrained('numerology_profiles')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('slug', 120)->unique();
                $table->unsignedInteger('version_number')->default(1);
                $table->string('input_hash', 64)->index();
                $table->string('method_id', 48)->default('pythagorean_decoz_v1');
                $table->string('method_version', 32)->default('1.0.0');
                $table->string('kb_version', 32)->default('1.0.0');
                $table->unsignedSmallInteger('viewed_year');
                $table->string('focus_topic', 40)->default('toan_bo');
                $table->text('user_context_note')->nullable();
                $table->json('calculation_payload_json');
                $table->json('interpretation_json');
                $table->boolean('ai_powered')->default(false);
                $table->string('ai_model', 120)->nullable();
                $table->string('ai_status', 32)->default('fallback');
                $table->string('privacy_mode', 24)->default('private');
                $table->string('share_token', 64)->nullable()->unique();
                $table->timestamp('share_revoked_at')->nullable();
                $table->boolean('share_hide_birth_date')->default(true);
                $table->string('feedback_rating', 32)->nullable();
                $table->boolean('is_favorite')->default(false);
                $table->string('idempotency_key', 96)->nullable()->unique();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
                $table->index(['profile_id', 'version_number']);
            });
        }

        if (! Schema::hasTable('numerology_followups')) {
            Schema::create('numerology_followups', function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('reading_id')->constrained('numerology_readings')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('topic_code', 48)->nullable();
                $table->text('question');
                $table->text('answer');
                $table->boolean('ai_powered')->default(false);
                $table->string('ai_model', 120)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('numerology_journals')) {
            Schema::create('numerology_journals', function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('reading_id')->constrained('numerology_readings')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedSmallInteger('milestone_days')->default(30);
                $table->text('current_goal')->nullable();
                $table->text('actions_taken')->nullable();
                $table->text('lessons_learned')->nullable();
                $table->text('relationship_note')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('numerology_relationship_reports')) {
            Schema::create('numerology_relationship_reports', function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('slug', 120)->unique();
                $table->foreignId('reading_a_id')->constrained('numerology_readings')->cascadeOnDelete();
                $table->foreignId('reading_b_id')->constrained('numerology_readings')->cascadeOnDelete();
                $table->string('relationship_stage', 40)->default('dang_yeu');
                $table->boolean('partner_consent_confirmed')->default(false);
                $table->json('couple_answers_json')->nullable();
                $table->json('promise_30_days_json')->nullable();
                $table->json('couple_messages_json')->nullable();
                $table->json('comparison_payload_json');
                $table->json('interpretation_json');
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
        Schema::dropIfExists('numerology_relationship_reports');
        Schema::dropIfExists('numerology_journals');
        Schema::dropIfExists('numerology_followups');
        Schema::dropIfExists('numerology_readings');
        Schema::dropIfExists('numerology_profiles');
    }
};
