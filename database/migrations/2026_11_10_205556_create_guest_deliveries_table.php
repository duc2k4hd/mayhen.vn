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
        Schema::create('guest_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained('weddings')->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained('guests')->cascadeOnDelete();
            $table->string('channel', 32)->default('copy'); // copy, zalo, sms, email
            $table->string('status', 32)->default('sent'); // queued, sent, delivered, failed
            $table->string('recipient', 190)->nullable();
            $table->text('message_body')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('error_message', 500)->nullable();
            $table->string('batch_id', 64)->nullable();
            $table->timestamps();

            $table->index(['wedding_id', 'channel']);
            $table->index(['guest_id', 'status']);
            $table->index('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_deliveries');
    }
};
