<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\VariantType;
use App\Models\VariantValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $status = $request->input('status', 'active');

        $query = Product::query()
            ->with(['category', 'brand', 'variants.variantValues.variantType', 'images', 'primaryImage']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhereHas('variants', function ($qv) use ($search) {
                        $qv->where('sku', 'like', "%{$search}%");
                    });
            });
        }

        if ($categoryId && $categoryId !== 'all') {
            $query->where('category_id', $categoryId);
        }

        if ($status !== 'all') {
            if ($status === 'active') {
                $query->where('status', 'active')
                    ->whereHas('variants', fn ($qv) => $qv->where('quantity', '>', 0));
            } elseif ($status === 'inactive') {
                $query->where('status', 'inactive');
            } elseif ($status === 'out_of_stock') {
                $query->where('status', 'active')
                    ->whereDoesntHave('variants', fn ($qv) => $qv->where('quantity', '>', 0));
            }
        }

        $products = $query->paginate(10);
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

    public function create()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $brands = Brand::orderBy('name')->get(['id', 'name']);
        $variantTypes = $this->activeVariantTypes();
        $variantTypeOptions = $this->variantTypeOptions($variantTypes);

        return view('admin.products.create', compact('categories', 'brands', 'variantTypes', 'variantTypeOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'description' => $validated['description'] ?? null,
            'status' => 'active',
        ]);

        $this->syncSubmittedVariants($request, $product, $validated);

        $this->storeImages($request, $product);
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
                    ->orWhereHas('values', fn ($valueQuery) => $valueQuery->where('value', 'like', "%{$search}%"));
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
        $productVariantRows = $product->variants->values()->map(fn ($variant): array => [
            'id' => $variant->id,
            'sku' => $variant->sku,
            'price' => (float) $variant->price,
            'sale_price' => $variant->sale_price !== null ? (float) $variant->sale_price : '',
            'quantity' => $variant->quantity,
            'status' => $variant->status,
            'value_ids' => $variant->variantValues->pluck('id')->values()->all(),
        ])->all();

        return view('admin.products.edit', compact('product', 'categories', 'brands', 'variantTypes', 'variantTypeOptions', 'productVariantRows'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::with('variants')->findOrFail($id);
        $firstVariant = $product->variants->first();
        $validated = $this->validateProduct($request, $firstVariant?->id);

        $product->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'description' => $validated['description'] ?? null,
        ]);

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

            $firstVariant
                ? $firstVariant->update($variantData)
                : $product->variants()->create($variantData);
        }

        $this->storeImages($request, $product);
        Cache::forget('api.home.sections.v1');

        return redirect()->route('admin.products')->with('success', 'Sản phẩm đã được cập nhật thành công.');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        Cache::forget('api.home.sections.v1');

        return redirect()->route('admin.products')->with('success', 'Sản phẩm đã được xóa thành công.');
    }

    private function validateProduct(Request $request, ?int $variantId = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:product_variants,sku'.($variantId ? ','.$variantId : ''),
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
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
        ]);

        foreach ($request->input('variants', []) as $index => $variant) {
            $salePrice = $variant['sale_price'] ?? null;
            $price = $variant['price'] ?? null;

            if ($salePrice !== null && $salePrice !== '' && $price !== null && (float) $salePrice >= (float) $price) {
                throw ValidationException::withMessages([
                    "variants.{$index}.sale_price" => 'Giá giảm của biến thể phải nhỏ hơn giá bán.',
                ]);
            }
        }

        $variantSkus = collect($request->input('variants', []))
            ->pluck('sku')
            ->filter()
            ->map(fn (string $sku): string => trim($sku))
            ->values();

        if ($variantSkus->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'variants' => 'SKU biến thể không được trùng nhau.',
            ]);
        }

        $variantIds = collect($request->input('variants', []))
            ->pluck('id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->all();

        $existingSku = \App\Models\ProductVariant::query()
            ->whereIn('sku', $variantSkus->all())
            ->when(! empty($variantIds), fn ($query) => $query->whereNotIn('id', $variantIds))
            ->value('sku');

        if ($existingSku) {
            throw ValidationException::withMessages([
                'variants' => "SKU {$existingSku} đã tồn tại.",
            ]);
        }

        return $validated;
    }

    private function syncSubmittedVariants(Request $request, Product $product, array $fallback): void
    {
        $variants = collect($request->input('variants', []))
            ->filter(fn (array $variant): bool => ! empty($variant['sku']));

        if ($variants->isEmpty()) {
            if ($skipVariantId === null && ! $product->variants()->exists()) {
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

            if (! empty($variantInput['id'])) {
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
            ->contains(fn (array $variant): bool => ! empty($variant['sku']));
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (Product::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function syncNewVariantValues(VariantType $variantType, string $rawValues): void
    {
        collect(explode(',', $rawValues))
            ->map(fn (string $value): string => trim($value))
            ->filter()
            ->unique(fn (string $value): string => mb_strtolower($value))
            ->each(fn (string $value) => $variantType->values()->firstOrCreate(['value' => $value]));
    }

    private function activeVariantTypes()
    {
        return VariantType::query()
            ->with(['values' => fn ($query) => $query->orderBy('value')])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    private function variantTypeOptions($variantTypes): array
    {
        return $variantTypes->map(fn (VariantType $type): array => [
            'id' => $type->id,
            'name' => $type->name,
            'values' => $type->values->map(fn (VariantValue $value): array => [
                'id' => $value->id,
                'value' => $value->value,
            ])->values()->all(),
        ])->values()->all();
    }

    private function cleanVariantValueIds(array $valueIds): array
    {
        return collect($valueIds)
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function storeImages(Request $request, Product $product): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $targetDir = public_path('image/products');

        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($request->file('images') as $image) {
            if (! $image->isValid()) {
                continue;
            }

            $filename = uniqid('product_', true).'.'.$image->getClientOriginalExtension();
            $image->move($targetDir, $filename);

            ProductImage::create([
                'product_id' => $product->id,
                'image_url' => 'image/products/'.$filename,
                'is_primary' => ! $hasPrimary,
            ]);

            $hasPrimary = true;
        }
    }
}
