<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hạn thanh toán cho đơn chuyển khoản: quá hạn mà chưa trả tiền thì lệnh
     * orders:expire-unpaid sẽ tự hủy đơn. Đơn COD để null (không có hạn).
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dateTime('expires_at')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
