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
        Schema::create('outbound_webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained('weddings')->cascadeOnDelete();
            $table->string('name', 255); // vd: Telegram Bot, Google Sheets, Zapier, Studio CRM
            $table->text('payload_url');
            $table->string('secret', 100); // Khóa ký HMAC-SHA256
            $table->json('events'); // mảng json: ["rsvp.submitted", "wish.created", "gift.received"]
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('failure_count')->default(0);
            $table->timestamp('last_dispatched_at')->nullable();
            $table->unsignedSmallInteger('last_status_code')->nullable();
            $table->timestamps();

            $table->index(['wedding_id', 'is_active']);
        });

        Schema::create('outbound_webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outbound_webhook_id')->constrained('outbound_webhooks')->cascadeOnDelete();
            $table->string('event_type', 50); // rsvp.submitted, wish.created, gift.received, ping
            $table->json('payload');
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('is_success')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['outbound_webhook_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbound_webhook_deliveries');
        Schema::dropIfExists('outbound_webhooks');
    }
};
