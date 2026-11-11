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
        Schema::create('wedding_keepsakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('invitation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false); // Bật/tắt chế độ thiệp kỷ niệm hậu sự kiện
            $table->string('thank_you_title', 255)->default('Lời Cảm Ơn Chân Thành Từ Chúng Mình');
            $table->text('thank_you_message')->nullable(); // Tâm thư cảm ơn quan khách
            $table->string('video_url', 500)->nullable(); // Video highlight hôn lễ (YouTube/Vimeo)
            $table->json('highlight_photos')->nullable(); // Danh sách ảnh chọn lọc sau cưới
            $table->boolean('show_wishes')->default(true); // Trưng bày sổ lời chúc kỷ niệm
            $table->boolean('show_photos')->default(true); // Trưng bày album ảnh kỷ niệm
            $table->boolean('show_video')->default(true); // Trưng bày video highlight
            $table->boolean('hide_rsvp')->default(true); // Tự động ẩn form RSVP khi chuyển sang kỷ niệm
            $table->boolean('hide_gifts')->default(false); // Tự động ẩn thông tin quà mừng
            $table->date('anniversary_date')->nullable(); // Ngày kỷ niệm cưới
            $table->timestamp('last_exported_at')->nullable(); // Thời điểm xuất sổ lưu niệm gần nhất
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wedding_keepsakes');
    }
};
