<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $this->validateRecentProductIds($request);

        $featuredProducts = $this->productCardQuery()
            ->orderByDesc('view_count')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $saleProducts = $this->productCardQuery()
            ->whereHas('variants', function ($query): void {
                $query->where('status', 'active')
                    ->where('sale_price', '>', 0)
                    ->whereColumn('sale_price', '<', 'price');
            })
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $newProducts = $this->productCardQuery()
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $newAccessories = $this->productCardQuery()
            ->whereHas('category', function ($query): void {
                $query->where('slug', 'phu-kien');
            })
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $recentViewedAccessories = $this->recentViewedAccessories(
            $this->recentProductIds($request),
        );

        return response()->json([
            'data' => [
                // Homepage hero slider data comes from formatBanners().
                'banners' => $this->formatBanners(),
                // Main category menu data comes from formatCategories().
                'categories' => $this->formatCategories(),
                // Brand strip data comes from formatBrands().
                'brands' => $this->formatBrands(),
                // Các phần sản phẩm bên dưới đều sử dụng lại định dạng Product().
                'featured_products' => $this->formatProducts($featuredProducts),
                'sale_products' => $this->formatProducts($saleProducts),
                'new_products' => $this->formatProducts($newProducts),
                'new_accessories' => $this->formatProducts($newAccessories),
                'recent_viewed_accessories' => $this->formatProducts($recentViewedAccessories),
                // Category blocks are built in productsByCategories().
                'products_by_categories' => $this->productsByCategories(),
                // Blog cards are built in latestBlogs().
                'latest_blogs' => $this->latestBlogs(),
            ],
        ]);
    }

    private function formatBanners(): array
    {
        return Banner::query()
            ->latest('created_at')
            ->get(['id', 'image', 'link', 'description'])
            ->map(fn (Banner $banner): array => [
                'id' => $banner->id,
                'image' => $banner->image,
                'link' => $banner->link,
                'description' => $banner->description,
            ])
            ->all();
    }

    private function formatCategories(): array
    {
        return Category::query()
            ->orderBy('id')
            ->get(['id', 'name', 'slug', 'image'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'image' => $category->image,
            ])
            ->all();
    }

    private function formatBrands(): array
    {
        return Brand::query()
            ->orderBy('id')
            ->get(['id', 'name', 'slug', 'image'])
            ->map(fn (Brand $brand): array => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'image' => $brand->image,
            ])
            ->all();
    }

    private function formatProducts(Collection $products): array
    {
        return $products
            ->map(function (Product $product): array {
                $activeVariants = $product->variants
                    ->where('status', 'active');

                $salePrices = $activeVariants
                    ->filter(fn (ProductVariant $variant): bool => $variant->hasValidSalePrice())
                    ->pluck('sale_price')
                    ->map(fn (string $price): float => (float) $price);

                $prices = $activeVariants
                    ->pluck('price')
                    ->map(fn (string $price): float => (float) $price);

                $displayVariant = $activeVariants
                    ->sortBy(fn (ProductVariant $variant): float => $variant->effectivePrice())
                    ->first();
                $displayPrice = $displayVariant
                    ? $displayVariant->effectivePrice()
                    : null;
                $compareAtPrice = $displayVariant?->hasValidSalePrice()
                        ? (float) $displayVariant->price
                        : null;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'image' => $product->primaryImage?->image_url,
                    'default_variant_id' => $displayVariant?->id,
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
                    'price_range' => [
                        'min' => $prices->min(),
                        'max' => $prices->max(),
                        'sale_min' => $salePrices->min(),
                        'sale_max' => $salePrices->max(),
                        'has_sale' => $salePrices->isNotEmpty(),
                        'display' => $displayPrice,
                        'compare_at' => $compareAtPrice,
                    ],
                    'stock_quantity' => $activeVariants->sum('quantity'),
                    'rating_average' => round((float) $product->rating_average, 1),
                    'rating_count' => (int) $product->rating_count,
                    'sold_quantity' => (int) $product->sold_quantity,
                ];
            })
            ->all();
    }

    private function productsByCategories(): array
    {
        return Category::query()
            ->orderBy('id')
            ->get(['id', 'name', 'slug', 'image'])
            ->map(function (Category $category): array {
                $products = $this->productCardQuery()
                    ->where('category_id', $category->id)
                    ->orderByDesc('sold_quantity')
                    ->orderByDesc('view_count')
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get();

                return [
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'image' => $category->image,
                    ],
                    'products' => $this->formatProducts($products),
                ];
            })
            ->all();
    }

    private function recentViewedAccessories(array $productIds): Collection
    {
        if ($productIds === []) {
            return new Collection;
        }

        $products = $this->productCardQuery()
            ->whereIn('id', $productIds)
            ->whereHas('category', function ($query): void {
                $query->where('slug', 'phu-kien');
            })
            ->get();

        $positions = array_flip($productIds);

        // Sắp đúng thứ tự người dùng vừa xem trước khi giới hạn; limit trong SQL có thể loại nhầm ID mới nhất.
        return $products
            ->sortBy(fn (Product $product): int => $positions[$product->id] ?? PHP_INT_MAX)
            ->take(8)
            ->values();
    }

    private function productCardQuery(): Builder
    {
        return Product::query()
            ->select('products.*')
            ->selectSub($this->ratingAverageSubquery(), 'rating_average')
            ->selectSub($this->ratingCountSubquery(), 'rating_count')
            ->selectSub($this->soldQuantitySubquery(), 'sold_quantity')
            ->with([
                'brand',
                'category',
                'primaryImage',
                'variants' => fn ($query) => $query->where('status', 'active'),
            ])
            ->where('products.status', 'active')
            ->whereHas('variants', fn (Builder $query) => $query->where('status', 'active'));
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

    private function soldQuantitySubquery(): \Closure
    {
        return fn ($query) => $query
            ->from('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0)')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->where('orders.order_status', 'completed');
    }

    private function recentProductIds(Request $request): array
    {
        $rawIds = $request->query('recent_product_ids', '');
        $ids = is_array($rawIds) ? $rawIds : explode(',', (string) $rawIds);

        return collect($ids)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function validateRecentProductIds(Request $request): void
    {
        $request->validate([
            'recent_product_ids' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) && ! is_array($value)) {
                        $fail('Danh sách sản phẩm đã xem phải là chuỗi hoặc mảng.');

                        return;
                    }

                    $ids = is_array($value) ? $value : explode(',', $value);

                    if (count($ids) > 50) {
                        $fail('Danh sách sản phẩm đã xem không được vượt quá 50 ID.');

                        return;
                    }

                    foreach ($ids as $id) {
                        if (filter_var(trim((string) $id), FILTER_VALIDATE_INT) === false || (int) $id < 1) {
                            $fail('Mỗi ID sản phẩm đã xem phải là số nguyên dương.');

                            return;
                        }
                    }
                },
            ],
        ]);
    }

    private function latestBlogs(): array
    {
        return Blog::query()
            ->with(['category', 'author'])
            ->where('status', 'active')
            ->whereHas('category', fn (Builder $category) => $category->where('status', 'active'))
            ->latest('created_at')
            ->limit(3)
            ->get()
            ->map(fn (Blog $blog): array => [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'description' => $blog->description,
                'image' => $blog->image,
                'view_count' => $blog->view_count,
                'created_at' => $blog->created_at?->toDateTimeString(),
                'category' => $blog->category ? [
                    'id' => $blog->category->id,
                    'name' => $blog->category->name,
                    'slug' => $blog->category->slug,
                ] : null,
                'author' => $blog->author ? [
                    'id' => $blog->author->id,
                    'name' => $blog->author->name,
                ] : null,
            ])
            ->all();
    }
}
