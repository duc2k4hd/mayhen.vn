<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tác động:
     * - weddings: thêm extra_data (json, nullable) để lưu album ảnh, timeline, dresscode mở rộng.
     * - invitation_templates: thêm current_version (varchar 20, default 'v1') để quản lý version schema.
     * - invitations: thêm template_version (varchar 20, default 'v1') để gắn version khi tạo thiệp.
     * Hoàn toàn an toàn, không ảnh hưởng dữ liệu cũ.
     */
    public function up(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            if (! Schema::hasColumn('weddings', 'extra_data')) {
                $table->json('extra_data')->nullable()->after('bank_account_name');
            }
        });

        Schema::table('invitation_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('invitation_templates', 'current_version')) {
                $table->string('current_version', 20)->default('v1')->after('default_config');
            }
        });

        Schema::table('invitations', function (Blueprint $table) {
            if (! Schema::hasColumn('invitations', 'template_version')) {
                $table->string('template_version', 20)->default('v1')->after('version_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            if (Schema::hasColumn('invitations', 'template_version')) {
                $table->dropColumn('template_version');
            }
        });

        Schema::table('invitation_templates', function (Blueprint $table) {
            if (Schema::hasColumn('invitation_templates', 'current_version')) {
                $table->dropColumn('current_version');
            }
        });

        Schema::table('weddings', function (Blueprint $table) {
            if (Schema::hasColumn('weddings', 'extra_data')) {
                $table->dropColumn('extra_data');
            }
        });
    }
};
