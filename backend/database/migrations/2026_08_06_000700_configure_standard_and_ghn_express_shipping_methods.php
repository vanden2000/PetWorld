<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE shipping_methods MODIFY fee_mode ENUM('live_quote', 'weight_rule', 'fixed', 'unavailable') NOT NULL DEFAULT 'fixed'");
        }

        $methods = DB::table('shipping_methods')->whereNull('code')->orderBy('id')->get();

        if ($standard = $methods->get(0)) {
            DB::table('shipping_methods')->where('id', $standard->id)->update([
                'code' => 'standard', 'provider' => 'petworld', 'fee_mode' => 'weight_rule',
                'description' => '2–5 ngày · Phí theo cân nặng', 'shipping_fee' => 30000,
            ]);
        }

        if ($express = $methods->get(1)) {
            DB::table('shipping_methods')->where('id', $express->id)->update([
                'code' => 'ghn_express', 'provider' => 'ghn', 'fee_mode' => 'live_quote',
                'description' => '1–2 ngày · Phí theo địa chỉ GHN', 'shipping_fee' => 0,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('shipping_methods')->where('code', 'standard')->update(['code' => null, 'provider' => null, 'fee_mode' => 'fixed', 'description' => null, 'shipping_fee' => 30000]);
        DB::table('shipping_methods')->where('code', 'ghn_express')->update(['code' => null, 'provider' => null, 'fee_mode' => 'fixed', 'description' => null, 'shipping_fee' => 45000]);
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE shipping_methods MODIFY fee_mode ENUM('live_quote', 'fixed', 'unavailable') NOT NULL DEFAULT 'fixed'");
        }
    }
};
