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
        Schema::create('order_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('creator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action_type', 40); // 'manual_approval', 'refund_request', 'refund_payout'
            $table->string('status', 30)->default('pending'); // 'pending', 'approved', 'rejected', 'completed'
            $table->unsignedInteger('amount');
            $table->string('reference_code', 100);
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_number', 64)->nullable();
            $table->string('bank_account_holder', 100)->nullable();
            $table->text('reconciliation_note')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamp('reconciled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['action_type', 'status']);
            $table->index('reference_code');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('reconciliation_status', 40)->default('none')->after('status');
            $table->timestamp('refunded_at')->nullable()->after('paid_at');
            $table->unsignedInteger('refund_amount')->nullable()->after('refunded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['reconciliation_status', 'refunded_at', 'refund_amount']);
        });

        Schema::dropIfExists('order_reconciliations');
    }
};
