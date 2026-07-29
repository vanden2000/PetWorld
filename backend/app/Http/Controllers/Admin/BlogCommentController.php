<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlogCommentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'blog_id' => ['nullable', 'integer', 'exists:blogs,id'],
            'status' => ['nullable', Rule::in(['visible', 'hidden'])],
        ]);

        $comments = BlogComment::query()
            ->with(['user:id,name,email', 'blog:id,title,slug'])
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($nested) use ($search) {
                $nested->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('blog', fn ($blog) => $blog->where('title', 'like', "%{$search}%"));
            }))
            ->when($filters['blog_id'] ?? null, fn ($query, int $blogId) => $query->where('blog_id', $blogId))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('is_hidden', $status === 'hidden'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $blogOptions = Blog::query()->orderBy('title')->pluck('title', 'id');
        $selectedBlog = ($filters['blog_id'] ?? null) ? $blogOptions->get($filters['blog_id']) : null;

        return view('admin.BlogComments.index', compact('comments', 'filters', 'blogOptions', 'selectedBlog'));
    }

    public function updateVisibility(Request $request, BlogComment $comment): RedirectResponse
    {
        $data = $request->validate([
            'is_hidden' => ['required', 'boolean'],
        ]);

        $comment->update(['is_hidden' => $data['is_hidden']]);

        return back()->with('success', $data['is_hidden']
            ? 'Đã ẩn bình luận khỏi bài viết.'
            : 'Đã hiển thị lại bình luận trên bài viết.');
    }
}
