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
        Schema::create('security_ip_rules', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45); // Hỗ trợ IPv4 & IPv6
            $table->string('type', 20)->default('blacklist'); // blacklist, whitelist
            $table->string('reason', 255);
            $table->timestamp('blocked_until')->nullable(); // null = vĩnh viễn
            $table->unsignedInteger('hit_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['ip_address', 'type']);
            $table->index('blocked_until');
        });

        Schema::create('security_threat_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 50); // rate_limit_exceeded, failed_login_burst, bot_spam, sql_injection_attempt, suspicious_payload
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->string('endpoint', 255);
            $table->text('payload_summary')->nullable();
            $table->string('action_taken', 30)->default('throttled'); // logged, throttled, blocked_auto
            $table->timestamp('created_at')->useCurrent();

            $table->index(['event_type', 'created_at']);
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_threat_logs');
        Schema::dropIfExists('security_ip_rules');
    }
};
