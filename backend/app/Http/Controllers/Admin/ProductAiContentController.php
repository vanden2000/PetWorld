<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\PetSpecies;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\VariantType;
use App\Services\ProductAiContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProductAiContentController extends Controller
{
    private const ACTIONS = [
        'generate_seo_content',
        'improve_existing_content',
        'generate_seo_meta',
        'audit_seo',
        'suggest_product_profile',
        'generate_image_alt',
        'generate_draft_image_alt',
        'generate_product_draft',
    ];

    private const LIFE_STAGES = ['kitten', 'puppy', 'adult', 'senior', 'all_life_stages'];

    private const NEEDS = ['daily_nutrition', 'picky_eater', 'skin_coat', 'weight_control', 'dental', 'indoor'];

    public function __invoke(Request $request, ProductAiContentService $ai): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', 'string', 'in:'.implode(',', self::ACTIONS)],
            'product.name' => ['required', 'string', 'max:255'],
            'product.category' => ['nullable', 'string', 'max:255'],
            'product.brand' => ['nullable', 'string', 'max:255'],
            'product.short_description' => ['nullable', 'string', 'max:500'],
            'product.description' => ['nullable', 'string', 'max:12000'],
            'product.focus_keyword' => ['nullable', 'string', 'max:120'],
            'product.seo_title' => ['nullable', 'string', 'max:255'],
            'product.seo_description' => ['nullable', 'string', 'max:320'],
            'product.variants' => ['nullable', 'array', 'max:30'],
            'product.variants.*' => ['string', 'max:255'],
            'product.pet_species' => ['nullable', 'array', 'max:10'],
            'product.pet_species.*' => ['string', 'max:80'],
            'options.length' => ['nullable', 'in:short,standard,detailed'],
            'options.tone' => ['nullable', 'in:professional,friendly'],
            'product_id' => ['required_if:action,generate_image_alt', 'nullable', 'integer', 'exists:products,id'],
            'image_ids' => ['required_if:action,generate_image_alt', 'nullable', 'array', 'max:8'],
            'image_ids.*' => ['integer', 'distinct'],
            'draft_images' => ['required_if:action,generate_draft_image_alt', 'nullable', 'array', 'max:8'],
            'draft_images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'draft_image_keys' => ['required_if:action,generate_draft_image_alt', 'nullable', 'array', 'max:8'],
            'draft_image_keys.*' => ['string', 'max:64', 'distinct'],
        ]);

        if ($data['action'] === 'generate_draft_image_alt') {
            $files = $request->file('draft_images', []);
            $keys = $data['draft_image_keys'] ?? [];
            $images = collect($files)->values()->map(function ($file, int $index) use ($keys): array {
                return [
                    'id' => 'new:'.($keys[$index] ?? $index),
                    'url' => 'data:'.($file->getMimeType() ?: 'image/jpeg').';base64,'.base64_encode((string) file_get_contents($file->getRealPath())),
                ];
            })->all();

            if ($images === []) {
                return response()->json(['message' => 'Hãy chọn ít nhất một ảnh sản phẩm.'], 422);
            }

            try {
                return response()->json(['data' => $ai->generateImageAlts($data['product'], $images)]);
            } catch (RuntimeException $exception) {
                report($exception);

                return response()->json(['message' => 'Chưa thể tạo gợi ý alt ảnh. Vui lòng thử lại sau.'], $ai->isConfigured() ? 502 : 503);
            }
        }

        if ($data['action'] === 'generate_image_alt') {
            $product = Product::query()->findOrFail($data['product_id']);
            $images = ProductImage::query()
                ->where('product_id', $product->id)
                ->whereIn('id', $data['image_ids'] ?? [])
                ->orderBy('sort_order')
                ->get()
                ->map(fn (ProductImage $image): ?array => $this->imageVisionPayload($image))
                ->filter()
                ->values()
                ->all();

            if ($images === []) {
                return response()->json(['message' => 'Không tìm thấy ảnh đã lưu có thể gửi cho AI.'], 422);
            }

            try {
                $suggestions = $ai->generateImageAlts($data['product'], $images);
            } catch (RuntimeException $exception) {
                report($exception);

                return response()->json([
                    'message' => 'Chưa thể tạo gợi ý alt ảnh. Vui lòng thử lại sau.',
                ], $ai->isConfigured() ? 502 : 503);
            }

            return response()->json(['data' => $suggestions]);
        }

        $catalog = [
            'categories' => Category::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Category $category): array => ['id' => $category->id, 'name' => $category->name])
                ->all(),
            'brands' => Brand::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Brand $brand): array => ['id' => $brand->id, 'name' => $brand->name])
                ->all(),
            'pet_species' => PetSpecies::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug'])
                ->map(fn (PetSpecies $species): array => ['id' => $species->id, 'name' => $species->name, 'slug' => $species->slug])
                ->all(),
            'life_stages' => self::LIFE_STAGES,
            'needs' => self::NEEDS,
            'variant_types' => VariantType::query()
                ->with(['values' => fn ($query) => $query->orderBy('value')])
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (VariantType $type): array => [
                    'id' => $type->id,
                    'name' => $type->name,
                    'values' => $type->values->map(fn ($value): array => [
                        'id' => $value->id,
                        'value' => $value->value,
                    ])->all(),
                ])
                ->all(),
        ];

        try {
            $suggestions = $ai->generate(
                $data['action'],
                $data['product'],
                $catalog,
                $data['options'] ?? [],
            );
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json([
                'message' => 'Chưa thể tạo đề xuất AI. Vui lòng thử lại sau.',
            ], $ai->isConfigured() ? 502 : 503);
        }

        return response()->json(['data' => $suggestions]);
    }

    private function imageVisionPayload(ProductImage $image): ?array
    {
        $path = ltrim((string) $image->image_url, '/');

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return ['id' => $image->id, 'url' => $path];
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($path) ?: 'image/jpeg';

        return [
            'id' => $image->id,
            'url' => 'data:'.$mime.';base64,'.base64_encode(Storage::disk('public')->get($path)),
        ];
    }
}
