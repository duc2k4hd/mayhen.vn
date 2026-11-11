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
        Schema::table('guest_responses', function (Blueprint $table) {
            $table->boolean('shuttle_bus_needed')->default(false)->after('children_count');
            $table->string('shuttle_pickup_point', 255)->nullable()->after('shuttle_bus_needed');
            $table->unsignedInteger('shuttle_passengers')->default(0)->after('shuttle_pickup_point');
            $table->string('menu_preference', 100)->nullable()->after('shuttle_passengers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guest_responses', function (Blueprint $table) {
            $table->dropColumn([
                'shuttle_bus_needed',
                'shuttle_pickup_point',
                'shuttle_passengers',
                'menu_preference',
            ]);
        });
    }
};

