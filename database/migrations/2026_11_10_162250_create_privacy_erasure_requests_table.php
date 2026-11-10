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
        Schema::create('privacy_erasure_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code', 30)->unique()->comment('Mã định danh yêu cầu DSAR Erasure');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('Chủ tài khoản gửi yêu cầu');
            $table->foreignId('wedding_id')->nullable()->constrained()->nullOnDelete()->comment('Đám cưới cụ thể nếu áp dụng');
            $table->enum('requested_by_type', ['customer', 'admin', 'guest'])->default('customer');
            $table->string('requester_email', 150);
            $table->string('requester_name', 150);
            $table->text('reason')->comment('Lý do yêu cầu quên / xóa dữ liệu cá nhân theo Luật 91/2025/QH15');
            $table->enum('status', ['pending', 'approved', 'rejected', 'executed'])->default('pending');
            $table->enum('retention_scope', ['guest_pii_only', 'full_wedding_anonymize', 'account_anonymize'])->default('account_anonymize');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->comment('Quản lý duyệt');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->json('affected_records_count')->nullable()->comment('Thống kê số lượng bản ghi bị ẩn danh');
            $table->boolean('accounting_records_preserved')->default(true)->comment('Bảo toàn chứng từ kế toán theo luật');
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('privacy_erasure_requests');
    }
};
