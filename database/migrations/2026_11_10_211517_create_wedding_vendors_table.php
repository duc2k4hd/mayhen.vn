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
        Schema::create('wedding_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained('weddings')->cascadeOnDelete();
            $table->string('name');
            $table->string('category', 64)->default('other');
            $table->string('contact_person')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('contract_status', 32)->default('contacting'); // contacting, contract_signed, in_progress, completed, cancelled
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->unsignedBigInteger('deposit_paid')->default(0);
            $table->date('payment_due_date')->nullable();
            $table->string('service_timeline', 255)->nullable();
            $table->text('notes')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index(['wedding_id', 'category']);
            $table->index(['wedding_id', 'contract_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wedding_vendors');
    }
};
