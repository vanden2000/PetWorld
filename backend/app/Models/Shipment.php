<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'provider',
        'tracking_code',
        'weight_grams',
        'shipping_fee',
        'cod_amount',
        'status',
        'provider_status_code',
        'label_url',
        'provider_payload',
    ];

    protected $casts = [
        'weight_grams' => 'integer',
        'shipping_fee' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'provider_payload' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
