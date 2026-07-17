<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeArticle extends Model
{
    protected $fillable = ['title', 'slug', 'category', 'content', 'status', 'version', 'published_at'];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
