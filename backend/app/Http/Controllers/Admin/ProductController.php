<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the products with filters.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $status = $request->input('status', 'active'); // Default to active (Đang hoạt động) matching the screen

        $query = Product::query()
            ->with(['category', 'brand', 'variants', 'images', 'primaryImage']);

        // Filter: Search name, slug, or SKU
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhereHas('variants', function ($qv) use ($search) {
                      $qv->where('sku', 'like', "%{$search}%");
                  });
            });
        }

        // Filter: Category
        if ($categoryId && $categoryId !== 'all') {
            $query->where('category_id', $categoryId);
        }

        // Filter: Status
        if ($status !== 'all') {
            if ($status === 'active') {
                // Active and has stock (Dang hoat dong)
                $query->where('status', 'active')
                      ->whereHas('variants', function ($qv) {
                          $qv->where('quantity', '>', 0);
                      });
            } elseif ($status === 'inactive') {
                // Hidden (Da an)
                $query->where('status', 'inactive');
            } elseif ($status === 'out_of_stock') {
                // Active but no stock (Het hang)
                $query->where('status', 'active')
                      ->whereDoesntHave('variants', function ($qv) {
                          $qv->where('quantity', '>', 0);
                      });
            }
        }

        // Paginate results
        /** @var \Illuminate\Pagination\LengthAwarePaginator $products */
        $products = $query->paginate(10);
        $products->withQueryString();

        // Get count helper
        $totalCount = Product::count();

        // Get categories for select input
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

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $brands = Brand::orderBy('name')->get(['id', 'name']);
        return view('admin.products.create', compact('categories', 'brands'));
    }

    /**
     * Display variants of products.
     */
    public function variants()
    {
        return view('admin.products.variants');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($id)
    {
        $product = Product::with(['category', 'brand', 'variants', 'images', 'primaryImage'])->findOrFail($id);
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $brands = Brand::orderBy('name')->get(['id', 'name']);
        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $product->update([
            'name' => $request->input('name'),
            'category_id' => $request->input('category_id'),
            'brand_id' => $request->input('brand_id'),
            'description' => $request->input('description'),
        ]);

        if ($product->variants->isNotEmpty()) {
            $variant = $product->variants->first();
            $variant->update([
                'sku' => $request->input('sku'),
                'price' => $request->input('price'),
                'quantity' => $request->input('quantity'),
            ]);
        }

        return redirect()->route('admin.products')->with('success', 'Sản phẩm đã được cập nhật thành công.');
    }

    /**
     * Remove the specified product from storage (soft delete).
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products')->with('success', 'Sản phẩm đã được xóa thành công.');
    }
}
