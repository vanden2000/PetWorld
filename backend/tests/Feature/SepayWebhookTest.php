<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SepayWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is required for this database test.');
        }

        parent::setUp();
        config(['services.sepay.webhook_api_key' => 'test-key']);
    }

    private function makeUnpaidOrder(float $total): Order
    {
        $user = User::create([
            'name' => 'Buyer', 'email' => 'buyer@example.test',
            'password' => 'password', 'role' => 'user', 'status' => 'active',
        ]);
        $address = Address::create([
            'user_id' => $user->id, 'recipient_name' => 'A', 'recipient_phone' => '0912345678',
            'address_line' => '1 ABC', 'ward' => 'P1', 'district' => 'Q1', 'province' => 'HCM',
            'is_default' => true, 'status' => 'active',
        ]);
        $shipping = ShippingMethod::create(['name' => 'Giao nhanh', 'shipping_fee' => 30000, 'status' => 'active']);
        $payment = PaymentMethod::create(['name' => 'Chuyển khoản ngân hàng', 'status' => 'active']);

        $order = Order::create([
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id,
            'address_id' => $address->id,
            'user_id' => $user->id,
            'recipient_name' => 'A', 'recipient_phone' => '0912345678',
            'recipient_address' => '1 ABC', 'delivery_area' => 'HCM',
            'shipping_fee' => 30000, 'discount_amount' => 0,
            'order_status' => 'pending', 'total_amount' => $total, 'payment_status' => 'unpaid',
        ]);
        $order->update(['payment_code' => 'PW'.$order->id]);

        return $order->refresh();
    }

    private function payload(Order $order, float $amount): array
    {
        return [
            'id' => 99001,
            'gateway' => 'MBBank',
            'transactionDate' => '2026-07-01 10:00:00',
            'accountNumber' => '0865130622',
            'transferType' => 'in',
            'transferAmount' => $amount,
            'content' => "Thanh toan {$order->payment_code}",
            'referenceCode' => 'FT123',
        ];
    }

    public function test_rejects_request_without_valid_api_key(): void
    {
        $order = $this->makeUnpaidOrder(210000);

        $this->postJson('/api/webhooks/sepay', $this->payload($order, 210000))
            ->assertUnauthorized();

        $this->assertSame('unpaid', $order->fresh()->payment_status);
    }

    public function test_marks_order_paid_on_matching_transfer(): void
    {
        $order = $this->makeUnpaidOrder(210000);

        $this->withHeaders(['Authorization' => 'Apikey test-key'])
            ->postJson('/api/webhooks/sepay', $this->payload($order, 210000))
            ->assertOk()
            ->assertJsonPath('success', true);

        $fresh = $order->fresh();
        $this->assertSame('paid', $fresh->payment_status);
        $this->assertSame('confirmed', $fresh->order_status);
        $this->assertDatabaseHas('sepay_transactions', ['sepay_id' => 99001, 'order_id' => $order->id]);
    }

    public function test_is_idempotent_for_duplicate_transaction(): void
    {
        $order = $this->makeUnpaidOrder(210000);
        $headers = ['Authorization' => 'Apikey test-key'];

        $this->withHeaders($headers)->postJson('/api/webhooks/sepay', $this->payload($order, 210000))->assertOk();
        $this->withHeaders($headers)->postJson('/api/webhooks/sepay', $this->payload($order, 210000))->assertOk();

        $this->assertDatabaseCount('sepay_transactions', 1);
    }

    public function test_insufficient_amount_keeps_order_unpaid(): void
    {
        $order = $this->makeUnpaidOrder(210000);

        $this->withHeaders(['Authorization' => 'Apikey test-key'])
            ->postJson('/api/webhooks/sepay', $this->payload($order, 100000))
            ->assertOk();

        $this->assertSame('unpaid', $order->fresh()->payment_status);
        // Vẫn ghi log để đối soát thủ công.
        $this->assertDatabaseHas('sepay_transactions', ['sepay_id' => 99001, 'order_id' => $order->id]);
    }
}
