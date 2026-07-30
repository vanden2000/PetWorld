<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PostAiContentService
{
    public function isConfigured(): bool
    {
        $ai = config('services.chatbot');

        return filled($ai['api_key'] ?? null)
            && filled($ai['model'] ?? null)
            && filled($ai['base_url'] ?? null);
    }

    public function generate(string $action, array $post, array $categories, array $options): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('AI provider is not configured.');
        }

        $ai = config('services.chatbot');
        $response = Http::acceptJson()
            ->withToken($ai['api_key'])
            ->timeout((int) $ai['timeout'])
            ->post(rtrim((string) $ai['base_url'], '/').'/chat/completions', [
                'model' => $ai['model'],
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt($action)],
                    ['role' => 'user', 'content' => json_encode([
                        'action' => $action,
                        'post' => $post,
                        'categories' => $categories,
                        'options' => $options,
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
                ],
                'temperature' => 0.35,
                'response_format' => ['type' => 'json_object'],
                ...(($ai['provider'] ?? null) !== 'gemini' ? ['store' => false] : []),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(sprintf(
                'AI provider returned HTTP %d: %s',
                $response->status(),
                mb_substr(trim($response->body()), 0, 500),
            ));
        }

        $content = $response->json('choices.0.message.content');
        $decoded = is_string($content) ? json_decode($this->stripCodeFence($content), true) : null;

        if (! is_array($decoded)) {
            throw new RuntimeException('AI provider returned invalid JSON.');
        }

        return $this->normalize($decoded, $categories);
    }

    private function systemPrompt(string $action): string
    {
        $base = <<<'PROMPT'
You are PetWorld's internal Vietnamese editorial assistant. Reply only with one valid JSON object.
Your suggestions are previews only. Never say a post was saved, published, verified, medically reviewed, or approved.
Use only the supplied draft and context. Do not browse, invent sources, statistics, product facts, veterinary advice, research, or links. If a factual detail is needed but absent, use neutral, useful guidance or add a Vietnamese warning.
All user-facing text must be natural Vietnamese. Write concrete, helpful content; avoid generic filler and keyword stuffing.
For animal health, nutrition, symptoms, medicine, or safety: do not diagnose or promise treatment. Encourage consulting a veterinarian when professional assessment is needed.
Return this exact shape: {"suggestions":{"title":"","description":"","content":"","seo_title":"","meta_description":"","focus_keyword":"","secondary_keywords":[],"search_intent":"","blog_category_id":null,"cover_alt":""},"audit":[{"field":"","level":"info|warning|success","message":""}],"warnings":[""]}.
content may contain only safe HTML tags p,h2,h3,ul,ol,li,strong,em,blockquote. Do not return links, images, tables, scripts, styles, or attributes. blog_category_id must be one ID from categories or null. secondary_keywords contains at most 6 short phrases. Use an empty string, empty array, or null if unsupported.
PROMPT;

        $task = match ($action) {
            'generate_post_draft' => <<<'PROMPT'
Create a complete draft from the post title, focus keyword, search intent, category and any brief already supplied. Write a detailed, reader-first article of about 800-1200 Vietnamese words for detailed length, or 600-900 words for standard length. It must have a direct opening, 3-5 useful H2 sections, and H3 only where it improves clarity. Also propose title, short description (120-160 characters), SEO title (30-60 characters), meta description (120-160 characters), natural focus/secondary keywords, intent, a category ID only if clearly appropriate, and a factual cover-alt suggestion only if the cover image is described in the input. Do not pretend unknown product or medical facts are true.
PROMPT,
            'improve_post_content' => <<<'PROMPT'
Improve the supplied existing article while preserving its actual meaning and verified facts. Return a full replacement content draft, not a summary. Expand thin sections into useful explanations, improve the opening and H2/H3 structure, remove repetition, and keep the intended reader in mind. Do not add claims, sources, statistics, links, or product information that were not supplied. Also return only SEO fields that genuinely need improvement.
PROMPT,
            'rewrite_intro' => <<<'PROMPT'
Rewrite only the opening of the supplied article. Return that opening as content using one or two p tags, plus a concise description if useful. Do not return or rewrite the rest of the article. Make the purpose clear in the first sentence without unsupported claims.
PROMPT,
            'generate_seo_meta' => <<<'PROMPT'
Keep content empty. Propose only SEO title, meta description, focus keyword, up to six secondary keywords, search intent, and a short description. Base every proposal on the actual supplied article, title and category. Do not use clickbait, misleading claims, or keyword stuffing.
PROMPT,
            'audit_seo' => <<<'PROMPT'
Keep every suggestions field empty, null, or an empty array. Return a practical Vietnamese audit of the supplied post. Prioritize missing search intent, weak title/meta, thin or unstructured content, missing keyword context, unsupported health claims, missing cover alt, and readability. Each audit item must have field, level, and a concrete message. Do not claim ranking predictions.
PROMPT,
        };

        return $base."\n\n".$task;
    }

    private function normalize(array $decoded, array $categories): array
    {
        $raw = is_array($decoded['suggestions'] ?? null) ? $decoded['suggestions'] : [];
        $allowedCategoryIds = collect($categories)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $categoryId = filter_var($raw['blog_category_id'] ?? null, FILTER_VALIDATE_INT);

        return [
            'suggestions' => [
                'title' => $this->text($raw['title'] ?? null, 255),
                'description' => $this->text($raw['description'] ?? null, 500),
                'content' => $this->safeHtml($raw['content'] ?? null),
                'seo_title' => $this->text($raw['seo_title'] ?? null, 70),
                'meta_description' => $this->text($raw['meta_description'] ?? null, 180),
                'focus_keyword' => $this->text($raw['focus_keyword'] ?? null, 120),
                'secondary_keywords' => $this->keywords($raw['secondary_keywords'] ?? []),
                'search_intent' => in_array($raw['search_intent'] ?? null, ['informational', 'commercial', 'transactional', 'navigational'], true)
                    ? $raw['search_intent']
                    : null,
                'blog_category_id' => $categoryId && in_array($categoryId, $allowedCategoryIds, true) ? $categoryId : null,
                'cover_alt' => $this->text($raw['cover_alt'] ?? null, 255),
            ],
            'audit' => $this->audit($decoded['audit'] ?? []),
            'warnings' => $this->messages($decoded['warnings'] ?? [], 8),
        ];
    }

    private function safeHtml(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        $html = strip_tags($value, '<p><h2><h3><ul><ol><li><strong><em><blockquote>');
        $html = preg_replace('/<(p|h2|h3|ul|ol|li|strong|em|blockquote)\\b[^>]*>/i', '<$1>', $html) ?? '';

        return mb_substr(trim($html), 0, 60000);
    }

    private function keywords(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn ($keyword) => $this->text($keyword, 120))
            ->filter()
            ->unique(fn (string $keyword) => mb_strtolower($keyword))
            ->take(6)
            ->values()
            ->all();
    }

    private function audit(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(fn ($item) => is_array($item))
            ->map(fn (array $item): array => [
                'field' => $this->text($item['field'] ?? null, 60),
                'level' => in_array($item['level'] ?? null, ['info', 'warning', 'success'], true) ? $item['level'] : 'info',
                'message' => $this->text($item['message'] ?? null, 300),
            ])
            ->filter(fn (array $item) => $item['message'] !== '')
            ->take(16)
            ->values()
            ->all();
    }

    private function messages(mixed $value, int $limit): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn ($message) => $this->text($message, 300))
            ->filter()
            ->unique()
            ->take($limit)
            ->values()
            ->all();
    }

    private function text(mixed $value, int $limit): string
    {
        return is_string($value) ? mb_substr(trim(strip_tags($value)), 0, $limit) : '';
    }

    private function stripCodeFence(string $content): string
    {
        return preg_replace('/^```(?:json)?\\s*|\\s*```$/i', '', trim($content)) ?? trim($content);
    }
}
