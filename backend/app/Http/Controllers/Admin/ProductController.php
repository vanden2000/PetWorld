<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductExport;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSlugHistory;
use App\Models\VariantType;
use App\Models\VariantValue;
use App\Models\PetSpecies;
use App\Queries\AdminProductQuery;
use App\Support\ProductDescriptionSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function __construct(private readonly ProductDescriptionSanitizer $descriptionSanitizer)
    {
    }

    public function index(Request $request, AdminProductQuery $productQuery)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $status = $request->input('status', 'all');

        $products = $productQuery->build($request->only([
            'search',
            'category_id',
            'status',
        ]))->paginate(10);
        $products->withQueryString();

        $totalCount = Product::count();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.products.index', compact(
            'products',
            'totalCount',
            'categories',
            'search',
            'categoryId',
            'status'
        ));
    }

    public function export(Request $request, AdminProductQuery $productQuery)
    {
        if ($request->input('category_id') === 'all') {
            $request->merge(['category_id' => null]);
        }

        $validated = $request->validate([
            'scope' => ['nullable', Rule::in(['filtered', 'all', 'active', 'inactive'])],
            'include_variants' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status' => ['nullable', Rule::in(['all', 'active', 'inactive'])],
        ]);

        $scope = $validated['scope'] ?? 'filtered';
        $filters = match ($scope) {
            'all' => [],
            'active' => ['status' => 'active'],
            'inactive' => ['status' => 'inactive'],
            default => [
                'search' => $validated['search'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'status' => $validated['status'] ?? 'all',
            ],
        };

        $query = $productQuery->build($filters);

        if (!(clone $query)->exists()) {
            return redirect()
                ->route('admin.products', $request->only(['search', 'category_id', 'status']))
                ->with('error', 'Không có sản phẩm phù hợp để xuất.');
        }

        $includeVariants = $request->boolean('include_variants', true);
        $filename = 'danh-sach-san-pham-' . now()->format('d-m-Y-Hi') . '.xlsx';

        return Excel::download(
            new ProductExport($query, $includeVariants),
            $filename,
        );
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $brands = Brand::orderBy('name')->get(['id', 'name']);
        $variantTypes = $this->activeVariantTypes();
        $variantTypeOptions = $this->variantTypeOptions($variantTypes);
        $product = new Product();
        $product->setRelation('images', collect());
        $product->setRelation('primaryImage', null);
        $productVariantRows = [];
        $isCreate = true;
        $petSpecies = PetSpecies::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.create', compact(
            'categories',
            'brands',
            'variantTypes',
            'variantTypeOptions',
            'product',
            'productVariantRows',
            'isCreate',
            'petSpecies',
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);
        $this->validateSpeciesLifeStages($validated);

        $product = DB::transaction(function () use ($request, $validated) {
            $product = Product::create([
                'name' => $validated['name'],
                'slug' => $this->uniqueSlug($validated['slug'] ?? $validated['name']),
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'],
                'description' => $this->descriptionSanitizer->sanitize($validated['description'] ?? null),
                'short_description' => $this->cleanSeoText($validated['short_description'] ?? null),
                'focus_keyword' => $this->cleanSeoText($validated['focus_keyword'] ?? null),
                'advice_attributes' => $this->adviceAttributes($validated),
                'seo_title' => $this->cleanSeoText($validated['seo_title'] ?? null),
                'seo_description' => $this->cleanSeoText($validated['seo_description'] ?? null),
                'status' => 'active',
            ]);

            $this->syncSubmittedVariants($request, $product, $validated);
            $product->petSpecies()->sync($validated['pet_species_ids'] ?? []);

            // Use the same metadata-aware image workflow as product updates so
            // alt text, primary image and sort order are persisted on first create.
            $imageChanges = $this->validateImageChanges($request, $product);
            $this->syncImages($product, $imageChanges);

            return $product;
        });

        Cache::forget('api.home.sections.v1');

        return redirect()->route('admin.products')->with('success', 'Sản phẩm đã được tạo thành công.');
    }

    public function variants(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'all');

        $query = VariantType::query()
            ->with(['values.productVariants'])
            ->withCount('values')
            ->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('values', fn($valueQuery) => $valueQuery->where('value', 'like', "%{$search}%"));
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $variantTypes = $query->paginate(8)->withQueryString();
        $totalTypes = VariantType::count();
        $totalValues = VariantValue::count();
        $usedValues = VariantValue::whereHas('productVariants')->count();

        return view('admin.products.variants', compact(
            'variantTypes',
            'totalTypes',
            'totalValues',
            'usedValues',
            'search',
            'status'
        ));
    }

    public function storeVariantType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:variant_types,name',
            'status' => 'required|in:active,inactive',
            'values' => 'nullable|string|max:2000',
        ]);

        $variantType = VariantType::create([
            'name' => $validated['name'],
            'status' => $validated['status'],
        ]);

        $this->syncNewVariantValues($variantType, $validated['values'] ?? '');

        return redirect()->route('admin.products.variants')->with('success', 'Đã thêm thuộc tính biến thể.');
    }

    public function updateVariantType(Request $request, VariantType $variantType)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('variant_types', 'name')->ignore($variantType->id),
            ],
            'status' => 'required|in:active,inactive',
        ]);

        $variantType->update($validated);

        return redirect()->route('admin.products.variants')->with('success', 'Đã cập nhật thuộc tính biến thể.');
    }

    public function destroyVariantType(VariantType $variantType)
    {
        $usedValuesCount = $variantType->values()
            ->whereHas('productVariants')
            ->count();

        if ($usedValuesCount > 0) {
            return redirect()
                ->route('admin.products.variants')
                ->with('error', 'Không thể xóa thuộc tính đang được dùng bởi sản phẩm. Hãy chuyển sang trạng thái ẩn.');
        }

        $variantType->values()->delete();
        $variantType->delete();

        return redirect()->route('admin.products.variants')->with('success', 'Đã xóa thuộc tính biến thể.');
    }

    public function storeVariantValue(Request $request, VariantType $variantType)
    {
        $validated = $request->validate([
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('variant_values', 'value')->where('variant_type_id', $variantType->id),
            ],
        ]);

        $variantType->values()->create($validated);

        return redirect()->route('admin.products.variants')->with('success', 'Đã thêm giá trị biến thể.');
    }

    public function updateVariantValue(Request $request, VariantValue $variantValue)
    {
        $validated = $request->validate([
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('variant_values', 'value')
                    ->where('variant_type_id', $variantValue->variant_type_id)
                    ->ignore($variantValue->id),
            ],
        ]);

        $variantValue->update($validated);

        return redirect()->route('admin.products.variants')->with('success', 'Đã cập nhật giá trị biến thể.');
    }

    public function destroyVariantValue(VariantValue $variantValue)
    {
        if ($variantValue->productVariants()->exists()) {
            return redirect()
                ->route('admin.products.variants')
                ->with('error', 'Không thể xóa giá trị đang được dùng bởi sản phẩm.');
        }

        $variantValue->delete();

        return redirect()->route('admin.products.variants')->with('success', 'Đã xóa giá trị biến thể.');
    }

    public function edit($id)
    {
        $product = Product::with(['category', 'brand', 'variants.variantValues.variantType', 'images', 'primaryImage'])->findOrFail($id);
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $brands = Brand::orderBy('name')->get(['id', 'name']);
        $variantTypes = $this->activeVariantTypes();
        $variantTypeOptions = $this->variantTypeOptions($variantTypes);
        $productVariantRows = $product->variants->values()->map(fn($variant): array => [
            'id' => $variant->id,
            'sku' => $variant->sku,
            'price' => (float) $variant->price,
            'sale_price' => $variant->sale_price !== null ? (float) $variant->sale_price : '',
            'quantity' => $variant->quantity,
            'status' => $variant->status,
            'value_ids' => $variant->variantValues->pluck('id')->values()->all(),
        ])->all();

        $petSpecies = PetSpecies::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories', 'brands', 'variantTypes', 'variantTypeOptions', 'productVariantRows', 'petSpecies'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::with('variants')->findOrFail($id);
        $firstVariant = $product->variants->first();
        $validated = $this->validateProduct($request, $firstVariant?->id, $product);
        $this->validateSpeciesLifeStages($validated);
        $imageChanges = $this->validateImageChanges($request, $product);
        $slug = $this->uniqueSlug($validated['slug'] ?? $validated['name'], $product->id);

        DB::transaction(function () use ($request, $product, $firstVariant, $validated, $slug): void {
            if ($product->slug !== $slug) {
                ProductSlugHistory::query()
                    ->where('product_id', $product->id)
                    ->where('slug', $slug)
                    ->delete();

                ProductSlugHistory::firstOrCreate([
                    'product_id' => $product->id,
                    'slug' => $product->slug,
                ]);
            }

            $product->update([
                'name' => $validated['name'],
                'slug' => $slug,
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'],
                'description' => $this->descriptionSanitizer->sanitize($validated['description'] ?? null),
                'short_description' => $this->cleanSeoText($validated['short_description'] ?? null),
                'focus_keyword' => $this->cleanSeoText($validated['focus_keyword'] ?? null),
                'advice_attributes' => $this->adviceAttributes($validated),
                'seo_title' => $this->cleanSeoText($validated['seo_title'] ?? null),
                'seo_description' => $this->cleanSeoText($validated['seo_description'] ?? null),
            ]);
            $product->petSpecies()->sync($validated['pet_species_ids'] ?? []);

            if ($this->hasSubmittedVariants($request)) {
                $this->syncSubmittedVariants($request, $product, $validated);
            } else {
                $variantData = [
                    'sku' => $validated['sku'],
                    'price' => $validated['price'],
                    'sale_price' => $validated['sale_price'] ?? null,
                    'quantity' => $validated['quantity'],
                    'status' => 'active',
                ];

                $currentVariant = $product->variants()->first();
                $currentVariant
                    ? $currentVariant->update($variantData)
                    : $product->variants()->create($variantData);
            }
        });

        $this->syncImages($product, $imageChanges);
        Cache::forget('api.home.sections.v1');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Sản phẩm đã được cập nhật thành công.',
                'updated_at' => now()->format('H:i d/m/Y'),
            ]);
        }

        return redirect()->route('admin.products')->with('success', 'Sản phẩm đã được cập nhật thành công.');
    }

    public function updateStatus(Request $request, Product $product)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $product->update(['status' => $validated['status']]);
        Cache::forget('api.home.sections.v1');

        $message = $validated['status'] === 'inactive'
            ? 'Đã ẩn sản phẩm thành công.'
            : 'Đã hiển thị lại sản phẩm.';

        return redirect()->route('admin.products')->with('success', $message);
    }

    private function validateProduct(Request $request, ?int $variantId = null, ?Product $product = null): array
    {
        $hasSubmittedVariants = $this->hasSubmittedVariants($request);
        $baseRequirement = $hasSubmittedVariants ? 'nullable' : 'required';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:180',
            'sku' => $baseRequirement . '|string|max:255|unique:product_variants,sku' . ($variantId ? ',' . $variantId : ''),
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'price' => $baseRequirement . '|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'quantity' => $baseRequirement . '|integer|min:0',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'focus_keyword' => 'nullable|string|max:120',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:320',
            'advice_pet_types' => 'nullable|array|max:2',
            'advice_pet_types.*' => 'in:cat,dog',
            'advice_life_stages' => 'nullable|array|max:5',
            'advice_life_stages.*' => 'in:kitten,puppy,adult,senior,all_life_stages',
            'advice_product_types' => 'nullable|array|max:6',
            'advice_product_types.*' => 'in:dry_food,wet_food,treat,toy,litter,accessory',
            'advice_needs' => 'nullable|array|max:6',
            'advice_needs.*' => 'in:daily_nutrition,picky_eater,skin_coat,weight_control,dental,indoor',
            'pet_species_ids' => 'nullable|array|max:10',
            'pet_species_ids.*' => 'integer|exists:pet_species,id',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer|exists:product_variants,id',
            'variants.*.sku' => 'nullable|string|max:255',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.quantity' => 'nullable|integer|min:0',
            'variants.*.visible' => 'nullable|in:1',
            'variants.*.value_ids' => 'nullable|array',
            'variants.*.value_ids.*' => 'integer|exists:variant_values,id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'images' => 'nullable|array|max:8',
            'deleted_image_ids' => 'nullable|array',
            'deleted_image_ids.*' => 'integer|exists:images,id',
            'primary_image_id' => 'nullable|integer|exists:images,id',
            'primary_image_new_index' => 'nullable|integer|min:0',
            'image_order' => 'nullable|array|max:8',
            'image_order.*' => 'string|max:64',
            'new_image_keys' => 'nullable|array|max:8',
            'new_image_keys.*' => 'string|max:64',
            'image_alt_texts' => 'nullable|array',
            'image_alt_texts.*' => 'nullable|string|max:255',
            'new_image_alt_texts' => 'nullable|array',
            'new_image_alt_texts.*' => 'nullable|string|max:255',
        ]);

        foreach ($request->input('variants', []) as $index => $variant) {
            $salePrice = $variant['sale_price'] ?? null;
            $price = $variant['price'] ?? null;
            $sku = trim((string) ($variant['sku'] ?? ''));

            if ($sku !== '' && ($price === null || $price === '' || ! array_key_exists('quantity', $variant) || $variant['quantity'] === '')) {
                throw ValidationException::withMessages([
                    "variants.{$index}" => 'Mỗi biến thể có SKU phải có giá bán và tồn kho.',
                ]);
            }

            if ($salePrice !== null && $salePrice !== '' && $price !== null && (float) $salePrice >= (float) $price) {
                throw ValidationException::withMessages([
                    "variants.{$index}.sale_price" => 'Giá giảm của biến thể phải nhỏ hơn giá bán.',
                ]);
            }
        }

        $variantSkuInputs = collect($request->input('variants', []))
            ->pluck('sku')
            ->filter()
            ->map(fn(string $sku): string => trim($sku))
            ->values();
        $variantSkus = $variantSkuInputs
            ->map(fn(string $sku): string => mb_strtolower($sku))
            ->values();

        if ($variantSkus->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'variants' => 'SKU biến thể không được trùng nhau.',
            ]);
        }

        $variantIds = collect($request->input('variants', []))
            ->pluck('id')
            ->filter()
            ->map(fn($id): int => (int) $id)
            ->all();

        if (count($variantIds) !== count(array_unique($variantIds))) {
            throw ValidationException::withMessages([
                'variants' => 'Một biến thể không thể xuất hiện nhiều lần trong cùng biểu mẫu.',
            ]);
        }

        if ($product && $variantIds !== []) {
            $ownedVariantCount = $product->variants()
                ->whereIn('id', $variantIds)
                ->count();

            if ($ownedVariantCount !== count($variantIds)) {
                throw ValidationException::withMessages([
                    'variants' => 'Có biến thể không thuộc sản phẩm này.',
                ]);
            }
        }

        $existingSku = \App\Models\ProductVariant::query()
            ->whereIn('sku', $variantSkuInputs->all())
            ->when(!empty($variantIds), fn($query) => $query->whereNotIn('id', $variantIds))
            ->value('sku');

        if ($existingSku) {
            throw ValidationException::withMessages([
                'variants' => "SKU {$existingSku} đã tồn tại.",
            ]);
        }

        return $validated;
    }

    private function adviceAttributes(array $validated): array
    {
        return array_filter([
            'pet_types' => array_values($validated['advice_pet_types'] ?? []),
            'life_stages' => array_values($validated['advice_life_stages'] ?? []),
            'product_types' => array_values($validated['advice_product_types'] ?? []),
            'needs' => array_values($validated['advice_needs'] ?? []),
        ], fn (array $values) => $values !== []);
    }

    private function validateSpeciesLifeStages(array $validated): void
    {
        $speciesIds = $validated['pet_species_ids'] ?? [];
        $lifeStages = $validated['advice_life_stages'] ?? [];

        if ($speciesIds === [] || $lifeStages === []) {
            return;
        }

        $slugs = PetSpecies::query()
            ->whereIn('id', $speciesIds)
            ->pluck('slug')
            ->all();
        $onlyCats = in_array('cat', $slugs, true) && !in_array('dog', $slugs, true);
        $onlyDogs = in_array('dog', $slugs, true) && !in_array('cat', $slugs, true);

        if (($onlyCats && in_array('puppy', $lifeStages, true)) || ($onlyDogs && in_array('kitten', $lifeStages, true))) {
            throw ValidationException::withMessages([
                'advice_life_stages' => 'Độ tuổi phù hợp chưa khớp với loài thú cưng đã chọn.',
            ]);
        }
    }

    private function syncSubmittedVariants(Request $request, Product $product, array $fallback): void
    {
        $variants = collect($request->input('variants', []))
            ->filter(fn(array $variant): bool => !empty($variant['sku']));

        if ($variants->isEmpty()) {
            if (! $product->variants()->exists()) {
                $product->variants()->create([
                    'sku' => $fallback['sku'],
                    'price' => $fallback['price'],
                    'sale_price' => $fallback['sale_price'] ?? null,
                    'quantity' => $fallback['quantity'],
                    'status' => 'active',
                ]);
            }

            return;
        }

        foreach ($variants as $variantInput) {
            $salePrice = $variantInput['sale_price'] ?? null;
            $price = $variantInput['price'] ?? $fallback['price'];
            $salePrice = $salePrice === '' ? null : $salePrice;

            $data = [
                'sku' => $variantInput['sku'],
                'price' => $price,
                'sale_price' => $salePrice,
                'quantity' => $variantInput['quantity'] ?? $fallback['quantity'],
                'status' => isset($variantInput['visible']) ? 'active' : 'inactive',
            ];

            if (!empty($variantInput['id'])) {
                $variant = $product->variants()->whereKey($variantInput['id'])->first();

                if ($variant) {
                    $variant->update($data);
                    $variant->syncVariantValues($this->cleanVariantValueIds($variantInput['value_ids'] ?? []));
                }

                continue;
            }

            $variant = $product->variants()->create($data);
            $variant->syncVariantValues($this->cleanVariantValueIds($variantInput['value_ids'] ?? []));
        }
    }

    private function hasSubmittedVariants(Request $request): bool
    {
        return collect($request->input('variants', []))
            ->contains(fn(array $variant): bool => !empty($variant['sku']));
    }

    private function uniqueSlug(string $value, ?int $ignoreProductId = null): string
    {
        $base = Str::slug($value);
        $base = Str::limit($base !== '' ? $base : 'san-pham', 170, '');
        $slug = $base;
        $counter = 2;

        while (
            Product::query()
                ->where('slug', $slug)
                ->when($ignoreProductId, fn($query) => $query->whereKeyNot($ignoreProductId))
                ->exists()
            || ProductSlugHistory::query()
                ->where('slug', $slug)
                ->when($ignoreProductId, fn($query) => $query->where('product_id', '!=', $ignoreProductId))
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function cleanSeoText(?string $value): ?string
    {
        $value = Str::squish(strip_tags((string) $value));

        return $value !== '' ? $value : null;
    }

    private function syncNewVariantValues(VariantType $variantType, string $rawValues): void
    {
        collect(explode(',', $rawValues))
            ->map(fn(string $value): string => trim($value))
            ->filter()
            ->unique(fn(string $value): string => mb_strtolower($value))
            ->each(fn(string $value) => $variantType->values()->firstOrCreate(['value' => $value]));
    }

    private function activeVariantTypes()
    {
        return VariantType::query()
            ->with(['values' => fn($query) => $query->orderBy('value')])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    private function variantTypeOptions($variantTypes): array
    {
        return $variantTypes->map(fn(VariantType $type): array => [
            'id' => $type->id,
            'name' => $type->name,
            'values' => $type->values->map(fn(VariantValue $value): array => [
                'id' => $value->id,
                'value' => $value->value,
            ])->values()->all(),
        ])->values()->all();
    }

    private function cleanVariantValueIds(array $valueIds): array
    {
        return collect($valueIds)
            ->filter()
            ->map(fn($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function validateImageChanges(Request $request, Product $product): array
    {
        $deletedIds = collect($request->input('deleted_image_ids', []))
            ->map(fn($id): int => (int) $id)
            ->unique()
            ->values();
        $imagesToDelete = $product->images()->whereIn('id', $deletedIds)->get();

        if ($imagesToDelete->count() !== $deletedIds->count()) {
            throw ValidationException::withMessages([
                'images' => 'Danh sách ảnh cần xóa không hợp lệ.',
            ]);
        }

        $newImages = collect($request->file('images', []));
        $remainingCount = $product->images()->count() - $imagesToDelete->count();
        $finalCount = $remainingCount + $newImages->count();

        if ($finalCount > 8) {
            throw ValidationException::withMessages([
                'images' => 'Mỗi sản phẩm được có tối đa 8 ảnh.',
            ]);
        }

        if ($finalCount < 1) {
            throw ValidationException::withMessages([
                'images' => 'Sản phẩm phải có ít nhất một ảnh.',
            ]);
        }

        $remainingImages = $product->images()
            ->whereNotIn('id', $deletedIds)
            ->get();
        $remainingIds = $remainingImages->pluck('id')->map(fn ($id): int => (int) $id);
        $primaryImageId = $request->integer('primary_image_id') ?: null;
        if ($primaryImageId && ! $remainingIds->contains($primaryImageId)) {
            throw ValidationException::withMessages([
                'primary_image_id' => 'Ảnh chính không thuộc sản phẩm này.',
            ]);
        }

        $newImageKeys = collect($request->input('new_image_keys', []))->values();
        if ($newImageKeys->count() !== $newImages->count() || $newImageKeys->unique()->count() !== $newImageKeys->count()) {
            throw ValidationException::withMessages([
                'images' => 'Danh sách ảnh mới không hợp lệ.',
            ]);
        }

        $newPrimaryIndex = $request->input('primary_image_new_index');
        if ($newPrimaryIndex !== null && !$newImages->has((int) $newPrimaryIndex)) {
            throw ValidationException::withMessages([
                'primary_image_new_index' => 'Ảnh chính mới không hợp lệ.',
            ]);
        }

        $expectedOrder = $remainingIds->map(fn (int $id): string => "existing:{$id}")
            ->merge($newImageKeys->map(fn (string $key): string => "new:{$key}"))
            ->sort()
            ->values();
        $imageOrder = collect($request->input('image_order', []))->values();

        if ($imageOrder->sort()->values()->all() !== $expectedOrder->all()) {
            throw ValidationException::withMessages([
                'image_order' => 'Thứ tự ảnh không hợp lệ.',
            ]);
        }

        $existingAltTexts = collect($request->input('image_alt_texts', []));
        if ($existingAltTexts->keys()->map(fn ($id): int => (int) $id)->diff($remainingIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'image_alt_texts' => 'Mô tả ảnh không hợp lệ.',
            ]);
        }

        $newAltTexts = collect($request->input('new_image_alt_texts', []));
        if ($newAltTexts->keys()->diff($newImageKeys)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'new_image_alt_texts' => 'Mô tả ảnh mới không hợp lệ.',
            ]);
        }

        $altPayload = json_decode((string) $request->input('image_alt_payload', '[]'), true);
        if (! is_array($altPayload)) {
            throw ValidationException::withMessages([
                'image_alt_payload' => 'Dữ liệu mô tả ảnh không hợp lệ.',
            ]);
        }

        $payloadAltTexts = collect($altPayload)
            ->filter(fn ($item): bool => is_array($item) && isset($item['token']))
            ->mapWithKeys(fn (array $item): array => [(string) $item['token'] => $item['alt_text'] ?? null]);
        $allowedTokens = $expectedOrder->flip();

        if ($payloadAltTexts->keys()->diff($allowedTokens->keys())->isNotEmpty()) {
            throw ValidationException::withMessages([
                'image_alt_payload' => 'Dữ liệu mô tả ảnh không hợp lệ.',
            ]);
        }

        $payloadAltTexts->each(function ($altText, string $token) use ($existingAltTexts, $newAltTexts): void {
            if (str_starts_with($token, 'existing:')) {
                $existingAltTexts->put((int) Str::after($token, 'existing:'), $altText);
            }

            if (str_starts_with($token, 'new:')) {
                $newAltTexts->put(Str::after($token, 'new:'), $altText);
            }
        });

        return compact(
            'imagesToDelete',
            'newImages',
            'newImageKeys',
            'primaryImageId',
            'newPrimaryIndex',
            'imageOrder',
            'existingAltTexts',
            'newAltTexts',
        );
    }

    private function syncImages(Product $product, array $changes): void
    {
        $createdFiles = [];
        $createdImages = collect();

        try {
            foreach ($changes['newImages'] as $index => $image) {
                if (!$image->isValid()) {
                    continue;
                }

                $filename = $this->imageFilename($product, $image->extension());
                $storagePath = 'products/'.$filename;
                Storage::disk('public')->putFileAs('products', $image, $filename);
                $createdFiles[] = $storagePath;
                $imageKey = $changes['newImageKeys']->get($index);
                $createdImages->put($imageKey, new ProductImage([
                    'product_id' => $product->id,
                    'image_url' => $storagePath,
                    'alt_text' => $this->cleanImageAltText($changes['newAltTexts']->get($imageKey)),
                    'is_primary' => false,
                ]));
            }

            DB::transaction(function () use ($product, $changes, $createdImages): void {
                $changes['imagesToDelete']->each->delete();
                $createdImages->each->save();

                $remaining = $product->images()->get();
                $primary = null;

                if ($changes['newPrimaryIndex'] !== null) {
                    $imageKey = $changes['newImageKeys']->get((int) $changes['newPrimaryIndex']);
                    $primary = $createdImages->get($imageKey);
                } elseif ($changes['primaryImageId']) {
                    $primary = $remaining->firstWhere('id', $changes['primaryImageId']);
                }

                $primary ??= $remaining->first();

                $primaryId = $primary?->id;
                $product->images()->update(['is_primary' => false]);
                if ($primaryId) {
                    $product->images()->whereKey($primaryId)->update(['is_primary' => true]);
                }

                // Do not use Collection::merge here: an empty collection can
                // reindex the token map and make every image lookup fail.
                $imagesByToken = $remaining
                    ->mapWithKeys(fn (ProductImage $image): array => ["existing:{$image->id}" => $image]);
                $createdImages->each(function (ProductImage $image, string $key) use ($imagesByToken): void {
                    $imagesByToken->put("new:{$key}", $image);
                });
                $orderedTokens = $changes['imageOrder']
                    ->filter(fn (string $token): bool => str_starts_with($token, 'existing:'))
                    ->merge($changes['imageOrder']->filter(fn (string $token): bool => str_starts_with($token, 'new:')))
                    ->values();

                $orderedTokens->each(function (string $token, int $sortOrder) use ($imagesByToken, $changes): void {
                    $image = $imagesByToken->get($token);
                    if (! $image) {
                        return;
                    }

                    $altText = str_starts_with($token, 'existing:')
                        ? $this->cleanImageAltText($changes['existingAltTexts']->get($image->id))
                        : $image->alt_text;
                    $image->update(['sort_order' => $sortOrder, 'alt_text' => $altText]);
                });

                $existingImageIds = $remaining
                    ->reject(fn (ProductImage $image): bool => $createdImages->contains($image))
                    ->pluck('id');
                $nextSortOrder = ((int) $product->images()
                    ->whereIn('id', $existingImageIds)
                    ->max('sort_order')) + 1;

                $createdImages->each(function (ProductImage $image) use (&$nextSortOrder): void {
                    $image->update(['sort_order' => $nextSortOrder++]);
                });
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($createdFiles);

            throw $exception;
        }

        foreach ($changes['imagesToDelete'] as $image) {
            $this->deleteProductImageFile($image->image_url);
        }
    }

    private function imageFilename(Product $product, string $extension): string
    {
        return $product->slug . '-' . Str::lower(Str::random(10)) . '.' . Str::lower($extension);
    }

    private function cleanImageAltText(?string $value): ?string
    {
        $value = Str::squish(strip_tags((string) $value));

        return $value !== '' ? $value : null;
    }

    private function deleteProductImageFile(string $imageUrl): void
    {
        $relativePath = ltrim(str_replace('\\', '/', $imageUrl), '/');

        if (!str_starts_with($relativePath, 'products/')) {
            return;
        }

        Storage::disk('public')->delete($relativePath);
    }
}
