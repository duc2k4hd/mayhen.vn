<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bổ sung cột photo_url để lưu trữ ảnh kỷ niệm do quan khách đính kèm khi gửi lời chúc (IDEA-D04).
     */
    public function up(): void
    {
        Schema::table('wedding_wishes', function (Blueprint $table) {
            $table->string('photo_url', 500)->nullable()->after('signature_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wedding_wishes', function (Blueprint $table) {
            $table->dropColumn('photo_url');
        });
    }
};

