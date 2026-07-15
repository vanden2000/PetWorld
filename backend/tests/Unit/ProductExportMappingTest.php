<?php

namespace Tests\Unit;

use App\Exports\ProductsSheet;
use App\Exports\ProductVariantsSheet;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantType;
use App\Models\VariantValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

class ProductExportMappingTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_product_sheet_maps_effective_price_stock_and_safe_text(): void
    {
        $product = (new Product)->forceFill([
            'id' => 10,
            'name' => '=Unsafe product',
            'slug' => 'unsafe-product',
            'short_description' => '<b>Mô tả ngắn</b>',
            'status' => 'active',
        ]);
        $product->setRelation('category', (new Category)->forceFill(['name' => 'Thức ăn']));
        $product->setRelation('brand', (new Brand)->forceFill(['name' => 'PetWorld']));
        $product->setRelation('variants', new Collection([
            (new ProductVariant)->forceFill(['price' => 120000, 'sale_price' => 99000, 'quantity' => 8]),
            (new ProductVariant)->forceFill(['price' => 180000, 'sale_price' => null, 'quantity' => 2]),
        ]));

        $row = (new ProductsSheet(Mockery::mock(Builder::class)))->map($product);

        $this->assertSame("'=Unsafe product", $row[2]);
        $this->assertSame(99000.0, $row[6]);
        $this->assertSame(180000.0, $row[7]);
        $this->assertSame(2, $row[8]);
        $this->assertSame(10, $row[9]);
        $this->assertSame('Đang hiển thị', $row[10]);
        $this->assertSame('Mô tả ngắn', $row[11]);
    }

    public function test_variant_sheet_maps_attributes_prices_and_status(): void
    {
        $type = (new VariantType)->forceFill(['id' => 3, 'name' => 'Khối lượng']);
        $value = (new VariantValue)->forceFill([
            'id' => 4,
            'variant_type_id' => 3,
            'value' => '1kg',
        ]);
        $value->setRelation('variantType', $type);

        $product = (new Product)->forceFill(['id' => 10, 'name' => 'Pate mèo']);
        $variant = (new ProductVariant)->forceFill([
            'id' => 20,
            'product_id' => 10,
            'sku' => '@PATE-001',
            'price' => 120000,
            'sale_price' => 99000,
            'quantity' => 8,
            'status' => 'inactive',
        ]);
        $variant->setRelation('product', $product);
        $variant->setRelation('variantValues', new Collection([$value]));

        $row = (new ProductVariantsSheet(Mockery::mock(Builder::class)))->map($variant);

        $this->assertSame('Khối lượng: 1kg', $row[4]);
        $this->assertSame("'@PATE-001", $row[5]);
        $this->assertSame(120000.0, $row[6]);
        $this->assertSame(99000.0, $row[7]);
        $this->assertSame(99000.0, $row[8]);
        $this->assertSame(8, $row[9]);
        $this->assertSame('Đã ẩn', $row[10]);
    }
}
