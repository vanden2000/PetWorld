<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Models\VariantType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is required for this database test.');
        }

        parent::setUp();
    }

    private function makeVariant(int $price, int $salePrice, int $quantity): ProductVariant
    {
        $category = Category::create(['name' => 'Food', 'slug' => 'food', 'image' => 'food.jpg']);
        $brand = Brand::create(['name' => 'PetWorld', 'slug' => 'petworld', 'image' => 'petworld.jpg']);
        $variantType = VariantType::create(['name' => 'Weight']);

        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Sample Food',
            'slug' => 'sample-food',
            'description' => 'Sample description',
            'view_count' => 0,
            'status' => 'active',
        ]);

        return ProductVariant::create([
            'variant_type_id' => $variantType->id,
            'product_id' => $product->id,
            'variant_name' => '1kg',
            'price' => $price,
            'sale_price' => $salePrice,
            'quantity' => $quantity,
            'status' => 'active',
        ]);
    }

    private function makeAddress(User $user): Address
    {
        return Address::create([
            'user_id' => $user->id,
            'recipient_name' => 'Nguyen Van A',
            'recipient_phone' => '0912345678',
            'address_line' => '137 Bình Long',
            'ward' => 'Bình Trị Đông',
            'district' => 'Bình Tân',
            'province' => 'Hồ Chí Minh',
            'is_default' => true,
            'status' => 'active',
        ]);
    }

    public function test_store_order_reprices_from_db_and_decrements_stock(): void
    {
        $user = User::create([
            'name' => 'Buyer', 'email' => 'buyer@example.test',
            'password' => 'password', 'role' => 'user', 'status' => 'active',
        ]);
        Sanctum::actingAs($user);

        $variant = $this->makeVariant(price: 100000, salePrice: 90000, quantity: 5);
        $address = $this->makeAddress($user);
        $shipping = ShippingMethod::create(['name' => 'Giao nhanh', 'shipping_fee' => 30000, 'status' => 'active']);
        $payment = PaymentMethod::create(['name' => 'Chuyển khoản', 'status' => 'active']);

        $response = $this->postJson('/api/orders', [
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id,
            // Giá gửi lên cố tình sai/không có — server phải tự tính lại.
            'items' => [['variant_id' => $variant->id, 'quantity' => 2]],
        ]);

        $response->assertCreated();
        // 90.000 (sale) * 2 + 30.000 ship = 210.000
        $response->assertJsonPath('data.total_amount', 210000);
        $response->assertJsonPath('data.payment_status', 'unpaid');
        $this->assertNotEmpty($response->json('data.payment_code'));

        $this->assertSame(3, $variant->fresh()->quantity);
    }

    public function test_store_order_rejects_quantity_over_stock(): void
    {
        $user = User::create([
            'name' => 'Buyer', 'email' => 'buyer@example.test',
            'password' => 'password', 'role' => 'user', 'status' => 'active',
        ]);
        Sanctum::actingAs($user);

        $variant = $this->makeVariant(price: 100000, salePrice: 0, quantity: 1);
        $address = $this->makeAddress($user);
        $shipping = ShippingMethod::create(['name' => 'Giao nhanh', 'shipping_fee' => 30000, 'status' => 'active']);
        $payment = PaymentMethod::create(['name' => 'COD', 'status' => 'active']);

        $response = $this->postJson('/api/orders', [
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id,
            'items' => [['variant_id' => $variant->id, 'quantity' => 3]],
        ]);

        $response->assertStatus(422);
        $this->assertSame(1, $variant->fresh()->quantity);
        $this->assertSame(0, $user->orders()->count());
    }

    public function test_cannot_order_with_another_users_address(): void
    {
        $user = User::create([
            'name' => 'Buyer', 'email' => 'buyer@example.test',
            'password' => 'password', 'role' => 'user', 'status' => 'active',
        ]);
        $other = User::create([
            'name' => 'Other', 'email' => 'other@example.test',
            'password' => 'password', 'role' => 'user', 'status' => 'active',
        ]);
        Sanctum::actingAs($user);

        $variant = $this->makeVariant(price: 100000, salePrice: 0, quantity: 5);
        $foreignAddress = $this->makeAddress($other);
        $shipping = ShippingMethod::create(['name' => 'Giao nhanh', 'shipping_fee' => 30000, 'status' => 'active']);
        $payment = PaymentMethod::create(['name' => 'COD', 'status' => 'active']);

        $response = $this->postJson('/api/orders', [
            'address_id' => $foreignAddress->id,
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id,
            'items' => [['variant_id' => $variant->id, 'quantity' => 1]],
        ]);

        $response->assertNotFound();
    }

    public function test_orders_require_authentication(): void
    {
        $this->postJson('/api/orders', [])->assertUnauthorized();
    }
}
