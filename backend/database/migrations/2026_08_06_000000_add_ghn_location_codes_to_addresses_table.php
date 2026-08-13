<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            // Lưu mã GHN song song với tên địa chỉ để báo giá và tạo vận đơn chính xác.
            // Các cột để nullable nhằm không làm mất hiệu lực sổ địa chỉ cũ.
            $table->unsignedInteger('ghn_province_id')->nullable()->after('province');
            $table->unsignedInteger('ghn_district_id')->nullable()->after('ghn_province_id');
            $table->string('ghn_ward_code', 20)->nullable()->after('ghn_district_id');

            $table->index(['ghn_district_id', 'ghn_ward_code'], 'addresses_ghn_destination_index');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropIndex('addresses_ghn_destination_index');
            $table->dropColumn([
                'ghn_province_id',
                'ghn_district_id',
                'ghn_ward_code',
            ]);
        });
    }
};
