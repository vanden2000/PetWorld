<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Models\VariantType;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is required for this database test.');
        }

        parent::setUp();
    }

    public function test_products_api_supports_filters_and_wishlist_state(): void
    {
        $category = Category::create([
            'name' => 'Food',
            'slug' => 'thuc-an-hat',
            'image' => 'food.jpg',
        ]);

        $brand = Brand::create([
            'name' => 'Royal Canin',
            'slug' => 'royal-canin',
            'image' => 'royal-canin.jpg',
        ]);

        $variantType = VariantType::create([
            'name' => 'Weight',
            'status' => 'active',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Royal Canin Mini Adult',
            'slug' => 'royal-canin-mini-adult',
            'description' => 'Dog food',
            'view_count' => 10,
            'status' => 'active',
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_url' => 'products/royal-canin-mini-adult.jpg',
            'is_primary' => true,
        ]);

        ProductVariant::create([
            'variant_type_id' => $variantType->id,
            'product_id' => $product->id,
            'variant_name' => '1kg',
            'price' => 230000,
            'sale_price' => 209000,
            'quantity' => 7,
            'status' => 'active',
        ]);

        $unavailableProduct = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Unavailable Food',
            'slug' => 'unavailable-food',
            'status' => 'active',
        ]);

        ProductVariant::create([
            'variant_type_id' => $variantType->id,
            'product_id' => $unavailableProduct->id,
            'variant_name' => 'Default',
            'price' => 900000,
            'quantity' => 1,
            'status' => 'inactive',
        ]);

        $hiddenProduct = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Hidden Food',
            'slug' => 'hidden-food',
            'status' => 'inactive',
        ]);

        ProductVariant::create([
            'variant_type_id' => $variantType->id,
            'product_id' => $hiddenProduct->id,
            'variant_name' => 'Default',
            'price' => 2000000,
            'quantity' => 1,
            'status' => 'active',
        ]);

        ProductVariant::create([
            'variant_type_id' => $variantType->id,
            'product_id' => $product->id,
            'variant_name' => '2kg',
            'price' => 250000,
            'sale_price' => 300000,
            'quantity' => 1,
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Mai Nguyen',
            'email' => 'mai@example.test',
            'password' => 'password',
            'role' => 'user',
            'status' => 'active',
        ]);

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $url = '/api/products?search=royal&category=thuc-an-hat&brand=royal-canin&min_price=0&max_price=500000&sort=price_asc';

        // user_id trên URL không được phép giả mạo trạng thái wishlist của người khác.
        $this->getJson($url.'&user_id='.$user->id)
            ->assertOk()
            ->assertJsonPath('data.products.0.is_wishlisted', false);

        Sanctum::actingAs($user);
        $response = $this->getJson($url);

        $response
            ->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.products.0.slug', 'royal-canin-mini-adult')
            ->assertJsonPath('data.products.0.price.min', 209000)
            ->assertJsonPath('data.products.0.price.max', 250000)
            ->assertJsonPath('data.products.0.price.sale_max', 209000)
            ->assertJsonPath('data.products.0.price.has_sale', true)
            ->assertJsonPath('data.products.0.stock_quantity', 8)
            ->assertJsonPath('data.products.0.wishlist_count', 1)
            ->assertJsonPath('data.products.0.is_wishlisted', true)
            ->assertJsonPath('data.filters.categories.0.slug', 'thuc-an-hat')
            ->assertJsonPath('data.filters.categories.0.product_count', 1)
            ->assertJsonPath('data.filters.brands.0.slug', 'royal-canin')
            ->assertJsonPath('data.filters.brands.0.product_count', 1)
            ->assertJsonPath('data.filters.price.max', 250000)
            ->assertJsonPath('data.pagination.current_page', 1);
    }

    public function test_product_detail_api_returns_variants_reviews_and_related_products(): void
    {
        $category = Category::create([
            'name' => 'Accessories',
            'slug' => 'phu-kien',
            'image' => 'accessories.jpg',
        ]);

        $brand = Brand::create([
            'name' => 'Petkit',
            'slug' => 'petkit',
            'image' => 'petkit.jpg',
        ]);

        $variantType = VariantType::create([
            'name' => 'Size',
            'status' => 'active',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Petkit Harness',
            'slug' => 'petkit-harness',
            'description' => '<p>Comfortable harness.</p>',
            'view_count' => 15,
            'status' => 'active',
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_url' => 'products/petkit-harness-main.jpg',
            'is_primary' => true,
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_url' => 'products/petkit-harness-side.jpg',
            'is_primary' => false,
        ]);

        $variant = ProductVariant::create([
            'variant_type_id' => $variantType->id,
            'product_id' => $product->id,
            'variant_name' => 'M',
            'price' => 180000,
            'sale_price' => 150000,
            'quantity' => 5,
            'status' => 'active',
        ]);

        $relatedProduct = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Petkit Leash',
            'slug' => 'petkit-leash',
            'description' => 'Leash',
            'view_count' => 20,
            'status' => 'active',
        ]);

        ProductImage::create([
            'product_id' => $relatedProduct->id,
            'image_url' => 'products/petkit-leash.jpg',
            'is_primary' => true,
        ]);

        ProductVariant::create([
            'variant_type_id' => $variantType->id,
            'product_id' => $relatedProduct->id,
            'variant_name' => 'Default',
            'price' => 99000,
            'quantity' => 3,
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Lan Tran',
            'email' => 'lan@example.test',
            'password' => 'password',
            'role' => 'user',
            'status' => 'active',
        ]);

        $address = Address::create([
            'user_id' => $user->id,
            'recipient_name' => 'Lan Tran',
            'recipient_phone' => '0900000000',
            'address_line' => '123 Nguyen Hue',
            'ward' => 'Ben Nghe',
            'district' => 'Quan 1',
            'province' => 'TP HCM',
            'is_default' => true,
            'status' => 'active',
        ]);

        $shippingMethod = ShippingMethod::create([
            'name' => 'Standard',
            'shipping_fee' => 20000,
            'status' => 'active',
        ]);

        $paymentMethod = PaymentMethod::create([
            'name' => 'COD',
            'status' => 'active',
        ]);

        $order = Order::create([
            'shipping_method_id' => $shippingMethod->id,
            'payment_method_id' => $paymentMethod->id,
            'address_id' => $address->id,
            'user_id' => $user->id,
            'recipient_name' => 'Lan Tran',
            'recipient_phone' => '0900000000',
            'recipient_address' => '123 Nguyen Hue',
            'delivery_area' => 'TP HCM',
            'shipping_fee' => 20000,
            'discount_amount' => 0,
            'order_status' => 'completed',
            'total_amount' => 170000,
            'payment_status' => 'paid',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'price' => 150000,
        ]);

        Review::create([
            'user_id' => $user->id,
            'order_item_id' => $orderItem->id,
            'rating' => 5,
            'comment' => 'Rat chac chan.',
            'status' => 'approved',
        ]);

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/products/petkit-harness');

        $response
            ->assertOk()
            ->assertJsonPath('data.product.slug', 'petkit-harness')
            ->assertJsonPath('data.product.view_count', 16)
            ->assertJsonPath('data.product.description', '<p>Comfortable harness.</p>')
            ->assertJsonPath('data.product.images.0.is_primary', true)
            ->assertJsonPath('data.product.variants.0.name', 'M')
            ->assertJsonPath('data.product.variants.0.effective_price', 150000)
            ->assertJsonPath('data.product.rating.average', 5.0)
            ->assertJsonPath('data.product.is_wishlisted', true)
            ->assertJsonPath('data.reviews.0.rating', 5)
            ->assertJsonPath('data.reviews.0.variant.name', 'M')
            ->assertJsonPath('data.related_products.0.slug', 'petkit-leash');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'view_count' => 16,
        ]);

        $this->getJson('/api/products/recent?slugs=petkit-leash,petkit-harness')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'petkit-leash')
            ->assertJsonPath('data.1.slug', 'petkit-harness');

        // API recent chỉ đọc dữ liệu, không được tính thêm lượt xem chi tiết.
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'view_count' => 16,
        ]);
    }
}
