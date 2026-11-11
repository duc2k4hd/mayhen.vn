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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('invoice_number', 50)->unique()->nullable();
            $table->string('customer_name', 150);
            $table->string('customer_email', 150);
            $table->string('customer_phone', 30)->nullable();
            $table->string('company_name', 255)->nullable();
            $table->string('tax_id', 50)->nullable(); // Mã số thuế
            $table->text('company_address')->nullable();
            $table->unsignedInteger('subtotal_amount')->default(0); // Tiền trước thuế
            $table->unsignedTinyInteger('vat_rate')->default(0); // % thuế VAT (0, 8, 10)
            $table->unsignedInteger('vat_amount')->default(0); // Tiền thuế
            $table->unsignedInteger('total_amount')->default(0); // Tổng thanh toán
            $table->string('status', 30)->default('pending'); // 'pending', 'issued', 'cancelled'
            $table->string('payment_method', 50)->default('vietqr');
            $table->timestamp('issued_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'issued_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
