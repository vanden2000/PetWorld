<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $blogId = $request->input('blog_id');

        $comments = BlogComment::query()
            ->with(['user:id,name,email', 'blog:id,title,slug'])
            ->when($search, function ($query, string $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('content', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('blog', fn ($blog) => $blog->where('title', 'like', "%{$search}%"));
                });
            })
            ->when($blogId, function ($query) use ($blogId) {
                $query->where('blog_id', $blogId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $selectedBlog = $blogId ? \App\Models\Blog::find($blogId) : null;

        return view('admin.BlogComments.index', compact('comments', 'search', 'blogId', 'selectedBlog'));
    }

    public function destroy(BlogComment $comment): RedirectResponse
    {
        $comment->delete();
        return back()->with('success', 'Đã xóa bình luận bài viết thành công.');
    }
}
