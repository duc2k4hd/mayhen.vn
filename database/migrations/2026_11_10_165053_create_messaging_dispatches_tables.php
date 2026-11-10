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
        Schema::create('messaging_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->string('campaign_name');
            $table->string('channel')->default('invitation_link'); // zalo_zns, sms_brandname, invitation_link
            $table->string('status')->default('draft'); // draft, dry_run_ready, processing, completed, failed, cancelled
            $table->string('filter_group')->nullable(); // Lọc theo nhóm khách (null = toàn bộ khách)
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('deduplicated_count')->default(0); // Số lượng SĐT bị loại bỏ chống spam trùng lặp
            $table->unsignedInteger('successful_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->decimal('unit_cost_vnd', 10, 2)->default(0); // Đơn giá mỗi tin
            $table->decimal('total_cost_vnd', 12, 2)->default(0); // Tổng chi phí thực tế
            $table->text('message_template'); // Mẫu tin nhắn cá nhân hóa
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('messaging_dispatch_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_id')->constrained('messaging_dispatches')->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->string('phone', 25);
            $table->string('guest_name');
            $table->string('guest_group')->nullable();
            $table->text('rendered_message'); // Nội dung tin nhắn đã cá nhân hóa
            $table->string('status')->default('dry_run'); // dry_run, sent, delivered, failed, skipped_duplicate
            $table->string('error_message')->nullable();
            $table->string('provider_ref')->nullable(); // Mã đối soát tin nhắn phía nhà mạng / Zalo ZNS
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['dispatch_id', 'status']);
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messaging_dispatch_logs');
        Schema::dropIfExists('messaging_dispatches');
    }
};
