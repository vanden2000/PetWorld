<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $featuredProducts = Product::query()
            ->with(['brand', 'category', 'primaryImage', 'variants'])
            ->where('status', 'active')
            ->orderByDesc('view_count')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $saleProducts = Product::query()
            ->with(['brand', 'category', 'primaryImage', 'variants'])
            ->where('status', 'active')
            ->whereHas('variants', function ($query): void {
                $query->where('status', 'active')
                    ->whereNotNull('sale_price');
            })
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $newAccessories = Product::query()
            ->with(['brand', 'category', 'primaryImage', 'variants'])
            ->where('status', 'active')
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
                // Product sections below all reuse formatProducts().
                'featured_products' => $this->formatProducts($featuredProducts),
                'sale_products' => $this->formatProducts($saleProducts),
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
                    ->whereNotNull('sale_price')
                    ->pluck('sale_price')
                    ->map(fn (string $price): float => (float) $price);

                $prices = $activeVariants
                    ->pluck('price')
                    ->map(fn (string $price): float => (float) $price);

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
                    'price_range' => [
                        'min' => $prices->min(),
                        'max' => $prices->max(),
                        'sale_min' => $salePrices->min(),
                        'sale_max' => $salePrices->max(),
                        'has_sale' => $salePrices->isNotEmpty(),
                    ],
                    'stock_quantity' => $activeVariants->sum('quantity'),
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
                $products = Product::query()
                    ->with(['brand', 'category', 'primaryImage', 'variants'])
                    ->where('status', 'active')
                    ->where('category_id', $category->id)
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
            return new Collection();
        }

        return Product::query()
            ->with(['brand', 'category', 'primaryImage', 'variants'])
            ->where('status', 'active')
            ->whereIn('id', $productIds)
            ->whereHas('category', function ($query): void {
                $query->where('slug', 'phu-kien');
            })
            ->orderByDesc('id')
            ->limit(8)
            ->get();
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

    private function latestBlogs(): array
    {
        return Blog::query()
            ->with(['category', 'author'])
            ->where('status', 'active')
            ->latest('created_at')
            ->limit(3)
            ->get()
            ->map(fn (Blog $blog): array => [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'description' => $blog->description,
                'content' => $blog->content,
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
