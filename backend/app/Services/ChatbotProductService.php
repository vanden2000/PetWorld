<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ChatbotProductService
{
    /**
     * The catalog remains the source of truth. Advice filters rank tagged
     * products first, while untagged legacy products remain discoverable.
     */
    public function search(array $filters): array
    {
        $filters = $this->normalizeFilters($filters);
        $products = $this->find($filters, $filters['query']);

        if ($products->isEmpty() && $filters['query'] !== '') {
            $products = $this->findByKeywords($filters);
        }

        return $this->format($products, $filters)
            ->sortBy([
                ['match_score', 'desc'],
                ['price.min', 'asc'],
            ])
            ->take($filters['limit'])
            ->values()
            ->all();
    }

    private function find(array $filters, string $query): Collection
    {
        return $this->productsQuery($filters)
            ->when($query !== '', fn (Builder $builder) => $this->matchesCatalogText($builder, $query))
            ->limit($filters['limit'] * 5)
            ->get();
    }

    private function findByKeywords(array $filters): Collection
    {
        $keywords = $this->keywords($filters['query']);
        if ($keywords === []) {
            return collect();
        }

        return $this->productsQuery($filters)
            ->where(function (Builder $builder) use ($keywords): void {
                foreach ($keywords as $keyword) {
                    $this->matchesCatalogText($builder, $keyword, 'or');
                }
            })
            ->limit($filters['limit'] * 5)
            ->get();
    }

    private function productsQuery(array $filters): Builder
    {
        return Product::query()
            ->with([
                'brand:id,name,slug',
                'category:id,name,slug',
                'petSpecies:id,name,slug',
                'primaryImage:id,product_id,image_url,alt_text',
                'variants' => function (HasMany $relation) use ($filters): void {
                    $relation->where('status', 'active')
                        ->when($filters['in_stock'], fn (Builder $builder) => $builder->where('quantity', '>', 0));
                },
            ])
            ->where('status', 'active')
            ->when($filters['pet_type'], function (Builder $builder, string $petType): void {
                $builder->whereHas('petSpecies', fn (Builder $species) => $species->where('slug', $petType));
            })
            ->whereHas('variants', function (Builder $builder) use ($filters): void {
                $builder->where('status', 'active')
                    ->when($filters['in_stock'], fn (Builder $query) => $query->where('quantity', '>', 0));

                $effectivePrice = ProductVariant::effectivePriceExpression();
                if ($filters['min_price'] !== null) {
                    $builder->whereRaw("{$effectivePrice} >= ?", [$filters['min_price']]);
                }
                if ($filters['max_price'] !== null) {
                    $builder->whereRaw("{$effectivePrice} <= ?", [$filters['max_price']]);
                }
            });
    }

    private function normalizeFilters(array $filters): array
    {
        $allowed = [
            'pet_type' => ['cat', 'dog'],
            'life_stage' => ['kitten', 'puppy', 'adult', 'senior', 'all_life_stages'],
            'product_type' => ['dry_food', 'wet_food', 'treat', 'toy', 'litter', 'accessory'],
        ];
        $value = fn (string $key) => in_array($filters[$key] ?? null, $allowed[$key], true) ? $filters[$key] : null;
        $needs = collect($filters['needs'] ?? [])
            ->filter(fn ($need) => in_array($need, ['daily_nutrition', 'picky_eater', 'skin_coat', 'weight_control', 'dental', 'indoor'], true))
            ->unique()->values()->all();

        return [
            'query' => trim((string) ($filters['query'] ?? '')),
            'pet_type' => $value('pet_type'),
            'life_stage' => $value('life_stage'),
            'product_type' => $value('product_type'),
            'needs' => $needs,
            'min_price' => $this->price($filters['min_price'] ?? null),
            'max_price' => $this->price($filters['max_price'] ?? null),
            'in_stock' => ! array_key_exists('in_stock', $filters) || (bool) $filters['in_stock'],
            'limit' => max(1, min((int) ($filters['limit'] ?? 3), 3)),
        ];
    }

    private function format(Collection $products, array $filters): Collection
    {
        return $products->map(function (Product $product) use ($filters): array {
            $variants = $product->variants;
            $effectivePrices = $variants->map(fn (ProductVariant $variant): float => $variant->effectivePrice());
            [$matchScore, $matchReasons] = $this->match($product, $filters);

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
                'pet_species' => $product->petSpecies->pluck('slug')->values()->all(),
                'life_stages' => $product->advice_attributes['life_stages'] ?? [],
                'price' => ['min' => $effectivePrices->min(), 'max' => $effectivePrices->max()],
                'stock_quantity' => $variants->sum('quantity'),
                'match_score' => $matchScore,
                'match_reasons' => $matchReasons,
            ];
        });
    }

    private function match(Product $product, array $filters): array
    {
        $attributes = $product->advice_attributes ?? [];
        $haystack = mb_strtolower(implode(' ', [$product->name, $product->category?->name, $product->short_description]));
        $score = 0;
        $reasons = [];

        $rules = [
            'pet_type' => ['pet_types', 50, ['cat' => 'mèo', 'dog' => 'chó'], 'Phù hợp cho %s'],
            'life_stage' => ['life_stages', 30, ['kitten' => 'mèo con', 'puppy' => 'chó con', 'adult' => 'trưởng thành', 'senior' => 'lớn tuổi'], 'Phù hợp thú cưng %s'],
            'product_type' => ['product_types', 15, ['dry_food' => 'hạt', 'wet_food' => 'pate', 'treat' => 'snack', 'toy' => 'đồ chơi', 'litter' => 'cát', 'accessory' => 'phụ kiện'], 'Đúng nhóm %s'],
        ];

        foreach ($rules as $filter => [$attribute, $points, $terms, $label]) {
            $value = $filters[$filter];
            if (! $value) continue;
            $matched = $filter === 'pet_type'
                ? $product->petSpecies->contains('slug', $value) || in_array($value, $attributes[$attribute] ?? [], true)
                : in_array($value, $attributes[$attribute] ?? [], true)
                    || ($filter === 'life_stage' && in_array('all_life_stages', $attributes[$attribute] ?? [], true));
            $fallback = isset($terms[$value]) && str_contains($haystack, $terms[$value]);
            if ($matched || $fallback) {
                $score += $points;
                $reasons[] = sprintf($label, $terms[$value] ?? $value);
            }
        }

        foreach ($filters['needs'] as $need) {
            if (in_array($need, $attributes['needs'] ?? [], true)) {
                $score += 25;
                $reasons[] = match ($need) {
                    'picky_eater' => 'Có gắn nhu cầu thú cưng kén ăn',
                    'skin_coat' => 'Có gắn nhu cầu da và lông',
                    'weight_control' => 'Có gắn nhu cầu kiểm soát cân nặng',
                    'dental' => 'Có gắn nhu cầu chăm sóc răng miệng',
                    'indoor' => 'Có gắn nhu cầu thú cưng nuôi trong nhà',
                    default => 'Có gắn nhu cầu dinh dưỡng hằng ngày',
                };
            }
        }

        return [$score, array_values(array_unique($reasons))];
    }

    private function matchesCatalogText(Builder $query, string $text, string $boolean = 'and'): void
    {
        $keyword = $this->escapeLike($text);
        $method = $boolean === 'or' ? 'orWhere' : 'where';
        $query->{$method}(function (Builder $builder) use ($keyword): void {
            $builder->where('name', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%")
                ->orWhere('short_description', 'like', "%{$keyword}%")
                ->orWhereHas('brand', fn (Builder $brand) => $brand->where('name', 'like', "%{$keyword}%"))
                ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', "%{$keyword}%"));
        });
    }

    private function keywords(string $query): array
    {
        $stopWords = ['cho', 'với', 'của', 'loại', 'một', 'cần', 'tìm', 'mua', 'tư vấn', 'giúp', 'em', 'bé', 'thức', 'ăn', 'sản phẩm'];
        return collect(preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($query), -1, PREG_SPLIT_NO_EMPTY))
            ->filter(fn (string $word) => mb_strlen($word) > 1 && ! in_array($word, $stopWords, true))
            ->unique()->take(4)->values()->all();
    }

    private function price(mixed $value): ?float
    {
        return is_numeric($value) && (float) $value >= 0 ? (float) $value : null;
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
