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
        Schema::create('notification_gateway_test_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 30); // zalo_zns, sms_brandname, email_smtp
            $table->string('provider', 50); // zalo, esms, speedsms, fptsms, smtp
            $table->string('recipient', 100); // Đã che PII
            $table->string('template_code', 50)->nullable(); // wedding_invite, rsvp_confirm, test_ping
            $table->string('status', 20)->default('success'); // success, failed, simulated
            $table->unsignedInteger('latency_ms')->default(0);
            $table->string('response_code', 50)->nullable();
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['channel', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_gateway_test_logs');
    }
};
