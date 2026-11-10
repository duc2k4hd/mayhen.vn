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
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code', 20)->unique()->comment('Mã ticket duy nhất, dạng TK-XXXXXXXX');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('Người dùng gửi ticket');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete()->comment('Staff được giao xử lý');
            $table->string('subject', 200)->comment('Tiêu đề ticket');
            $table->enum('category', ['technical', 'billing', 'invitation', 'general'])->default('general')->comment('Phân loại');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->comment('Mức ưu tiên');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open')->comment('Trạng thái');
            $table->text('body')->comment('Nội dung ticket đầu tiên');
            $table->timestamp('first_responded_at')->nullable()->comment('Thời điểm phản hồi đầu tiên từ staff');
            $table->timestamp('resolved_at')->nullable()->comment('Thời điểm đánh dấu resolved');
            $table->timestamp('closed_at')->nullable()->comment('Thời điểm đóng ticket');
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index(['user_id', 'status']);
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
