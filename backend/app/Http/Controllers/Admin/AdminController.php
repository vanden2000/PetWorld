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

        // -------------------------------------------------------------
        // 1. XÁC ĐỊNH MỐC THỜI GIAN & TÍNH BIỂU ĐỒ THEO CẢM BIẾN REAL-TIME
        // -------------------------------------------------------------
        switch ($period) {
            case 'today':
                $startDate = $now->copy()->startOfDay();
                $chartLabels = ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'];
                
                $chartRevenueData = [];
                $chartOrdersData = [];
                for ($i = 0; $i < 6; $i++) {
                    $bStart = $now->copy()->startOfDay()->addHours($i * 4);
                    $bEnd = $bStart->copy()->addHours(4);
                    
                    $bRev = (float) Order::query()
                        ->where('payment_status', 'paid')
                        ->where('order_status', '!=', 'cancelled')
                        ->whereBetween('created_at', [$bStart, $bEnd])
                        ->sum('total_amount');
                        
                    $bOrders = Order::query()
                        ->where('order_status', '!=', 'cancelled')
                        ->whereBetween('created_at', [$bStart, $bEnd])
                        ->count();
                        
                    $chartRevenueData[] = round($bRev / 1000000, 3);
                    $chartOrdersData[] = $bOrders;
                }
                break;

            case '7days':
                $startDate = $now->copy()->subDays(6)->startOfDay();
                $chartLabels = [];
                $chartRevenueData = [];
                $chartOrdersData = [];
                
                for ($i = 0; $i < 7; $i++) {
                    $day = $startDate->copy()->addDays($i);
                    $bStart = $day->copy()->startOfDay();
                    $bEnd = $day->copy()->endOfDay();
                    
                    $chartLabels[] = $day->format('d/m');
                    
                    $bRev = (float) Order::query()
                        ->where('payment_status', 'paid')
                        ->where('order_status', '!=', 'cancelled')
                        ->whereBetween('created_at', [$bStart, $bEnd])
                        ->sum('total_amount');
                        
                    $bOrders = Order::query()
                        ->where('order_status', '!=', 'cancelled')
                        ->whereBetween('created_at', [$bStart, $bEnd])
                        ->count();
                        
                    $chartRevenueData[] = round($bRev / 1000000, 3);
                    $chartOrdersData[] = $bOrders;
                }
                break;

            case 'year':
                $startDate = $now->copy()->startOfYear();
                $chartLabels = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
                $chartRevenueData = [];
                $chartOrdersData = [];
                
                for ($m = 1; $m <= 12; $m++) {
                    $mStart = $now->copy()->month($m)->startOfMonth();
                    $mEnd = $mStart->copy()->endOfMonth();
                    
                    $bRev = (float) Order::query()
                        ->where('payment_status', 'paid')
                        ->where('order_status', '!=', 'cancelled')
                        ->whereBetween('created_at', [$mStart, $mEnd])
                        ->sum('total_amount');
                        
                    $bOrders = Order::query()
                        ->where('order_status', '!=', 'cancelled')
                        ->whereBetween('created_at', [$mStart, $mEnd])
                        ->count();
                        
                    $chartRevenueData[] = round($bRev / 1000000, 3);
                    $chartOrdersData[] = $bOrders;
                }
                break;

            case '30days':
            default:
                $period = '30days';
                $startDate = $now->copy()->subDays(29)->startOfDay();
                $chartLabels = [];
                $chartRevenueData = [];
                $chartOrdersData = [];
                
                for ($i = 0; $i < 6; $i++) {
                    $bStart = $startDate->copy()->addDays($i * 5);
                    $bEnd = $bStart->copy()->addDays(5)->subSecond();
                    
                    $chartLabels[] = $bStart->format('d/m') . '–' . $bEnd->format('d/m');
                    
                    $bRev = (float) Order::query()
                        ->where('payment_status', 'paid')
                        ->where('order_status', '!=', 'cancelled')
                        ->whereBetween('created_at', [$bStart, $bEnd])
                        ->sum('total_amount');
                        
                    $bOrders = Order::query()
                        ->where('order_status', '!=', 'cancelled')
                        ->whereBetween('created_at', [$bStart, $bEnd])
                        ->count();
                        
                    $chartRevenueData[] = round($bRev / 1000000, 3);
                    $chartOrdersData[] = $bOrders;
                }
                break;
        }

        // -------------------------------------------------------------
        // 2. TÍNH TOÁN CÁC THÔNG SỐ KPI TỜI GIAN THỰC (REAL-TIME DB)
        // -------------------------------------------------------------
        $lengthSeconds = max(1, $now->getTimestamp() - $startDate->getTimestamp());
        $prevEnd = $startDate->copy();
        $prevStart = $startDate->copy()->subSeconds($lengthSeconds);

        $currentRevenue = (float) Order::query()
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $now])
            ->sum('total_amount');

        $currentOrders = Order::query()
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $now])
            ->count();

        $avgOrderValue = $currentOrders > 0 ? ($currentRevenue / $currentOrders) : 0;

        // Kỳ trước để so sánh % tăng trưởng
        $prevRevenue = (float) Order::query()
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->sum('total_amount');

        $prevOrders = Order::query()
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        $prevAov = $prevOrders > 0 ? ($prevRevenue / $prevOrders) : 0;

        $revenueGrowth = $prevRevenue > 0 ? (($currentRevenue - $prevRevenue) / $prevRevenue * 100) : ($currentRevenue > 0 ? 100.0 : 0.0);
        $ordersGrowth = $prevOrders > 0 ? (($currentOrders - $prevOrders) / $prevOrders * 100) : ($currentOrders > 0 ? 100.0 : 0.0);
        $aovGrowth = $prevAov > 0 ? (($avgOrderValue - $prevAov) / $prevAov * 100) : ($avgOrderValue > 0 ? 100.0 : 0.0);

        // Tỷ lệ quay lại của khách hàng
        $uniqueUsers = DB::table('orders')
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $now])
            ->distinct()
            ->count('user_id');

        $returningUsers = DB::table('orders')
            ->select('user_id')
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $now])
            ->groupBy('user_id')
            ->havingRaw('COUNT(id) >= 2')
            ->get()
            ->count();

        $returnRateVal = $uniqueUsers > 0 ? ($returningUsers / $uniqueUsers * 100) : 0;
        $returnRate = number_format($returnRateVal, 1, ',', '.') . '%';

        // Kỳ trước để so sánh % tăng trưởng tỷ lệ quay lại
        $prevUniqueUsers = DB::table('orders')
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->distinct()
            ->count('user_id');

        $prevReturningUsers = DB::table('orders')
            ->select('user_id')
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->groupBy('user_id')
            ->havingRaw('COUNT(id) >= 2')
            ->get()
            ->count();

        $prevReturnRateVal = $prevUniqueUsers > 0 ? ($prevReturningUsers / $prevUniqueUsers * 100) : 0;
        $returnRateGrowth = $prevReturnRateVal > 0 
            ? (($returnRateVal - $prevReturnRateVal) / $prevReturnRateVal * 100) 
            : ($returnRateVal > 0 ? 100.0 : 0.0);

        $totalRevenueAllTime = $currentRevenue;
        $totalOrders = $currentOrders;

        $totalUsersCount = User::query()->where('role', '!=', 'admin')->count();
        $newUsersThisMonth = DB::table('orders')
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $now])
            ->whereIn('user_id', function ($q) use ($now) {
                $q->select('user_id')
                    ->from('orders')
                    ->where('payment_status', 'paid')
                    ->where('order_status', '!=', 'cancelled')
                    ->where('created_at', '<=', $now)
                    ->groupBy('user_id')
                    ->havingRaw('COUNT(id) = 1');
            })
            ->distinct('user_id')
            ->count('user_id');

        $prevUsersCount = User::query()->where('role', '!=', 'admin')->where('created_at', '<=', $startDate)->count();
        $totalUsersGrowth = $prevUsersCount > 0 
            ? (($totalUsersCount - $prevUsersCount) / $prevUsersCount * 100) 
            : ($totalUsersCount > 0 ? 100.0 : 0.0);

        $prevNewUsers = DB::table('orders')
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->whereIn('user_id', function ($q) use ($prevEnd) {
                $q->select('user_id')
                    ->from('orders')
                    ->where('payment_status', 'paid')
                    ->where('order_status', '!=', 'cancelled')
                    ->where('created_at', '<=', $prevEnd)
                    ->groupBy('user_id')
                    ->havingRaw('COUNT(id) = 1');
            })
            ->distinct('user_id')
            ->count('user_id');

        $newUsersGrowth = $prevNewUsers > 0 
            ? (($newUsersThisMonth - $prevNewUsers) / $prevNewUsers * 100) 
            : ($newUsersThisMonth > 0 ? 100.0 : 0.0);

        // -------------------------------------------------------------
        // 3. DANH SÁCH BẢNG PHỤ THỜI GIAN THỰC
        // -------------------------------------------------------------
        // Đơn hàng gần đây
        $recentOrders = Order::query()
            ->with('items:id,order_id,product_name')
            ->latest()
            ->limit(5)
            ->get();

        // Top Sản phẩm bán chạy
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

        // Cảnh báo tồn kho khẩn cấp
        $lowStockProducts = ProductVariant::query()
            ->with('product')
            ->where('quantity', '<', 10)
            ->orderBy('quantity', 'asc')
            ->limit(5)
            ->get();

        // Top Khách hàng chi tiêu theo thời gian lọc
        $topCustomers = DB::table('users as u')
            ->join('orders as o', function ($join) use ($startDate, $now) {
                $join->on('o.user_id', '=', 'u.id')
                    ->where('o.order_status', '!=', 'cancelled')
                    ->where('o.payment_status', '=', 'paid')
                    ->whereBetween('o.created_at', [$startDate, $now]);
            })
            ->where('u.role', '!=', 'admin')
            ->groupBy('u.id', 'u.name')
            ->select(
                'u.name as recipient_name',
                DB::raw('COUNT(o.id) as total_orders'),
                DB::raw('COALESCE(SUM(o.total_amount), 0) as total_spent')
            )
            ->havingRaw('total_spent > 0')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();

        // Nếu chưa có khách hàng chi tiêu trong kỳ, lấy top 5 khách hàng chi tiêu nhiều nhất tổng quan
        if ($topCustomers->isEmpty()) {
            $topCustomers = DB::table('users as u')
                ->join('orders as o', function ($join) {
                    $join->on('o.user_id', '=', 'u.id')
                        ->where('o.order_status', '!=', 'cancelled')
                        ->where('o.payment_status', '=', 'paid');
                })
                ->where('u.role', '!=', 'admin')
                ->groupBy('u.id', 'u.name')
                ->select(
                    'u.name as recipient_name',
                    DB::raw('COUNT(o.id) as total_orders'),
                    DB::raw('COALESCE(SUM(o.total_amount), 0) as total_spent')
                )
                ->havingRaw('total_spent > 0')
                ->orderByDesc('total_spent')
                ->limit(5)
                ->get();
        }

        // Cơ cấu tỷ trọng Danh mục
        $categoryRevenueDb = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->leftJoin('products', 'product_variants.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.order_status', '!=', 'cancelled')
            ->whereBetween('orders.created_at', [$startDate, $now])
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
            $categoryShare = [
                ['name' => 'Thức ăn hạt (Dry Food)', 'percent' => 50, 'color' => '#ff782d'],
                ['name' => 'Pate & Thức ăn ướt', 'percent' => 30, 'color' => '#059669'],
                ['name' => 'Phụ kiện & Đồ dùng', 'percent' => 15, 'color' => '#3b82f6'],
                ['name' => 'Đồ chơi & Spa', 'percent' => 5, 'color' => '#8b5cf6'],
            ];
        }

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
            'shipping' => [],
            'completed' => [],
            'cancelled' => [],
        ];

        $pendingCodRevenue = (float) Order::query()
            ->whereIn('payment_status', ['customer_paid', 'reconciling'])
            ->where('order_status', '!=', 'cancelled')
            ->sum('total_amount');

        $pendingCodCount = Order::query()
            ->whereIn('payment_status', ['customer_paid', 'reconciling'])
            ->where('order_status', '!=', 'cancelled')
            ->count();

        $discrepancyCount = Order::query()
            ->where('payment_status', 'discrepancy')
            ->count();

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
            'returnRateGrowth',
            'avgOrderValue',
            'totalUsersCount',
            'totalUsersGrowth',
            'newUsersThisMonth',
            'newUsersGrowth',
            'recentOrders',
            'bestSellers',
            'lowStockProducts',
            'topCustomers',
            'categoryShare',
            'orderStatusLabels',
            'orderStatusClasses',
            'nextOrderStatusesMap',
            'pendingCodRevenue',
            'pendingCodCount',
            'discrepancyCount'
        ));
    }
}

