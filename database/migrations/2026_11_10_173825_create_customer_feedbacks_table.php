<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * NXT-13: Customer Delight Feedback Loop (Đo lường & Vòng lặp phản hồi CSAT/NPS).
     */
    public function up(): void
    {
        Schema::create('customer_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('wedding_id')->nullable()->constrained('weddings')->nullOnDelete();
            $table->string('milestone', 50)->default('general'); // after_publish, after_payment, post_wedding, general
            $table->unsignedTinyInteger('rating')->default(5); // 1 - 5 stars
            $table->string('sentiment', 30)->default('satisfied'); // frustrated, neutral, satisfied, delighted
            $table->string('category', 50)->default('general'); // studio_editor, pricing, customer_service, guest_rsvp, template_design, general
            $table->text('comment')->nullable();
            $table->foreignId('linked_ticket_id')->nullable()->constrained('support_tickets')->nullOnDelete();
            $table->string('action_status', 30)->default('pending_review'); // pending_review, contacted, resolved, dismissed
            $table->text('action_plan_note')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['milestone', 'rating']);
            $table->index('action_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_feedbacks');
    }
};
