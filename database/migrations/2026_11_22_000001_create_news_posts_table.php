<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('news_posts')) {
            if (DB::table('news_posts')->count() > 0) {
                return;
            }
            Schema::drop('news_posts');
        }

        Schema::create('news_posts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category_label', 120)->default('Cập Nhật Hệ Thống')->index();
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->string('cover_image_url')->nullable();
            $table->string('author_name', 150)->default('Ban Phát Triển Mây Hẹn');
            $table->json('tags')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedInteger('reading_time_minutes')->default(3);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('send_email_on_publish')->default(true);
            $table->timestamp('email_broadcasted_at')->nullable();
            $table->unsignedInteger('email_recipients_count')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_posts');
    }
};
