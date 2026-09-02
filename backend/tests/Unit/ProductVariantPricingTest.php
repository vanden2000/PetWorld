<?php

namespace Tests\Unit;

use App\Models\ProductVariant;
use PHPUnit\Framework\TestCase;

class ProductVariantPricingTest extends TestCase
{
    public function test_it_returns_the_highest_valid_discount_among_variants(): void
    {
        $variants = [
            (new ProductVariant)->forceFill(['price' => 200000, 'sale_price' => 180000]),
            (new ProductVariant)->forceFill(['price' => 500000, 'sale_price' => 350000]),
            (new ProductVariant)->forceFill(['price' => 100000, 'sale_price' => null]),
        ];

        $this->assertSame(30, ProductVariant::maxDiscountPercentage($variants));
    }

    public function test_it_ignores_invalid_sale_prices(): void
    {
        $variants = [
            (new ProductVariant)->forceFill(['price' => 200000, 'sale_price' => 200000]),
            (new ProductVariant)->forceFill(['price' => 100000, 'sale_price' => 0]),
        ];

        $this->assertNull(ProductVariant::maxDiscountPercentage($variants));
    }
}
