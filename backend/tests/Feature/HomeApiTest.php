<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is required for this database test.');
        }

        parent::setUp();
    }

    public function test_home_api_returns_homepage_sections(): void
    {
        $category = Category::create([
            'name' => 'Food',
            'slug' => 'food',
            'image' => 'food.jpg',
        ]);

        $accessoryCategory = Category::create([
            'name' => 'Accessories',
            'slug' => 'phu-kien',
            'image' => 'phu-kien.jpg',
        ]);

        $brand = Brand::create([
            'name' => 'PetWorld',
            'slug' => 'petworld',
            'image' => 'petworld.jpg',
        ]);

        $variantType = VariantType::create([
            'name' => 'Weight',
        ]);

        Banner::create([
            'image' => 'banners/home.jpg',
            'link' => '/products',
            'description' => 'Homepage banner',
        ]);

        $author = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $blogCategory = BlogCategory::create([
            'name' => 'Care',
            'slug' => 'care',
            'status' => 'active',
        ]);

        foreach ([1, 2, 3, 4] as $index) {
            Blog::create([
                'blog_category_id' => $blogCategory->id,
                'user_id' => $author->id,
                'title' => "Blog {$index}",
                'slug' => "blog-{$index}",
                'description' => "SEO description {$index}",
                'content' => "<article><p>Content {$index}</p></article>",
                'view_count' => $index,
                'image' => "blogs/blog-{$index}.jpg",
                'status' => 'active',
                'created_at' => now()->addMinutes($index),
                'updated_at' => now()->addMinutes($index),
            ]);
        }

        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Sample Food',
            'slug' => 'sample-food',
            'description' => 'Sample description',
            'view_count' => 15,
            'status' => 'active',
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_url' => 'products/sample-food.jpg',
            'is_primary' => true,
        ]);

        ProductVariant::create([
            'variant_type_id' => $variantType->id,
            'product_id' => $product->id,
            'variant_name' => '1kg',
            'price' => 100000,
            'sale_price' => 90000,
            'quantity' => 5,
            'status' => 'active',
        ]);

        ProductVariant::create([
            'variant_type_id' => $variantType->id,
            'product_id' => $product->id,
            'variant_name' => '2kg',
            'price' => 180000,
            'sale_price' => null,
            'quantity' => 3,
            'status' => 'active',
        ]);

        $accessory = Product::create([
            'category_id' => $accessoryCategory->id,
            'brand_id' => $brand->id,
            'name' => 'Sample Leash',
            'slug' => 'sample-leash',
            'description' => 'Sample accessory',
            'view_count' => 5,
            'status' => 'active',
        ]);

        ProductImage::create([
            'product_id' => $accessory->id,
            'image_url' => 'products/sample-leash.jpg',
            'is_primary' => true,
        ]);

        ProductVariant::create([
            'variant_type_id' => $variantType->id,
            'product_id' => $accessory->id,
            'variant_name' => 'M',
            'price' => 120000,
            'sale_price' => 150000,
            'quantity' => 4,
            'status' => 'active',
        ]);

        $response = $this->getJson("/api/home?recent_product_ids={$product->id},{$accessory->id}");

        $response
            ->assertOk()
            ->assertJsonPath('data.banners.0.image', 'banners/home.jpg')
            ->assertJsonPath('data.categories.0.slug', 'food')
            ->assertJsonPath('data.brands.0.slug', 'petworld')
            ->assertJsonPath('data.featured_products.0.slug', 'sample-food')
            ->assertJsonPath('data.featured_products.0.image', 'products/sample-food.jpg')
            ->assertJsonPath('data.featured_products.0.price_range.min', 100000)
            ->assertJsonPath('data.featured_products.0.price_range.max', 180000)
            ->assertJsonPath('data.featured_products.0.price_range.sale_min', 90000)
            ->assertJsonPath('data.featured_products.0.price_range.display', 90000)
            ->assertJsonPath('data.featured_products.0.price_range.compare_at', 100000)
            ->assertJsonPath('data.featured_products.0.price_range.has_sale', true)
            ->assertJsonPath('data.featured_products.0.rating_average', 0.0)
            ->assertJsonPath('data.featured_products.0.rating_count', 0)
            ->assertJsonPath('data.featured_products.0.stock_quantity', 8)
            ->assertJsonPath('data.sale_products.0.slug', 'sample-food')
            ->assertJsonPath('data.new_products.0.slug', 'sample-leash')
            ->assertJsonPath('data.new_accessories.0.slug', 'sample-leash')
            ->assertJsonPath('data.new_accessories.0.price_range.display', 120000)
            ->assertJsonPath('data.new_accessories.0.price_range.has_sale', false)
            ->assertJsonPath('data.recent_viewed_accessories.0.slug', 'sample-leash')
            ->assertJsonPath('data.products_by_categories.0.category.slug', 'food')
            ->assertJsonPath('data.products_by_categories.0.products.0.slug', 'sample-food')
            ->assertJsonPath('data.products_by_categories.1.category.slug', 'phu-kien')
            ->assertJsonPath('data.products_by_categories.1.products.0.slug', 'sample-leash')
            ->assertJsonPath('data.latest_blogs.0.slug', 'blog-4')
            ->assertJsonMissingPath('data.latest_blogs.0.content')
            ->assertJsonCount(3, 'data.latest_blogs');
    }

    public function test_recent_accessories_are_ordered_before_the_eight_item_limit(): void
    {
        $category = Category::create([
            'name' => 'Accessories',
            'slug' => 'phu-kien',
        ]);
        $brand = Brand::create([
            'name' => 'PetWorld',
            'slug' => 'petworld',
        ]);
        $variantType = VariantType::create([
            'name' => 'Size',
            'status' => 'active',
        ]);

        $productIds = [];

        foreach (range(1, 9) as $index) {
            $product = Product::create([
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => "Accessory {$index}",
                'slug' => "accessory-{$index}",
                'status' => 'active',
            ]);

            ProductVariant::create([
                'variant_type_id' => $variantType->id,
                'product_id' => $product->id,
                'variant_name' => 'Default',
                'price' => 100000 + $index,
                'quantity' => 1,
                'status' => 'active',
            ]);

            $productIds[] = $product->id;
        }

        // ID mới xem nhất được gửi trước; kết quả phải giữ thứ tự này rồi mới lấy tám sản phẩm.
        $recentIds = implode(',', array_reverse($productIds));

        $this->getJson("/api/home?recent_product_ids={$recentIds}")
            ->assertOk()
            ->assertJsonCount(8, 'data.recent_viewed_accessories')
            ->assertJsonPath('data.recent_viewed_accessories.0.slug', 'accessory-9')
            ->assertJsonPath('data.recent_viewed_accessories.7.slug', 'accessory-2');
    }
}
