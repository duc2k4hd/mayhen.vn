<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tarot_readings')) {
            Schema::create('tarot_readings', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('guest_session_id', 100)->nullable()->index();
                $table->string('session_token', 64)->unique();
                $table->string('slug', 120)->unique();
                $table->string('status', 24)->default('SHUFFLED')->index();
                $table->string('deck_id', 60)->default('rws_heritage_v1');
                $table->string('deck_version', 20)->default('1.0.0');
                $table->string('spread_id', 60)->index();
                $table->string('spread_version', 20)->default('1.0.0');
                $table->string('randomization_version', 40)->default('fisher_yates_csprng_v1');
                $table->string('orientation_mode', 24)->default('upright_reversed');
                $table->string('selection_mode', 24)->default('interactive');
                $table->string('category', 40)->default('general')->index();
                $table->string('relationship_stage', 40)->nullable();
                $table->string('question', 400)->nullable();
                $table->boolean('is_question_redacted')->default(false);
                $table->string('context_note', 800)->nullable();
                $table->string('commitment_hash', 64)->nullable();
                $table->string('commitment_nonce', 64)->nullable();
                $table->longText('shuffled_deck_json')->nullable();
                $table->longText('selected_cards_json')->nullable();
                $table->longText('interpretation_json')->nullable();
                $table->boolean('ai_powered')->default(false);
                $table->string('ai_model', 80)->nullable();
                $table->string('ai_status', 24)->default('pending');
                $table->string('privacy_mode', 20)->default('private');
                $table->string('share_token', 64)->nullable()->unique();
                $table->boolean('share_hide_question')->default(true);
                $table->string('feedback_rating', 30)->nullable();
                $table->boolean('is_favorite')->default(false);
                $table->string('daily_key', 32)->nullable()->index();
                $table->string('idempotency_key', 80)->nullable()->index();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tarot_reading_followups')) {
            Schema::create('tarot_reading_followups', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('tarot_reading_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('question', 400);
                $table->text('answer');
                $table->boolean('ai_powered')->default(false);
                $table->string('ai_model', 80)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tarot_reading_journals')) {
            Schema::create('tarot_reading_journals', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('tarot_reading_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('feeling_note', 500)->nullable();
                $table->string('realization_note', 500)->nullable();
                $table->string('action_note', 500)->nullable();
                $table->string('outcome_note', 500)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tarot_reading_journals');
        Schema::dropIfExists('tarot_reading_followups');
        Schema::dropIfExists('tarot_readings');
    }
};
