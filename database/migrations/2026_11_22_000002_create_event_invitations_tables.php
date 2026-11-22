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
        Schema::create('event_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('template_slug', 100)->index();
            $table->string('category_slug', 80)->index();
            $table->string('slug', 160)->unique();
            $table->string('title', 255);
            $table->string('event_badge', 120)->nullable();
            $table->string('host_name', 150);
            $table->string('sub_host_name', 150)->nullable();
            $table->text('invitation_message')->nullable();
            $table->date('event_date')->nullable();
            $table->string('welcome_time', 20)->nullable();
            $table->string('party_time', 20)->nullable();
            $table->string('lunar_note', 180)->nullable();
            $table->string('venue_name', 180)->nullable();
            $table->string('venue_address', 255)->nullable();
            $table->string('map_url', 500)->nullable();
            $table->string('dresscode_note', 180)->nullable();
            $table->string('color_palette', 50)->default('ruby');
            $table->boolean('enable_rsvp')->default(true);
            $table->boolean('enable_gift_qr')->default(false);
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_number', 60)->nullable();
            $table->string('bank_account_holder', 120)->nullable();
            $table->string('gift_note', 255)->nullable();
            $table->json('custom_data')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamps();
        });

        Schema::create('event_invitation_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_invitation_id')->constrained('event_invitations')->cascadeOnDelete();
            $table->string('guest_name', 120);
            $table->string('guest_phone', 40)->nullable();
            $table->string('attendance_status', 30)->default('attending')->index();
            $table->unsignedSmallInteger('guests_count')->default(1);
            $table->unsignedSmallInteger('children_count')->default(0);
            $table->text('wish_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_invitation_responses');
        Schema::dropIfExists('event_invitations');
    }
};
