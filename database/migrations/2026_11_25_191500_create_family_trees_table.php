<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('family_trees') && DB::table('family_trees')->count() === 0) {
            Schema::drop('family_trees');
        }

        if (! Schema::hasTable('family_trees')) {
            Schema::create('family_trees', function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('slug', 160)->unique();
                $table->string('title', 180);
                $table->string('family_surname', 80)->nullable();
                $table->string('ancestral_hall', 180)->nullable();
                $table->string('template_style', 60)->default('hoang-kim-tu-duong');
                $table->string('layout_mode', 30)->default('vertical');
                $table->longText('intro_chronicle')->nullable();
                $table->longText('clan_rules')->nullable();
                $table->longText('members_json');
                $table->string('visibility', 20)->default('public');
                $table->string('access_password')->nullable();
                $table->string('plan_slug', 30)->default('premium');
                $table->timestamp('expires_at')->nullable();
                $table->unsignedInteger('view_count')->default(0);
                $table->boolean('is_published')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['user_id', 'is_published']);
                $table->index(['expires_at', 'is_published']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('family_trees');
    }
};
