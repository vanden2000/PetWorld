<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'blog_id',
        'user_id',
        'content',
    ];

    /**
     * Mối quan hệ với bài viết Blog.
     */
    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }

    /**
     * Mối quan hệ với Người dùng.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
