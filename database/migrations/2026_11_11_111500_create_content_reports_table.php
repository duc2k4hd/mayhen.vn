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
        Schema::create('content_reports', function (Blueprint $table) {
            $table->id();
            $table->string('reportable_type');
            $table->unsignedBigInteger('reportable_id');
            $table->foreignId('wedding_id')->constrained('weddings')->cascadeOnDelete();
            $table->string('reporter_name', 100)->nullable();
            $table->string('reporter_phone', 20)->nullable();
            $table->string('reporter_ip', 45)->nullable();
            $table->string('reason', 50)->default('inappropriate');
            $table->text('details')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->string('action_taken', 30)->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index(['reportable_type', 'reportable_id']);
            $table->index('wedding_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_reports');
    }
};
