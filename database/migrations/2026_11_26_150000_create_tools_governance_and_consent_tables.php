<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_cookie_consents')) {
            Schema::create('user_cookie_consents', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('session_id', 100)->index();
                $table->string('ip_address', 45)->nullable();
                $table->string('ip_hash', 64)->nullable()->index();
                $table->string('user_agent', 300)->nullable();
                $table->string('policy_version', 30)->default('2026.1');
                $table->boolean('consent_essential')->default(true);
                $table->boolean('consent_analytics')->default(false);
                $table->boolean('consent_personalization')->default(false);
                $table->boolean('consent_marketing')->default(false);
                $table->string('source_url', 300)->nullable();
                $table->timestamp('accepted_at')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('user_activity_logs')) {
            Schema::create('user_activity_logs', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('session_id', 100)->nullable()->index();
                $table->string('tool_key', 40)->index();
                $table->string('action', 60)->index();
                $table->string('route_name', 120)->nullable();
                $table->string('method', 10)->default('GET');
                $table->string('url', 300)->nullable();
                $table->string('resource_type', 60)->nullable();
                $table->unsignedBigInteger('resource_id')->nullable();
                $table->string('resource_slug', 160)->nullable();
                $table->string('summary', 255)->nullable();
                $table->json('metadata_json')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 300)->nullable();
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }

        if (! Schema::hasTable('tool_retention_archives')) {
            Schema::create('tool_retention_archives', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->string('tool_key', 40)->index();
                $table->string('source_table', 80);
                $table->unsignedBigInteger('source_record_id');
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('user_email', 160)->nullable();
                $table->string('user_plan_tier', 30)->nullable();
                $table->string('resource_slug', 160)->nullable();
                $table->string('title', 220)->nullable();
                $table->timestamp('created_originally_at')->nullable();
                $table->timestamp('expired_at')->nullable();
                $table->timestamp('archived_at')->useCurrent()->index();
                $table->json('analytics_summary_json')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_retention_archives');
        Schema::dropIfExists('user_activity_logs');
        Schema::dropIfExists('user_cookie_consents');
    }
};
