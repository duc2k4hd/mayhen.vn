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
        Schema::create('wedding_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->string('sender_name', 190);
            $table->string('sender_group', 50)->nullable();
            $table->unsignedBigInteger('amount')->default(0);
            $table->string('payment_method', 30)->default('cash'); // 'cash', 'bank_transfer', 'other'
            $table->text('notes')->nullable();
            $table->boolean('is_acknowledged')->default(false);
            $table->timestamps();

            $table->index(['wedding_id', 'created_at']);
            $table->index(['wedding_id', 'payment_method']);
            $table->index(['wedding_id', 'sender_group']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wedding_gifts');
    }
};
