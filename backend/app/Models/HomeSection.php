<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'custom_title',
        'order',
        'is_active',
        'limit',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'limit' => 'integer',
        'settings' => 'array',
    ];
}
