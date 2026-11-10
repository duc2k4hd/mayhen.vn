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
        Schema::create('invitation_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->foreignId('wedding_id')->constrained('weddings')->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->string('visitor_hash', 64)->comment('SHA-256 an toàn ẩn danh bảo vệ PII theo luật 91/2025/QH15');
            $table->string('device_type', 20)->default('mobile')->comment('mobile, tablet, desktop');
            $table->string('platform', 30)->default('other')->comment('ios, android, windows, macos, linux, other');
            $table->string('browser', 30)->default('other')->comment('zalo, facebook, safari, chrome, edge, firefox, other');
            $table->string('referer_host', 100)->nullable();
            $table->timestamp('viewed_at')->useCurrent();

            $table->index(['invitation_id', 'viewed_at']);
            $table->index(['wedding_id', 'viewed_at']);
            $table->index(['invitation_id', 'device_type']);
            $table->index(['invitation_id', 'visitor_hash', 'viewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitation_views');
    }
};
