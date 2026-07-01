<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mã đối soát thanh toán: nội dung chuyển khoản khách nhập, dùng để khớp
     * giao dịch tự động (vd webhook SePay) với đúng đơn hàng.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_code')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['payment_code']);
            $table->dropColumn('payment_code');
        });
    }
};
