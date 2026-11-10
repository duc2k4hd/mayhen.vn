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
        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('Người gửi reply (user hoặc staff)');
            $table->text('body')->comment('Nội dung phản hồi');
            $table->boolean('is_staff_reply')->default(false)->comment('true nếu là staff/admin gửi');
            $table->boolean('is_internal_note')->default(false)->comment('Ghi chú nội bộ, chỉ staff thấy');
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_ticket_replies');
    }
};
