<?php

namespace App\Services;

use App\Models\KnowledgeArticle;
use Illuminate\Support\Str;

class ChatbotKnowledgeService
{
    /** Only reviewed, published content may be supplied to the model. */
    public function search(array $filters): array
    {
        $query = trim((string) ($filters['query'] ?? ''));
        $category = in_array($filters['category'] ?? null, ['shipping', 'payment', 'returns', 'voucher', 'contact'], true)
            ? $filters['category']
            : null;

        return KnowledgeArticle::query()
            ->where('status', 'published')
            ->when($category, fn ($builder) => $builder->where('category', $category))
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($nested) use ($query): void {
                    $nested->where('title', 'like', "%{$query}%")
                        ->orWhere('summary', 'like', "%{$query}%")
                        ->orWhere('questions', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                });
            })
            ->orderByDesc('published_at')
            ->limit(3)
            ->get()
            ->map(fn (KnowledgeArticle $article) => [
                'id' => $article->id,
                'title' => $article->title,
                'category' => $article->category,
                'summary' => $article->summary,
                'questions' => $article->questions ?? [],
                'excerpt' => Str::limit(trim(strip_tags($article->summary ?: $article->content)), 700),
                'published_at' => $article->published_at?->toDateString(),
            ])
            ->all();
    }
}
