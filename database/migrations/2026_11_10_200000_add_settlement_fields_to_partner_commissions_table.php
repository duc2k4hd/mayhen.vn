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
        Schema::table('partner_commissions', function (Blueprint $table) {
            $table->string('payout_reference_code', 100)->nullable()->after('description');
            $table->timestamp('reconciled_at')->nullable()->after('payout_reference_code');
            $table->foreignId('reconciled_by')->nullable()->after('reconciled_at')->constrained('users')->nullOnDelete();
            $table->text('reconciliation_note')->nullable()->after('reconciled_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_commissions', function (Blueprint $table) {
            $table->dropForeign(['reconciled_by']);
            $table->dropColumn([
                'payout_reference_code',
                'reconciled_at',
                'reconciled_by',
                'reconciliation_note',
            ]);
        });
    }
};
