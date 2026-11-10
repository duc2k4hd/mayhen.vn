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
            $table->timestamp('opened_at')->nullable()->after('is_sent');
            $table->timestamp('last_opened_at')->nullable()->after('opened_at');
            $table->unsignedInteger('open_count')->default(0)->after('last_opened_at');
            $table->string('last_device', 100)->nullable()->after('open_count');
            $table->timestamp('sent_at')->nullable()->after('last_device');

            $table->index(['wedding_id', 'open_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropIndex(['wedding_id', 'open_count']);
            $table->dropColumn([
                'opened_at',
                'last_opened_at',
                'open_count',
                'last_device',
                'sent_at',
            ]);
        });
    }
};
