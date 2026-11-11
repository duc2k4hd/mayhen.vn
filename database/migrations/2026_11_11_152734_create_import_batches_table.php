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
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_id')->unique()->comment('Mã định danh duy nhất của phiên nhập khách');
            $table->foreignId('wedding_id')->constrained('weddings')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_name', 255)->comment('Tên tệp tin gốc');
            $table->string('file_path', 255)->nullable()->comment('Đường dẫn lưu tệp tạm');
            $table->enum('status', ['uploaded', 'mapped', 'processing', 'completed', 'partial_failed', 'failed'])
                ->default('uploaded')
                ->comment('Trạng thái tiến trình 4 bước');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('imported_count')->default(0);
            $table->unsignedInteger('updated_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->json('column_mapping')->nullable()->comment('Cấu hình ánh xạ cột nguồn sang trường đích');
            $table->string('duplicate_mode', 20)->default('skip')->comment('Chế độ xử lý trùng: skip, update, create_always');
            $table->json('error_summary')->nullable()->comment('Chi tiết các dòng bị lỗi để xuất CSV');
            $table->longText('raw_preview_data')->nullable()->comment('Dữ liệu thô trích xuất từ file phục vụ preview và retry');
            $table->timestamps();

            $table->index(['wedding_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_batches');
    }
};
