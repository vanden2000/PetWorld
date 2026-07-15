<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSlugHistory extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'slug',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
