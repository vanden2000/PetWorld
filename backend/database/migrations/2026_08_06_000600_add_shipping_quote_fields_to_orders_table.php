<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('shipping_method_code', 40)->nullable()->after('shipping_method_id');
            $table->unsignedInteger('shipping_weight_grams')->default(0)->after('shipping_fee');
            $table->decimal('shipping_fee_original', 12, 2)->default(0)->after('shipping_weight_grams');
            $table->decimal('shipping_discount', 12, 2)->default(0)->after('shipping_fee_original');

            $table->index('shipping_method_code', 'orders_shipping_method_code_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('orders_shipping_method_code_idx');
            $table->dropColumn([
                'shipping_method_code',
                'shipping_weight_grams',
                'shipping_fee_original',
                'shipping_discount',
            ]);
        });
    }
};
