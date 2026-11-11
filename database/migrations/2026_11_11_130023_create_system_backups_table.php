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
        Schema::create('system_backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename', 255);
            $table->string('disk', 50)->default('local');
            $table->string('file_path', 255);
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->string('type', 30)->default('database'); // database, media, full
            $table->string('status', 30)->default('completed'); // completed, failed, testing_restore
            $table->string('sha256_checksum', 64)->nullable();
            $table->unsignedInteger('tables_count')->nullable();
            $table->unsignedInteger('records_count')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_log')->nullable();
            $table->string('note', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_backups');
    }
};
