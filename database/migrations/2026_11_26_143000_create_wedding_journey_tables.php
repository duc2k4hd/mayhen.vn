<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wedding_projects')) {
            Schema::create('wedding_projects', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('slug', 120)->unique();
                $table->string('title', 180);
                $table->string('groom_name', 120)->nullable();
                $table->string('bride_name', 120)->nullable();
                $table->unsignedSmallInteger('groom_birth_year')->nullable();
                $table->unsignedSmallInteger('bride_birth_year')->nullable();
                $table->date('wedding_date')->nullable();
                $table->string('status', 30)->default('draft');
                $table->string('region_style', 20)->default('bac');
                $table->string('wedding_style', 30)->default('nha_hang');
                $table->unsignedBigInteger('budget_total_vnd')->default(150000000);
                $table->unsignedInteger('guest_target')->default(150);
                $table->unsignedBigInteger('invitation_id')->nullable()->index();
                $table->json('candidates_json')->nullable();
                $table->json('floorplan_json')->nullable();
                $table->text('ceremony_notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('wedding_tasks')) {
            Schema::create('wedding_tasks', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('wedding_project_id')->constrained('wedding_projects')->cascadeOnDelete();
                $table->string('code', 20)->nullable();
                $table->string('phase', 100)->default('Chung');
                $table->string('title', 220);
                $table->date('due_date')->nullable();
                $table->string('priority', 20)->default('medium');
                $table->string('status', 20)->default('todo');
                $table->string('assignee', 120)->nullable();
                $table->unsignedBigInteger('depends_on_task_id')->nullable()->index();
                $table->unsignedInteger('sort_order')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('wedding_expenses')) {
            Schema::create('wedding_expenses', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('wedding_project_id')->constrained('wedding_projects')->cascadeOnDelete();
                $table->string('category', 50)->default('venue_catering');
                $table->string('title', 180);
                $table->string('payer_side', 30)->default('couple');
                $table->string('status', 30)->default('planned');
                $table->unsignedBigInteger('budgeted_vnd')->default(0);
                $table->unsignedBigInteger('estimated_vnd')->default(0);
                $table->unsignedBigInteger('committed_vnd')->default(0);
                $table->unsignedBigInteger('deposit_vnd')->default(0);
                $table->unsignedBigInteger('paid_vnd')->default(0);
                $table->unsignedBigInteger('refunded_vnd')->default(0);
                $table->date('due_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('wedding_timeline_items')) {
            Schema::create('wedding_timeline_items', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('wedding_project_id')->constrained('wedding_projects')->cascadeOnDelete();
                $table->string('event_group', 60)->default('Ngày Cưới Chính');
                $table->string('title', 180);
                $table->string('start_time', 5);
                $table->string('end_time', 5);
                $table->string('location', 180)->nullable();
                $table->string('assignee', 120)->nullable();
                $table->text('notes')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('wedding_journey_vendors')) {
            Schema::create('wedding_journey_vendors', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('wedding_project_id')->constrained('wedding_projects')->cascadeOnDelete();
                $table->string('category', 60)->default('venue_catering');
                $table->string('name', 160);
                $table->string('phone', 40)->nullable();
                $table->unsignedBigInteger('quote_vnd')->default(0);
                $table->unsignedBigInteger('deposit_vnd')->default(0);
                $table->string('status', 30)->default('shortlisted');
                $table->text('pros')->nullable();
                $table->text('cons')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('wedding_scripts')) {
            Schema::create('wedding_scripts', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('wedding_project_id')->nullable()->constrained('wedding_projects')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('script_type', 40);
                $table->string('title', 180);
                $table->longText('content');
                $table->string('source', 20)->default('template');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_scripts');
        Schema::dropIfExists('wedding_journey_vendors');
        Schema::dropIfExists('wedding_timeline_items');
        Schema::dropIfExists('wedding_expenses');
        Schema::dropIfExists('wedding_tasks');
        Schema::dropIfExists('wedding_projects');
    }
};
