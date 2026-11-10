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
        // 1. Mở rộng bảng invitation_templates phục vụ Release Governance & Staged Rollout
        Schema::table('invitation_templates', function (Blueprint $table) {
            $table->string('release_status', 30)->default('published')->after('is_featured');
            $table->string('previous_version', 30)->nullable()->after('current_version');
            $table->string('staged_version', 30)->nullable()->after('previous_version');
            $table->unsignedTinyInteger('rollout_percentage')->default(100)->after('staged_version');
            $table->text('release_notes')->nullable()->after('rollout_percentage');
            $table->timestamp('published_at')->nullable()->after('updated_at');
            $table->timestamp('staged_at')->nullable()->after('published_at');
            $table->timestamp('rolled_back_at')->nullable()->after('staged_at');
        });

        // 2. Tạo bảng lưu trữ lịch sử phát hành & phiên bản mẫu thiệp (Template Release Ledger)
        Schema::create('invitation_template_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('invitation_templates')->cascadeOnDelete();
            $table->string('version', 30);
            $table->string('release_status', 30)->default('staged'); // staged, published, rolled_back, deprecated
            $table->unsignedTinyInteger('rollout_percentage')->default(100);
            $table->text('release_notes')->nullable();
            $table->json('preflight_snapshot')->nullable(); // Lưu kết quả kiểm định 10 Section, Live Sync, Zero CDN
            $table->foreignId('creator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('publisher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('rolled_back_at')->nullable();
            $table->string('rollback_reason', 255)->nullable();
            $table->timestamps();

            $table->index(['template_id', 'version']);
            $table->index(['template_id', 'release_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitation_template_releases');

        Schema::table('invitation_templates', function (Blueprint $table) {
            $table->dropColumn([
                'release_status',
                'previous_version',
                'staged_version',
                'rollout_percentage',
                'release_notes',
                'published_at',
                'staged_at',
                'rolled_back_at',
            ]);
        });
    }
};

