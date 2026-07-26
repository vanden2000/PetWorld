<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\PetSpecies;
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

        $this->createProductVariant([
            'product_id' => $product->id,
            'price' => 230000,
            'sale_price' => 209000,
            'quantity' => 7,
            'status' => 'active',
        ], [$variantType->id => '1kg']);

        $dog = PetSpecies::query()->where('slug', 'dog')->firstOrFail();
        $product->petSpecies()->attach($dog);

        $unavailableProduct = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Unavailable Food',
            'slug' => 'unavailable-food',
            'status' => 'active',
        ]);

        $this->createProductVariant([
            'product_id' => $unavailableProduct->id,
            'price' => 900000,
            'quantity' => 1,
            'status' => 'inactive',
        ], [$variantType->id => 'Default']);

        $hiddenProduct = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Hidden Food',
            'slug' => 'hidden-food',
            'status' => 'inactive',
        ]);

        $this->createProductVariant([
            'product_id' => $hiddenProduct->id,
            'price' => 2000000,
            'quantity' => 1,
            'status' => 'active',
        ], [$variantType->id => 'Default']);

        $this->createProductVariant([
            'product_id' => $product->id,
            'price' => 250000,
            'sale_price' => 300000,
            'quantity' => 1,
            'status' => 'active',
        ], [$variantType->id => '2kg']);

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

        $url = '/api/products?search=royal&category=thuc-an-hat&brand=royal-canin&pet=dog&min_price=0&max_price=500000&sort=price_asc';

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
            ->assertJsonFragment(['slug' => 'dog', 'product_count' => 1])
            ->assertJsonPath('data.filters.price.max', 250000)
            ->assertJsonPath('data.pagination.current_page', 1);
    }

    public function test_default_product_search_prioritizes_name_matches(): void
    {
        $category = Category::create([
            'name' => 'Food',
            'slug' => 'food',
            'image' => 'food.jpg',
        ]);
        $brand = Brand::create([
            'name' => 'PetWorld',
            'slug' => 'petworld',
            'image' => 'petworld.jpg',
        ]);
        $variantType = VariantType::create([
            'name' => 'Weight',
            'status' => 'active',
        ]);

        $nameMatch = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Royal Canin Mini',
            'slug' => 'royal-canin-mini',
            'description' => 'Thức ăn cho chó nhỏ.',
            'status' => 'active',
        ]);
        $this->createProductVariant([
            'product_id' => $nameMatch->id,
            'price' => 200000,
            'quantity' => 5,
            'status' => 'active',
        ], [$variantType->id => '1kg']);

        $descriptionMatch = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Dog Food Premium',
            'slug' => 'dog-food-premium',
            'description' => 'Được sản xuất cùng Royal Canin.',
            'status' => 'active',
        ]);
        $this->createProductVariant([
            'product_id' => $descriptionMatch->id,
            'price' => 180000,
            'quantity' => 5,
            'status' => 'active',
        ], [$variantType->id => '1kg']);

        $this->getJson('/api/products?search=Royal')
            ->assertOk()
            ->assertJsonPath('data.products.0.slug', 'royal-canin-mini');
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
        $packagingType = VariantType::create([
            'name' => 'Packaging',
            'status' => 'active',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Petkit Harness',
            'slug' => 'petkit-harness',
            'description' => '<p>Comfortable harness.</p>',
            'short_description' => 'Dây đeo nhẹ, chắc chắn và phù hợp cho những buổi đi dạo hằng ngày.',
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

        $variant = $this->createProductVariant([
            'product_id' => $product->id,
            'price' => 180000,
            'sale_price' => 150000,
            'quantity' => 5,
            'status' => 'active',
        ], [
            $variantType->id => 'M',
            $packagingType->id => 'Hộp',
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

        $this->createProductVariant([
            'product_id' => $relatedProduct->id,
            'price' => 99000,
            'quantity' => 3,
            'status' => 'active',
        ], [$variantType->id => 'Default']);

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
            ->assertJsonPath('data.product.short_description', 'Dây đeo nhẹ, chắc chắn và phù hợp cho những buổi đi dạo hằng ngày.')
            ->assertJsonPath('data.product.images.0.is_primary', true)
            ->assertJsonPath('data.product.variants.0.name', 'M - Hộp')
            ->assertJsonPath('data.product.variants.0.options.0.type_name', 'Size')
            ->assertJsonPath('data.product.variants.0.options.1.value', 'Hộp')
            ->assertJsonPath('data.product.variants.0.effective_price', 150000)
            ->assertJsonPath('data.product.rating.average', 5.0)
            ->assertJsonPath('data.product.is_wishlisted', true)
            ->assertJsonPath('data.reviews.0.rating', 5)
            ->assertJsonPath('data.reviews.0.variant.name', 'M - Hộp')
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
