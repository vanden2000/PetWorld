<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'default_user_id',
        'recipient_name',
        'recipient_phone',
        'address_line',
        'ward',
        'district',
        'province',
        'is_default',
        'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Address $address): void {
            $address->default_user_id = $address->is_default
                ? (int) $address->user_id
                : null;

            if (! $address->is_default || ! $address->user_id) {
                return;
            }

            // Khi chọn địa chỉ mặc định mới, bỏ mặc định cũ trước để mỗi user luôn chỉ có một địa chỉ mặc định.
            $otherDefaults = static::query()
                ->where('user_id', $address->user_id)
                ->where('is_default', true);

            if ($address->exists) {
                $otherDefaults->whereKeyNot($address->getKey());
            }

            $otherDefaults->update([
                'is_default' => false,
                'default_user_id' => null,
            ]);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
