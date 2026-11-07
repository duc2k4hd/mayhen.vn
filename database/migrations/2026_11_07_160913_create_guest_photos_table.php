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
        Schema::create('guest_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('uploader_name', 150);
            $table->string('photo_url', 500);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('caption', 255)->nullable();
            $table->boolean('is_approved')->default(true);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['wedding_id', 'is_approved']);
            $table->index(['wedding_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_photos');
    }
};
