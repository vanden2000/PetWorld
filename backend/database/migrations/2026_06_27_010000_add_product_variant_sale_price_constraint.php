<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Dữ liệu cũ không hợp lệ được trả về trạng thái không sale trước khi thêm ràng buộc.
        DB::table('product_variants')
            ->whereNotNull('sale_price')
            ->where(function ($query): void {
                $query->where('sale_price', '<=', 0)
                    ->orWhereColumn('sale_price', '>=', 'price');
            })
            ->update(['sale_price' => null]);

        DB::statement(
            'ALTER TABLE product_variants '
            .'ADD CONSTRAINT product_variants_sale_price_valid '
            .'CHECK (sale_price IS NULL OR (sale_price > 0 AND sale_price < price))'
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            'ALTER TABLE product_variants DROP CHECK product_variants_sale_price_valid'
        );
    }
};
