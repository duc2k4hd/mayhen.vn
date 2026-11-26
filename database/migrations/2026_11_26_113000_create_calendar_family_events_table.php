<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('calendar_family_events')) {
            Schema::create('calendar_family_events', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('title', 160);
                $table->string('event_type', 40)->default('family'); // birthday, anniversary, death_anniversary, wedding, family
                $table->string('calendar_type', 16)->default('lunar'); // lunar | solar
                $table->unsignedTinyInteger('event_day');
                $table->unsignedTinyInteger('event_month');
                $table->unsignedSmallInteger('event_year')->nullable();
                $table->boolean('is_leap_month')->default(false);
                $table->boolean('is_recurring')->default(true);
                $table->string('short_month_rule', 32)->default('last_day_of_month'); // last_day_of_month | skip
                $table->string('leap_month_rule', 32)->default('regular_only'); // regular_only | leap_if_exists | both
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'calendar_type', 'event_month']);
            });
        }

        if (! Schema::hasTable('calendar_ai_daily_caches')) {
            Schema::create('calendar_ai_daily_caches', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->date('solar_date');
                $table->string('rule_version', 64);
                $table->json('payload_json');
                $table->string('ai_model', 80)->nullable();
                $table->timestamps();

                $table->unique(['solar_date', 'rule_version']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_ai_daily_caches');
        Schema::dropIfExists('calendar_family_events');
    }
};
