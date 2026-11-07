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
            $table->string('table_name', 100)->nullable()->after('group_name')->index();
            $table->timestamp('checked_in_at')->nullable()->after('expected_attendees')->index();
            $table->unsignedInteger('checked_in_attendees')->nullable()->after('checked_in_at');
            $table->string('check_in_note', 255)->nullable()->after('checked_in_attendees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropIndex(['table_name']);
            $table->dropIndex(['checked_in_at']);
            $table->dropColumn(['table_name', 'checked_in_at', 'checked_in_attendees', 'check_in_note']);
        });
    }
};
