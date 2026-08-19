<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'blog_category_id',
        'user_id',
        'title',
        'seo_title',
        'slug',
        'description',
        'meta_description',
        'focus_keyword',
        'secondary_keywords',
        'search_intent',
        'content',
        'view_count',
        'image',
        'cover_alt',
        'canonical_url',
        'noindex',
        'status',
        'published_at',
    ];

    protected $casts = [
        'view_count' => 'integer',
        'secondary_keywords' => 'array',
        'noindex' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BlogComment::class);
    }
}
