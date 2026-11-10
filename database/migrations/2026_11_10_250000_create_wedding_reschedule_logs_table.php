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
        Schema::create('wedding_reschedule_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained('weddings')->onDelete('cascade');
            $table->foreignId('invitation_id')->nullable()->constrained('invitations')->onDelete('set null');
            $table->date('old_date')->nullable();
            $table->date('new_date');
            $table->string('old_venue')->nullable();
            $table->string('new_venue')->nullable();
            $table->text('reason')->nullable();
            $table->string('rsvp_action', 30)->default('keep_existing'); // keep_existing, request_reconfirm
            $table->unsignedInteger('affected_guests_count')->default(0);
            $table->unsignedInteger('notified_guests_count')->default(0);
            $table->timestamps();

            $table->index(['wedding_id', 'created_at']);
        });

        Schema::table('guest_responses', function (Blueprint $table) {
            $table->boolean('needs_reconfirmation')->default(false)->after('attending_status');
            $table->timestamp('reconfirmed_at')->nullable()->after('needs_reconfirmation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guest_responses', function (Blueprint $table) {
            $table->dropColumn(['needs_reconfirmation', 'reconfirmed_at']);
        });

        Schema::dropIfExists('wedding_reschedule_logs');
    }
};
