<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $this->baseProductQuery();

        $this->applyFilters($query, $request);
        $this->applySorting($query, $request->query('sort', 'newest'));

        $products = $query->paginate(
            perPage: $this->perPage($request),
            page: $request->integer('page', 1),
        );

        return response()->json([
            'data' => [
                'breadcrumb' => [
                    ['label' => 'Trang chu', 'url' => '/'],
                    ['label' => 'Cua Hang', 'url' => '/products'],
                ],
                'title' => 'Tat ca san pham',
                'total' => $products->total(),
                // Sidebar data comes from formatFilters().
                'filters' => $this->formatFilters(),
                // Sort dropdown data comes from sortOptions().
                'sort_options' => $this->sortOptions(),
                // Product cards come from formatProducts().
                'products' => $this->formatProducts(collect($products->items()), $request->integer('user_id')),
                // Pagination buttons come from paginationMeta().
                'pagination' => $this->paginationMeta($products),
            ],
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $product = $this->baseProductQuery()
            ->with(['images', 'variants.variantType'])
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'breadcrumb' => [
                    ['label' => 'Trang chu', 'url' => '/'],
                    ['label' => 'Cua Hang', 'url' => '/products'],
                    ['label' => $product->category?->name, 'url' => $product->category ? '/products?category=' . $product->category->slug : null],
                    ['label' => $product->name, 'url' => '/products/' . $product->slug],
                ],
                'product' => $this->formatProductDetail($product, $request->integer('user_id')),
                'reviews' => $this->formatReviews($product),
                'related_products' => $this->formatProducts($this->relatedProducts($product), $request->integer('user_id')),
            ],
        ]);
    }

    private function baseProductQuery(): Builder
    {
        return Product::query()
            ->select('products.*')
            ->selectSub($this->minEffectivePriceSubquery(), 'min_effective_price')
            ->selectSub($this->maxEffectivePriceSubquery(), 'max_effective_price')
            ->selectSub($this->saleVariantCountSubquery(), 'sale_variant_count')
            ->selectSub($this->ratingAverageSubquery(), 'rating_average')
            ->selectSub($this->ratingCountSubquery(), 'rating_count')
            ->with(['brand', 'category', 'primaryImage', 'variants'])
            ->withCount('wishlists')
            ->where('status', 'active')
            ->whereHas('variants', fn (Builder $query) => $query->where('status', 'active'));
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $keyword = trim((string) $request->query('search'));

            $query->where(function (Builder $query) use ($keyword): void {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhereHas('brand', fn (Builder $brand) => $brand->where('name', 'like', "%{$keyword}%"))
                    ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', "%{$keyword}%"));
            });
        }

        if ($request->filled('category')) {
            $categorySlugs = $this->csvValues($request->query('category'));

            $query->whereHas('category', fn (Builder $category) => $category->whereIn('slug', $categorySlugs));
        }

        if ($request->filled('brand')) {
            $brandSlugs = $this->csvValues($request->query('brand'));

            $query->whereHas('brand', fn (Builder $brand) => $brand->whereIn('slug', $brandSlugs));
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $minPrice = $request->filled('min_price') ? (float) $request->query('min_price') : 0;
            $maxPrice = $request->filled('max_price') ? (float) $request->query('max_price') : null;

            $query->whereHas('variants', function (Builder $variant) use ($minPrice, $maxPrice): void {
                $variant->where('status', 'active')
                    ->whereRaw('COALESCE(sale_price, price) >= ?', [$minPrice]);

                if ($maxPrice !== null) {
                    $variant->whereRaw('COALESCE(sale_price, price) <= ?', [$maxPrice]);
                }
            });
        }
    }

    private function applySorting(Builder $query, mixed $sort): void
    {
        match ($sort) {
            'price_asc' => $query->orderBy('min_effective_price')->orderByDesc('id'),
            'price_desc' => $query->orderByDesc('max_effective_price')->orderByDesc('id'),
            'popular' => $query->orderByDesc('view_count')->orderByDesc('id'),
            'sale' => $query->orderByDesc('sale_variant_count')->orderByDesc('id'),
            'rating' => $query->orderByDesc('rating_average')->orderByDesc('rating_count')->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };
    }

    private function formatProducts(Collection $products, int $userId = 0): array
    {
        return $products
            ->map(function (Product $product) use ($userId): array {
                $activeVariants = $product->variants->where('status', 'active');
                $regularPrices = $activeVariants->pluck('price')->map(fn (string $price): float => (float) $price);
                $salePrices = $activeVariants->whereNotNull('sale_price')->pluck('sale_price')->map(fn (string $price): float => (float) $price);
                $effectivePrices = $activeVariants->map(fn ($variant): float => (float) ($variant->sale_price ?? $variant->price));

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'image' => $product->primaryImage?->image_url,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                        'slug' => $product->category->slug,
                    ] : null,
                    'brand' => $product->brand ? [
                        'id' => $product->brand->id,
                        'name' => $product->brand->name,
                        'slug' => $product->brand->slug,
                    ] : null,
                    'rating' => [
                        'average' => round((float) $product->rating_average, 1),
                        'count' => (int) $product->rating_count,
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
                    'wishlist_count' => (int) $product->wishlists_count,
                    'is_wishlisted' => $userId > 0
                        ? $product->wishlists()->where('user_id', $userId)->exists()
                        : false,
                ];
            })
            ->all();
    }

    private function formatProductDetail(Product $product, int $userId = 0): array
    {
        $card = $this->formatProducts(new Collection([$product]), $userId)[0];
        $activeVariants = $product->variants->where('status', 'active');

        return array_merge($card, [
            'description' => $product->description,
            'view_count' => $product->view_count,
            'images' => $product->images
                ->sortByDesc('is_primary')
                ->values()
                ->map(fn ($image): array => [
                    'id' => $image->id,
                    'image_url' => $image->image_url,
                    'is_primary' => (bool) $image->is_primary,
                ])
                ->all(),
            'variants' => $activeVariants
                ->values()
                ->map(fn ($variant): array => [
                    'id' => $variant->id,
                    'name' => $variant->variant_name,
                    'type' => $variant->variantType ? [
                        'id' => $variant->variantType->id,
                        'name' => $variant->variantType->name,
                    ] : null,
                    'price' => (float) $variant->price,
                    'sale_price' => $variant->sale_price !== null ? (float) $variant->sale_price : null,
                    'effective_price' => (float) ($variant->sale_price ?? $variant->price),
                    'quantity' => $variant->quantity,
                    'status' => $variant->status,
                ])
                ->all(),
        ]);
    }

    private function formatReviews(Product $product): array
    {
        return Review::query()
            ->with(['user', 'orderItem.productVariant'])
            ->where('status', 'approved')
            ->whereHas('orderItem.productVariant', fn (Builder $query) => $query->where('product_id', $product->id))
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Review $review): array => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at?->toDateTimeString(),
                'user' => $review->user ? [
                    'id' => $review->user->id,
                    'name' => $review->user->name,
                    'avatar' => $review->user->avatar,
                ] : null,
                'variant' => $review->orderItem?->productVariant ? [
                    'id' => $review->orderItem->productVariant->id,
                    'name' => $review->orderItem->productVariant->variant_name,
                ] : null,
            ])
            ->all();
    }

    private function relatedProducts(Product $product): Collection
    {
        return $this->baseProductQuery()
            ->whereKeyNot($product->id)
            ->where('category_id', $product->category_id)
            ->orderByDesc('view_count')
            ->orderByDesc('id')
            ->limit(4)
            ->get();
    }

    private function formatFilters(): array
    {
        return [
            'categories' => Category::query()
                ->orderBy('id')
                ->get(['id', 'name', 'slug', 'image'])
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image' => $category->image,
                    'product_count' => Product::where('category_id', $category->id)->where('status', 'active')->count(),
                ])
                ->all(),
            'brands' => Brand::query()
                ->orderBy('id')
                ->get(['id', 'name', 'slug', 'image'])
                ->map(fn (Brand $brand): array => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'image' => $brand->image,
                    'product_count' => Product::where('brand_id', $brand->id)->where('status', 'active')->count(),
                ])
                ->all(),
            'price' => [
                'min' => 0,
                'max' => (float) (DB::table('product_variants')
                    ->where('status', 'active')
                    ->whereNull('deleted_at')
                    ->selectRaw('MAX(COALESCE(sale_price, price)) as max_price')
                    ->value('max_price') ?? 0),
            ],
        ];
    }

    private function sortOptions(): array
    {
        return [
            ['label' => 'Moi nhat', 'value' => 'newest'],
            ['label' => 'Gia tang dan', 'value' => 'price_asc'],
            ['label' => 'Gia giam dan', 'value' => 'price_desc'],
            ['label' => 'Pho bien', 'value' => 'popular'],
            ['label' => 'Dang giam gia', 'value' => 'sale'],
            ['label' => 'Danh gia cao', 'value' => 'rating'],
        ];
    }

    private function paginationMeta(LengthAwarePaginator $products): array
    {
        return [
            'current_page' => $products->currentPage(),
            'per_page' => $products->perPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
            'from' => $products->firstItem(),
            'to' => $products->lastItem(),
        ];
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 8), 1), 24);
    }

    private function csvValues(mixed $value): array
    {
        $values = is_array($value) ? $value : explode(',', (string) $value);

        return collect($values)
            ->map(fn (mixed $item): string => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function minEffectivePriceSubquery(): \Closure
    {
        return fn ($query) => $query
            ->from('product_variants')
            ->selectRaw('MIN(COALESCE(sale_price, price))')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->where('product_variants.status', 'active')
            ->whereNull('product_variants.deleted_at');
    }

    private function maxEffectivePriceSubquery(): \Closure
    {
        return fn ($query) => $query
            ->from('product_variants')
            ->selectRaw('MAX(COALESCE(sale_price, price))')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->where('product_variants.status', 'active')
            ->whereNull('product_variants.deleted_at');
    }

    private function saleVariantCountSubquery(): \Closure
    {
        return fn ($query) => $query
            ->from('product_variants')
            ->selectRaw('COUNT(*)')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->where('product_variants.status', 'active')
            ->whereNotNull('product_variants.sale_price')
            ->whereNull('product_variants.deleted_at');
    }

    private function ratingAverageSubquery(): \Closure
    {
        return fn ($query) => $query
            ->from('reviews')
            ->join('order_items', 'reviews.order_item_id', '=', 'order_items.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->selectRaw('COALESCE(AVG(reviews.rating), 0)')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->where('reviews.status', 'approved');
    }

    private function ratingCountSubquery(): \Closure
    {
        return fn ($query) => $query
            ->from('reviews')
            ->join('order_items', 'reviews.order_item_id', '=', 'order_items.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->selectRaw('COUNT(reviews.id)')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->where('reviews.status', 'approved');
    }
}
