<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogComment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BlogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->validateIndexRequest($request);

        $query = $this->visibleBlogQuery();

        if ($request->filled('search')) {
            $keyword = $this->escapeLikeKeyword(trim((string) $request->query('search')));

            $query->where(function (Builder $blogs) use ($keyword): void {
                $blogs->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category')) {
            $query->whereHas(
                'category',
                fn (Builder $category) => $category->where('slug', $request->query('category')),
            );
        }

        match ($request->query('sort', 'newest')) {
            'popular' => $query->orderByDesc('view_count')->orderByDesc('id'),
            default => $query->latest('created_at')->latest('id'),
        };

        $blogs = $query->paginate(
            perPage: $request->integer('per_page', 9),
            page: $request->integer('page', 1),
        );

        return response()->json([
            'data' => [
                'breadcrumb' => [
                    ['label' => 'Trang chủ', 'url' => '/'],
                    ['label' => 'Tin tức', 'url' => '/news'],
                ],
                'title' => 'Bài viết mới nhất',
                'total' => $blogs->total(),
                'categories' => $this->formatCategoryFilters(),
                'sort_options' => [
                    ['label' => 'Mới nhất', 'value' => 'newest'],
                    ['label' => 'Xem nhiều nhất', 'value' => 'popular'],
                ],
                'blogs' => collect($blogs->items())
                    ->map(fn (Blog $blog): array => $this->formatBlogCard($blog))
                    ->all(),
                'pagination' => $this->paginationMeta($blogs),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $blog = $this->visibleBlogQuery()
            ->where('slug', $slug)
            ->firstOrFail();

        $this->recordBlogView($blog);

        $relatedBlogs = $this->visibleBlogQuery()
            ->whereKeyNot($blog->id)
            ->where('blog_category_id', $blog->blog_category_id)
            ->latest('created_at')
            ->limit(3)
            ->get()
            ->map(fn (Blog $related): array => $this->formatBlogCard($related))
            ->all();

        $comments = $blog->comments()
            ->with('user')
            ->latest()
            ->get()
            ->map(fn (BlogComment $comment): array => [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at?->toDateTimeString(),
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'avatar' => $comment->user->avatar,
                ]
            ])
            ->all();

        return response()->json([
            'data' => [
                'breadcrumb' => [
                    ['label' => 'Trang chủ', 'url' => '/'],
                    ['label' => 'Tin tức', 'url' => '/news'],
                    ['label' => $blog->category->name, 'url' => '/news?category='.$blog->category->slug],
                    ['label' => $blog->title, 'url' => '/news/'.$blog->slug],
                ],
                'blog' => array_merge($this->formatBlogCard($blog), [
                    'content' => $blog->content,
                    'comments' => $comments,
                ]),
                'related_blogs' => $relatedBlogs,
            ],
        ]);
    }

    private function visibleBlogQuery(): Builder
    {
        return Blog::query()
            ->with(['category', 'author'])
            ->where('status', 'active')
            ->whereHas('category', fn (Builder $category) => $category->where('status', 'active'));
    }

    private function formatBlogCard(Blog $blog): array
    {
        return [
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'description' => $blog->description,
            'image' => $blog->image,
            'view_count' => $blog->view_count,
            'created_at' => $blog->created_at?->toDateTimeString(),
            'category' => [
                'id' => $blog->category->id,
                'name' => $blog->category->name,
                'slug' => $blog->category->slug,
            ],
            'author' => $blog->author ? [
                'id' => $blog->author->id,
                'name' => $blog->author->name,
            ] : null,
        ];
    }

    private function formatCategoryFilters(): array
    {
        return BlogCategory::query()
            ->where('status', 'active')
            ->withCount([
                'blogs as blog_count' => fn (Builder $blogs) => $blogs->where('status', 'active'),
            ])
            ->orderBy('id')
            ->get(['id', 'name', 'slug'])
            ->map(fn (BlogCategory $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'blog_count' => (int) $category->blog_count,
            ])
            ->all();
    }

    private function recordBlogView(Blog $blog): void
    {
        // Tăng trực tiếp trong database để các lượt xem đồng thời không ghi đè lẫn nhau.
        Blog::query()->whereKey($blog->getKey())->increment('view_count');

        $blog->view_count = (int) Blog::query()
            ->whereKey($blog->getKey())
            ->value('view_count');
    }

    private function validateIndexRequest(Request $request): void
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'string', 'in:newest,popular'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'between:1,24'],
        ], [
            'search.string' => 'Từ khóa tìm kiếm phải là chuỗi.',
            'search.max' => 'Từ khóa tìm kiếm không được vượt quá 100 ký tự.',
            'category.string' => 'Danh mục bài viết phải là chuỗi.',
            'category.max' => 'Danh mục bài viết không được vượt quá 100 ký tự.',
            'sort.in' => 'Kiểu sắp xếp bài viết không hợp lệ.',
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải bắt đầu từ 1.',
            'per_page.integer' => 'Số bài viết mỗi trang phải là số nguyên.',
            'per_page.between' => 'Số bài viết mỗi trang phải từ 1 đến 24.',
        ]);
    }

    private function paginationMeta(LengthAwarePaginator $blogs): array
    {
        return [
            'current_page' => $blogs->currentPage(),
            'per_page' => $blogs->perPage(),
            'last_page' => $blogs->lastPage(),
            'total' => $blogs->total(),
            'from' => $blogs->firstItem(),
            'to' => $blogs->lastItem(),
        ];
    }

    private function escapeLikeKeyword(string $keyword): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $keyword);
    }

    public function storeComment(Request $request, string $slug): JsonResponse
    {
        $blog = $this->visibleBlogQuery()
            ->where('slug', $slug)
            ->firstOrFail();

        $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'content.required' => 'Nội dung bình luận không được để trống.',
            'content.string' => 'Nội dung bình luận phải là dạng chuỗi.',
            'content.min' => 'Nội dung bình luận phải từ 3 ký tự trở lên.',
            'content.max' => 'Nội dung bình luận không được vượt quá 1000 ký tự.',
        ]);

        $comment = $blog->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $request->input('content'),
        ]);

        $comment->load('user');

        return response()->json([
            'data' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at?->toDateTimeString(),
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'avatar' => $comment->user->avatar,
                ]
            ],
            'message' => 'Gửi bình luận thành công!'
        ], 201);
    }
}
