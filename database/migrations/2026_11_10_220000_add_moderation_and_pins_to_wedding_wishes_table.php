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
        Schema::table('wedding_wishes', function (Blueprint $table) {
            $table->foreignId('guest_id')->nullable()->after('wedding_id')->constrained('guests')->nullOnDelete();
            $table->boolean('is_pinned')->default(false)->after('is_approved');
            $table->boolean('is_private')->default(false)->after('is_pinned');
            $table->string('signature_url', 500)->nullable()->after('is_private');

            $table->index(['wedding_id', 'is_pinned', 'created_at']);
            $table->index(['wedding_id', 'is_approved', 'is_private']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wedding_wishes', function (Blueprint $table) {
            $table->dropForeign(['guest_id']);
            $table->dropIndex(['wedding_id', 'is_pinned', 'created_at']);
            $table->dropIndex(['wedding_id', 'is_approved', 'is_private']);
            $table->dropColumn(['guest_id', 'is_pinned', 'is_private', 'signature_url']);
        });
    }
};

