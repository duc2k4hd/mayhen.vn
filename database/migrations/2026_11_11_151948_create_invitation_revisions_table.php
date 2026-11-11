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
        Schema::create('invitation_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->unsignedInteger('version_number')->comment('Số thứ tự phiên bản tăng dần: 1, 2, 3...');
            $table->enum('revision_type', ['autosave', 'manual_save', 'published', 'restored', 'template_switched'])
                ->default('manual_save')
                ->comment('Phân loại snapshot: autosave, lưu thủ công, xuất bản, phục hồi, đổi mẫu');
            $table->string('title', 150)->nullable()->comment('Tiêu đề hoặc ghi chú phiên bản');
            $table->longText('config_data')->comment('Snapshot JSON cấu hình 10 section của thiệp cưới');
            $table->json('diff_summary')->nullable()->comment('Tóm tắt các trường / section bị thay đổi so với bản trước');
            $table->string('checksum', 64)->index()->comment('SHA-256 hash của config_data để chống lưu trùng lặp snapshot');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('Người thực hiện lưu hoặc phục hồi');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes phục vụ tra cứu tốc độ cao
            $table->unique(['invitation_id', 'version_number'], 'idx_invitation_revision_version');
            $table->index(['invitation_id', 'revision_type', 'created_at'], 'idx_invitation_type_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitation_revisions');
    }
};
