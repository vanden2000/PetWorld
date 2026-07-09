<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;


class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with([ 'products'])->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = Category::query()
            
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:categories,slug',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'description' => 'nullable|string',
           
            'status' => 'required|in:active,draft',
           
        ]);

        $data = $request->only(['name', 'slug', 'description', 'status']);

        if ($request->hasFile('image')) {
            // Lưu đường dẫn tương đối; URL công khai sẽ là /storage/categories/{tên-file}.
            $imagePath = $request->file('image')->store('categories', 'public');
            $data['image'] = $imagePath;
        }

        Category::create($data);
        Cache::forget('api.home.sections.v1');

        return redirect()->route('admin.categories')->with('success', 'Tạo danh mục mới thành công!');
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $parentCategories = Category::query()
            ->where('status', 'active')
            ->whereKeyNot($category->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:categories,slug,' . $id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'description' => 'nullable|string',
            'status' => 'required|in:active,draft',
        ]);

        $data = $request->only(['name', 'slug', 'description', 'parent_id', 'status', 'meta_title', 'meta_description', 'meta_keywords']);

        if ($request->hasFile('image')) {
            // Dùng cùng quy ước với lúc tạo để frontend chỉ cần một cách dựng URL.
            $imagePath = $request->file('image')->store('categories', 'public');
            $data['image'] = $imagePath;
        }

        $category->update($data);
        Cache::forget('api.home.sections.v1');

        return redirect()->route('admin.categories')->with('success', 'Cập nhật danh mục thành công!');
    }
}
