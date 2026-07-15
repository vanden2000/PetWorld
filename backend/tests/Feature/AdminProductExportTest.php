<?php

namespace Tests\Feature;

use App\Exports\ProductExport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class AdminProductExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is required for this database test.');
        }

        parent::setUp();
    }

    public function test_guest_and_non_admin_cannot_export_products(): void
    {
        $this->get(route('admin.products.export'))
            ->assertRedirect(route('admin.login'));

        $user = User::create([
            'name' => 'Customer',
            'email' => 'customer@example.test',
            'password' => 'password',
            'role' => 'user',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('admin.products.export'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_filtered_export_uses_shared_filters_and_creates_two_sheets(): void
    {
        Excel::fake();

        [$category, $brand] = $this->createCatalogContext();
        $matchingProduct = $this->createProduct($category, $brand, 'Pate mèo', 'pate-meo', 'active');
        $this->createVariant($matchingProduct, 'PATE-001', 120000, 99000, 8);

        $otherProduct = $this->createProduct($category, $brand, 'Hạt cho chó', 'hat-cho-cho', 'inactive');
        $this->createVariant($otherProduct, 'DOG-001', 250000, null, 3);

        $response = $this->actingAs($this->createAdmin())->get(route('admin.products.export', [
            'scope' => 'filtered',
            'include_variants' => 1,
            'search' => 'PATE-001',
            'category_id' => $category->id,
            'status' => 'active',
        ]));

        $response->assertOk();

        Excel::matchByRegex();
        Excel::assertDownloaded('/^danh-sach-san-pham-\d{2}-\d{2}-\d{4}-\d{4}\.xlsx$/', function (ProductExport $export) use ($matchingProduct): bool {
            $sheets = $export->sheets();

            return count($sheets) === 2
                && $sheets[0]->query()->pluck('products.id')->all() === [$matchingProduct->id]
                && $sheets[1]->query()->pluck('product_variants.product_id')->all() === [$matchingProduct->id];
        });
    }

    public function test_status_scope_overrides_current_filters_and_product_only_has_one_sheet(): void
    {
        Excel::fake();

        [$category, $brand] = $this->createCatalogContext();
        $activeProduct = $this->createProduct($category, $brand, 'Active product', 'active-product', 'active');
        $this->createProduct($category, $brand, 'Hidden product', 'hidden-product', 'inactive');

        $response = $this->actingAs($this->createAdmin())->get(route('admin.products.export', [
            'scope' => 'active',
            'include_variants' => 0,
            'search' => 'does-not-match',
            'status' => 'inactive',
        ]));

        $response->assertOk();

        Excel::matchByRegex();
        Excel::assertDownloaded('/^danh-sach-san-pham-.*\.xlsx$/', function (ProductExport $export) use ($activeProduct): bool {
            $sheets = $export->sheets();

            return count($sheets) === 1
                && $sheets[0]->query()->pluck('products.id')->all() === [$activeProduct->id];
        });
    }

    public function test_empty_export_redirects_with_clear_message(): void
    {
        $this->actingAs($this->createAdmin())
            ->get(route('admin.products.export', [
                'scope' => 'filtered',
                'search' => 'missing-product',
            ]))
            ->assertRedirect(route('admin.products', ['search' => 'missing-product']))
            ->assertSessionHas('error', 'Không có sản phẩm phù hợp để xuất.');
    }

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    private function createCatalogContext(): array
    {
        $category = Category::create([
            'name' => 'Thức ăn',
            'slug' => 'thuc-an',
            'image' => 'category.jpg',
        ]);

        $brand = Brand::create([
            'name' => 'PetWorld',
            'slug' => 'petworld',
            'image' => 'brand.jpg',
        ]);

        return [$category, $brand];
    }

    private function createProduct(Category $category, Brand $brand, string $name, string $slug, string $status): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => $name,
            'slug' => $slug,
            'description' => $name,
            'status' => $status,
        ]);
    }

    private function createVariant(Product $product, string $sku, float $price, ?float $salePrice, int $quantity): ProductVariant
    {
        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'price' => $price,
            'sale_price' => $salePrice,
            'quantity' => $quantity,
            'status' => 'active',
        ]);
    }
}
