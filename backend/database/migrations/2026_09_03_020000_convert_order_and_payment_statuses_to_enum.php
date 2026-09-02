<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Chuyển order_status sang ENUM chặt chẽ gồm 6 trạng thái chuẩn
            DB::statement("
                ALTER TABLE `orders` 
                MODIFY COLUMN `order_status` ENUM('pending', 'confirmed', 'shipping', 'completed', 'returned', 'cancelled') 
                NOT NULL DEFAULT 'pending'
            ");

            // Chuyển payment_status sang ENUM chặt chẽ gồm 7 trạng thái thanh toán & đối soát
            DB::statement("
                ALTER TABLE `orders` 
                MODIFY COLUMN `payment_status` ENUM('unpaid', 'customer_paid', 'reconciling', 'paid', 'discrepancy', 'failed', 'refunded') 
                NOT NULL DEFAULT 'unpaid'
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE `orders` 
                MODIFY COLUMN `order_status` VARCHAR(50) 
                NOT NULL DEFAULT 'pending'
            ");

            DB::statement("
                ALTER TABLE `orders` 
                MODIFY COLUMN `payment_status` VARCHAR(30) 
                NOT NULL DEFAULT 'unpaid'
            ");
        }
    }
};
