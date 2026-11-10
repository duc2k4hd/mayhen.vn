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
        Schema::table('guests', function (Blueprint $table) {
            $table->json('revoked_tokens')->nullable()->after('guest_token');
            $table->unsignedSmallInteger('max_attendees')->nullable()->after('expected_attendees');
            $table->json('event_ids')->nullable()->after('max_attendees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn(['revoked_tokens', 'max_attendees', 'event_ids']);
        });
    }
};
