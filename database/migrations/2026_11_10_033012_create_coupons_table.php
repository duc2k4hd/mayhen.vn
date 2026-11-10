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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->enum('discount_type', ['percent', 'fixed'])->default('percent');
            $table->unsignedInteger('discount_value'); // % (1-100) hoặc số tiền VND
            $table->unsignedInteger('min_order_amount')->default(0); // Giá trị đơn tối thiểu để áp dụng
            $table->unsignedInteger('max_discount_amount')->nullable(); // Mức giảm tối đa cho loại percent (VND)
            $table->unsignedInteger('usage_limit')->nullable(); // Số lượt dùng tối đa toàn hệ thống (null = vô hạn)
            $table->unsignedInteger('used_count')->default(0); // Số lượt đã sử dụng thực tế
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
