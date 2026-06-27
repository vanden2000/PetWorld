<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'code',
        'usage_limit',
        'discount_value',
        'description',
        'min_order_value',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'usage_limit' => 'integer',
        'discount_value' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function canBeApplied(float $orderValue, ?CarbonInterface $at = null, ?int $ignoreOrderId = null): bool
    {
        $at ??= now();

        if ($this->status !== 'active'
            || $orderValue < (float) $this->min_order_value
            || $at->lt($this->start_date)
            || $at->gt($this->end_date)) {
            return false;
        }

        // usage_limit = 0 được hiểu là không giới hạn số lượt sử dụng.
        if ((int) $this->usage_limit === 0) {
            return true;
        }

        $usedOrders = $this->orders()
            ->where('order_status', '<>', 'cancelled')
            ->when($ignoreOrderId, fn ($query) => $query->whereKeyNot($ignoreOrderId))
            ->count();

        return $usedOrders < (int) $this->usage_limit;
    }
}
