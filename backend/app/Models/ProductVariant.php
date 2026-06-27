<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'variant_type_id',
        'product_id',
        'variant_name',
        'price',
        'sale_price',
        'quantity',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'quantity' => 'integer',
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

    public function variantType(): BelongsTo
    {
        return $this->belongsTo(VariantType::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
