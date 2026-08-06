<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table): void {
            // Existing vouchers remain product discounts by default.
            $table->enum('applies_to', ['product', 'shipping', 'order'])->default('product')->after('code');
            $table->boolean('is_automatic')->default(false)->after('applies_to');
            $table->string('shipping_method_code', 40)->nullable()->after('is_automatic');
            $table->decimal('max_shipping_discount', 12, 2)->nullable()->after('discount_value');

            $table->index(['applies_to', 'is_automatic', 'status'], 'vouchers_shipping_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table): void {
            $table->dropIndex('vouchers_shipping_lookup_idx');
            $table->dropColumn([
                'applies_to',
                'is_automatic',
                'shipping_method_code',
                'max_shipping_discount',
            ]);
        });
    }
};
