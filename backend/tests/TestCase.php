<?php

namespace Tests;

use App\Models\ProductVariant;
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

        $variant->variantTypes()->sync(
            collect($options)
                ->mapWithKeys(fn (string $value, int|string $typeId): array => [
                    (int) $typeId => ['value' => $value],
                ])
                ->all(),
        );

        return $variant;
    }
}
