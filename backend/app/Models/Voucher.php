<?php

namespace App\Models;

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
}
