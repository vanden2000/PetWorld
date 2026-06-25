<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantType;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $response = $this->getJson('/api/products?search=royal&category=thuc-an-hat&brand=royal-canin&min_price=0&max_price=500000&sort=price_asc&user_id=' . $user->id);

        $response
            ->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.products.0.slug', 'royal-canin-mini-adult')
            ->assertJsonPath('data.products.0.price.min', 209000)
            ->assertJsonPath('data.products.0.price.has_sale', true)
            ->assertJsonPath('data.products.0.stock_quantity', 7)
            ->assertJsonPath('data.products.0.wishlist_count', 1)
            ->assertJsonPath('data.products.0.is_wishlisted', true)
            ->assertJsonPath('data.filters.categories.0.slug', 'thuc-an-hat')
            ->assertJsonPath('data.filters.brands.0.slug', 'royal-canin')
            ->assertJsonPath('data.pagination.current_page', 1);
    }
}
