<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\PetSpecies;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Voucher;
use App\Models\HomeSection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $this->validateRecentProductIds($request);
        // sold_quantity đã được tính trong productCardQuery().
        $recentViewedAccessories = $this->recentViewedAccessories(
            $this->recentProductIds($request),
        );

        // Các khối trang chủ giống nhau với mọi khách nên cache ngắn hạn để giảm truy vấn lặp.
        $data = Cache::remember('api.home.sections.v1', now()->addSeconds(30), function (): array {
            $sectionsConfig = HomeSection::orderBy('order', 'asc')->get();
            $limits = $sectionsConfig->pluck('limit', 'key')->toArray();
            $featuredProductLimit = max(5, min((int) ($limits['featured_products'] ?? 15), 30));

            // Đặt truy vấn trong callback để lần cache hit có thể bỏ qua hoàn toàn phần database.
            $featuredProducts = $this->productCardQuery()
                ->having('sold_quantity', '>', 10)
                ->orderByDesc('sold_quantity')
                ->orderByDesc('products.id')
                ->limit($featuredProductLimit)
                ->get();

            $saleProducts = $this->productCardQuery()
                ->whereHas('variants', function ($query): void {
                    $query->where('status', 'active')
                        ->where('sale_price', '>', 0)
                        ->whereColumn('sale_price', '<', 'price');
                })
                ->orderByDesc('id')
                ->limit($limits['sale_products_tabs'] ?? 8)
                ->get();

            $newProducts = $this->productCardQuery()
                ->orderByDesc('id')
                ->limit($limits['new_products'] ?? 15)
                ->get();

            $newAccessories = $this->productCardQuery()
                ->whereHas('category', fn($query) => $query->whereIn('slug', ['phu-kien', 'do-choi']))
                ->orderByDesc('id')
                ->limit($limits['accessories_promo'] ?? 20)
                ->get();

            return [
                'sections' => $sectionsConfig->map(fn(HomeSection $s): array => [
                    'key' => $s->key,
                    'name' => $s->name,
                    'custom_title' => $s->custom_title,
                    'order' => (int) $s->order,
                    'is_active' => (bool) $s->is_active,
                    'limit' => $s->limit,
                ])->values()->toArray(),
                // Homepage hero slider data comes from formatBanners().
                'banners' => $this->formatBanners(),
                // Main category menu data comes from formatCategories().
                'categories' => $this->formatCategories(),
                // Brand strip data comes from formatBrands().
                'brands' => $this->formatBrands($limits['brands'] ?? 12),
                // The homepage shows no more than two featured pet species.
                'pet_species' => $this->formatFeaturedPetSpecies(),
                // Các phần sản phẩm bên dưới đều sử dụng lại định dạng Product().
                'featured_products' => $this->formatProducts($featuredProducts),
                'sale_products' => $this->formatProducts($saleProducts),
                'new_products' => $this->formatProducts($newProducts),
                'new_accessories' => $this->formatProducts($newAccessories),
                // Category blocks are built in productsByCategories().
                'products_by_categories' => $this->productsByCategories(),
                // Blog cards are built in latestBlogs().
                'latest_blogs' => $this->latestBlogs($limits['latest_blogs'] ?? 3),
                // Đánh giá tốt từ khách hàng đã mua sản phẩm.
                'top_reviews' => $this->featuredReviews($limits['testimonials'] ?? 6),
                // Mã giảm giá còn hiệu lực, dùng cho dải khuyến mãi ở khối phụ kiện.
                'active_vouchers' => $this->activeVouchers(),
            ];
        });


        // Sản phẩm vừa xem phụ thuộc từng trình duyệt nên không đưa vào cache dùng chung.
        $data['recent_viewed_accessories'] = $this->formatProducts($recentViewedAccessories);

        return response()->json([
            'data' => $data,
        ]);
    }

    private function formatBanners(): array
    {
        return Banner::query()
            ->where('status', 'active')
            ->latest('created_at')
            ->get(['id', 'image', 'link', 'description'])
            ->map(fn(Banner $banner): array => [
                'id' => $banner->id,
                'image' => $banner->image,
                'image_version' => $this->bannerImageVersion($banner->image),
                'link' => $banner->link,
                'description' => $banner->description,
            ])
            ->all();
    }

    private function bannerImageVersion(?string $image): ?int
    {
        if (!$image || str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return null;
        }

        $path = ltrim($image, '/');
        $candidates = [
            public_path($path),
            storage_path('app/public/' . $path),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return filemtime($candidate) ?: null;
            }
        }

        return null;
    }

    private function formatCategories(): array
    {
        return Category::query()
            ->orderBy('id')
            ->where('status', 'active')
            ->get(['id', 'name', 'slug', 'image', 'status'])
            ->map(fn(Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'image' => $category->image,
                'status' => $category->status,
            ])
            ->all();
    }

    private function formatBrands(int $limit = 12): array
    {
        return Brand::query()
            ->orderBy('id')
            ->where('status', 'active')
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'image'])
            ->map(fn(Brand $brand): array => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'image' => $brand->image,
            ])
            ->all();
    }


    private function formatFeaturedPetSpecies(): array
    {
        return PetSpecies::query()
            ->where('is_active', true)
            ->where('show_on_home', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(2)
            ->get(['id', 'name', 'slug', 'image', 'background_color', 'sort_order'])
            ->map(fn(PetSpecies $species): array => [
                'id' => $species->id,
                'name' => $species->name,
                'slug' => $species->slug,
                'image' => $species->image,
                'background_color' => $species->background_color,
                'sort_order' => $species->sort_order,
                'show_on_home' => true,
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
                    ->filter(fn(ProductVariant $variant): bool => $variant->hasValidSalePrice())
                    ->pluck('sale_price')
                    ->map(fn(string $price): float => (float) $price);

                $prices = $activeVariants
                    ->pluck('price')
                    ->map(fn(string $price): float => (float) $price);

                $maxDiscountPercentage = ProductVariant::maxDiscountPercentage($activeVariants);

                $displayVariant = $activeVariants
                    ->sortBy(function (ProductVariant $variant): array {
                        return [
                            $variant->hasValidSalePrice() ? 0 : 1,
                            $variant->effectivePrice(),
                        ];
                    })
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
                    'image' => $displayVariant?->image ?: $product->primaryImage?->image_url,
                    'image_alt' => $product->primaryImage?->alt_text ?: $product->name,
                    'default_variant_id' => $displayVariant?->id,
                    // Ngày đăng, dùng cho nhãn "Sản Phẩm Mới" trên thẻ sản phẩm.
                    'created_at' => $product->created_at?->toIso8601String(),
                    'is_new' => $product->isNew(),
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
                        'max_discount_percentage' => $maxDiscountPercentage,
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
            ->where('status', 'active')
            ->get(['id', 'name', 'slug', 'image'])
            ->map(function (Category $category): array {
                // Ưu tiên lấy các sản phẩm đang có giá khuyến mãi hợp lệ trong danh mục này.
                $saleProducts = $this->productCardQuery()
                    ->where('category_id', $category->id)
                    ->whereHas('variants', function (Builder $query): void {
                        $query->where('status', 'active')
                            ->where('sale_price', '>', 0)
                            ->whereColumn('sale_price', '<', 'price');
                    })
                    ->orderByDesc('sold_quantity')
                    ->orderByDesc('view_count')
                    ->orderByDesc('id')
                    ->get();

                // Nếu danh mục có sản phẩm khuyến mãi thì dùng saleProducts; nếu không có sản phẩm khuyến mãi thì dùng sản phẩm bán chạy của danh mục.
                $products = $this->formatProducts($saleProducts);
                $averageDiscountPercent = collect($products)
                    ->map(function (array $product): float {
                        $displayPrice = (float) ($product['price_range']['display'] ?? 0);
                        $compareAtPrice = (float) ($product['price_range']['compare_at'] ?? 0);

                        return $compareAtPrice > $displayPrice && $displayPrice > 0
                            ? (($compareAtPrice - $displayPrice) / $compareAtPrice) * 100
                            : 0;
                    })
                    ->avg() ?? 0;

                return [
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'image' => $category->image,
                    ],
                    'sale_product_count' => count($products),
                    'total_sold_quantity' => (int) $saleProducts->sum('sold_quantity'),
                    'average_discount_percent' => round($averageDiscountPercent, 2),
                    'products' => $products,
                ];
            })
            ->filter(fn(array $group): bool => count($group['products']) > 0)
            ->sort(function (array $left, array $right): int {
                return $right['sale_product_count'] <=> $left['sale_product_count']
                    ?: $right['total_sold_quantity'] <=> $left['total_sold_quantity']
                    ?: $right['average_discount_percent'] <=> $left['average_discount_percent']
                    ?: $left['category']['id'] <=> $right['category']['id'];
            })
            ->values()
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
            ->sortBy(fn(Product $product): int => $positions[$product->id] ?? PHP_INT_MAX)
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
                'variants' => fn($query) => $query->where('status', 'active'),
            ])
            ->where('products.status', 'active')
            ->whereHas('category', fn(Builder $query) => $query->where('status', 'active'))
            ->whereHas('variants', fn(Builder $query) => $query->where('status', 'active'));
    }

    private function ratingAverageSubquery(): \Closure
    {
        return fn($query) => $query
            ->from('reviews')
            ->join('order_items', 'reviews.order_item_id', '=', 'order_items.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->selectRaw('COALESCE(AVG(reviews.rating), 0)')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->where('reviews.status', 'approved');
    }

    private function ratingCountSubquery(): \Closure
    {
        return fn($query) => $query
            ->from('reviews')
            ->join('order_items', 'reviews.order_item_id', '=', 'order_items.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->selectRaw('COUNT(reviews.id)')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->where('reviews.status', 'approved');
    }

    private function soldQuantitySubquery(): \Closure
    {
        return fn($query) => $query
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
            ->map(fn(mixed $id): int => (int) $id)
            ->filter(fn(int $id): bool => $id > 0)
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
                    if (!is_string($value) && !is_array($value)) {
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

    private function latestBlogs(int $limit = 3): array
    {
        return Blog::query()
            ->with(['category', 'author'])
            ->where('status', 'active')
            ->whereHas('category', fn(Builder $category) => $category->where('status', 'active'))
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn(Blog $blog): array => [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'description' => $blog->description,
                'image' => $blog->image,
                'cover_alt' => $blog->cover_alt,
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

    private function featuredReviews(int $limit = 6): array
    {
        return Review::query()
            ->with([
                'user:id,name,email',
                'orderItem.productVariant.product:id,name,slug',
                'orderItem.productVariant.product.primaryImage',
            ])
            ->where('status', 'approved')
            ->where('rating', '>=', 4)
            ->whereHas('orderItem.order', function (Builder $query): void {
                $query->where('order_status', 'completed');
            })
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(function (Review $review): array {
                $product = $review->orderItem?->productVariant?->product;

                return [
                    'id' => $review->id,
                    'user_name' => $review->user?->name ?: 'Khách hàng PetWorld',
                    'rating' => (int) $review->rating,
                    'comment' => $review->comment,
                    'verified_purchase' => true,
                    'created_at' => $review->created_at?->format('d/m/Y'),
                    'product' => $product ? [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'image' => $product->primaryImage?->image_url,
                    ] : null,
                ];
            })
            ->all();
    }

    /**
     * Mã giảm giá đang chạy để hiển thị ở dải khuyến mãi trang chủ.
     * Chỉ lấy voucher khách tự nhập được (is_automatic = false) và còn lượt dùng —
     * voucher tự động không cần quảng bá vì hệ thống tự áp khi đủ điều kiện.
     */
    private function activeVouchers(int $limit = 6): array
    {
        return Voucher::query()
            ->where('status', 'active')
            ->where('is_automatic', false)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderByDesc('discount_value')
            ->limit($limit)
            ->get()
            ->filter(fn (Voucher $voucher): bool => $voucher->canBeApplied((float) $voucher->min_order_value))
            ->map(fn (Voucher $voucher): array => [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'applies_to' => $voucher->applies_to,
                'description' => $voucher->description,
                'discount_value' => (float) $voucher->discount_value,
                'min_order_value' => (float) $voucher->min_order_value,
                'end_date' => $voucher->end_date?->toIso8601String(),
            ])
            ->values()
            ->all();
    }

}
