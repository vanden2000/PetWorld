<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class KnowledgeArticleController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::in(KnowledgeArticle::categoryKeys())],
            'status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
        ]);
        $search = trim((string) ($filters['search'] ?? ''));
        $articles = KnowledgeArticle::query()
            ->when($search !== '', fn ($query) => $query->where(function ($nested) use ($search) {
                $nested->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('questions', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            }))
            ->when($filters['category'] ?? null, fn ($query, string $category) => $query->where('category', $category))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest('updated_at')->paginate(15)->withQueryString();
        return view('admin.knowledge.index', compact('articles', 'filters'));
    }

    public function create() { return view('admin.knowledge.form', ['article' => new KnowledgeArticle()]); }

    public function store(Request $request)
    {
        $article = KnowledgeArticle::create($this->data($request));
        return redirect()->route('admin.knowledge.edit', $article)->with('success', 'Đã tạo bài kiến thức.');
    }

    public function edit(KnowledgeArticle $article) { return view('admin.knowledge.form', compact('article')); }

    public function update(Request $request, KnowledgeArticle $article)
    {
        $article->update($this->data($request, $article));
        return back()->with('success', 'Đã cập nhật bài kiến thức.');
    }

    private function data(Request $request, ?KnowledgeArticle $article = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', 'in:' . implode(',', KnowledgeArticle::categoryKeys())],
            'content' => ['required', 'string'],
            'questions' => ['nullable', 'array', 'max:10'],
            'questions.*' => ['nullable', 'string', 'max:200'],
            'status' => ['required', 'in:draft,published,archived'],
        ]);
        $data['questions'] = collect($data['questions'] ?? [])
            ->map(fn ($question) => trim((string) $question))
            ->filter()
            ->values()
            ->all();
        $slug = Str::slug($data['title']) ?: 'knowledge';
        $base = $slug; $number = 2;
        while (KnowledgeArticle::query()->where('slug', $slug)->when($article, fn ($query) => $query->whereKeyNot($article->id))->exists()) {
            $slug = $base . '-' . $number;
            $number++;
        }
        $publishedAt = $article?->published_at;
        if ($data['status'] === 'published' && ! $publishedAt) {
            $publishedAt = now();
        }

        return [
            ...$data,
            'slug' => $slug,
            'version' => ($article?->version ?? 0) + 1,
            'published_at' => $publishedAt,
        ];
    }
}
