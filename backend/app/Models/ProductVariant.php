<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'sale_price',
        'quantity',
        'weight_grams',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'quantity' => 'integer',
        'weight_grams' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function hasValidSalePrice(): bool
    {
        if ($this->sale_price === null) {
            return false;
        }

        $salePrice = (float) $this->sale_price;

        return $salePrice > 0 && $salePrice < (float) $this->price;
    }

    public function effectivePrice(): float
    {
        return $this->hasValidSalePrice()
            ? (float) $this->sale_price
            : (float) $this->price;
    }

    public function discountPercentage(): ?int
    {
        if (! $this->hasValidSalePrice()) {
            return null;
        }

        return (int) round((((float) $this->price - (float) $this->sale_price) / (float) $this->price) * 100);
    }

    /** @param iterable<ProductVariant> $variants */
    public static function maxDiscountPercentage(iterable $variants): ?int
    {
        $maxDiscountPercentage = null;

        foreach ($variants as $variant) {
            $discountPercentage = $variant->discountPercentage();

            if ($discountPercentage !== null && ($maxDiscountPercentage === null || $discountPercentage > $maxDiscountPercentage)) {
                $maxDiscountPercentage = $discountPercentage;
            }
        }

        return $maxDiscountPercentage;
    }

    public static function effectivePriceExpression(string $table = 'product_variants'): string
    {
        $prefix = $table !== '' ? $table.'.' : '';

        // SQL dùng cùng quy tắc với effectivePrice() để lọc và sắp xếp không lệch giá hiển thị.
        return "CASE WHEN {$prefix}sale_price > 0 AND {$prefix}sale_price < {$prefix}price "
            ."THEN {$prefix}sale_price ELSE {$prefix}price END";
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variantValues(): BelongsToMany
    {
        return $this->belongsToMany(
            VariantValue::class,
            'product_variant_values',
            'product_variant_id',
            'variant_value_id',
        )->with('variantType')->withTimestamps();
    }

    public function syncVariantValues(array $valueIds): void
    {
        $valueIds = collect($valueIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        $values = VariantValue::query()
            ->whereIn('id', $valueIds)
            ->get(['id', 'variant_type_id']);

        if ($values->count() !== $valueIds->count()) {
            throw new \InvalidArgumentException('Danh sách giá trị biến thể không hợp lệ.');
        }

        if ($values->groupBy('variant_type_id')->contains(fn ($group): bool => $group->count() > 1)) {
            throw new \InvalidArgumentException('Mỗi SKU chỉ được có một giá trị cho mỗi loại biến thể.');
        }

        $this->variantValues()->sync($valueIds->all());
    }

    public function getDisplayNameAttribute(): string
    {
        $values = $this->relationLoaded('variantValues')
            ? $this->variantValues
            : $this->variantValues()->with('variantType')->get();

        return $values
            ->sortBy('variant_type_id')
            ->map(fn (VariantValue $value): string => $value->value)
            ->implode(' - ');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
