<?php

namespace Tests;

use App\Models\ProductVariant;
use App\Models\VariantValue;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function createProductVariant(array $attributes, array $options): ProductVariant
    {
        $variantAttributes = [
            ...$attributes,
            'sku' => $attributes['sku'] ?? 'TEST-'.Str::upper(Str::random(12)),
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('product_variants', 'variant_type_id')) {
            $variantAttributes['variant_type_id'] = array_key_first($options) ?? 1;
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('product_variants', 'variant_name')) {
            $variantAttributes['variant_name'] = reset($options) ?: 'Default';
        }

        $variant = new ProductVariant();
        $variant->forceFill($variantAttributes);
        $variant->save();

        $valueIds = collect($options)
            ->map(function (string $value, int|string $typeId): int {
                return VariantValue::firstOrCreate(
                    ['variant_type_id' => (int) $typeId, 'value' => $value],
                )->id;
            });

        $variant->syncVariantValues($valueIds->all());

        return $variant;
    }
}
