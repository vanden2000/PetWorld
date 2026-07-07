<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BrandController extends Controller
{
    public function index()
    {
        $brands = \App\Models\Brand::all();
        if ($brands->isEmpty()) {
            \App\Models\Brand::create([
                'name' => 'Royal Canin',
                'slug' => 'royal-canin',
                'website' => 'https://royalcanin.com',
                'description' => 'Thương hiệu thức ăn thú cưng cao cấp nhập khẩu Pháp.',
                'image' => null,
                'status' => 'active'
            ]);
            $brands = \App\Models\Brand::all();
        }
        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'status' => 'required|in:active,draft',
        ]);

        $data = $request->only(['name', 'slug', 'website', 'description', 'status']);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Store public/uploads/brands
            $file->move(public_path('uploads/brands'), $filename);
            $data['image'] = 'uploads/brands/' . $filename;
        }

        \App\Models\Brand::create($data);
        Cache::forget('api.home.sections.v1');

        return redirect()->route('admin.brands')->with('success', 'Thêm thương hiệu mới thành công!');
    }

    public function edit($id)
    {
        $brand = \App\Models\Brand::findOrFail($id);
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        $brand = \App\Models\Brand::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug,' . $brand->id,
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'status' => 'required|in:active,draft',
        ]);

        $data = $request->only(['name', 'slug', 'website', 'description', 'status']);

        if ($request->hasFile('image')) {
            // Delete old file if exists
            if ($brand->image && file_exists(public_path($brand->image))) {
                @unlink(public_path($brand->image));
            }

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/brands'), $filename);
            $data['image'] = 'uploads/brands/' . $filename;
        } elseif ($request->input('image_prefilled') !== 'yes') {
            // User deleted current image preview
            if ($brand->image && file_exists(public_path($brand->image))) {
                @unlink(public_path($brand->image));
            }
            $data['image'] = null;
        }

        $brand->update($data);
        Cache::forget('api.home.sections.v1');

        return redirect()->route('admin.brands')->with('success', 'Cập nhật thương hiệu thành công!');
    }
}
