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
        Schema::create('wedding_checklist_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained('weddings')->onDelete('cascade');
            $table->string('category', 50)->default('general');
            $table->string('phase', 50)->default('wedding_day');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('assignee_name', 100)->nullable();
            $table->date('due_date')->nullable();
            $table->string('priority', 20)->default('medium'); // urgent, high, medium, low
            $table->string('status', 20)->default('pending'); // pending, in_progress, completed, cancelled
            $table->timestamp('completed_at')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index(['wedding_id', 'phase']);
            $table->index(['wedding_id', 'status']);
        });

        Schema::create('wedding_budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained('weddings')->onDelete('cascade');
            $table->string('category', 50)->default('other');
            $table->string('item_name');
            $table->unsignedBigInteger('estimated_cost')->default(0);
            $table->unsignedBigInteger('actual_cost')->default(0);
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->string('vendor_name')->nullable();
            $table->string('vendor_contact')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_paid_in_full')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index(['wedding_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wedding_budget_items');
        Schema::dropIfExists('wedding_checklist_tasks');
    }
};
