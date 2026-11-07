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
        Schema::create('wedding_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained('weddings')->cascadeOnDelete();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('subdomain', 63)->nullable()->unique();
            $table->string('custom_domain', 191)->nullable()->unique();
            $table->string('cname_target', 100)->default('cname.mayhen.vn');
            $table->string('status', 30)->default('pending'); // 'pending', 'active', 'failed'
            $table->string('ssl_status', 30)->default('pending'); // 'pending', 'active', 'failed'
            $table->timestamp('dns_verified_at')->nullable();
            $table->json('dns_records')->nullable();
            $table->timestamps();

            $table->index(['wedding_id', 'status']);
            $table->index(['invitation_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wedding_domains');
    }
};
