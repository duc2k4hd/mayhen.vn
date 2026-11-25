<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bổ sung checkout_token riêng cho mỗi phiên thanh toán và nhật ký lần kiểm tra SePay gần nhất.
     * Tác động: Chỉ thêm 4 cột nullable vào bảng orders, không ảnh hưởng dữ liệu đơn hàng cũ.
     */
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'checkout_token')) {
                $table->string('checkout_token', 64)->nullable()->unique()->after('order_code');
            }
            if (! Schema::hasColumn('orders', 'checkout_expires_at')) {
                $table->timestamp('checkout_expires_at')->nullable()->after('paid_at');
            }
            if (! Schema::hasColumn('orders', 'last_sepay_check_at')) {
                $table->timestamp('last_sepay_check_at')->nullable()->after('checkout_expires_at');
            }
            if (! Schema::hasColumn('orders', 'last_sepay_check_message')) {
                $table->string('last_sepay_check_message', 255)->nullable()->after('last_sepay_check_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $dropColumns = [];
            foreach (['checkout_token', 'checkout_expires_at', 'last_sepay_check_at', 'last_sepay_check_message'] as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $dropColumns[] = $col;
                }
            }
            if ($dropColumns !== []) {
                if (in_array('checkout_token', $dropColumns, true)) {
                    $table->dropUnique(['checkout_token']);
                }
                $table->dropColumn($dropColumns);
            }
        });
    }
};
