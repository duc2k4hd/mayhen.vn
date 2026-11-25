<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iching_readings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug', 120)->unique();
            $table->string('method', 40)->default('coins_3');
            $table->string('topic', 40)->default('tong_quan');
            $table->string('question', 400);
            $table->json('lines_json');
            $table->unsignedTinyInteger('primary_hexagram_no');
            $table->unsignedTinyInteger('mutual_hexagram_no');
            $table->unsignedTinyInteger('changed_hexagram_no')->nullable();
            $table->json('moving_lines_json');
            $table->json('ti_yong_analysis_json');
            $table->longText('interpretation_json');
            $table->boolean('ai_powered')->default(false);
            $table->string('ai_model', 100)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('primary_hexagram_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iching_readings');
    }
};
