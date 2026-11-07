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
        Schema::create('wedding_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained('weddings')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('shape', 30)->default('round'); // round, long, square
            $table->unsignedSmallInteger('capacity')->default(10);
            $table->integer('pos_x')->default(0);
            $table->integer('pos_y')->default(0);
            $table->string('zone', 50)->nullable();
            $table->string('notes', 255)->nullable();
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();

            $table->index(['wedding_id', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wedding_tables');
    }
};
