<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tool_insight_snapshots')) {
            Schema::create('tool_insight_snapshots', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->string('tool_key', 40)->index();
                $table->string('intent_category', 60)->index();
                $table->string('region_or_topic', 80)->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('user_plan_tier', 20)->default('free')->index();
                $table->string('session_hash', 24)->nullable()->index();
                $table->json('user_inputs_json');
                $table->json('analysis_summary_json')->nullable();
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_insight_snapshots');
    }
};
