<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_chat_test_scenarios')) {
            Schema::create('ai_chat_test_scenarios', function (Blueprint $table) {
                $table->id();
                $table->string('name', 160);
                $table->string('category', 40)->default('support')->index();
                $table->text('prompt');
                $table->string('expected_mode', 40)->default('any_answer');
                $table->string('expected_cards', 30)->default('any');
                $table->text('must_contain')->nullable();
                $table->text('must_not_contain')->nullable();
                $table->string('last_status', 20)->nullable()->index();
                $table->unsignedTinyInteger('last_failed_step')->nullable();
                $table->string('last_summary', 255)->nullable();
                $table->unsignedInteger('last_latency_ms')->nullable();
                $table->timestamp('last_run_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_test_scenarios');
    }
};
