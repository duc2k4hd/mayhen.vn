<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tác động: Bổ sung event_id (nullable) vào bảng wedding_tables để phân tách bàn tiệc
     * theo từng sự kiện ngày cưới riêng biệt (G2-004), bảo đảm 2 event khác địa điểm không dùng nhầm bàn.
     */
    public function up(): void
    {
        Schema::table('wedding_tables', function (Blueprint $table) {
            $table->foreignId('event_id')->nullable()->after('wedding_id')->constrained('wedding_events')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wedding_tables', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');
        });
    }
};
