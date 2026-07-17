<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KnowledgeArticleController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $articles = KnowledgeArticle::query()
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->latest('updated_at')->paginate(15)->withQueryString();
        return view('admin.knowledge.index', compact('articles', 'search'));
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
            'category' => ['required', 'in:shipping,payment,returns,voucher,contact'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
        ]);
        $slug = Str::slug($data['title']) ?: 'knowledge';
        $base = $slug; $number = 2;
        while (KnowledgeArticle::query()->where('slug', $slug)->when($article, fn ($query) => $query->whereKeyNot($article->id))->exists()) {
            $slug = $base . '-' . $number;
            $number++;
        }
        return [
            ...$data,
            'slug' => $slug,
            'version' => ($article?->version ?? 0) + 1,
            'published_at' => $data['status'] === 'published' ? ($article?->published_at ?? now()) : null,
        ];
    }
}
