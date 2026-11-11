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
        Schema::create('seo_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('source_path')->unique()->index(); // Ví dụ: /thiep-cuoi-cu hoặc thiep-cuoi-cu
            $table->string('target_url'); // Ví dụ: /mau-thiep hoặc https://mayhen.vn/mau-thiep
            $table->unsignedSmallInteger('status_code')->default(301); // 301 (Permanent) hoặc 302 (Temporary)
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->string('note', 500)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_redirects');
    }
};
