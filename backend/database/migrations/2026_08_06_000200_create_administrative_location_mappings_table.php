<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administrative_location_mappings', function (Blueprint $table): void {
            $table->id();
            // Tên đơn vị tỉnh/thành sau sáp nhập, dùng cho giao diện khách hàng.
            $table->string('current_province_name', 100);
            // Đơn vị GHN hiện dùng để chọn tuyến giao và tính phí.
            $table->unsignedInteger('ghn_province_id');
            $table->string('ghn_province_name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['current_province_name', 'ghn_province_id'], 'administrative_location_mapping_unique');
            $table->index(['current_province_name', 'is_active'], 'adm_location_current_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrative_location_mappings');
    }
};
