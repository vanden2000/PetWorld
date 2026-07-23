<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::query()
            ->withCount('products')
            ->latest()
            ->get();

        if ($brands->isEmpty()) {
            Brand::create([
                'name' => 'Royal Canin',
                'slug' => 'royal-canin',
                'website' => 'https://royalcanin.com',
                'description' => 'Thương hiệu thức ăn thú cưng cao cấp nhập khẩu Pháp.',
                'image' => null,
                'status' => 'active',
            ]);

            $brands = Brand::query()
                ->withCount('products')
                ->latest()
                ->get();
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
            $file->move(public_path('uploads/brands'), $filename);
            $data['image'] = 'uploads/brands/' . $filename;
        }

        Brand::create($data);
        Cache::forget('api.home.sections.v1');

        return redirect()
            ->route('admin.brands')
            ->with('success', 'Thêm thương hiệu mới thành công!');
    }

    public function edit($id)
    {
        $brand = Brand::query()
            ->with([
                'products' => function ($query) {
                    $query->with(['category', 'primaryImage', 'variants'])
                        ->latest('id')
                        ->limit(6);
                },
            ])
            ->withCount('products')
            ->findOrFail($id);

        $revenueSeries = $this->brandRevenueSeries($brand);

        return view('admin.brands.edit', compact('brand', 'revenueSeries'));
    }

    /**
     * Doanh thu thật của thương hiệu theo tháng (đơn đã thanh toán, chưa hủy).
     * Trả về 2 chuỗi cho biểu đồ cột: 'year' (12 tháng năm nay) và '6m' (6 tháng gần nhất).
     */
    private function brandRevenueSeries(Brand $brand): array
    {
        $monthly = function (Carbon $start, Carbon $end) use ($brand) {
            return DB::table('order_items as oi')
                ->join('orders as o', 'o.id', '=', 'oi.order_id')
                ->join('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
                ->join('products as p', 'p.id', '=', 'pv.product_id')
                ->where('p.brand_id', $brand->id)
                ->where('o.payment_status', 'paid')
                ->where('o.order_status', '!=', 'cancelled')
                ->whereBetween('o.created_at', [$start, $end])
                ->selectRaw("DATE_FORMAT(o.created_at, '%Y-%m') as ym, SUM(oi.price * oi.quantity) as revenue")
                ->groupByRaw("DATE_FORMAT(o.created_at, '%Y-%m')")
                ->pluck('revenue', 'ym');
        };

        $now = Carbon::now();

        $yearMap = $monthly($now->copy()->startOfYear(), $now->copy()->endOfYear());
        $year = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = sprintf('%04d-%02d', $now->year, $m);
            $year[] = ['label' => 'Th.' . $m, 'value' => (float) ($yearMap[$key] ?? 0)];
        }

        $sixMap = $monthly($now->copy()->startOfMonth()->subMonths(5), $now->copy()->endOfMonth());
        $six = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $six[] = ['label' => 'Th.' . $month->month, 'value' => (float) ($sixMap[$month->format('Y-m')] ?? 0)];
        }

        return ['year' => $year, '6m' => $six];
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);
        $oldStatus = $brand->status;

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
            if ($brand->image && file_exists(public_path($brand->image))) {
                @unlink(public_path($brand->image));
            }

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/brands'), $filename);
            $data['image'] = 'uploads/brands/' . $filename;
        } elseif ($request->input('image_prefilled') !== 'yes') {
            if ($brand->image && file_exists(public_path($brand->image))) {
                @unlink(public_path($brand->image));
            }

            $data['image'] = null;
        }

        $brand->update($data);
        Cache::forget('api.home.sections.v1');

        $message = 'Cập nhật thương hiệu thành công!';
        if ($oldStatus !== $brand->status) {
            $message = $brand->status === 'active'
                ? 'Thương hiệu đã được hiển thị lại thành công!'
                : 'Thương hiệu đã được ẩn thành công!';
        }

        return redirect()
            ->route('admin.brands')
            ->with('success', $message);
    }
}
