<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Models\VariantType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is required for this database test.');
        }

        parent::setUp();
    }

    public function test_selecting_a_new_default_address_unsets_the_previous_one(): void
    {
        $user = $this->createUser('owner@example.test');
        $first = $this->createAddress($user, 'Địa chỉ thứ nhất');
        $second = $this->createAddress($user, 'Địa chỉ thứ hai');

        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);
        $this->assertSame(1, Address::where('user_id', $user->id)->where('is_default', true)->count());
    }

    public function test_review_requires_valid_rating_owner_and_completed_order(): void
    {
        $owner = $this->createUser('owner@example.test');
        $outsider = $this->createUser('outsider@example.test');
        $orderItem = $this->createOrderItem($owner, 'pending');

        foreach ([
            ['user_id' => $owner->id, 'rating' => 6, 'error' => 'rating'],
            ['user_id' => $outsider->id, 'rating' => 5, 'error' => 'user_id'],
            ['user_id' => $owner->id, 'rating' => 5, 'error' => 'order_item_id'],
        ] as $case) {
            try {
                Review::create([
                    'user_id' => $case['user_id'],
                    'order_item_id' => $orderItem->id,
                    'rating' => $case['rating'],
                    'status' => 'approved',
                ]);
                $this->fail('Review không hợp lệ đã được lưu.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey($case['error'], $exception->errors());
            }
        }

        $orderItem->order->update(['order_status' => 'completed']);

        $review = Review::create([
            'user_id' => $owner->id,
            'order_item_id' => $orderItem->id,
            'rating' => 5,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'rating' => 5]);
    }

    private function createUser(string $email): User
    {
        return User::create([
            'name' => 'Người dùng',
            'email' => $email,
            'password' => 'password',
            'role' => 'user',
            'status' => 'active',
        ]);
    }

    private function createAddress(User $user, string $line): Address
    {
        return Address::create([
            'user_id' => $user->id,
            'recipient_name' => $user->name,
            'recipient_phone' => '0900000000',
            'address_line' => $line,
            'ward' => 'Bến Thành',
            'district' => 'Quận 1',
            'province' => 'Hồ Chí Minh',
            'is_default' => true,
            'status' => 'active',
        ]);
    }

    private function createOrderItem(User $owner, string $status): OrderItem
    {
        $category = Category::create(['name' => 'Pate', 'slug' => 'pate']);
        $brand = Brand::create(['name' => 'PetWorld', 'slug' => 'petworld']);
        $type = VariantType::create(['name' => 'Mặc định', 'status' => 'active']);
        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Pate mẫu',
            'slug' => 'pate-mau',
            'status' => 'active',
        ]);
        $variant = ProductVariant::create([
            'variant_type_id' => $type->id,
            'product_id' => $product->id,
            'variant_name' => 'Mặc định',
            'price' => 100000,
            'quantity' => 1,
            'status' => 'active',
        ]);
        $address = $this->createAddress($owner, 'Địa chỉ nhận hàng');
        $shipping = ShippingMethod::create(['name' => 'Tiêu chuẩn', 'shipping_fee' => 30000, 'status' => 'active']);
        $payment = PaymentMethod::create(['name' => 'COD', 'status' => 'active']);
        $order = Order::create([
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id,
            'address_id' => $address->id,
            'user_id' => $owner->id,
            'recipient_name' => $owner->name,
            'recipient_phone' => '0900000000',
            'recipient_address' => 'Địa chỉ nhận hàng',
            'delivery_area' => 'Hồ Chí Minh',
            'shipping_fee' => 30000,
            'discount_amount' => 0,
            'order_status' => $status,
            'total_amount' => 130000,
            'payment_status' => 'paid',
        ]);

        return OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'price' => 100000,
        ]);
    }
}
