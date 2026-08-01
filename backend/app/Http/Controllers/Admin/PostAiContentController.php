<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Services\PostAiContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PostAiContentController extends Controller
{
    private const ACTIONS = [
        'generate_post_draft',
        'improve_post_content',
        'rewrite_intro',
        'generate_seo_meta',
        'audit_seo',
    ];

    public function __invoke(Request $request, PostAiContentService $ai): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', 'string', 'in:'.implode(',', self::ACTIONS)],
            'post.title' => ['required', 'string', 'max:255'],
            'post.blog_category_id' => ['nullable', 'integer', 'exists:blog_categories,id'],
            'post.description' => ['nullable', 'string', 'max:500'],
            'post.content' => ['nullable', 'string', 'max:60000'],
            'post.seo_title' => ['nullable', 'string', 'max:70'],
            'post.meta_description' => ['nullable', 'string', 'max:180'],
            'post.focus_keyword' => ['nullable', 'string', 'max:120'],
            'post.secondary_keywords' => ['nullable', 'array', 'max:6'],
            'post.secondary_keywords.*' => ['string', 'max:120'],
            'post.search_intent' => ['nullable', 'in:informational,commercial,transactional,navigational'],
            'post.cover_alt' => ['nullable', 'string', 'max:255'],
            'options.length' => ['nullable', 'in:standard,detailed'],
            'options.tone' => ['nullable', 'in:professional,friendly'],
        ]);

        $categories = BlogCategory::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (BlogCategory $category): array => ['id' => $category->id, 'name' => $category->name])
            ->all();

        try {
            $suggestions = $ai->generate(
                $data['action'],
                $data['post'],
                $categories,
                $data['options'] ?? [],
            );
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json([
                'message' => 'Chưa thể tạo đề xuất AI cho bài viết. Vui lòng thử lại sau.',
            ], $ai->isConfigured() ? 502 : 503);
        }

        return response()->json(['data' => $suggestions]);
    }
}
