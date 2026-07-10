<?php

namespace Tests\Unit;

use App\Models\Voucher;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class VoucherRuleTest extends TestCase
{
    public function test_unlimited_active_voucher_obeys_date_and_minimum_order_rules(): void
    {
        $now = Carbon::now();
        $voucher = new Voucher;
        $voucher->setRawAttributes([
            'usage_limit' => 0,
            'discount_value' => 50000,
            'min_order_value' => 300000,
            'start_date' => $now->copy()->subDay(),
            'end_date' => $now->copy()->addDay(),
            'status' => 'active',
        ]);

        $this->assertTrue($voucher->canBeApplied(300000, $now));
        $this->assertFalse($voucher->canBeApplied(299999, $now));

        $voucher->status = 'inactive';
        $this->assertFalse($voucher->canBeApplied(300000, $now));

        $voucher->setRawAttributes(array_merge($voucher->getAttributes(), [
            'status' => 'active',
            'start_date' => $now->copy()->addMinute(),
        ]));
        $this->assertFalse($voucher->canBeApplied(300000, $now));

        $voucher->setRawAttributes(array_merge($voucher->getAttributes(), [
            'start_date' => $now->copy()->subDay(),
            'end_date' => $now->copy()->subMinute(),
        ]));
        $this->assertFalse($voucher->canBeApplied(300000, $now));
    }
}
