<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlogCommentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $blogId = $request->input('blog_id');
        $status = $request->input('status', 'pending');
        $allowedStatuses = ['all', 'pending', 'approved', 'hidden', 'deleted'];

        $status = in_array($status, $allowedStatuses, true) ? $status : 'pending';

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
            ->when($status === 'deleted', fn ($query) => $query->onlyTrashed())
            ->when(in_array($status, ['pending', 'approved', 'hidden'], true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $selectedBlog = $blogId ? \App\Models\Blog::find($blogId) : null;
        $commentCounts = BlogComment::query()
            ->when($blogId, fn ($query) => $query->where('blog_id', $blogId))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $deletedCount = BlogComment::onlyTrashed()
            ->when($blogId, fn ($query) => $query->where('blog_id', $blogId))
            ->count();
        $commentCounts->put('deleted', $deletedCount);

        return view('admin.BlogComments.index', compact('comments', 'search', 'blogId', 'status', 'selectedBlog', 'commentCounts'));
    }

    public function updateStatus(Request $request, BlogComment $comment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'hidden'])],
        ]);

        $comment->update(['status' => $data['status']]);

        return back()->with('success', 'Đã cập nhật trạng thái bình luận.');
    }

    public function destroy(BlogComment $comment): RedirectResponse
    {
        $comment->delete();
        return back()->with('success', 'Đã chuyển bình luận vào mục đã xóa.');
    }

    public function restore(int $comment): RedirectResponse
    {
        $deletedComment = BlogComment::onlyTrashed()->findOrFail($comment);
        $deletedComment->restore();

        return back()->with('success', 'Đã khôi phục bình luận.');
    }

    public function forceDestroy(int $comment): RedirectResponse
    {
        $deletedComment = BlogComment::onlyTrashed()->findOrFail($comment);
        $deletedComment->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn bình luận.');
    }
}
