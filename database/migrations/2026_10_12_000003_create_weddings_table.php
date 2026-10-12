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
        Schema::create('weddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('groom_name', 255);
            $table->string('bride_name', 255);
            $table->text('groom_parents')->nullable();
            $table->text('bride_parents')->nullable();
            $table->date('wedding_date');
            $table->string('venue_name', 255)->nullable();
            $table->text('venue_address')->nullable();
            $table->text('story')->nullable();
            $table->string('cover_photo_url', 500)->nullable();
            $table->string('music_url', 500)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_name', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weddings');
    }
};
