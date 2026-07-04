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
        $variant = ProductVariant::create([
            ...$attributes,
            'sku' => $attributes['sku'] ?? 'TEST-'.Str::upper(Str::random(12)),
        ]);

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
