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
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Mã định danh của khối trang chủ');
            $table->string('name')->comment('Tên hiển thị trong trang Admin');
            $table->string('custom_title')->nullable()->comment('Tiêu đề tùy chỉnh ngoài trang chủ');
            $table->integer('order')->default(0)->comment('Thứ tự hiển thị (từ nhỏ đến lớn)');
            $table->boolean('is_active')->default(true)->comment('Trạng thái Bật/Tắt');
            $table->integer('limit')->nullable()->comment('Số lượng mục tối đa hiển thị');
            $table->json('settings')->nullable()->comment('Cấu hình bổ sung dạng JSON');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_sections');
    }
};
