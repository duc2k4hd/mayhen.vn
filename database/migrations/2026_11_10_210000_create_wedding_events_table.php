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
        Schema::create('wedding_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained('weddings')->cascadeOnDelete();
            $table->string('name', 255); // vd: Lễ Vu Quy, Lễ Thành Hôn, Tiệc Cưới Nhà Trai...
            $table->string('event_type', 50)->default('banquet'); // ceremony_bride, ceremony_groom, engagement, banquet, after_party, other
            $table->date('event_date');
            $table->string('event_time', 20)->nullable(); // vd: "09:30", "11:00", "18:00"
            $table->string('lunar_date', 255)->nullable(); // vd: "Ngày 16 tháng 10 năm Giáp Thìn"
            $table->string('venue_name', 255); // vd: "Tư gia Nhà Gái", "White Palace Hoàng Văn Thụ"
            $table->text('venue_address')->nullable();
            $table->text('map_url')->nullable();
            $table->string('dress_code', 255)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['wedding_id', 'display_order']);
            $table->index(['wedding_id', 'event_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wedding_events');
    }
};
