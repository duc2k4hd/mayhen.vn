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
        Schema::create('payment_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('gateway', 50)->default('sepay'); // sepay, vietqr, bank_transfer
            $table->string('transaction_id', 100)->nullable();
            $table->string('order_code', 50)->nullable();
            $table->unsignedBigInteger('amount')->default(0);
            $table->string('status', 30)->default('success'); // success, mismatch, unverified, duplicate, pending_review
            $table->string('error_reason', 255)->nullable();
            $table->json('raw_payload')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['gateway', 'status']);
            $table->index('transaction_id');
            $table->index('order_code');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_webhook_logs');
    }
};
