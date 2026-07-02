<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->validateIndexRequest($request);

        $query = $this->baseProductQuery($this->authenticatedUserId($request));

        $this->applyFilters($query, $request);
        $this->applySorting($query, $request->query('sort', 'newest'));

        $products = $query->paginate(
            perPage: $this->perPage($request),
            page: $request->integer('page', 1),
        );

        return response()->json([
            'data' => [
                'breadcrumb' => [
                    ['label' => 'Trang chủ', 'url' => '/'],
                    ['label' => 'Cửa hàng', 'url' => '/shop'],
                ],
                'title' => 'Tất cả sản phẩm',
                'total' => $products->total(),
                // Sidebar data comes from formatFilters().
                'filters' => $this->formatFilters(),
                // Sort dropdown data comes from sortOptions().
                'sort_options' => $this->sortOptions(),
                // Product cards come from formatProducts().
                'products' => $this->formatProducts(collect($products->items())),
                // Pagination buttons come from paginationMeta().
                'pagination' => $this->paginationMeta($products),
            ],
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {   
        
        $userId = $this->authenticatedUserId($request);
        $product = $this->baseProductQuery($userId)
            ->with(['images', 'variants.variantType'])
            ->where('slug', $slug)
            ->firstOrFail();

        $this->recordProductView($product);

        return response()->json([
            'data' => [
                'breadcrumb' => [
                    ['label' => 'Trang chủ', 'url' => '/'],
                    ['label' => 'Cửa hàng', 'url' => '/shop'],
                    ['label' => $product->category?->name, 'url' => $product->category ? '/shop?category='.$product->category->slug : null],
                    ['label' => $product->name, 'url' => '/shop/'.$product->slug],
                ],
                'product' => $this->formatProductDetail($product),
                'reviews' => $this->formatReviews($product),
                'related_products' => $this->formatProducts($this->relatedProducts($product, $userId)),
            ],
        ]);
    }

    public function recent(Request $request): JsonResponse
    {
        $request->validate([
            'slugs' => ['nullable', $this->listQueryRule(maxItems: 12, maxItemLength: 150)],
        ], [
            'slugs.*' => 'Danh sách sản phẩm đã xem không hợp lệ.',
        ]);

        // Giới hạn danh sách đầu vào trước khi query để giữ đúng 12 slug mới nhất từ frontend.
        $slugs = array_slice($this->csvValues($request->query('slugs', '')), 0, 12);

        if ($slugs === []) {
            return response()->json(['data' => []]);
        }

        $positions = array_flip($slugs);
        $products = $this->baseProductQuery($this->authenticatedUserId($request))
            ->whereIn('slug', $slugs)
            ->get()
            ->sortBy(fn (Product $product): int => $positions[$product->slug] ?? PHP_INT_MAX)
            ->values();

        return response()->json([
            'data' => $this->formatProducts($products),
        ]);
    }

    private function baseProductQuery(?int $userId = null): Builder
    {
        $query = Product::query()
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

        if ($userId !== null) {
            // Gắn trạng thái wishlist ngay trong query chính để tránh một query cho mỗi sản phẩm.
            $query->withExists([
                'wishlists as is_wishlisted' => fn (Builder $wishlist) => $wishlist->where('user_id', $userId),
            ]);
        }

        return $query;
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $keyword = $this->escapeLikeKeyword(trim((string) $request->query('search')));

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
            $effectivePrice = ProductVariant::effectivePriceExpression();

            $query->whereHas('variants', function (Builder $variant) use ($effectivePrice, $minPrice, $maxPrice): void {
                $variant->where('status', 'active')
                    ->whereRaw("{$effectivePrice} >= ?", [$minPrice]);

                if ($maxPrice !== null) {
                    $variant->whereRaw("{$effectivePrice} <= ?", [$maxPrice]);
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

    private function formatProducts(Collection $products): array
    {
        return $products
            ->map(function (Product $product): array {
                $activeVariants = $product->variants->where('status', 'active');
                $regularPrices = $activeVariants->pluck('price')->map(fn (string $price): float => (float) $price);
                $salePrices = $activeVariants
                    ->filter(fn (ProductVariant $variant): bool => $variant->hasValidSalePrice())
                    ->pluck('sale_price')
                    ->map(fn (string $price): float => (float) $price);
                $effectivePrices = $activeVariants->map(fn (ProductVariant $variant): float => $variant->effectivePrice());

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
                    'is_wishlisted' => (bool) ($product->is_wishlisted ?? false),
                ];
            })
            ->all();
    }

    private function formatProductDetail(Product $product): array
    {
        $card = $this->formatProducts(new Collection([$product]))[0];
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
                        'status' => $variant->variantType->status,
                    ] : null,
                    'price' => (float) $variant->price,
                    'sale_price' => $variant->hasValidSalePrice() ? (float) $variant->sale_price : null,
                    'effective_price' => $variant->effectivePrice(),
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

    private function relatedProducts(Product $product, ?int $userId = null): Collection
    {
        return $this->baseProductQuery($userId)
            ->whereKeyNot($product->id)
            ->where('category_id', $product->category_id)
            ->orderByDesc('view_count')
            ->orderByDesc('id')
            ->limit(4)
            ->get();
    }

    private function recordProductView(Product $product): void
    {
        // Tăng trực tiếp trong database để các lượt xem đồng thời không ghi đè lẫn nhau.
        Product::query()
            ->whereKey($product->getKey())
            ->increment('view_count');

        // Đồng bộ lại model để response trả về bộ đếm mới nhất sau khi tăng.
        $product->view_count = (int) Product::query()
            ->whereKey($product->getKey())
            ->value('view_count');
    }

    private function authenticatedUserId(Request $request): ?int
    {
        // Không tin user_id trên URL; danh tính cá nhân chỉ đến từ Sanctum.
        $user = $request->user('sanctum');

        return $user ? (int) $user->getAuthIdentifier() : null;
    }

    private function validateIndexRequest(Request $request): void
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', $this->listQueryRule(maxItems: 20, maxItemLength: 100)],
            'brand' => ['nullable', $this->listQueryRule(maxItems: 20, maxItemLength: 100)],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => [
                'nullable',
                'numeric',
                'min:0',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if ($request->filled('min_price')
                        && is_numeric($value)
                        && is_numeric($request->query('min_price'))
                        && (float) $value < (float) $request->query('min_price')) {
                        $fail('Giá tối đa phải lớn hơn hoặc bằng giá tối thiểu.');
                    }
                },
            ],
            'sort' => ['nullable', 'string', 'in:newest,price_asc,price_desc,popular,sale,rating'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'between:1,24'],
        ], [
            'search.string' => 'Từ khóa tìm kiếm phải là chuỗi.',
            'search.max' => 'Từ khóa tìm kiếm không được vượt quá 100 ký tự.',
            'min_price.numeric' => 'Giá tối thiểu phải là số.',
            'min_price.min' => 'Giá tối thiểu không được âm.',
            'max_price.numeric' => 'Giá tối đa phải là số.',
            'max_price.min' => 'Giá tối đa không được âm.',
            'sort.in' => 'Kiểu sắp xếp không hợp lệ.',
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải bắt đầu từ 1.',
            'per_page.integer' => 'Số sản phẩm mỗi trang phải là số nguyên.',
            'per_page.between' => 'Số sản phẩm mỗi trang phải từ 1 đến 24.',
        ]);
    }

    private function listQueryRule(int $maxItems, int $maxItemLength): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($maxItemLength, $maxItems): void {
            if (! is_string($value) && ! is_array($value)) {
                $fail("{$attribute} phải là chuỗi hoặc mảng.");

                return;
            }

            $items = is_array($value) ? $value : explode(',', $value);

            if (count($items) > $maxItems) {
                $fail("{$attribute} không được vượt quá {$maxItems} giá trị.");

                return;
            }

            foreach ($items as $item) {
                if (! is_string($item) || mb_strlen(trim($item)) > $maxItemLength) {
                    $fail("Mỗi giá trị của {$attribute} không được vượt quá {$maxItemLength} ký tự.");

                    return;
                }
            }
        };
    }

    private function escapeLikeKeyword(string $keyword): string
    {
        // Không để %, _ và dấu gạch chéo trong input biến thành wildcard của câu LIKE.
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $keyword);
    }

    private function formatFilters(): array
    {
        return [
            'categories' => Category::query()
                ->withCount([
                    'products as product_count' => fn (Builder $products) => $this->onlyStorefrontProducts($products),
                ])
                ->orderBy('id')
                ->get(['id', 'name', 'slug', 'image'])
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image' => $category->image,
                    'product_count' => (int) $category->product_count,
                ])
                ->all(),
            'brands' => Brand::query()
                ->withCount([
                    'products as product_count' => fn (Builder $products) => $this->onlyStorefrontProducts($products),
                ])
                ->orderBy('id')
                ->get(['id', 'name', 'slug', 'image'])
                ->map(fn (Brand $brand): array => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'image' => $brand->image,
                    'product_count' => (int) $brand->product_count,
                ])
                ->all(),
            'price' => [
                'min' => 0,
                'max' => (float) (DB::table('product_variants')
                    ->join('products', 'product_variants.product_id', '=', 'products.id')
                    ->where('product_variants.status', 'active')
                    ->whereNull('product_variants.deleted_at')
                    ->where('products.status', 'active')
                    ->whereNull('products.deleted_at')
                    ->selectRaw('MAX('.ProductVariant::effectivePriceExpression().') as max_price')
                    ->value('max_price') ?? 0),
            ],
        ];
    }

    private function onlyStorefrontProducts(Builder $products): Builder
    {
        // Bộ lọc phải đếm đúng cùng tập sản phẩm mà API danh sách có thể trả về.
        return $products
            ->where('status', 'active')
            ->whereHas('variants', fn (Builder $variants) => $variants->where('status', 'active'));
    }

    private function sortOptions(): array
    {
        return [
            ['label' => 'Mới nhất', 'value' => 'newest'],
            ['label' => 'Giá tăng dần', 'value' => 'price_asc'],
            ['label' => 'Giá giảm dần', 'value' => 'price_desc'],
            ['label' => 'Phổ biến', 'value' => 'popular'],
            ['label' => 'Đang giảm giá', 'value' => 'sale'],
            ['label' => 'Đánh giá cao', 'value' => 'rating'],
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
            ->selectRaw('MIN('.ProductVariant::effectivePriceExpression().')')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->where('product_variants.status', 'active')
            ->whereNull('product_variants.deleted_at');
    }

    private function maxEffectivePriceSubquery(): \Closure
    {
        return fn ($query) => $query
            ->from('product_variants')
            ->selectRaw('MAX('.ProductVariant::effectivePriceExpression().')')
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
            ->where('product_variants.sale_price', '>', 0)
            ->whereColumn('product_variants.sale_price', '<', 'product_variants.price')
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
