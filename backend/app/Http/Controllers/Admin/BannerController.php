<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::query()
            ->latest('created_at')
            ->get();

        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'link' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,draft',
        ]);

        $data = $request->only(['link', 'description', 'status']);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $destinationPath = public_path('uploads/banners');
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            $file->move($destinationPath, $filename);
            $data['image'] = 'uploads/banners/' . $filename;
        }

        Banner::create($data);
        Cache::forget('api.home.sections.v1');

        return redirect()->route('admin.banners')->with('success', 'Thêm banner mới thành công!');
    }

    public function edit($id)
    {
        $banner = Banner::findOrFail($id);
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'link' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,draft',
        ]);

        $data = $request->only(['link', 'description', 'status']);

        if ($request->hasFile('image')) {
            // Delete old file if exists
            if ($banner->image && file_exists(public_path($banner->image))) {
                @unlink(public_path($banner->image));
            }

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $destinationPath = public_path('uploads/banners');
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $data['image'] = 'uploads/banners/' . $filename;
        } elseif ($request->input('image_prefilled') !== 'yes') {
            // User deleted current image preview
            if ($banner->image && file_exists(public_path($banner->image))) {
                @unlink(public_path($banner->image));
            }
            $data['image'] = null;
        }

        // If the banner image gets cleared, but it's required, we should prevent it.
        if (!isset($data['image']) && !$banner->image && $request->input('image_prefilled') !== 'yes') {
            return back()->withErrors(['image' => 'Hình ảnh banner là bắt buộc.'])->withInput();
        }

        $banner->update(array_filter($data, function ($value) {
            return $value !== null;
        }));
        
        // If image was explicitly set or prefilled remains yes, we update/keep image path
        if (isset($data['image'])) {
            $banner->image = $data['image'];
            $banner->save();
        }

        Cache::forget('api.home.sections.v1');

        return redirect()->route('admin.banners')->with('success', 'Cập nhật banner thành công!');
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);

        // Delete old file if exists
        if ($banner->image && file_exists(public_path($banner->image))) {
            @unlink(public_path($banner->image));
        }

        $banner->delete();
        Cache::forget('api.home.sections.v1');

        return redirect()->route('admin.banners')->with('success', 'Xóa banner thành công!');
    }

    public function toggleStatus($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->status = $banner->status === 'active' ? 'draft' : 'active';
        $banner->save();

        Cache::forget('api.home.sections.v1');

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái banner thành công!',
                'status' => $banner->status
            ]);
        }

        return redirect()->route('admin.banners')->with('success', 'Cập nhật trạng thái banner thành công!');
    }
}
