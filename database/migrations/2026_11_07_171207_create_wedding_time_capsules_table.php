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
        Schema::create('wedding_time_capsules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained('weddings')->cascadeOnDelete();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('sender_type', 30)->default('couple'); // 'groom', 'bride', 'couple', 'guest'
            $table->string('sender_name', 100);
            $table->string('recipient_name', 100);
            $table->string('title', 191);
            $table->text('content');
            $table->unsignedSmallInteger('milestone_years')->default(1); // 1, 3, 5, 10, 25
            $table->date('unlock_date');
            $table->boolean('is_sealed')->default(true);
            $table->string('seal_color', 20)->default('ruby'); // 'ruby', 'gold', 'emerald', 'sapphire'
            $table->string('sender_email', 191)->nullable();
            $table->json('media_urls')->nullable();
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamps();

            $table->index(['wedding_id', 'unlock_date']);
            $table->index(['invitation_id', 'is_sealed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wedding_time_capsules');
    }
};
