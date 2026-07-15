<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_item_id',
        'rating',
        'comment',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Review $review): void {
            $rawRating = $review->getAttributes()['rating'] ?? null;

            if (filter_var($rawRating, FILTER_VALIDATE_INT) === false
                || (int) $rawRating < 1
                || (int) $rawRating > 5) {
                throw ValidationException::withMessages([
                    'rating' => 'Điểm đánh giá phải là số nguyên từ 1 đến 5.',
                ]);
            }

            $orderItem = OrderItem::query()
                ->with('order')
                ->find($review->order_item_id);

            if (! $orderItem?->order) {
                throw ValidationException::withMessages([
                    'order_item_id' => 'Sản phẩm trong đơn hàng không tồn tại.',
                ]);
            }

            if ((int) $orderItem->order->user_id !== (int) $review->user_id) {
                throw ValidationException::withMessages([
                    'user_id' => 'Người đánh giá phải là chủ sở hữu đơn hàng.',
                ]);
            }

            if ($orderItem->order->order_status !== 'completed') {
                throw ValidationException::withMessages([
                    'order_item_id' => 'Chỉ sản phẩm thuộc đơn hàng đã hoàn thành mới được đánh giá.',
                ]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
