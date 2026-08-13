<?php

namespace Tests\Unit;

use App\Models\Voucher;
use App\Services\GhnService;
use App\Services\ShippingQuoteService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingVoucherQuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_shipping_voucher_is_applied_and_capped_by_shipping_fee(): void
    {
        $voucher = Voucher::create([
            'code' => 'THEDIEM',
            'applies_to' => 'shipping',
            'is_automatic' => false,
            'usage_limit' => 5,
            'discount_value' => 300000,
            'max_shipping_discount' => 80000,
            'min_order_value' => 1000,
            'start_date' => Carbon::now()->subDay(),
            'end_date' => Carbon::now()->addDay(),
            'status' => 'active',
        ]);

        $service = new ShippingQuoteService(app(GhnService::class));
        $quote = $this->invokeApplyPromotion($service, ['shipping_fee_original' => 30000, 'shipping_fee' => 30000], $voucher);

        $this->assertEquals(30000.0, $quote['shipping_discount']);
        $this->assertEquals(0.0, $quote['shipping_fee']);
        $this->assertSame($voucher->id, $quote['shipping_voucher_id']);
    }

    private function invokeApplyPromotion(ShippingQuoteService $service, array $quote, Voucher $voucher): array
    {
        $method = new \ReflectionMethod($service, 'applyPromotion');
        $method->setAccessible(true);

        return $method->invoke($service, $quote, $voucher);
    }
}
