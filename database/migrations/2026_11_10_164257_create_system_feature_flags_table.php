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
        Schema::create('system_feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique()->comment('Mã định danh cờ tính năng');
            $table->string('name', 100)->comment('Tên hiển thị');
            $table->string('description', 255)->nullable()->comment('Mô tả tính năng và tác động');
            $table->enum('category', ['core', 'ai', 'commerce', 'collaboration', 'safety'])->default('core');
            $table->boolean('is_enabled')->default(true)->comment('Trạng thái bật/tắt toàn cục');
            $table->unsignedTinyInteger('rollout_percentage')->default(100)->comment('Tỷ lệ phát hành theo phần trăm 0-100%');
            $table->json('target_roles')->nullable()->comment('Danh sách vai trò được áp dụng');
            $table->boolean('kill_switch_active')->default(false)->comment('Khóa ngắt khẩn cấp');
            $table->string('kill_switch_reason', 255)->nullable()->comment('Lý do ngắt khẩn cấp');
            $table->foreignId('last_toggled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_toggled_at')->nullable();
            $table->timestamps();

            $table->index(['key', 'is_enabled']);
            $table->index('kill_switch_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_feature_flags');
    }
};
