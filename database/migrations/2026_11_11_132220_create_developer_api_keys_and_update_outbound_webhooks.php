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
        Schema::create('developer_api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('environment', 10)->default('live'); // live, test
            $table->string('key_prefix', 24); // vd: mh_live_abc123...
            $table->string('key_hash', 64)->unique(); // SHA-256 hash của full token
            $table->json('scopes'); // Danh sách quyền: ["templates:read", "orders:read", ...]
            $table->unsignedSmallInteger('rate_limit_per_minute')->default(60);
            $table->string('status', 20)->default('active'); // active, revoked
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_used_ip', 45)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'environment']);
        });

        Schema::table('outbound_webhooks', function (Blueprint $table) {
            $table->foreignId('wedding_id')->nullable()->change();
            $table->foreignId('created_by')->nullable()->after('last_status_code')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outbound_webhooks', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
            // Không revert wedding_id nullable để tránh mất dữ liệu platform webhooks
        });

        Schema::dropIfExists('developer_api_keys');
    }
};
