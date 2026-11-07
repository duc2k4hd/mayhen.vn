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
        Schema::table('weddings', function (Blueprint $table) {
            $table->foreignId('partner_id')->nullable()->after('user_id')->constrained('partners')->nullOnDelete();
            $table->string('handover_code', 32)->nullable()->after('partner_id'); // Mã bàn giao cho cô dâu chú rể
            $table->text('client_notes')->nullable()->after('bank_account_name'); // Ghi chú nội bộ của studio
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            $table->dropForeign(['partner_id']);
            $table->dropColumn(['partner_id', 'handover_code', 'client_notes']);
        });
    }
};
