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
        Schema::dropIfExists('lifecycle_automation_logs');
        Schema::dropIfExists('lifecycle_automation_rules');

        Schema::create('lifecycle_automation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('trigger_event', 50)->default('wedding_date_offset'); // wedding_date_offset
            $table->integer('offset_days'); // -30, -14, -7, -3, -1, 1, 30
            $table->string('channel', 30)->default('email'); // email, zalo_zns, sms_brandname, in_app
            $table->string('template_code', 50)->nullable();
            $table->string('send_time', 10)->default('09:00'); // HH:MM
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('execution_count')->default(0);
            $table->timestamp('last_executed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['trigger_event', 'offset_days', 'is_active'], 'lar_trigger_offset_active_idx');
        });

        Schema::create('lifecycle_automation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('lifecycle_automation_rules')->cascadeOnDelete();
            $table->foreignId('wedding_id')->constrained('weddings')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('channel', 30);
            $table->string('recipient', 150); // Đã che PII
            $table->string('status', 30)->default('dispatched'); // dispatched, simulated, skipped_duplicate, failed
            $table->text('message_summary')->nullable();
            $table->timestamp('dispatched_at')->useCurrent();
            $table->text('error_message')->nullable();

            $table->index(['rule_id', 'wedding_id']);
            $table->index(['status', 'dispatched_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lifecycle_automation_logs');
        Schema::dropIfExists('lifecycle_automation_rules');
    }
};
