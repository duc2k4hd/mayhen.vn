<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bazi_charts')) {
            Schema::create('bazi_charts', function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('slug', 40)->unique();
                $table->string('profile_name', 120)->nullable();
                $table->string('gender', 16)->nullable();
                $table->string('method_id', 60)->default('tu_binh_default_v1');
                $table->string('engine_version', 40);
                $table->string('rule_version', 40);
                $table->string('calendar_version', 40);
                $table->string('calculation_hash', 64)->index();
                $table->string('day_master_label', 40)->nullable();
                $table->string('four_pillars_summary', 80)->nullable();
                $table->json('input_json');
                $table->json('chart_json');
                $table->json('ai_report_json')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_shared')->default(false);
                $table->string('share_token', 48)->nullable()->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('bazi_followups')) {
            Schema::create('bazi_followups', function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('bazi_chart_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->text('question');
                $table->text('answer');
                $table->boolean('ai_powered')->default(false);
                $table->string('ai_model', 80)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('bazi_relationship_reports')) {
            Schema::create('bazi_relationship_reports', function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('slug', 40)->unique();
                $table->string('person_a_name', 120)->nullable();
                $table->string('person_b_name', 120)->nullable();
                $table->json('report_json');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bazi_relationship_reports');
        Schema::dropIfExists('bazi_followups');
        Schema::dropIfExists('bazi_charts');
    }
};
