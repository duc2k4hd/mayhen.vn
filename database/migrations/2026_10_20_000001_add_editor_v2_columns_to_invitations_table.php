<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tác động an toàn (Additive Migration):
     * - schema_version: Quản lý phiên bản Document Schema (mặc định '2.0.0').
     * - revision_id: Khóa lạc quan (Optimistic Concurrency Control) chống ghi đè dữ liệu khi mở nhiều tab.
     * - draft_config: Tách biệt cấu hình bản nháp đang chỉnh sửa với bản công khai.
     * - last_autosaved_at: Ghi nhận thời điểm autosave gần nhất.
     */
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            if (! Schema::hasColumn('invitations', 'schema_version')) {
                $table->string('schema_version', 20)->default('2.0.0')->after('template_version');
            }
            if (! Schema::hasColumn('invitations', 'revision_id')) {
                $table->unsignedBigInteger('revision_id')->default(1)->after('schema_version');
            }
            if (! Schema::hasColumn('invitations', 'draft_config')) {
                $table->json('draft_config')->nullable()->after('custom_config');
            }
            if (! Schema::hasColumn('invitations', 'last_autosaved_at')) {
                $table->timestamp('last_autosaved_at')->nullable()->after('draft_config');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('invitations', 'last_autosaved_at')) {
                $columnsToDrop[] = 'last_autosaved_at';
            }
            if (Schema::hasColumn('invitations', 'draft_config')) {
                $columnsToDrop[] = 'draft_config';
            }
            if (Schema::hasColumn('invitations', 'revision_id')) {
                $columnsToDrop[] = 'revision_id';
            }
            if (Schema::hasColumn('invitations', 'schema_version')) {
                $columnsToDrop[] = 'schema_version';
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
