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
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('business_name', 255);
            $table->string('slug', 191)->unique();
            $table->string('partner_code', 50)->unique();
            $table->string('phone', 20);
            $table->string('email', 255);
            $table->string('address', 500)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('tier', 30)->default('silver'); // silver, gold, platinum
            $table->decimal('commission_rate', 5, 2)->default(20.00); // 20% mặc định
            $table->decimal('wallet_balance', 12, 2)->default(0.00);
            $table->string('status', 30)->default('active'); // active, pending, suspended
            $table->json('custom_config')->nullable(); // co-branding footer text, brand colors...
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
