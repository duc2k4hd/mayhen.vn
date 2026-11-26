<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('couple_compatibility_reports')) {
            Schema::create('couple_compatibility_reports', function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('slug', 40)->unique();
                $table->string('title', 160);
                $table->string('husband_can_chi', 40);
                $table->string('wife_can_chi', 40);
                $table->string('cao_ly_id', 32)->index();
                $table->string('du_nien_code', 32)->index();
                $table->string('engine_version', 40);
                $table->string('calculation_hash', 64)->index();
                $table->json('report_json');
                $table->json('ai_report_json')->nullable();
                $table->boolean('is_shared')->default(false);
                $table->string('share_token', 48)->nullable()->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('couple_compatibility_followups')) {
            Schema::create('couple_compatibility_followups', function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('report_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->text('question');
                $table->text('answer');
                $table->boolean('ai_powered')->default(false);
                $table->string('ai_model', 80)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('couple_compatibility_followups');
        Schema::dropIfExists('couple_compatibility_reports');
    }
};
