<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SepayTransaction extends Model
{
    protected $fillable = [
        'sepay_id',
        'order_id',
        'gateway',
        'transaction_date',
        'account_number',
        'transfer_type',
        'amount',
        'content',
        'reference_code',
        'raw_payload',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2',
        'raw_payload' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
