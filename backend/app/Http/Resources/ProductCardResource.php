<?php

namespace App\Http\Resources;

use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activeVariants = $this->variants->where('status', 'active');
        $regularPrices = $activeVariants
            ->pluck('price')
            ->map(fn (string $price): float => (float) $price);
        $salePrices = $activeVariants
            ->filter(fn (ProductVariant $variant): bool => $variant->hasValidSalePrice())
            ->pluck('sale_price')
            ->map(fn (string $price): float => (float) $price);
        $effectivePrices = $activeVariants
            ->map(fn (ProductVariant $variant): float => $variant->effectivePrice());

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->primaryImage?->image_url,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null,
            'brand' => $this->brand ? [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
                'slug' => $this->brand->slug,
            ] : null,
            'rating' => [
                'average' => round((float) ($this->rating_average ?? 0), 1),
                'count' => (int) ($this->rating_count ?? 0),
            ],
            'price' => [
                'min' => $effectivePrices->min(),
                'max' => $effectivePrices->max(),
                'regular_min' => $regularPrices->min(),
                'regular_max' => $regularPrices->max(),
                'sale_min' => $salePrices->min(),
                'sale_max' => $salePrices->max(),
                'has_sale' => $salePrices->isNotEmpty(),
            ],
            'stock_quantity' => $activeVariants->sum('quantity'),
            'wishlist_count' => (int) ($this->wishlists_count ?? 0),
            'is_wishlisted' => true,
        ];
    }
}
