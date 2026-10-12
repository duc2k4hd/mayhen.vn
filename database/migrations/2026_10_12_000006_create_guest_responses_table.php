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
        Schema::create('guest_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name', 255);
            $table->string('attending_status', 30)->default('attending'); // 'attending', 'declined', 'uncertain'
            $table->unsignedInteger('adults_count')->default(1);
            $table->unsignedInteger('children_count')->default(0);
            $table->text('dietary_requirements')->nullable();
            $table->text('wishes')->nullable();
            $table->boolean('is_wishes_public')->default(true);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_responses');
    }
};
