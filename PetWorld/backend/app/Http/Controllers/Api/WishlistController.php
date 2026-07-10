<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = $request->user()
            ->wishlistProducts()
            ->select('products.*')
            ->selectSub(function ($query) {
                $query->from('reviews')
                    ->join('order_items', 'reviews.order_item_id', '=', 'order_items.id')
                    ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
                    ->selectRaw('COALESCE(AVG(reviews.rating), 0)')
                    ->whereColumn('product_variants.product_id', 'products.id')
                    ->where('reviews.status', 'approved');
            }, 'rating_average')
            ->selectSub(function ($query) {
                $query->from('reviews')
                    ->join('order_items', 'reviews.order_item_id', '=', 'order_items.id')
                    ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
                    ->selectRaw('COUNT(reviews.id)')
                    ->whereColumn('product_variants.product_id', 'products.id')
                    ->where('reviews.status', 'approved');
            }, 'rating_count')
            ->with(['brand', 'category', 'primaryImage', 'variants'])
            ->withCount('wishlists')
            ->get();

        return response()->json([
            'message' => 'Danh sách sản phẩm yêu thích.',
            'data' => ProductCardResource::collection($products),
        ]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $request->user()->wishlistProducts()->syncWithoutDetaching([
            $product->id,
        ]);

        return response()->json([
            'message' => 'Đã thêm sản phẩm vào yêu thích.',
            'data' => [
                'product_id' => $product->id,
                'is_wishlisted' => true,
            ],
        ]);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $request->user()->wishlistProducts()->detach($product->id);

        return response()->json([
            'message' => 'Đã xóa sản phẩm khỏi yêu thích.',
            'data' => [
                'product_id' => $product->id,
                'is_wishlisted' => false,
            ],
        ]);
    }
}
