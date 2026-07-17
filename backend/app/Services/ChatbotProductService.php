<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ChatbotProductService
{
    /**
     * Search only products that are publicly sellable. This service is the
     * single data boundary between Gemini function calls and the catalog.
     */
    public function search(array $filters): array
    {
        $queryText = trim((string) ($filters['query'] ?? ''));
        $minPrice = $this->price($filters['min_price'] ?? null);
        $maxPrice = $this->price($filters['max_price'] ?? null);
        $inStock = ! array_key_exists('in_stock', $filters) || (bool) $filters['in_stock'];
        $limit = max(1, min((int) ($filters['limit'] ?? 3), 5));

        $products = Product::query()
            ->with([
                'brand:id,name,slug',
                'category:id,name,slug',
                'primaryImage:id,product_id,image_url,alt_text',
                'variants' => function (Builder $query) use ($inStock): void {
                    $query->where('status', 'active')
                        ->when($inStock, fn (Builder $query) => $query->where('quantity', '>', 0));
                },
            ])
            ->where('status', 'active')
            ->whereHas('variants', function (Builder $query) use ($inStock, $minPrice, $maxPrice): void {
                $query->where('status', 'active')
                    ->when($inStock, fn (Builder $query) => $query->where('quantity', '>', 0));

                $effectivePrice = ProductVariant::effectivePriceExpression();

                if ($minPrice !== null) {
                    $query->whereRaw("{$effectivePrice} >= ?", [$minPrice]);
                }

                if ($maxPrice !== null) {
                    $query->whereRaw("{$effectivePrice} <= ?", [$maxPrice]);
                }
            })
            ->when($queryText !== '', function (Builder $query) use ($queryText): void {
                $keyword = $this->escapeLike($queryText);

                $query->where(function (Builder $query) use ($keyword): void {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%")
                        ->orWhere('short_description', 'like', "%{$keyword}%")
                        ->orWhereHas('brand', fn (Builder $brand) => $brand->where('name', 'like', "%{$keyword}%"))
                        ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', "%{$keyword}%"));
                });
            })
            ->limit($limit * 3)
            ->get();

        return $this->format($products)
            ->sortBy('price.min')
            ->take($limit)
            ->values()
            ->all();
    }

    private function format(Collection $products): Collection
    {
        return $products->map(function (Product $product): array {
            $variants = $product->variants;
            $effectivePrices = $variants->map(fn (ProductVariant $variant): float => $variant->effectivePrice());

            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'url' => '/shop/' . $product->slug,
                'image' => $product->primaryImage?->image_url,
                'image_alt' => $product->primaryImage?->alt_text ?: $product->name,
                'short_description' => Str::limit(trim(strip_tags((string) ($product->short_description ?: $product->description))), 280),
                'category' => $product->category?->name,
                'brand' => $product->brand?->name,
                'price' => [
                    'min' => $effectivePrices->min(),
                    'max' => $effectivePrices->max(),
                ],
                'stock_quantity' => $variants->sum('quantity'),
            ];
        });
    }

    private function price(mixed $value): ?float
    {
        if (! is_numeric($value) || (float) $value < 0) {
            return null;
        }

        return (float) $value;
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
