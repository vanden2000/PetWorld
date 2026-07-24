<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\User;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $authorId = $request->input('author_id');
        $status = $request->input('status');
        $sort = $request->input('sort', 'newest');

        $posts = Blog::query()
            ->with(['category:id,name,slug', 'author:id,name'])
            ->withCount('comments')
            ->when($search, function ($query, string $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('blog_category_id', $categoryId);
            })
            ->when($authorId, function ($query, $authorId) {
                $query->where('user_id', $authorId);
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($sort === 'oldest', function ($query) {
                $query->oldest();
            })
            ->when($sort === 'popular', function ($query) {
                $query->orderBy('view_count', 'desc');
            })
            ->when($sort === 'newest' || !$sort, function ($query) {
                $query->latest();
            })
            ->paginate(10)
            ->withQueryString();

        $categories = BlogCategory::all();
        $authors = User::select('id', 'name')->get();

        return view('admin.posts.index', compact('posts', 'search', 'categoryId', 'authorId', 'status', 'sort', 'categories', 'authors'));
    }

    public function create()
    {
        $categories = BlogCategory::where('status', 'active')->get();
        return view('admin.posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255', 'unique:blogs,title'],
            'blog_category_id' => ['required', 'exists:blog_categories,id'],
            'description' => ['required', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề bài viết.',
            'title.unique' => 'Tiêu đề bài viết này đã tồn tại.',
            'blog_category_id.required' => 'Vui lòng chọn danh mục bài viết.',
            'blog_category_id.exists' => 'Danh mục bài viết không hợp lệ.',
            'description.required' => 'Vui lòng nhập mô tả ngắn.',
            'content.required' => 'Vui lòng nhập nội dung bài viết.',
            'image.required' => 'Vui lòng tải lên ảnh bìa cho bài viết.',
            'image.file' => 'Ảnh bìa phải là định dạng tệp tin.',
            'image.mimes' => 'Ảnh bìa chỉ hỗ trợ JPG, JPEG, PNG, GIF, WEBP.',
            'image.max' => 'Ảnh bìa không được vượt quá 5MB.',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('blogs', 'public');
            $imagePath = $path; // Lưu dạng 'blogs/filename.ext'
        }

        $slug = Str::slug($request->input('title'));

        Blog::create([
            'blog_category_id' => $request->input('blog_category_id'),
            'user_id' => auth()->id() ?: 1, // Fallback
            'title' => $request->input('title'),
            'slug' => $slug,
            'description' => $request->input('description'),
            'content' => $request->input('content'),
            'view_count' => 0,
            'image' => $imagePath,
            'status' => $request->input('status'),
        ]);

        return redirect()->route('admin.posts')->with('success', 'Đã thêm bài viết mới thành công.');
    }

    public function edit(Blog $post)
    {
        $categories = BlogCategory::where('status', 'active')->get();
        return view('admin.posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, Blog $post)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255', 'unique:blogs,title,' . $post->id],
            'blog_category_id' => ['required', 'exists:blog_categories,id'],
            'description' => ['required', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề bài viết.',
            'title.unique' => 'Tiêu đề bài viết này đã tồn tại.',
            'blog_category_id.required' => 'Vui lòng chọn danh mục bài viết.',
            'blog_category_id.exists' => 'Danh mục bài viết không hợp lệ.',
            'description.required' => 'Vui lòng nhập mô tả ngắn.',
            'content.required' => 'Vui lòng nhập nội dung bài viết.',
            'image.file' => 'Ảnh bìa phải là định dạng tệp tin.',
            'image.mimes' => 'Ảnh bìa chỉ hỗ trợ JPG, JPEG, PNG, GIF, WEBP.',
            'image.max' => 'Ảnh bìa không được vượt quá 5MB.',
        ]);

        $imagePath = $post->image;
        if ($request->hasFile('image')) {
            if ($post->image && !str_contains($post->image, 'http') && \Illuminate\Support\Facades\Storage::disk('public')->exists($post->image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($post->image);
            }
            $path = $request->file('image')->store('blogs', 'public');
            $imagePath = $path;
        }

        $slug = Str::slug($request->input('title'));

        $post->update([
            'blog_category_id' => $request->input('blog_category_id'),
            'title' => $request->input('title'),
            'slug' => $slug,
            'description' => $request->input('description'),
            'content' => $request->input('content'),
            'image' => $imagePath,
            'status' => $request->input('status'),
        ]);

        return redirect()->route('admin.posts')->with('success', 'Đã cập nhật bài viết thành công.');
    }

    public function destroy(Blog $post)
    {
        if ($post->image && !str_contains($post->image, 'http') && \Illuminate\Support\Facades\Storage::disk('public')->exists($post->image)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($post->image);
        }

        $post->comments()->delete();
        $post->delete();

        return redirect()->route('admin.posts')->with('success', 'Đã xóa bài viết thành công.');
    }
}
