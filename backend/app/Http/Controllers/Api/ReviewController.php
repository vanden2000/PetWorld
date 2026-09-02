<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_item_id' => [
                'required',
                'integer',
                Rule::exists('order_items', 'id'),
                Rule::unique('reviews')->where(fn ($query) => $query->where('user_id', $request->user()->id)),
            ],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ], [
            'order_item_id.unique' => 'Bạn đã đánh giá sản phẩm trong đơn hàng này.',
            'rating.between' => 'Điểm đánh giá phải từ 1 đến 5 sao.',
        ]);

        $orderItem = OrderItem::query()
            ->whereKey($data['order_item_id'])
            ->whereHas('order', fn ($query) => $query
                ->where('user_id', $request->user()->id)
                ->where('order_status', 'completed'))
            ->firstOrFail();

        $review = $request->user()->reviews()->create([
            'order_item_id' => $orderItem->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Cảm ơn bạn đã đánh giá sản phẩm.',
            'data' => ['review' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
            ]],
        ], 201);
    }
}
