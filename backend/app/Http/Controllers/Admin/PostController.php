<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id', 'all');
        $authorId = $request->input('author_id', 'all');
        $status = $request->input('status', 'all');
        $sort = $request->input('sort', 'latest');
        $allowedSorts = ['latest', 'oldest', 'most_viewed', 'most_commented'];

        // Chỉ chấp nhận các giá trị sắp xếp mà giao diện hỗ trợ.
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'latest';

        $postsQuery = Blog::query()
            ->with(['category:id,name,slug', 'author:id,name'])
            ->withCount('comments')
            ->when($search, function ($query, string $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($cat) => $cat->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($categoryId !== 'all', fn ($query) => $query->where('blog_category_id', $categoryId))
            ->when($authorId !== 'all', fn ($query) => $query->where('user_id', $authorId))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('status', $status));

        match ($sort) {
            'oldest' => $postsQuery->orderBy('created_at')->orderBy('id'),
            'most_viewed' => $postsQuery->orderByDesc('view_count')->orderByDesc('created_at')->orderByDesc('id'),
            'most_commented' => $postsQuery->orderByDesc('comments_count')->orderByDesc('created_at')->orderByDesc('id'),
            default => $postsQuery->orderByDesc('created_at')->orderByDesc('id'),
        };

        $posts = $postsQuery
            ->paginate(10)
            ->withQueryString();

        $categories = BlogCategory::orderBy('name')->get(['id', 'name']);
        $authors = User::whereIn('id', Blog::select('user_id'))->orderBy('name')->get(['id', 'name']);

        $totalCount = Blog::count();
        $publishedCount = Blog::where('status', 'active')->count();
        $draftCount = Blog::where('status', 'inactive')->count();
        $totalViews = (int) Blog::sum('view_count');

        return view('admin.posts.index', compact(
            'posts',
            'categories',
            'authors',
            'search',
            'categoryId',
            'authorId',
            'status',
            'sort',
            'totalCount',
            'publishedCount',
            'draftCount',
            'totalViews'
        ));
    }

    public function updateStatus(Request $request, Blog $post)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $post->update(['status' => $validated['status']]);

        $message = $validated['status'] === 'inactive'
            ? 'Đã chuyển bài viết về bản nháp.'
            : 'Đã xuất bản bài viết.';

        return back()->with('success', $message);
    }

    public function create()
    {
        $categories = BlogCategory::where('status', 'active')->get();
        $post = new Blog();

        return view('admin.posts.create', compact('categories', 'post'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255', 'unique:blogs,title'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:blogs,slug'],
            'blog_category_id' => ['required', 'exists:blog_categories,id'],
            'description' => ['required', 'string', 'max:500'],
            'meta_description' => ['nullable', 'string', 'max:180'],
            'focus_keyword' => ['nullable', 'string', 'max:120'],
            'secondary_keywords' => ['nullable', 'string', 'max:700'],
            'search_intent' => ['nullable', 'in:informational,commercial,transactional,navigational'],
            'content' => ['required', 'string'],
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'cover_alt' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề bài viết.',
            'title.unique' => 'Tiêu đề bài viết này đã tồn tại.',
            'slug.regex' => 'Đường dẫn chỉ gồm chữ thường không dấu, số và dấu gạch ngang.',
            'slug.unique' => 'Đường dẫn này đã được dùng cho một bài viết khác.',
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

        $slug = $this->resolveSlug($request->input('slug'), $request->input('title'));

        $post = Blog::create([
            'blog_category_id' => $request->input('blog_category_id'),
            'user_id' => auth()->id() ?: 1, // Fallback
            'title' => $request->input('title'),
            ...$this->seoPayload($request),
            'slug' => $slug,
            'description' => $request->input('description'),
            'content' => $request->input('content'),
            'view_count' => 0,
            'image' => $imagePath,
            'status' => $request->input('status'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đã thêm bài viết mới thành công.',
                'data' => [
                    'id' => $post->id,
                    'slug' => $post->slug,
                    'edit_url' => route('admin.posts.edit', $post),
                ],
            ], 201);
        }

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
            'seo_title' => ['nullable', 'string', 'max:70'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:blogs,slug,' . $post->id],
            'blog_category_id' => ['required', 'exists:blog_categories,id'],
            'description' => ['required', 'string', 'max:500'],
            'meta_description' => ['nullable', 'string', 'max:180'],
            'focus_keyword' => ['nullable', 'string', 'max:120'],
            'secondary_keywords' => ['nullable', 'string', 'max:700'],
            'search_intent' => ['nullable', 'in:informational,commercial,transactional,navigational'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'cover_alt' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề bài viết.',
            'title.unique' => 'Tiêu đề bài viết này đã tồn tại.',
            'slug.regex' => 'Đường dẫn chỉ gồm chữ thường không dấu, số và dấu gạch ngang.',
            'slug.unique' => 'Đường dẫn này đã được dùng cho một bài viết khác.',
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

        $slug = $this->resolveSlug($request->input('slug'), $request->input('title'), $post->id);

        $post->update([
            'blog_category_id' => $request->input('blog_category_id'),
            'title' => $request->input('title'),
            ...$this->seoPayload($request),
            'slug' => $slug,
            'description' => $request->input('description'),
            'content' => $request->input('content'),
            'image' => $imagePath,
            'status' => $request->input('status'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đã cập nhật bài viết thành công.',
                'data' => ['id' => $post->id, 'slug' => $post->slug],
            ]);
        }

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

    /**
     * Sinh slug hợp lệ cho cột `blogs.slug` (unique trong ERD).
     * Ưu tiên slug admin tự nhập, nếu bỏ trống thì lấy từ tiêu đề.
     * Trùng slug sẽ được thêm hậu tố -2, -3... thay vì lỗi ràng buộc CSDL.
     */
    private function resolveSlug(?string $slug, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $title) ?: 'bai-viet';
        $candidate = $base;
        $suffix = 2;

        while (
            Blog::where('slug', $candidate)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function seoPayload(Request $request): array
    {
        $keywords = collect(explode(',', (string) $request->input('secondary_keywords')))
            ->map(fn (string $keyword) => trim($keyword))
            ->filter()
            ->unique(fn (string $keyword) => mb_strtolower($keyword))
            ->take(6)
            ->values()
            ->all();

        return [
            'seo_title' => $this->cleanSeoValue($request->input('seo_title')),
            'meta_description' => $this->cleanSeoValue($request->input('meta_description')),
            'focus_keyword' => $this->cleanSeoValue($request->input('focus_keyword')),
            'secondary_keywords' => $keywords ?: null,
            'search_intent' => $request->input('search_intent') ?: null,
            'cover_alt' => $this->cleanSeoValue($request->input('cover_alt')),
        ];
    }

    private function cleanSeoValue(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
