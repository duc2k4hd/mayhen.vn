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
        Schema::table('invitation_templates', function (Blueprint $table) {
            $table->index(['is_active', 'is_featured'], 'idx_templates_active_featured');
            $table->index(['is_active', 'category'], 'idx_templates_active_category');
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->index('is_published', 'idx_invitations_is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitation_templates', function (Blueprint $table) {
            $table->dropIndex('idx_templates_active_featured');
            $table->dropIndex('idx_templates_active_category');
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->dropIndex('idx_invitations_is_published');
        });
    }
};
