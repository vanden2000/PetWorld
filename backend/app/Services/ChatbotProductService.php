<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatbotProductService
{
    /** Keeps the tool payload small enough to stay inside the model context. */
    private const DESCRIPTION_LIMIT = 1200;

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

        // An explicit price sort is what the customer asked for, so it outranks
        // the advice match score. Without one, relevance leads and price only
        // breaks ties.
        $order = match ($filters['sort']) {
            'price_desc' => [['price.max', 'desc'], ['match_score', 'desc']],
            'price_asc' => [['price.min', 'asc'], ['match_score', 'desc']],
            'best_selling' => [['sold_count', 'desc'], ['match_score', 'desc']],
            'newest' => [['published_at', 'desc'], ['match_score', 'desc']],
            'oldest' => [['published_at', 'asc'], ['match_score', 'desc']],
            default => [['match_score', 'desc'], ['price.min', 'asc']],
        };

        return $this->format($products, $filters)
            ->sortBy($order)
            ->take($filters['limit'])
            ->values()
            ->all();
    }

    private function find(array $filters, string $query): Collection
    {
        return $this->productsQuery($filters)
            ->when($query !== '', fn (Builder $builder) => $this->matchesCatalogText($builder, $query))
            ->limit($this->candidateLimit($filters))
            ->get();
    }

    /**
     * A sorted question ranks the whole catalog, so the candidate pool cannot be
     * cut to a multiple of the answer size or the true top row may never load.
     */
    private function candidateLimit(array $filters): int
    {
        return $filters['sort'] !== null ? 50 : $filters['limit'] * 5;
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
            ->select('products.*')
            ->selectSub($this->soldCountSubquery(), 'sold_count')
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
            // Products with no pet_species row must stay discoverable, otherwise a
            // catalog that has not been tagged yet answers every pet_type search
            // with nothing. Tagged mismatches are still excluded.
            ->when($filters['pet_type'], function (Builder $builder, string $petType): void {
                $builder->where(function (Builder $query) use ($petType): void {
                    $query->whereHas('petSpecies', fn (Builder $species) => $species->where('slug', $petType))
                        ->orWhereDoesntHave('petSpecies');
                });
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

    /**
     * Units sold per product, counting every order that was not cancelled.
     * Returned as a subquery so one statement covers the whole result set.
     */
    private function soldCountSubquery(): \Illuminate\Database\Query\Builder
    {
        return DB::table('order_items')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->whereNotIn('orders.order_status', ['cancelled'])
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0)');
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
        $sort = in_array($filters['sort'] ?? null, ['price_asc', 'price_desc', 'best_selling', 'newest', 'oldest'], true) ? $filters['sort'] : null;
        $limit = $sort !== null
            ? max(1, min((int) ($filters['limit'] ?? 1), 3))
            : max(1, min((int) ($filters['limit'] ?? 3), 3));

        return [
            'query' => trim((string) ($filters['query'] ?? '')),
            'pet_type' => $value('pet_type'),
            'life_stage' => $value('life_stage'),
            'product_type' => $value('product_type'),
            'needs' => $needs,
            'sort' => $sort,
            'min_price' => $this->price($filters['min_price'] ?? null),
            'max_price' => $this->price($filters['max_price'] ?? null),
            'in_stock' => ! array_key_exists('in_stock', $filters) || (bool) $filters['in_stock'],
            'limit' => $limit,
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
                'short_description' => Str::limit($this->plainText($product->short_description ?: $product->description), 280),
                // The card only shows the short blurb; the model still needs the
                // full copy so it can answer follow-up questions about a product.
                'description' => Str::limit($this->plainText($product->description), self::DESCRIPTION_LIMIT),
                'keywords' => $this->productKeywords($product),
                'category' => $product->category?->name,
                'brand' => $product->brand?->name,
                'pet_species' => $product->petSpecies->pluck('slug')->values()->all(),
                'life_stages' => $product->advice_attributes['life_stages'] ?? [],
                'price' => ['min' => $effectivePrices->min(), 'max' => $effectivePrices->max()],
                'stock_quantity' => $variants->sum('quantity'),
                'sold_count' => (int) ($product->sold_count ?? 0),
                'published_at' => $product->created_at?->toDateString(),
                'published_at_label' => $product->created_at?->format('d/m/Y'),
                'match_score' => $matchScore,
                'match_reasons' => $matchReasons,
            ];
        });
    }

    private function match(Product $product, array $filters): array
    {
        $attributes = $product->advice_attributes ?? [];
        $haystack = mb_strtolower(implode(' ', [
            $product->name,
            $product->category?->name,
            $product->focus_keyword,
            $this->plainText($product->short_description),
            $this->plainText($product->description),
        ]));
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

        // A product whose own copy mentions what the customer asked for should
        // outrank one that only matched on the structured filters.
        $focusKeyword = mb_strtolower(trim((string) $product->focus_keyword));
        foreach ($this->keywords($filters['query']) as $keyword) {
            if ($focusKeyword !== '' && str_contains($focusKeyword, $keyword)) {
                $score += 20;
                $reasons[] = sprintf('Khớp từ khóa "%s"', $keyword);
            } elseif (str_contains($haystack, $keyword)) {
                $score += 8;
                $reasons[] = sprintf('Mô tả có nhắc đến "%s"', $keyword);
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
                ->orWhere('focus_keyword', 'like', "%{$keyword}%")
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

    /** Collapse rich-text copy into a single readable line for the model. */
    private function plainText(?string $value): string
    {
        $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    /**
     * Keywords the model can echo back to the customer: the admin focus keyword
     * first, then the tagged advice attributes, then the catalog labels.
     */
    private function productKeywords(Product $product): array
    {
        $attributes = $product->advice_attributes ?? [];

        return collect([
            ...preg_split('/[,;|]+/u', (string) $product->focus_keyword, -1, PREG_SPLIT_NO_EMPTY) ?: [],
            ...(array) ($attributes['needs'] ?? []),
            ...(array) ($attributes['product_types'] ?? []),
            $product->brand?->name,
            $product->category?->name,
        ])
            ->map(fn ($keyword) => trim((string) $keyword))
            ->filter()
            ->unique()
            ->take(12)
            ->values()
            ->all();
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
