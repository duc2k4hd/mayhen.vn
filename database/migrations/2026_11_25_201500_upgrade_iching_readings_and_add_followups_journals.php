<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('iching_readings', 'rule_version')) {
            Schema::table('iching_readings', function (Blueprint $table) {
                $table->string('rule_version', 80)->nullable()->after('method');
                $table->string('kb_version', 32)->nullable()->after('rule_version');
                $table->string('sub_topic', 120)->nullable()->after('topic');
                $table->text('context_note')->nullable()->after('question');
                $table->boolean('is_question_redacted')->default(false)->after('context_note');
                $table->boolean('is_favorite')->default(false)->after('is_question_redacted');
                $table->string('privacy_mode', 24)->default('private')->after('is_favorite');
                $table->string('share_token', 64)->nullable()->unique()->after('privacy_mode');
                $table->timestamp('share_revoked_at')->nullable()->after('share_token');
                $table->string('idempotency_key', 80)->nullable()->unique()->after('share_revoked_at');
                $table->json('cast_inputs_json')->nullable()->after('lines_json');
                $table->json('selected_texts_json')->nullable()->after('ti_yong_analysis_json');
                $table->string('guidance_state', 40)->nullable()->after('selected_texts_json');
                $table->string('ai_status', 24)->default('completed')->after('ai_model');
                $table->string('feedback_rating', 32)->nullable()->after('ai_status');
                $table->json('action_checklist_json')->nullable()->after('feedback_rating');
            });
        }

        if (! Schema::hasTable('iching_reading_followups')) {
            Schema::create('iching_reading_followups', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('reading_id')->constrained('iching_readings')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->text('question');
                $table->text('answer');
                $table->boolean('ai_powered')->default(false);
                $table->string('ai_model', 120)->nullable();
                $table->timestamps();

                $table->index(['reading_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('iching_reading_journals')) {
            Schema::create('iching_reading_journals', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('reading_id')->constrained('iching_readings')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedSmallInteger('milestone_days')->default(7);
                $table->text('actions_taken')->nullable();
                $table->text('progress_note')->nullable();
                $table->text('mindset_shift')->nullable();
                $table->text('lessons_learned')->nullable();
                $table->timestamps();

                $table->index(['reading_id', 'milestone_days']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('iching_reading_journals');
        Schema::dropIfExists('iching_reading_followups');

        Schema::table('iching_readings', function (Blueprint $table) {
            $table->dropUnique(['share_token']);
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn([
                'rule_version',
                'kb_version',
                'sub_topic',
                'context_note',
                'is_question_redacted',
                'is_favorite',
                'privacy_mode',
                'share_token',
                'share_revoked_at',
                'idempotency_key',
                'cast_inputs_json',
                'selected_texts_json',
                'guidance_state',
                'ai_status',
                'feedback_rating',
                'action_checklist_json',
            ]);
        });
    }
};
