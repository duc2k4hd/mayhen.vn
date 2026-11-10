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
        Schema::create('guest_response_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_response_id')->constrained('guest_responses')->cascadeOnDelete();
            $table->foreignId('wedding_id')->constrained('weddings')->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->string('previous_status', 32);
            $table->string('new_status', 32);
            $table->unsignedInteger('previous_adults')->default(1);
            $table->unsignedInteger('new_adults')->default(1);
            $table->unsignedInteger('previous_children')->default(0);
            $table->unsignedInteger('new_children')->default(0);
            $table->string('previous_dietary')->nullable();
            $table->string('new_dietary')->nullable();
            $table->text('previous_wishes')->nullable();
            $table->text('new_wishes')->nullable();
            $table->string('source', 32)->default('guest_online'); // guest_online, manual_host
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['guest_response_id', 'created_at']);
            $table->index(['wedding_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_response_logs');
    }
};
