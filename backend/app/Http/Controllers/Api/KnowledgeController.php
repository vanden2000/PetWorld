<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API public cho bài kiến thức chatbot (chính sách / hướng dẫn).
 *
 * Cùng nguồn dữ liệu `knowledge_articles` mà admin quản lý ở "Kiến thức chatbot"
 * và chatbot tiêu thụ qua ChatbotKnowledgeService — chỉ trả bài đã xuất bản.
 * Giúp một nội dung do admin soạn vừa nuôi chatbot vừa hiển thị ra trang web.
 */
class KnowledgeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'in:' . implode(',', KnowledgeArticle::categoryKeys())],
        ]);

        $query = $this->visibleQuery();

        if ($request->filled('search')) {
            $keyword = $this->escapeLikeKeyword(trim((string) $request->query('search')));
            $query->where(function (Builder $articles) use ($keyword): void {
                $articles->where('title', 'like', "%{$keyword}%")
                    ->orWhere('summary', 'like', "%{$keyword}%")
                    ->orWhere('questions', 'like', "%{$keyword}%")
                    ->orWhere('content', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        $articles = $query->orderByDesc('published_at')->latest('id')->get();

        return response()->json([
            'data' => [
                'breadcrumb' => [
                    ['label' => 'Trang chủ', 'url' => '/'],
                    ['label' => 'Chính sách & hướng dẫn', 'url' => '/chinh-sach'],
                ],
                'title' => 'Chính sách & hướng dẫn',
                'total' => $articles->count(),
                'categories' => $this->formatCategoryFilters($articles),
                'articles' => $articles->map(fn (KnowledgeArticle $article): array => $this->formatArticleCard($article))->all(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $article = $this->visibleQuery()
            ->where('slug', $slug)
            ->firstOrFail();

        $related = $this->visibleQuery()
            ->whereKeyNot($article->id)
            ->where('category', $article->category)
            ->orderByDesc('published_at')
            ->latest('id')
            ->limit(3)
            ->get()
            ->map(fn (KnowledgeArticle $item): array => $this->formatArticleCard($item))
            ->all();

        return response()->json([
            'data' => [
                'breadcrumb' => [
                    ['label' => 'Trang chủ', 'url' => '/'],
                    ['label' => 'Chính sách & hướng dẫn', 'url' => '/chinh-sach'],
                    ['label' => KnowledgeArticle::CATEGORIES[$article->category] ?? $article->category, 'url' => '/chinh-sach?category=' . $article->category],
                    ['label' => $article->title, 'url' => $this->publicUrl($article)],
                ],
                'article' => array_merge($this->formatArticleCard($article), [
                    'content' => $article->content,
                ]),
                'related' => $related,
            ],
        ]);
    }

    public function sitemap(): JsonResponse
    {
        $articles = $this->visibleQuery()
            ->orderBy('id')
            ->get(['id', 'slug', 'category', 'updated_at', 'created_at'])
            ->map(fn (KnowledgeArticle $article): array => [
                'slug' => $article->slug,
                'url' => $this->publicUrl($article),
                'updated_at' => $article->updated_at?->toDateTimeString(),
                'created_at' => $article->created_at?->toDateTimeString(),
            ])
            ->all();

        return response()->json(['data' => ['articles' => $articles]]);
    }

    private function visibleQuery(): Builder
    {
        return KnowledgeArticle::query()->where('status', 'published');
    }

    private function formatArticleCard(KnowledgeArticle $article): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'category' => $article->category,
            'category_label' => KnowledgeArticle::CATEGORIES[$article->category] ?? $article->category,
            'summary' => $article->summary,
            'published_at' => $article->published_at?->toDateTimeString(),
            'updated_at' => $article->updated_at?->toDateTimeString(),
            'url' => $this->publicUrl($article),
        ];
    }

    /**
     * Nhóm "Điều khoản sử dụng" (terms) và "Chính sách bảo mật" (privacy) có
     * URL riêng ở gốc site (giữ URL cũ). Các nhóm khác ở /chinh-sach/{slug}.
     */
    private function publicUrl(KnowledgeArticle $article): string
    {
        return in_array($article->category, ['terms', 'privacy'], true)
            ? '/' . $article->slug
            : '/chinh-sach/' . $article->slug;
    }

    private function formatCategoryFilters($articles): array
    {
        // Đếm số bài published theo category dựa trên kết quả đã truy xuất.
        $counts = [];
        foreach (KnowledgeArticle::CATEGORIES as $value => $label) {
            $counts[$value] = 0;
        }
        foreach ($articles as $article) {
            if (array_key_exists($article->category, $counts)) {
                $counts[$article->category]++;
            }
        }

        $filters = [];
        foreach (KnowledgeArticle::CATEGORIES as $value => $label) {
            if ($counts[$value] > 0) {
                $filters[] = [
                    'value' => $value,
                    'label' => $label,
                    'count' => $counts[$value],
                ];
            }
        }

        return $filters;
    }

    private function escapeLikeKeyword(string $keyword): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $keyword);
    }
}