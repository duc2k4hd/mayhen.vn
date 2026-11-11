<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paper_invitation_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invitation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('wedding_id')->nullable()->constrained()->nullOnDelete();
            $table->string('image_path', 500);
            $table->longText('raw_extracted_text')->nullable();
            $table->json('parsed_data');
            $table->json('verified_data')->nullable();
            $table->decimal('confidence_score', 5, 2)->default(0.00);
            $table->string('status', 50)->default('parsed'); // uploaded, parsed, verified, applied, rejected
            $table->string('ai_model', 50)->default('heuristic_vision_parser');
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('invitation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paper_invitation_scans');
    }
};
