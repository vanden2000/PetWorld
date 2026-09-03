<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Category;
use App\Models\PetSpecies;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    /**
     * Tìm kiếm thông minh: Phân tích ngữ cảnh loài thú cưng (Mèo/Chó),
     * gợi ý danh mục liên quan, sản phẩm tiêu biểu và bài viết cẩm nang.
     */
    public function smart(Request $request): JsonResponse
    {
        $rawQuery = trim((string) ($request->query('q') ?? $request->query('query') ?? ''));
        $cleanQuery = strip_tags($rawQuery);

        if (mb_strlen($cleanQuery) === 0) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'query' => '',
                    'detected_species' => null,
                    'categories' => $this->getTrendingCategories(),
                    'products' => $this->getTrendingProducts(),
                    'blogs' => $this->getTrendingBlogs(),
                    'total_products' => 0,
                ],
            ]);
        }

        // 1. Phân tích ngữ cảnh loài thú cưng (Cat / Dog)
        $detectedSpecies = $this->detectPetSpecies($cleanQuery);

        // 2. Lấy danh mục liên quan
        $categories = $this->getRelatedCategories($cleanQuery, $detectedSpecies);

        // 3. Lấy sản phẩm tiêu biểu
        [$products, $totalProducts] = $this->searchProducts($cleanQuery, $detectedSpecies);

        // 4. Lấy bài viết cẩm nang liên quan
        $blogs = $this->searchBlogs($cleanQuery, $detectedSpecies);

        return response()->json([
            'status' => 'success',
            'data' => [
                'query' => $cleanQuery,
                'detected_species' => $detectedSpecies ? [
                    'id' => $detectedSpecies->id,
                    'name' => $detectedSpecies->name,
                    'slug' => $detectedSpecies->slug,
                    'icon' => $detectedSpecies->slug === 'cat' ? '🐱' : '🐶',
                ] : null,
                'categories' => $categories,
                'products' => $products,
                'blogs' => $blogs,
                'total_products' => $totalProducts,
            ],
        ]);
    }

    /**
     * Nhận diện loài thú cưng dựa trên từ khóa tiếng Việt có dấu/không dấu
     */
    private function detectPetSpecies(string $query): ?PetSpecies
    {
        if (!Schema::hasTable('pet_species')) {
            return null;
        }

        $normalized = mb_strtolower($this->removeAccents($query));

        // Từ khóa nhận diện Mèo
        $catKeywords = ['meo', 'cat', 'mimi', 'miu', 'meomeo', 'kitten'];
        foreach ($catKeywords as $kw) {
            if (preg_match('/\b' . preg_quote($kw, '/') . '\b/u', $normalized) || str_contains($normalized, $kw)) {
                return PetSpecies::query()->where('slug', 'cat')->first();
            }
        }

        // Từ khóa nhận diện Chó
        $dogKeywords = ['cho', 'dog', 'cun', 'puppy', 'gause', 'cuncun'];
        foreach ($dogKeywords as $kw) {
            if (preg_match('/\b' . preg_quote($kw, '/') . '\b/u', $normalized) || str_contains($normalized, $kw)) {
                return PetSpecies::query()->where('slug', 'dog')->first();
            }
        }

        return null;
    }

    /**
     * Lấy các danh mục liên quan tới loài hoặc từ khóa
     */
    private function getRelatedCategories(string $query, ?PetSpecies $species): array
    {
        $escaped = $this->escapeLike($query);

        $catQuery = Category::query();

        if ($species) {
            $catQuery->whereHas('products', function (Builder $p) use ($species) {
                $p->where('status', 'active')
                    ->whereHas('petSpecies', fn (Builder $s) => $s->where('pet_species.id', $species->id));
            });
        } else {
            $catQuery->where(function (Builder $q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                    ->orWhere('slug', 'like', "%{$escaped}%");
            });
        }

        return $catQuery->withCount(['products as product_count' => function (Builder $p) use ($species) {
                $p->where('status', 'active');
                if ($species) {
                    $p->whereHas('petSpecies', fn (Builder $s) => $s->where('pet_species.id', $species->id));
                }
            }])
            ->having('product_count', '>', 0)
            ->orderByDesc('product_count')
            ->limit(6)
            ->get(['id', 'name', 'slug', 'image'])
            ->map(fn (Category $c): array => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'product_count' => (int) $c->product_count,
            ])
            ->all();
    }

    /**
     * Tìm kiếm sản phẩm thông minh
     */
    private function searchProducts(string $query, ?PetSpecies $species): array
    {
        $escaped = $this->escapeLike($query);

        // Lọc bớt từ khóa loài nếu người dùng gõ từ ghép (ví dụ "pate mèo" -> "pate")
        $refinedKeyword = trim(preg_replace('/\b(mèo|meo|cat|chó|cho|dog|cún|cun)\b/ui', '', $query));
        if (mb_strlen($refinedKeyword) < 2) {
            $refinedKeyword = '';
        }
        $refinedEscaped = $this->escapeLike($refinedKeyword);

        $base = Product::query()
            ->with([
                'primaryImage',
                'category',
                'petSpecies',
                'variants' => fn ($v) => $v->where('status', 'active'),
            ])
            ->where('status', 'active')
            ->whereHas('variants', fn (Builder $v) => $v->where('status', 'active'));

        // Kiểm tra xem từ khóa có phải CHỈ là tên loài (ví dụ "mèo", "meo", "cat", "chó", "cho", "dog") hay không
        $isOnlySpecies = $species && ($refinedKeyword === '');

        if ($isOnlySpecies) {
            // Khách tìm riêng loài Mèo / Chó: Lấy sản phẩm của riêng loài đó
            $base->whereHas('petSpecies', fn (Builder $s) => $s->where('pet_species.id', $species->id));
        } elseif ($species && $refinedKeyword !== '') {
            // Khách tìm từ ghép loài + sản phẩm (ví dụ "hạt mèo", "pate chó"):
            $base->whereHas('petSpecies', fn (Builder $s) => $s->where('pet_species.id', $species->id))
                ->where(function (Builder $q) use ($refinedEscaped) {
                    $q->where('name', 'like', "%{$refinedEscaped}%")
                        ->orWhere('slug', 'like', "%{$refinedEscaped}%")
                        ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', "%{$refinedEscaped}%")->orWhere('slug', 'like', "%{$refinedEscaped}%"))
                        ->orWhereHas('brand', fn (Builder $b) => $b->where('name', 'like', "%{$refinedEscaped}%"));
                });
        } else {
            // Khách tìm từ khóa sản phẩm cụ thể (ví dụ "hat", "hạt", "pate", "sữa tắm", "vòng cổ"):
            // CHỈ tìm trên Tên, Slug, Danh mục, Thương hiệu (TUYỆT ĐỐI không tìm trên trường description dài để tránh trùng từ phụ)
            $base->where(function (Builder $q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                    ->orWhere('slug', 'like', "%{$escaped}%")
                    ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', "%{$escaped}%")->orWhere('slug', 'like', "%{$escaped}%"))
                    ->orWhereHas('brand', fn (Builder $b) => $b->where('name', 'like', "%{$escaped}%"));
            });
        }

        $totalCount = (clone $base)->count();

        // Xếp hạng: Đưa sản phẩm có tên hoặc slug khớp từ khóa lên hàng đầu
        $products = $base->orderByRaw(
            'CASE
                WHEN name LIKE ? THEN 0
                WHEN slug LIKE ? THEN 1
                ELSE 2
            END',
            ["%{$escaped}%", "%{$escaped}%"]
        )
            ->orderByDesc('view_count')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $formatted = $products->map(function (Product $product): array {
            $activeVariants = $product->variants->where('status', 'active');
            $displayVariant = $activeVariants
                ->sortBy(fn (ProductVariant $v): array => [$v->hasValidSalePrice() ? 0 : 1, $v->effectivePrice()])
                ->first();

            $price = (float) ($displayVariant?->price ?? 0);
            $effectivePrice = (float) ($displayVariant?->effectivePrice() ?? 0);
            $hasSale = $displayVariant?->hasValidSalePrice() ?? false;
            $discountPercent = ProductVariant::maxDiscountPercentage($activeVariants);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image' => $product->primaryImage?->image_url ?: asset('image/logo/Special_Offer_1-removebg-preview.png'),
                'price' => $price,
                'sale_price' => $hasSale ? $effectivePrice : null,
                'effective_price' => $effectivePrice,
                'discount_percentage' => $discountPercent,
                'category_name' => $product->category?->name,
                'category_slug' => $product->category?->slug,
                'rating_average' => round((float) $product->rating_average, 1),
            ];
        })->all();

        return [$formatted, $totalCount];
    }

    /**
     * Tìm bài viết blog / cẩm nang liên quan
     */
    private function searchBlogs(string $query, ?PetSpecies $species): array
    {
        $escaped = $this->escapeLike($query);

        $blogQuery = Blog::query()
            ->with('category')
            ->where('status', 'active');

        if ($species) {
            $speciesName = $species->slug === 'cat' ? 'mèo' : 'chó';
            $blogQuery->where(function (Builder $b) use ($speciesName, $escaped) {
                $b->where('title', 'like', "%{$speciesName}%")
                    ->orWhere('description', 'like', "%{$speciesName}%")
                    ->orWhere('title', 'like', "%{$escaped}%")
                    ->orWhere('description', 'like', "%{$escaped}%");
            });
        } else {
            $blogQuery->where(function (Builder $b) use ($escaped) {
                $b->where('title', 'like', "%{$escaped}%")
                    ->orWhere('description', 'like', "%{$escaped}%")
                    ->orWhere('content', 'like', "%{$escaped}%");
            });
        }

        return $blogQuery->orderByDesc('view_count')
            ->orderByDesc('id')
            ->limit(3)
            ->get()
            ->map(fn (Blog $blog): array => [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'image' => $blog->image,
                'category_name' => $blog->category?->name ?? 'Cẩm nang',
                'published_date' => ($blog->published_at ?? $blog->created_at)?->format('d/m/Y'),
            ])
            ->all();
    }

    private function getTrendingCategories(): array
    {
        return Category::query()
            ->withCount(['products as product_count' => fn (Builder $p) => $p->where('status', 'active')])
            ->having('product_count', '>', 0)
            ->orderByDesc('product_count')
            ->limit(5)
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $c): array => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'product_count' => (int) $c->product_count,
            ])
            ->all();
    }

    private function getTrendingProducts(): array
    {
        return Product::query()
            ->with(['primaryImage', 'category', 'variants' => fn ($v) => $v->where('status', 'active')])
            ->where('status', 'active')
            ->whereHas('variants', fn (Builder $v) => $v->where('status', 'active'))
            ->orderByDesc('view_count')
            ->limit(4)
            ->get()
            ->map(fn (Product $p): array => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'image' => $p->primaryImage?->image_url ?: asset('image/logo/Special_Offer_1-removebg-preview.png'),
                'effective_price' => (float) ($p->variants->first()?->effectivePrice() ?? 0),
                'category_name' => $p->category?->name,
            ])
            ->all();
    }

    private function getTrendingBlogs(): array
    {
        return Blog::query()
            ->where('status', 'active')
            ->orderByDesc('view_count')
            ->limit(3)
            ->get(['id', 'title', 'slug', 'image', 'created_at'])
            ->map(fn (Blog $b): array => [
                'id' => $b->id,
                'title' => $b->title,
                'slug' => $b->slug,
                'image' => $b->image,
                'category_name' => 'Cẩm nang',
                'published_date' => $b->created_at?->format('d/m/Y'),
            ])
            ->all();
    }

    private function escapeLike(string $keyword): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $keyword);
    }

    private function removeAccents(string $str): string
    {
        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        ];
        foreach ($unicode as $nonAccent => $accent) {
            $str = preg_replace("/($accent)/i", $nonAccent, $str);
        }
        return $str;
    }
}
