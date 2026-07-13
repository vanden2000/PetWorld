<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'hidden'])],
            'rating' => ['nullable', 'integer', 'between:1,5'],
        ]);

        $reviews = Review::query()
            ->with(['user:id,name,email', 'orderItem:id,order_id,product_variant_id,product_name', 'orderItem.productVariant:id,sku'])
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($nested) use ($search) {
                $nested->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('orderItem', fn ($item) => $item->where('product_name', 'like', "%{$search}%"));
            }))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['rating'] ?? null, fn ($query, int $rating) => $query->where('rating', $rating))
            ->latest()->paginate(10)->withQueryString();

        return view('admin.reviews.index', compact('reviews', 'filters'));
    }

    public function updateStatus(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['pending', 'approved', 'hidden'])]]);
        $review->update(['status' => $data['status']]);
        return back()->with('success', 'Đã cập nhật trạng thái đánh giá.');
    }
}
