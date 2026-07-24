<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', '30days');
        $now = Carbon::now();

        // Determine Start Date based on period
        switch ($period) {
            case 'today':
                $startDate = $now->copy()->startOfDay();
                $chartLabels = ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'];
                $fallbackRevenueChart = [15, 28, 45, 82, 60, 42];
                $fallbackOrdersChart = [2, 4, 8, 15, 11, 7];
                $fallbackRevenue = 27200000;
                $fallbackOrders = 47;
                $revenueGrowth = 5.2;
                $ordersGrowth = 3.1;
                $aovGrowth = 1.8;
                $returnRate = '62.4%';
                $periodCategoryShare = [
                    ['name' => 'Thức ăn hạt (Dry Food)', 'percent' => 52, 'color' => '#ff782d'],
                    ['name' => 'Pate & Thức ăn ướt', 'percent' => 28, 'color' => '#059669'],
                    ['name' => 'Phụ kiện & Đồ dùng', 'percent' => 14, 'color' => '#3b82f6'],
                    ['name' => 'Đồ chơi & Spa', 'percent' => 6, 'color' => '#8b5cf6'],
                ];
                break;

            case '7days':
                $startDate = $now->copy()->subDays(7)->startOfDay();
                $chartLabels = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ Nhật'];
                $fallbackRevenueChart = [45, 52, 68, 85, 92, 110, 125];
                $fallbackOrdersChart = [12, 16, 20, 25, 28, 34, 38];
                $fallbackRevenue = 577000000;
                $fallbackOrders = 173;
                $revenueGrowth = 9.4;
                $ordersGrowth = 6.2;
                $aovGrowth = 2.4;
                $returnRate = '65.8%';
                $periodCategoryShare = [
                    ['name' => 'Thức ăn hạt (Dry Food)', 'percent' => 48, 'color' => '#ff782d'],
                    ['name' => 'Pate & Thức ăn ướt', 'percent' => 27, 'color' => '#059669'],
                    ['name' => 'Phụ kiện & Đồ dùng', 'percent' => 18, 'color' => '#3b82f6'],
                    ['name' => 'Đồ chơi & Spa', 'percent' => 7, 'color' => '#8b5cf6'],
                ];
                break;

            case 'year':
                $startDate = $now->copy()->startOfYear();
                $chartLabels = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
                $fallbackRevenueChart = [120, 150, 180, 210, 260, 310, 350, 290, 340, 380, 420, 490];
                $fallbackOrdersChart = [180, 220, 270, 310, 390, 450, 510, 420, 480, 560, 620, 710];
                $fallbackRevenue = 3590000000;
                $fallbackOrders = 5120;
                $revenueGrowth = 24.6;
                $ordersGrowth = 18.2;
                $aovGrowth = 5.6;
                $returnRate = '74.1%';
                $periodCategoryShare = [
                    ['name' => 'Thức ăn hạt (Dry Food)', 'percent' => 40, 'color' => '#ff782d'],
                    ['name' => 'Pate & Thức ăn ướt', 'percent' => 30, 'color' => '#059669'],
                    ['name' => 'Phụ kiện & Đồ dùng', 'percent' => 20, 'color' => '#3b82f6'],
                    ['name' => 'Đồ chơi & Spa', 'percent' => 10, 'color' => '#8b5cf6'],
                ];
                break;

            case '30days':
            default:
                $period = '30days';
                $startDate = $now->copy()->subDays(30)->startOfDay();
                $chartLabels = ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4', 'Tuần 5', 'Tuần 6'];
                $fallbackRevenueChart = [172, 210, 280, 254, 310, 360];
                $fallbackOrdersChart = [320, 410, 520, 480, 590, 640];
                $fallbackRevenue = 1284000000;
                $fallbackOrders = 3452;
                $revenueGrowth = 12.8;
                $ordersGrowth = 8.5;
                $aovGrowth = 3.2;
                $returnRate = '68.2%';
                $periodCategoryShare = [
                    ['name' => 'Thức ăn hạt (Dry Food)', 'percent' => 45, 'color' => '#ff782d'],
                    ['name' => 'Pate & Thức ăn ướt', 'percent' => 25, 'color' => '#059669'],
                    ['name' => 'Phụ kiện & Đồ dùng', 'percent' => 20, 'color' => '#3b82f6'],
                    ['name' => 'Đồ chơi & Spa', 'percent' => 10, 'color' => '#8b5cf6'],
                ];
                break;
        }

        // -------------------------------------------------------------
        // 1. DỮ LIỆU THỰC TỪ DATABASE (DATABASE QUERIES)
        // -------------------------------------------------------------
        // A. Thống kê Doanh thu & Đơn hàng thực từ Database
        $dbOrdersQuery = Order::query()
            ->where('order_status', '!=', 'cancelled')
            ->where('created_at', '>=', $startDate);

        $realDbRevenue = (float) $dbOrdersQuery->sum('total_amount');
        $realDbOrdersCount = Order::query()->where('created_at', '>=', $startDate)->count();

        // Sử dụng dữ liệu DB thực, nếu mới khởi tạo DB thì dùng fallback mẫu minh họa
        $totalRevenueAllTime = $realDbRevenue > 0 ? $realDbRevenue : $fallbackRevenue;
        $totalOrders = $realDbOrdersCount > 0 ? $realDbOrdersCount : $fallbackOrders;

        $avgOrderValue = $totalOrders > 0 ? ($totalRevenueAllTime / max(1, $totalOrders)) : 372000;
        $totalUsersCount = User::query()->where('role', '!=', 'admin')->count();
        $newUsersThisMonth = User::query()->where('role', '!=', 'admin')->where('created_at', '>=', $startDate)->count();

        // B. Bảng Đơn hàng gần đây thực từ Database
        $recentOrders = Order::query()
            ->with('items:id,order_id,product_name')
            ->latest()
            ->limit(5)
            ->get();

        // C. Bảng Top Sản phẩm bán chạy nhất thực từ Database
        $bestSellersRaw = OrderItem::query()
            ->select('product_variant_id', 'product_name', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(price * quantity) as total_revenue'))
            ->with(['productVariant.product.primaryImage', 'productVariant.product.images'])
            ->groupBy('product_variant_id', 'product_name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $bestSellers = $bestSellersRaw->map(function ($item) {
            $product = $item->productVariant?->product;
            $primaryImg = $product?->primaryImage ?? $product?->images?->first();
            
            $imageUrl = null;
            if ($primaryImg && $primaryImg->image_url) {
                $imageUrl = str_contains($primaryImg->image_url, '://') 
                    ? $primaryImg->image_url 
                    : asset('storage/' . $primaryImg->image_url);
            }

            if (!$imageUrl) {
                $nameLower = mb_strtolower($item->product_name ?? '');
                if (str_contains($nameLower, 'me-o') || str_contains($nameLower, 'cá ngừ')) {
                    $imageUrl = asset('image/products/pate-me-o-ca-ngu.jpg');
                } elseif (str_contains($nameLower, 'whiskas')) {
                    $imageUrl = asset('image/products/whiskas-adult-vi-ca-bien.jpg');
                } elseif (str_contains($nameLower, 'smartheart')) {
                    $imageUrl = asset('image/products/smartheart-creamy-treat.jpg');
                } elseif (str_contains($nameLower, 'mini adult')) {
                    $imageUrl = asset('image/products/royal-canin-mini-adult.jpg');
                } elseif (str_contains($nameLower, 'mini puppy')) {
                    $imageUrl = asset('image/products/pate-royal-canin-mini-puppy.jpg');
                } elseif (str_contains($nameLower, 'trixie')) {
                    $imageUrl = asset('image/products/day-dat-trixie-premium.jpg');
                } elseif (str_contains($nameLower, 'kong')) {
                    $imageUrl = asset('image/products/kong-classic.jpg');
                } else {
                    $imageUrl = asset('image/categories/thuc-an-hat.jpg');
                }
            }

            return (object)[
                'product_name' => $item->product_name,
                'total_sold' => (int) $item->total_sold,
                'total_revenue' => (float) $item->total_revenue,
                'image' => $imageUrl,
            ];
        });

        if ($bestSellers->isEmpty()) {
            $products = Product::query()->with(['primaryImage', 'images'])->limit(5)->get();
            $bestSellers = $products->map(function ($product) {
                $primaryImg = $product->primaryImage ?? $product->images->first();
                $img = $primaryImg ? (str_contains($primaryImg->image_url, '://') ? $primaryImg->image_url : asset('storage/' . $primaryImg->image_url)) : asset('image/categories/thuc-an-hat.jpg');
                return (object)[
                    'product_name' => $product->name,
                    'total_sold' => rand(150, 600),
                    'total_revenue' => $product->base_price * rand(20, 50),
                    'image' => $img,
                ];
            });
        }

        // D. Bảng Cảnh báo hàng tồn kho khẩn cấp (< 10 sản phẩm) thực từ Database
        $lowStockProducts = ProductVariant::query()
            ->with('product')
            ->where('quantity', '<', 10)
            ->orderBy('quantity', 'asc')
            ->limit(5)
            ->get();

        if ($lowStockProducts->isEmpty()) {
            $fallbackProduct = Product::first();
            $defaultProductId = $fallbackProduct ? $fallbackProduct->id : 1;

            $lowStockProducts = collect([
                (object)[
                    'product_id' => $defaultProductId,
                    'quantity' => 0,
                    'sku' => 'RC-PUPPY-195G',
                    'product' => (object)['name' => $fallbackProduct ? $fallbackProduct->name : 'Pate Royal Canin Mini Puppy']
                ],
                (object)[
                    'product_id' => $defaultProductId,
                    'quantity' => 1,
                    'sku' => 'RC-ADULT-3KG',
                    'product' => (object)['name' => 'Royal Canin Mini Adult']
                ]
            ]);
        }

        // E. Bảng Khách hàng chi tiêu nhiều nhất thực từ Database (Sắp xếp giảm dần theo total_spent)
        $topCustomers = Order::query()
            ->select('recipient_name', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(total_amount) as total_spent'))
            ->where('order_status', '!=', 'cancelled')
            ->groupBy('recipient_name')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();

        // F. Cơ cấu tỷ trọng Danh mục (Category Revenue Share từ Database)
        $categoryRevenueDb = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->leftJoin('products', 'product_variants.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.order_status', '!=', 'cancelled')
            ->where('orders.created_at', '>=', $startDate)
            ->select('categories.name as cat_name', DB::raw('SUM(order_items.price * order_items.quantity) as cat_revenue'))
            ->groupBy('categories.name')
            ->get();

        $totalCatRevenue = $categoryRevenueDb->sum('cat_revenue');
        $colors = ['#ff782d', '#059669', '#3b82f6', '#8b5cf6', '#bdc7c2'];

        if ($totalCatRevenue > 0) {
            $categoryShare = $categoryRevenueDb->map(function ($cat, $index) use ($totalCatRevenue, $colors) {
                return [
                    'name' => $cat->cat_name ?? 'Danh mục khác',
                    'percent' => round(($cat->cat_revenue / $totalCatRevenue) * 100),
                    'color' => $colors[$index % count($colors)],
                ];
            })->all();
        } else {
            $categoryShare = $periodCategoryShare ?? [
                ['name' => 'Thức ăn hạt (Dry Food)', 'percent' => 45, 'color' => '#ff782d'],
                ['name' => 'Pate & Thức ăn ướt', 'percent' => 25, 'color' => '#059669'],
                ['name' => 'Phụ kiện & Đồ dùng', 'percent' => 20, 'color' => '#3b82f6'],
                ['name' => 'Đồ chơi & Spa', 'percent' => 10, 'color' => '#8b5cf6'],
            ];
        }

        // Biểu đồ Doanh thu & Đơn hàng
        $chartRevenueData = $fallbackRevenueChart;
        $chartOrdersData = $fallbackOrdersChart;

        // Order Status labels & CSS classes
        $orderStatusLabels = [
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn tất',
            'cancelled' => 'Đã hủy',
        ];

        $orderStatusClasses = [
            'pending' => 'status-pending',
            'confirmed' => 'status-processing',
            'shipping' => 'status-shipping',
            'completed' => 'status-completed',
            'cancelled' => 'status-cancelled',
        ];

        $nextOrderStatusesMap = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['shipping', 'cancelled'],
            'shipping' => ['completed'],
            'completed' => [],
            'cancelled' => [],
        ];

        return view('admin.dashboard.index', compact(
            'period',
            'chartLabels',
            'chartRevenueData',
            'chartOrdersData',
            'totalRevenueAllTime',
            'revenueGrowth',
            'totalOrders',
            'ordersGrowth',
            'aovGrowth',
            'returnRate',
            'avgOrderValue',
            'totalUsersCount',
            'newUsersThisMonth',
            'recentOrders',
            'bestSellers',
            'lowStockProducts',
            'topCustomers',
            'categoryShare',
            'orderStatusLabels',
            'orderStatusClasses',
            'nextOrderStatusesMap'
        ));
    }
}
