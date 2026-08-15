<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /** Bảng màu cho danh mục (theo màu chủ đạo + phối bổ trợ). */
    private const CATEGORY_PALETTE = ['#ff782d', '#4b6b60', '#825736', '#2563eb', '#9333ea', '#0d9488', '#bdc7c2'];

    /** Số khách hàng hiển thị trong bảng "chi tiêu nhiều nhất". */
    private const TOP_CUSTOMERS_LIMIT = 8;

    public function revenue()
    {
        $now = Carbon::now();

        $periods = [
            'today' => $this->revenueForRange($now->copy()->startOfDay(), $now->copy(), 'hour'),
            '7days' => $this->revenueForRange($now->copy()->subDays(6)->startOfDay(), $now->copy(), 'day'),
            '30days' => $this->revenueForRange($now->copy()->subDays(29)->startOfDay(), $now->copy(), 'week'),
        ];

        return view('Admin.reports.revenue', compact('periods'));
    }

    /**
     * Chỉ tính doanh thu từ đơn đã thanh toán và chưa hủy (tiền thực sự thu về).
     */
    private function baseRevenueQuery(): Builder
    {
        return Order::query()
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled');
    }

    /**
     * Gom toàn bộ số liệu cho một khoảng thời gian: KPI, xu hướng so với kỳ trước,
     * doanh thu theo danh mục và bảng chi tiết theo thời gian.
     */
    private function revenueForRange(Carbon $start, Carbon $end, string $bucket): array
    {
        $current = $this->rangeAggregate($start, $end);

        // Kỳ liền trước có cùng độ dài để tính % thay đổi.
        $lengthSeconds = max(1, $end->getTimestamp() - $start->getTimestamp());
        $prevEnd = $start->copy();
        $prevStart = $start->copy()->subSeconds($lengthSeconds);
        $previous = $this->rangeAggregate($prevStart, $prevEnd);

        $table = $this->revenueTableRows($start, $end, $bucket);

        return [
            'revenue' => $this->money($current['net']),
            'orders' => number_format($current['count'], 0, ',', '.') . ' đơn',
            'aov' => $this->money($current['aov']),
            'discountRate' => $this->percent($current['discountRate']),
            'trends' => [
                'revenue' => $this->trend($current['net'], $previous['net']),
                'orders' => $this->trend($current['count'], $previous['count']),
                'aov' => $this->trend($current['aov'], $previous['aov']),
                'discount' => $this->trend($current['discountRate'], $previous['discountRate']),
            ],
            'categories' => $this->revenueByCategory($start, $end),
            'table' => $table,
            // Biểu đồ cột: đảo về thứ tự thời gian tăng dần (cũ -> mới), giá trị số để vẽ.
            'chart' => array_map(
                fn (array $row): array => ['label' => $row['time'], 'value' => $row['netRaw']],
                array_reverse($table)
            ),
        ];
    }

    /** KPI tổng hợp cho một khoảng: số đơn, doanh thu thực, giảm giá, AOV, tỷ lệ giảm giá. */
    private function rangeAggregate(Carbon $start, Carbon $end): array
    {
        $row = $this->baseRevenueQuery()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(total_amount), 0) as net, COALESCE(SUM(discount_amount), 0) as discount')
            ->first();

        $count = (int) ($row->cnt ?? 0);
        $net = (float) ($row->net ?? 0);
        $discount = (float) ($row->discount ?? 0);
        $gross = $net + $discount; // doanh thu thô = doanh thu thực + giảm giá

        return [
            'count' => $count,
            'net' => $net,
            'discount' => $discount,
            'aov' => $count > 0 ? $net / $count : 0,
            'discountRate' => $gross > 0 ? $discount / $gross * 100 : 0,
        ];
    }

    /** Doanh thu theo danh mục (dựa trên giá trị dòng hàng của các đơn đã thanh toán). */
    private function revenueByCategory(Carbon $start, Carbon $end): array
    {
        $rows = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->where('o.payment_status', 'paid')
            ->where('o.order_status', '!=', 'cancelled')
            ->whereBetween('o.created_at', [$start, $end])
            ->groupBy('c.id', 'c.name')
            ->selectRaw('c.name as name, SUM(oi.price * oi.quantity) as revenue')
            ->orderByDesc('revenue')
            ->get();

        $total = (float) $rows->sum('revenue');

        return $rows->values()->map(function ($row, $index) use ($total): array {
            $revenue = (float) $row->revenue;

            return [
                'name' => $row->name,
                'percentage' => ($total > 0 ? round($revenue / $total * 100) : 0) . '%',
                'val' => $this->money($revenue),
                'color' => self::CATEGORY_PALETTE[$index % count(self::CATEGORY_PALETTE)],
            ];
        })->all();
    }

    /** Bảng chi tiết doanh thu chia theo giờ / ngày / tuần tùy khoảng thời gian. */
    private function revenueTableRows(Carbon $start, Carbon $end, string $bucket): array
    {
        $base = $this->baseRevenueQuery()->whereBetween('created_at', [$start, $end]);

        if ($bucket === 'hour') {
            $rows = $base
                ->selectRaw("DATE_FORMAT(created_at, '%H:00') as label, MIN(created_at) as sort_key, COUNT(*) as cnt, COALESCE(SUM(total_amount), 0) as net, COALESCE(SUM(discount_amount), 0) as discount")
                ->groupByRaw("DATE_FORMAT(created_at, '%H:00')")
                ->orderByRaw('MIN(created_at) DESC')
                ->get();
        } elseif ($bucket === 'day') {
            $rows = $base
                ->selectRaw('DATE(created_at) as day, COUNT(*) as cnt, COALESCE(SUM(total_amount), 0) as net, COALESCE(SUM(discount_amount), 0) as discount')
                ->groupByRaw('DATE(created_at)')
                ->orderByRaw('DATE(created_at) DESC')
                ->get();
        } else { // week
            $rows = $base
                ->selectRaw('YEARWEEK(created_at, 3) as yw, COUNT(*) as cnt, COALESCE(SUM(total_amount), 0) as net, COALESCE(SUM(discount_amount), 0) as discount')
                ->groupByRaw('YEARWEEK(created_at, 3)')
                ->orderByRaw('YEARWEEK(created_at, 3) DESC')
                ->get();
        }

        return $rows->map(function ($row) use ($bucket): array {
            $net = (float) $row->net;
            $discount = (float) $row->discount;
            $gross = $net + $discount;

            if ($bucket === 'hour') {
                $label = $row->label;
            } elseif ($bucket === 'day') {
                $label = Carbon::parse($row->day)->format('d/m');
            } else {
                $label = $this->weekLabel($row->yw);
            }

            return [
                'time' => $label,
                'count' => (int) $row->cnt,
                'gross' => $this->money($gross),
                'discount' => $this->money($discount),
                'net' => $this->money($net),
                'netRaw' => $net,
            ];
        })->all();
    }

    /** So sánh với kỳ trước, trả về chuỗi % và hướng (tăng/giảm). */
    private function trend(float $current, float $previous): array
    {
        if ($previous <= 0) {
            $pct = $current > 0 ? 100.0 : 0.0;
        } else {
            $pct = ($current - $previous) / $previous * 100;
        }

        $up = $pct >= 0;

        return [
            'pct' => ($up ? '+' : '−') . number_format(abs($pct), 1, ',', '.') . '%',
            'up' => $up,
        ];
    }

    /**
     * Nhãn tuần ISO (thứ Hai–Chủ Nhật) dựng từ YEARWEEK, không phụ thuộc ngày
     * có đơn sớm/muộn nhất nên tuần chỉ có một đơn vẫn hiện đủ 7 ngày.
     */
    private function weekLabel(string|int $yearWeek): string
    {
        $yw = (string) $yearWeek;
        $year = (int) substr($yw, 0, 4);
        $week = (int) substr($yw, 4);

        $start = Carbon::now()->setISODate($year, $week)->startOfWeek();

        return 'Tuần ' . $start->format('d/m') . '–' . $start->copy()->endOfWeek()->format('d/m');
    }

    private function money(float $value): string
    {
        return number_format($value, 0, ',', '.') . 'đ';
    }

    private function percent(float $value): string
    {
        return number_format($value, 1, ',', '.') . '%';
    }

    public function orderStatus()
    {
        $now = Carbon::now();

        $periods = [
            'today' => $this->orderStatusForRange($now->copy()->startOfDay(), $now->copy(), 'hour'),
            '7days' => $this->orderStatusForRange($now->copy()->subDays(6)->startOfDay(), $now->copy(), 'day'),
            '30days' => $this->orderStatusForRange($now->copy()->subDays(29)->startOfDay(), $now->copy(), 'week'),
        ];

        return view('Admin.reports.order_status', compact('periods'));
    }

    public function customers()
    {
        $now = Carbon::now();

        $periods = [
            'today' => $this->customersForRange($now->copy()->startOfDay(), $now->copy(), 'hour'),
            '7days' => $this->customersForRange($now->copy()->subDays(6)->startOfDay(), $now->copy(), 'day'),
            '30days' => $this->customersForRange($now->copy()->subDays(29)->startOfDay(), $now->copy(), 'week'),
        ];

        return view('Admin.reports.customers', compact('periods'));
    }

    public function bestSellers()
    {
        $now = Carbon::now();

        $periods = [
            'today' => $this->bestSellersForRange($now->copy()->startOfDay(), $now->copy(), 'hour'),
            '7days' => $this->bestSellersForRange($now->copy()->subDays(6)->startOfDay(), $now->copy(), 'day'),
            '30days' => $this->bestSellersForRange($now->copy()->subDays(29)->startOfDay(), $now->copy(), 'week'),
        ];

        return view('Admin.reports.best_sellers', compact('periods'));
    }

    public function lowStock()
    {
        $periods = [
            'today' => $this->lowStockData(),
            '7days' => $this->lowStockData(),
            '30days' => $this->lowStockData(),
        ];

        return view('Admin.reports.low_stock', compact('periods'));
    }

    public function latestOrders()
    {
        $now = Carbon::now();

        \Illuminate\Support\Facades\Log::info('--- DEBUG LATEST ORDERS ---');
        \Illuminate\Support\Facades\Log::info('DB Database: ' . \Illuminate\Support\Facades\DB::connection()->getDatabaseName());
        \Illuminate\Support\Facades\Log::info('Total orders in DB: ' . \App\Models\Order::count());

        $periods = [
            'today' => $this->latestOrdersForRange($now->copy()->startOfDay(), $now->copy(), 'hour'),
            '7days' => $this->latestOrdersForRange($now->copy()->subDays(6)->startOfDay(), $now->copy(), 'day'),
            '30days' => $this->latestOrdersForRange($now->copy()->subDays(29)->startOfDay(), $now->copy(), 'week'),
        ];

        return view('Admin.reports.latest_orders', compact('periods'));
    }

    public function profit()
    {
        $now = Carbon::now();

        $periods = [
            'today' => $this->profitForRange($now->copy()->startOfDay(), $now->copy(), 'hour'),
            '7days' => $this->profitForRange($now->copy()->subDays(6)->startOfDay(), $now->copy(), 'day'),
            '30days' => $this->profitForRange($now->copy()->subDays(29)->startOfDay(), $now->copy(), 'week'),
        ];

        return view('Admin.reports.profit', compact('periods'));
    }

    private function profitForRange(Carbon $start, Carbon $end, string $bucket): array
    {
        $current = $this->profitRangeAggregate($start, $end);

        $lengthSeconds = max(1, $end->getTimestamp() - $start->getTimestamp());
        $prevEnd = $start->copy();
        $prevStart = $start->copy()->subSeconds($lengthSeconds);
        $previous = $this->profitRangeAggregate($prevStart, $prevEnd);

        $table = $this->profitTableRows($start, $end, $bucket);

        return [
            'revenue' => $this->money($current['revenue']),
            'cost' => $this->money($current['cost']),
            'profit' => $this->money($current['profit']),
            'margin' => $this->percent($current['margin']),
            'trends' => [
                'revenue' => $this->trend($current['revenue'], $previous['revenue']),
                'cost' => $this->trend($current['cost'], $previous['cost']),
                'profit' => $this->trend($current['profit'], $previous['profit']),
                'margin' => $this->trend($current['margin'], $previous['margin']),
            ],
            'categories' => $this->profitByCategory($start, $end),
            'table' => $table,
            'chart' => array_map(
                fn (array $row): array => [
                    'label' => $row['time'], 
                    'revenue' => $row['revenueRaw'], 
                    'profit' => $row['profitRaw']
                ],
                array_reverse($table)
            ),
        ];
    }

    private function profitRangeAggregate(Carbon $start, Carbon $end): array
    {
        $rows = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->where('o.payment_status', 'paid')
            ->where('o.order_status', '!=', 'cancelled')
            ->whereBetween('o.created_at', [$start, $end])
            ->selectRaw('
                SUM(oi.price * oi.quantity) as gross_revenue,
                SUM(oi.price * oi.quantity * (
                    CASE 
                        WHEN c.name LIKE "%Thức ăn%" THEN 0.70 
                        WHEN c.name LIKE "%Phụ kiện%" THEN 0.50 
                        WHEN c.name LIKE "%Đồ chơi%" THEN 0.45 
                        ELSE 0.60 
                    END
                )) as gross_cost
            ')
            ->first();

        $grossRevenue = (float) ($rows->gross_revenue ?? 0);
        $cost = (float) ($rows->gross_cost ?? 0);

        $orderStats = DB::table('orders')
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('SUM(total_amount) as net_revenue, SUM(discount_amount) as discount')
            ->first();

        $netRevenue = (float) ($orderStats->net_revenue ?? 0);

        if ($grossRevenue > 0) {
            $cost = $cost * ($netRevenue / $grossRevenue);
        } else {
            $cost = 0;
        }

        $profit = $netRevenue - $cost;
        $margin = $netRevenue > 0 ? ($profit / $netRevenue) * 100 : 0;

        return [
            'revenue' => $netRevenue,
            'cost' => $cost,
            'profit' => $profit,
            'margin' => $margin,
        ];
    }

    private function profitByCategory(Carbon $start, Carbon $end): array
    {
        $rows = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->where('o.payment_status', 'paid')
            ->where('o.order_status', '!=', 'cancelled')
            ->whereBetween('o.created_at', [$start, $end])
            ->groupBy('c.id', 'c.name')
            ->selectRaw('
                c.name as name, 
                SUM(oi.price * oi.quantity) as revenue,
                SUM(oi.price * oi.quantity * (
                    CASE 
                        WHEN c.name LIKE "%Thức ăn%" THEN 0.70 
                        WHEN c.name LIKE "%Phụ kiện%" THEN 0.50 
                        WHEN c.name LIKE "%Đồ chơi%" THEN 0.45 
                        ELSE 0.60 
                    END
                )) as cost
            ')
            ->get();

        $grossRevenue = (float) $rows->sum('revenue');
        $netRevenue = (float) DB::table('orders')
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_amount');

        $factor = $grossRevenue > 0 ? ($netRevenue / $grossRevenue) : 1.0;

        $totalProfit = 0;
        $categoriesData = [];

        foreach ($rows as $row) {
            $rev = (float) $row->revenue * $factor;
            $cst = (float) $row->cost * $factor;
            $prof = $rev - $cst;
            $totalProfit += $prof;

            $categoriesData[] = [
                'name' => $row->name,
                'revenue' => $rev,
                'cost' => $cst,
                'profit' => $prof,
                'margin' => $rev > 0 ? ($prof / $rev) * 100 : 0,
            ];
        }

        usort($categoriesData, fn($a, $b) => $b['profit'] <=> $a['profit']);

        return array_map(function ($cat, $index) use ($totalProfit) {
            return [
                'name' => $cat['name'],
                'percentage' => ($totalProfit > 0 ? round($cat['profit'] / $totalProfit * 100) : 0) . '%',
                'revenue' => $this->money($cat['revenue']),
                'cost' => $this->money($cat['cost']),
                'profit' => $this->money($cat['profit']),
                'margin' => $this->percent($cat['margin']),
                'color' => self::CATEGORY_PALETTE[$index % count(self::CATEGORY_PALETTE)],
            ];
        }, $categoriesData, array_keys($categoriesData));
    }

    private function profitTableRows(Carbon $start, Carbon $end, string $bucket): array
    {
        if ($bucket === 'hour') {
            $rows = DB::table('order_items as oi')
                ->join('orders as o', 'o.id', '=', 'oi.order_id')
                ->join('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
                ->join('products as p', 'p.id', '=', 'pv.product_id')
                ->join('categories as c', 'c.id', '=', 'p.category_id')
                ->where('o.payment_status', 'paid')
                ->where('o.order_status', '!=', 'cancelled')
                ->whereBetween('o.created_at', [$start, $end])
                ->selectRaw('
                    DATE_FORMAT(o.created_at, "%H:00") as label,
                    MIN(o.created_at) as sort_key,
                    SUM(oi.price * oi.quantity) as revenue,
                    SUM(oi.price * oi.quantity * (
                        CASE 
                            WHEN c.name LIKE "%Thức ăn%" THEN 0.70 
                            WHEN c.name LIKE "%Phụ kiện%" THEN 0.50 
                            WHEN c.name LIKE "%Đồ chơi%" THEN 0.45 
                            ELSE 0.60 
                        END
                    )) as cost
                ')
                ->groupByRaw('DATE_FORMAT(o.created_at, "%H:00")')
                ->orderByRaw('MIN(o.created_at) DESC')
                ->get();
        } elseif ($bucket === 'day') {
            $rows = DB::table('order_items as oi')
                ->join('orders as o', 'o.id', '=', 'oi.order_id')
                ->join('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
                ->join('products as p', 'p.id', '=', 'pv.product_id')
                ->join('categories as c', 'c.id', '=', 'p.category_id')
                ->where('o.payment_status', 'paid')
                ->where('o.order_status', '!=', 'cancelled')
                ->whereBetween('o.created_at', [$start, $end])
                ->selectRaw('
                    DATE(o.created_at) as day,
                    SUM(oi.price * oi.quantity) as revenue,
                    SUM(oi.price * oi.quantity * (
                        CASE 
                            WHEN c.name LIKE "%Thức ăn%" THEN 0.70 
                            WHEN c.name LIKE "%Phụ kiện%" THEN 0.50 
                            WHEN c.name LIKE "%Đồ chơi%" THEN 0.45 
                            ELSE 0.60 
                        END
                    )) as cost
                ')
                ->groupByRaw('DATE(o.created_at)')
                ->orderByRaw('DATE(o.created_at) DESC')
                ->get();
        } else {
            $rows = DB::table('order_items as oi')
                ->join('orders as o', 'o.id', '=', 'oi.order_id')
                ->join('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
                ->join('products as p', 'p.id', '=', 'pv.product_id')
                ->join('categories as c', 'c.id', '=', 'p.category_id')
                ->where('o.payment_status', 'paid')
                ->where('o.order_status', '!=', 'cancelled')
                ->whereBetween('o.created_at', [$start, $end])
                ->selectRaw('
                    YEARWEEK(o.created_at, 3) as yw,
                    SUM(oi.price * oi.quantity) as revenue,
                    SUM(oi.price * oi.quantity * (
                        CASE 
                            WHEN c.name LIKE "%Thức ăn%" THEN 0.70 
                            WHEN c.name LIKE "%Phụ kiện%" THEN 0.50 
                            WHEN c.name LIKE "%Đồ chơi%" THEN 0.45 
                            ELSE 0.60 
                        END
                    )) as cost
                ')
                ->groupByRaw('YEARWEEK(o.created_at, 3)')
                ->orderByRaw('YEARWEEK(o.created_at, 3) DESC')
                ->get();
        }

        $grossRevenue = (float) $rows->sum('revenue');
        $netRevenue = (float) DB::table('orders')
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_amount');

        $factor = $grossRevenue > 0 ? ($netRevenue / $grossRevenue) : 1.0;

        return $rows->map(function ($row) use ($bucket, $factor): array {
            $rev = (float) $row->revenue * $factor;
            $cst = (float) $row->cost * $factor;
            $prof = $rev - $cst;
            $marg = $rev > 0 ? ($prof / $rev) * 100 : 0;

            if ($bucket === 'hour') {
                $label = $row->label;
            } elseif ($bucket === 'day') {
                $label = Carbon::parse($row->day)->format('d/m');
            } else {
                $label = $this->weekLabel($row->yw);
            }

            return [
                'time' => $label,
                'revenue' => $this->money($rev),
                'cost' => $this->money($cst),
                'profit' => $this->money($prof),
                'margin' => $this->percent($marg),
                'revenueRaw' => $rev,
                'profitRaw' => $prof,
            ];
        })->all();
    }

    private function orderStatusForRange(Carbon $start, Carbon $end, string $bucket): array
    {
        $current = $this->orderStatusRangeAggregate($start, $end);

        $lengthSeconds = max(1, $end->getTimestamp() - $start->getTimestamp());
        $prevEnd = $start->copy();
        $prevStart = $start->copy()->subSeconds($lengthSeconds);
        $previous = $this->orderStatusRangeAggregate($prevStart, $prevEnd);

        $table = $this->orderStatusTableRows($start, $end, $bucket);

        return [
            'total' => number_format($current['total'], 0, ',', '.') . ' đơn',
            'completed' => number_format($current['completed'], 0, ',', '.') . ' đơn',
            'pending' => number_format($current['pending'], 0, ',', '.') . ' đơn',
            'cancelled' => number_format($current['cancelled'], 0, ',', '.') . ' đơn',
            'totalTrend' => $this->trend($current['total'], $previous['total']),
            'completedTrend' => $this->trend($current['completed'], $previous['completed']),
            'pendingTrend' => $this->trend($current['pending'], $previous['pending']),
            'cancelledTrend' => $this->trend($current['cancelled'], $previous['cancelled']),
            'statuses' => $this->orderStatusBreakdown($start, $end, $current['total']),
            'chart' => array_map(
                fn (array $row): array => ['label' => $row['time'], 'value' => $row['count']],
                array_reverse($table)
            ),
        ];
    }

    private function orderStatusRangeAggregate(Carbon $start, Carbon $end): array
    {
        $rows = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN order_status = "completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN order_status = "pending" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN order_status = "cancelled" THEN 1 ELSE 0 END) as cancelled
            ')
            ->first();

        return [
            'total' => (int) ($rows->total ?? 0),
            'completed' => (int) ($rows->completed ?? 0),
            'pending' => (int) ($rows->pending ?? 0),
            'cancelled' => (int) ($rows->cancelled ?? 0),
        ];
    }

    private function orderStatusBreakdown(Carbon $start, Carbon $end, int $total): array
    {
        $rows = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('order_status as status, COUNT(*) as count')
            ->groupBy('order_status')
            ->get();

        $statusMap = [
            'completed' => ['name' => 'Hoàn tất', 'color' => '#10b981', 'badge' => 'badge-completed', 'note' => 'Hoàn thành tốt'],
            'pending' => ['name' => 'Chờ xác nhận', 'color' => '#f59e0b', 'badge' => 'badge-pending', 'note' => 'Đang chờ duyệt'],
            'confirmed' => ['name' => 'Đã xác nhận', 'color' => '#8b5cf6', 'badge' => 'badge-confirmed', 'note' => 'Đang chuẩn bị hàng'],
            'cancelled' => ['name' => 'Đã hủy', 'color' => '#ef4444', 'badge' => 'badge-cancelled', 'note' => 'Kiểm soát tốt'],
            'shipping' => ['name' => 'Đang giao', 'color' => '#3b82f6', 'badge' => 'badge-shipping', 'note' => 'Đúng tiến độ'],
        ];

        return $rows->map(function ($row) use ($total, $statusMap) {
            $status = $row->status;
            $info = $statusMap[$status] ?? ['name' => $status, 'color' => '#94a3b8', 'badge' => 'badge-pending', 'note' => 'Ghi nhận'];
            
            return [
                'name' => $info['name'],
                'count' => (int) $row->count,
                'percentage' => ($total > 0 ? round($row->count / $total * 100, 1) : 0) . '%',
                'color' => $info['color'],
                'badge' => $info['badge'],
                'note' => $info['note'],
                'noteUp' => true,
            ];
        })->all();
    }

    private function orderStatusTableRows(Carbon $start, Carbon $end, string $bucket): array
    {
        $base = Order::query()->whereBetween('created_at', [$start, $end]);

        if ($bucket === 'hour') {
            $rows = $base
                ->selectRaw("DATE_FORMAT(created_at, '%H:00') as label, MIN(created_at) as sort_key, COUNT(*) as count")
                ->groupByRaw("DATE_FORMAT(created_at, '%H:00')")
                ->orderByRaw('MIN(created_at) DESC')
                ->get();
        } elseif ($bucket === 'day') {
            $rows = $base
                ->selectRaw('DATE(created_at) as day, COUNT(*) as count')
                ->groupByRaw('DATE(created_at)')
                ->orderByRaw('DATE(created_at) DESC')
                ->get();
        } else {
            $rows = $base
                ->selectRaw('YEARWEEK(created_at, 3) as yw, COUNT(*) as count')
                ->groupByRaw('YEARWEEK(created_at, 3)')
                ->orderByRaw('YEARWEEK(created_at, 3) DESC')
                ->get();
        }

        return $rows->map(function ($row) use ($bucket) {
            if ($bucket === 'hour') {
                $label = $row->label;
            } elseif ($bucket === 'day') {
                $label = Carbon::parse($row->day)->format('d/m');
            } else {
                $label = $this->weekLabel($row->yw);
            }

            return [
                'time' => $label,
                'count' => (int) $row->count,
            ];
        })->all();
    }

    private function customersForRange(Carbon $start, Carbon $end, string $bucket): array
    {
        $totalCustomers = User::where('role', 'user')->count();
        
        // Khách mua hàng lần đầu tiên (có đúng 1 đơn hàng thanh toán thành công)
        $newCustomers = DB::table('orders')
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('user_id', function ($q) use ($end) {
                $q->select('user_id')
                    ->from('orders')
                    ->where('payment_status', 'paid')
                    ->where('order_status', '!=', 'cancelled')
                    ->where('created_at', '<=', $end)
                    ->groupBy('user_id')
                    ->havingRaw('COUNT(id) = 1');
            })
            ->distinct('user_id')
            ->count('user_id');

        $lengthSeconds = max(1, $end->getTimestamp() - $start->getTimestamp());
        $prevEnd = $start->copy();
        $prevStart = $start->copy()->subSeconds($lengthSeconds);
        
        $prevNew = DB::table('orders')
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

        $orderedStats = DB::table('orders')
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('COUNT(DISTINCT user_id) as unique_users, SUM(total_amount) as total_spent')
            ->first();

        $uniqueUsers = (int) ($orderedStats->unique_users ?? 0);
        $totalSpent = (float) ($orderedStats->total_spent ?? 0);

        // Khách mua từ lần thứ 2 trở lên
        $returningUsers = DB::table('orders')
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('user_id', function ($q) use ($end) {
                $q->select('user_id')
                    ->from('orders')
                    ->where('payment_status', 'paid')
                    ->where('order_status', '!=', 'cancelled')
                    ->where('created_at', '<=', $end)
                    ->groupBy('user_id')
                    ->havingRaw('COUNT(id) >= 2');
            })
            ->distinct('user_id')
            ->count('user_id');

        $returningRate = $uniqueUsers > 0 ? ($returningUsers / $uniqueUsers) * 100 : 0;
        $avgSpent = $uniqueUsers > 0 ? $totalSpent / $uniqueUsers : 0;

        $prevOrderedStats = DB::table('orders')
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->selectRaw('COUNT(DISTINCT user_id) as unique_users, SUM(total_amount) as total_spent')
            ->first();
        
        $prevUniqueUsers = (int) ($prevOrderedStats->unique_users ?? 0);
        $prevTotalSpent = (float) ($prevOrderedStats->total_spent ?? 0);
        $prevAvgSpent = $prevUniqueUsers > 0 ? $prevTotalSpent / $prevUniqueUsers : 0;

        $prevReturningUsers = DB::table('orders')
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
                    ->havingRaw('COUNT(id) >= 2');
            })
            ->distinct('user_id')
            ->count('user_id');
        $prevReturningRate = $prevUniqueUsers > 0 ? ($prevReturningUsers / $prevUniqueUsers) * 100 : 0;

        $topCustomers = $this->topCustomersList($start, $end);
        $table = $this->newCustomersTableRows($start, $end, $bucket);

        return [
            'total' => number_format($totalCustomers, 0, ',', '.'),
            'totalTrend' => ['pct' => '+0.0%', 'up' => true],
            'new' => number_format($newCustomers, 0, ',', '.') . ' thành viên',
            'newTrend' => $this->trend($newCustomers, $prevNew),
            'returning' => number_format($returningRate, 1, ',', '.') . '%',
            'returningTrend' => $this->trend($returningRate, $prevReturningRate),
            'spent' => $this->money($avgSpent),
            'spentTrend' => $this->trend($avgSpent, $prevAvgSpent),
            'customers' => $topCustomers,
            'chart' => array_map(
                fn (array $row): array => ['label' => $row['time'], 'value' => $row['count']],
                array_reverse($table)
            ),
        ];
    }

    /**
     * Top khách chi tiêu nhiều nhất trong kỳ.
     * 'share' là tỷ trọng trên tổng chi tiêu của TOÀN BỘ khách trong kỳ (không phải trên top),
     * 'barWidth' chuẩn hóa theo khách đứng đầu để mắt so sánh nhanh giữa các dòng.
     */
    private function topCustomersList(Carbon $start, Carbon $end): array
    {
        $rows = DB::table('users as u')
            ->leftJoin('orders as o', function ($join) use ($start, $end) {
                $join->on('o.user_id', '=', 'u.id')
                    ->where('o.payment_status', '=', 'paid')
                    ->where('o.order_status', '!=', 'cancelled')
                    ->whereBetween('o.created_at', [$start, $end]);
            })
            ->where(function ($q) {
                $q->where('u.role', 'user')->orWhereNull('u.role');
            })
            ->groupBy('u.id', 'u.name', 'u.email')
            ->selectRaw('u.id as id, u.name as name, u.email as email, COUNT(o.id) as count, COALESCE(SUM(o.total_amount), 0) as total_spent')
            ->orderByDesc('total_spent')
            ->orderBy('u.name')
            ->get();

        $periodSpent = (float) $rows->sum('total_spent');
        $topSpent = (float) ($rows->first()->total_spent ?? 0);

        return $rows->values()
            ->map(function ($row, $index) use ($periodSpent, $topSpent): array {
                $spent = (float) $row->total_spent;

                return [
                    'position' => $index + 1,
                    'name' => $row->name ?? 'Chưa cập nhật',
                    'email' => $row->email ?? '',
                    'count' => (int) $row->count,
                    'totalSpent' => $this->money($spent),
                    'totalSpentRaw' => $spent,
                    'share' => $this->percent($periodSpent > 0 ? $spent / $periodSpent * 100 : 0),
                    'barWidth' => $topSpent > 0 ? round($spent / $topSpent * 100, 1) : 0,
                ];
            })
            ->all();
    }

    private function newCustomersTableRows(Carbon $start, Carbon $end, string $bucket): array
    {
        $base = User::where('role', 'user')->whereBetween('created_at', [$start, $end]);

        if ($bucket === 'hour') {
            $rows = $base
                ->selectRaw("DATE_FORMAT(created_at, '%H:00') as label, MIN(created_at) as sort_key, COUNT(*) as count")
                ->groupByRaw("DATE_FORMAT(created_at, '%H:00')")
                ->orderByRaw('MIN(created_at) DESC')
                ->get();
        } elseif ($bucket === 'day') {
            $rows = $base
                ->selectRaw('DATE(created_at) as day, COUNT(*) as count')
                ->groupByRaw('DATE(created_at)')
                ->orderByRaw('DATE(created_at) DESC')
                ->get();
        } else {
            $rows = $base
                ->selectRaw('YEARWEEK(created_at, 3) as yw, COUNT(*) as count')
                ->groupByRaw('YEARWEEK(created_at, 3)')
                ->orderByRaw('YEARWEEK(created_at, 3) DESC')
                ->get();
        }

        return $rows->map(function ($row) use ($bucket) {
            if ($bucket === 'hour') {
                $label = $row->label;
            } elseif ($bucket === 'day') {
                $label = Carbon::parse($row->day)->format('d/m');
            } else {
                $label = $this->weekLabel($row->yw);
            }

            return [
                'time' => $label,
                'count' => (int) $row->count,
            ];
        })->all();
    }

    private function bestSellersForRange(Carbon $start, Carbon $end, string $bucket): array
    {
        $rows = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('brands as b', 'b.id', '=', 'p.brand_id')
            ->where('o.payment_status', 'paid')
            ->where('o.order_status', '!=', 'cancelled')
            ->whereBetween('o.created_at', [$start, $end])
            ->groupBy('p.id', 'p.name', 'c.name', 'b.name', 'p.slug')
            ->selectRaw('
                p.id as product_id,
                p.name as name,
                p.slug as slug,
                c.name as cat,
                COALESCE(b.name, "Khác") as brand,
                COUNT(DISTINCT oi.product_variant_id) as variant_count,
                SUM(oi.quantity) as units,
                SUM(oi.price * oi.quantity) as revenue
            ')
            ->orderByDesc('units')
            ->limit(5)
            ->get();

        // Chi tiết từng biến thể của các sản phẩm trên, dùng cho hàng mở rộng.
        $variantBreakdown = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
            ->where('o.payment_status', 'paid')
            ->where('o.order_status', '!=', 'cancelled')
            ->whereBetween('o.created_at', [$start, $end])
            ->whereIn('pv.product_id', $rows->pluck('product_id')->all())
            ->groupBy('pv.product_id', 'oi.product_variant_id')
            ->selectRaw('
                pv.product_id,
                oi.product_variant_id,
                SUM(oi.quantity) as units,
                SUM(oi.price * oi.quantity) as revenue
            ')
            ->orderByDesc('units')
            ->get()
            ->groupBy('product_id');

        // Calculate actual total units sold in this period across all items
        $totalUnits = (int) DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('o.payment_status', 'paid')
            ->where('o.order_status', '!=', 'cancelled')
            ->whereBetween('o.created_at', [$start, $end])
            ->sum('oi.quantity');

        // Eager load the variants and their values to get display_name
        $variantIds = $variantBreakdown->flatten(1)->pluck('product_variant_id')->unique()->all();
        $variants = \App\Models\ProductVariant::with('variantValues')->whereIn('id', $variantIds)->get()->keyBy('id');

        $topProductRow = $rows->first();
        $topProductVariantName = 'N/A';
        if ($topProductRow) {
            $topBest = optional($variantBreakdown->get($topProductRow->product_id))->first();
            $topV = $topBest ? $variants->get($topBest->product_variant_id) : null;
            $topProductVariantName = $topProductRow->name . ($topV && $topV->display_name ? ' (' . $topV->display_name . ')' : '');
        }
        
        $topCatRow = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->where('o.payment_status', 'paid')
            ->where('o.order_status', '!=', 'cancelled')
            ->whereBetween('o.created_at', [$start, $end])
            ->groupBy('c.name')
            ->selectRaw('c.name as name, SUM(oi.quantity) as units')
            ->orderByDesc('units')
            ->first();

        $topBrandRow = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('brands as b', 'b.id', '=', 'p.brand_id')
            ->where('o.payment_status', 'paid')
            ->where('o.order_status', '!=', 'cancelled')
            ->whereBetween('o.created_at', [$start, $end])
            ->groupBy('b.name')
            ->selectRaw('COALESCE(b.name, "Khác") as name, SUM(oi.quantity) as units')
            ->orderByDesc('units')
            ->first();

        $lengthSeconds = max(1, $end->getTimestamp() - $start->getTimestamp());
        $prevEnd = $start->copy();
        $prevStart = $start->copy()->subSeconds($lengthSeconds);
        
        $prevSold = (int) DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('o.payment_status', 'paid')
            ->where('o.order_status', '!=', 'cancelled')
            ->whereBetween('o.created_at', [$prevStart, $prevEnd])
            ->sum('oi.quantity');

        $sellers = $rows->map(function ($row, $index) use ($variants, $variantBreakdown) {
            $breakdown = $variantBreakdown->get($row->product_id, collect());

            $variantRows = $breakdown->map(function ($v) use ($variants): array {
                $model = $variants->get($v->product_variant_id);

                return [
                    'name' => $model && $model->display_name ? $model->display_name : 'Mặc định',
                    'units' => (int) $v->units,
                    'revenue' => $this->money((float) $v->revenue),
                ];
            })->values()->all();

            // Biến thể bán chạy nhất hiển thị ngay trên hàng chính.
            $variantName = $variantRows[0]['name'] ?? '';

            $imageRow = DB::table('images')
                ->where('product_id', $row->product_id)
                ->orderByDesc('is_primary')
                ->first();

            $imageUrl = null;
            if ($imageRow && $imageRow->image_url) {
                $path = ltrim((string) $imageRow->image_url, '/');
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    $imageUrl = $path;
                } elseif (str_starts_with($path, 'storage/') || str_starts_with($path, 'image/')) {
                    $imageUrl = asset($path);
                } elseif (file_exists(public_path('image/' . $path))) {
                    $imageUrl = asset('image/' . $path);
                } elseif (file_exists(public_path('storage/' . $path))) {
                    $imageUrl = asset('storage/' . $path);
                } else {
                    $imageUrl = asset($path);
                }
            }
            
            $badgeClass = 'badge-food';
            if (str_contains($row->cat, 'Pate') || str_contains($row->cat, 'ướt')) $badgeClass = 'badge-pate';
            elseif (str_contains($row->cat, 'Phụ kiện')) $badgeClass = 'badge-accessories';
            elseif (str_contains($row->cat, 'Đồ chơi')) $badgeClass = 'badge-toys';

            return [
                'rank' => $index + 1,
                'name' => $row->name,
                'variant' => $variantName,
                'variants' => $variantRows,
                'variantCount' => (int) $row->variant_count,
                'cat' => $row->cat,
                'brand' => $row->brand,
                'units' => (int) $row->units,
                'revenue' => $this->money($row->revenue),
                'image' => $imageUrl ?: asset('image/logo/logo.png'),
                'badgeClass' => $badgeClass,
            ];
        })->all();

        $categoriesData = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->where('o.payment_status', 'paid')
            ->where('o.order_status', '!=', 'cancelled')
            ->whereBetween('o.created_at', [$start, $end])
            ->groupBy('c.name')
            ->selectRaw('c.name as name, SUM(oi.quantity) as count')
            ->orderByDesc('count')
            ->get();

        $totalCatUnits = $categoriesData->sum('count');
        
        $categories = $categoriesData->values()->map(function ($cat, $index) use ($totalCatUnits) {
            return [
                'name' => $cat->name,
                'count' => (int) $cat->count,
                'percentage' => ($totalCatUnits > 0 ? round($cat->count / $totalCatUnits * 100, 1) : 0) . '%',
                'color' => self::CATEGORY_PALETTE[$index % count(self::CATEGORY_PALETTE)],
            ];
        })->all();

        $table = $this->bestSellersTableRows($start, $end, $bucket);

        return [
            'totalSold' => number_format($totalUnits, 0, ',', '.') . ' sản phẩm',
            'soldTrend' => $this->trend($totalUnits, $prevSold),
            'topProduct' => $topProductVariantName,
            'topCategory' => $topCatRow ? $topCatRow->name : 'N/A',
            'topBrand' => $topBrandRow ? $topBrandRow->name : 'N/A',
            'sellers' => $sellers,
            'categories' => $categories,
            'chart' => array_map(
                fn (array $row): array => ['label' => $row['time'], 'value' => $row['count']],
                array_reverse($table)
            ),
        ];
    }

    private function bestSellersTableRows(Carbon $start, Carbon $end, string $bucket): array
    {
        $base = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('o.payment_status', 'paid')
            ->where('o.order_status', '!=', 'cancelled')
            ->whereBetween('o.created_at', [$start, $end]);

        if ($bucket === 'hour') {
            $rows = $base
                ->selectRaw("DATE_FORMAT(o.created_at, '%H:00') as label, MIN(o.created_at) as sort_key, SUM(oi.quantity) as count")
                ->groupByRaw("DATE_FORMAT(o.created_at, '%H:00')")
                ->orderByRaw('MIN(o.created_at) DESC')
                ->get();
        } elseif ($bucket === 'day') {
            $rows = $base
                ->selectRaw('DATE(o.created_at) as day, SUM(oi.quantity) as count')
                ->groupByRaw('DATE(o.created_at)')
                ->orderByRaw('DATE(o.created_at) DESC')
                ->get();
        } else {
            $rows = $base
                ->selectRaw('YEARWEEK(o.created_at, 3) as yw, SUM(oi.quantity) as count')
                ->groupByRaw('YEARWEEK(o.created_at, 3)')
                ->orderByRaw('YEARWEEK(o.created_at, 3) DESC')
                ->get();
        }

        return $rows->map(function ($row) use ($bucket) {
            if ($bucket === 'hour') {
                $label = $row->label;
            } elseif ($bucket === 'day') {
                $label = Carbon::parse($row->day)->format('d/m');
            } else {
                $label = $this->weekLabel($row->yw);
            }

            return [
                'time' => $label,
                'count' => (int) $row->count,
            ];
        })->all();
    }

    private function lowStockData(): array
    {
        $lowStockVariants = \App\Models\ProductVariant::where('status', 'active')
            ->where('quantity', '>', 0)
            ->where('quantity', '<', 10)
            ->count();

        $outOfStockVariants = \App\Models\ProductVariant::where('status', 'active')
            ->where('quantity', '=', 0)
            ->count();

        $totalStock = (int) \App\Models\ProductVariant::where('status', 'active')
            ->sum('quantity');

        $allVariants = \App\Models\ProductVariant::where('status', 'active')
            ->count();

        $safetyRate = $allVariants > 0 ? (($allVariants - $lowStockVariants - $outOfStockVariants) / $allVariants * 100) : 100;

        $variants = \App\Models\ProductVariant::with(['product.category', 'variantValues'])
            ->where('status', 'active')
            ->where('quantity', '<', 10)
            ->get();

        $items = $variants->map(function ($variant) {
            $label = $variant->display_name;

            return [
                'name' => $variant->product ? $variant->product->name : 'Sản phẩm',
                'variant' => $label ?: 'Mặc định',
                // Phân biệt biến thể thật với SKU không có thuộc tính, để UI hiển thị khác nhau.
                'hasVariant' => $label !== '',
                'sku' => $variant->sku,
                'cat' => ($variant->product && $variant->product->category) ? $variant->product->category->name : 'Khác',
                'stock' => (int) $variant->quantity,
                'status' => $variant->quantity == 0 ? 'HẾT HÀNG' : 'SẮP HẾT HÀNG',
                'statusClass' => $variant->quantity == 0 ? 'badge-cancelled' : 'badge-pending',
            ];
        })->all();

        $statusBreakdown = [
            ['name' => 'Hết hàng', 'count' => $outOfStockVariants, 'color' => '#ef4444', 'percentage' => ($allVariants > 0 ? round($outOfStockVariants / $allVariants * 100, 1) : 0) . '%'],
            ['name' => 'Sắp hết hàng', 'count' => $lowStockVariants, 'color' => '#f59e0b', 'percentage' => ($allVariants > 0 ? round($lowStockVariants / $allVariants * 100, 1) : 0) . '%'],
            ['name' => 'Tồn kho an toàn', 'count' => max(0, $allVariants - $lowStockVariants - $outOfStockVariants), 'color' => '#10b981', 'percentage' => ($allVariants > 0 ? round(max(0, $allVariants - $lowStockVariants - $outOfStockVariants) / $allVariants * 100, 1) : 0) . '%'],
        ];

        $categoriesBreakdownData = $variants->groupBy(function ($v) {
            return ($v->product && $v->product->category) ? $v->product->category->name : 'Khác';
        })->map(function ($group) {
            return $group->count();
        });

        $categoriesBreakdown = [
            'labels' => $categoriesBreakdownData->keys()->all(),
            'values' => $categoriesBreakdownData->values()->all(),
        ];

        return [
            'lowStock' => number_format($lowStockVariants, 0, ',', '.') . ' sản phẩm',
            'outOfStock' => number_format($outOfStockVariants, 0, ',', '.') . ' sản phẩm',
            'safety' => number_format($safetyRate, 1, ',', '.') . '%',
            'total' => number_format($totalStock, 0, ',', '.') . ' đơn vị',
            'items' => $items,
            'statusBreakdown' => $statusBreakdown,
            'categoriesBreakdown' => $categoriesBreakdown,
        ];
    }

    private function latestOrdersForRange(Carbon $start, Carbon $end, string $bucket): array
    {
        $current = $this->latestOrdersRangeAggregate($start, $end);

        $lengthSeconds = max(1, $end->getTimestamp() - $start->getTimestamp());
        $prevEnd = $start->copy();
        $prevStart = $start->copy()->subSeconds($lengthSeconds);
        $previous = $this->latestOrdersRangeAggregate($prevStart, $prevEnd);

        $ordersList = $this->latestOrdersList($start, $end);
        $statusBreakdown = $this->latestOrdersStatusBreakdown($start, $end);
        $table = $this->orderStatusTableRows($start, $end, $bucket);

        return [
            'total' => number_format($current['total'], 0, ',', '.') . ' đơn',
            'totalTrend' => $this->trend($current['total'], $previous['total']),
            'revenue' => $this->money($current['revenue']),
            'revenueTrend' => $this->trend($current['revenue'], $previous['revenue']),
            'aov' => $this->money($current['aov']),
            'pending' => number_format($current['pending'], 0, ',', '.') . ' đơn',
            'orders' => $ordersList,
            'statusBreakdown' => $statusBreakdown,
            'chart' => array_map(
                fn (array $row): array => ['label' => $row['time'], 'value' => $row['count']],
                array_reverse($table)
            ),
        ];
    }

    private function latestOrdersRangeAggregate(Carbon $start, Carbon $end): array
    {
        $row = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN payment_status = "paid" AND order_status != "cancelled" THEN total_amount ELSE 0 END), 0) as revenue,
                SUM(CASE WHEN order_status = "pending" THEN 1 ELSE 0 END) as pending
            ')
            ->first();

        $total = (int) ($row->total ?? 0);
        $revenue = (float) ($row->revenue ?? 0);
        $pending = (int) ($row->pending ?? 0);

        return [
            'total' => $total,
            'revenue' => $revenue,
            'aov' => $total > 0 ? $revenue / $total : 0,
            'pending' => $pending,
        ];
    }

    private function latestOrdersList(Carbon $start, Carbon $end): array
    {
        $orders = Order::query()
            ->with(['user', 'items.productVariant.product'])
            ->whereBetween('created_at', [$start, $end])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return $orders->map(function ($order) {
            $customerName = $order->user ? $order->user->name : ($order->recipient_name ?: 'Khách vãng lai');
            
            $itemsList = $order->items->map(function ($oi) {
                $pName = $oi->productVariant && $oi->productVariant->product ? $oi->productVariant->product->name : 'Sản phẩm';
                $vName = $oi->productVariant ? $oi->productVariant->display_name : '';
                return $vName ? "$pName ($vName)" : $pName;
            })->implode(', ');

            if (mb_strlen($itemsList, 'UTF-8') > 60) {
                $itemsList = mb_substr($itemsList, 0, 57, 'UTF-8') . '...';
            }

            $status = 'CHỜ XỬ LÝ';
            $statusClass = 'badge-pending';
            if ($order->order_status === 'completed') {
                $status = 'HOÀN TẤT';
                $statusClass = 'badge-completed';
            } elseif ($order->order_status === 'cancelled') {
                $status = 'ĐÃ HỦY';
                $statusClass = 'badge-cancelled';
            }

            return [
                'id' => '#PW-' . $order->id,
                'customer' => $customerName,
                'items' => $itemsList ?: 'Không rõ',
                'total' => $this->money($order->total_amount),
                'status' => $status,
                'statusClass' => $statusClass,
            ];
        })->all();
    }

    private function latestOrdersStatusBreakdown(Carbon $start, Carbon $end): array
    {
        $rows = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('order_status as status, COUNT(*) as count')
            ->groupBy('order_status')
            ->get();

        $total = $rows->sum('count');

        $statusMap = [
            'completed' => ['name' => 'Hoàn tất', 'color' => '#10b981'],
            'pending' => ['name' => 'Chờ xử lý', 'color' => '#f59e0b'],
            'cancelled' => ['name' => 'Đã hủy', 'color' => '#ef4444'],
            'shipping' => ['name' => 'Đang giao', 'color' => '#3b82f6'],
        ];

        return $rows->map(function ($row) use ($total, $statusMap) {
            $status = $row->status;
            $info = $statusMap[$status] ?? ['name' => $status, 'color' => '#94a3b8'];
            
            return [
                'name' => $info['name'],
                'count' => (int) $row->count,
                'percentage' => ($total > 0 ? round($row->count / $total * 100, 1) : 0) . '%',
                'color' => $info['color'],
            ];
        })->all();
    }

    /** Nhãn hiển thị cho từng mốc thời gian được phép xuất. */
    private const PERIOD_LABELS = [
        'today' => 'Hôm nay',
        '7days' => '7 ngày qua',
        '30days' => '30 ngày qua',
    ];

    /**
     * Xuất báo cáo admin ra file Excel.
     *
     * $type chọn loại báo cáo, $period chọn mốc thời gian (mặc định 30 ngày).
     */
    public function export(string $type, \Illuminate\Http\Request $request)
    {
        $period = $request->query('period', '30days');

        if (! isset(self::PERIOD_LABELS[$period])) {
            $period = '30days';
        }

        $now = Carbon::now();
        [$start, $bucket] = match ($period) {
            'today' => [$now->copy()->startOfDay(), 'hour'],
            '7days' => [$now->copy()->subDays(6)->startOfDay(), 'day'],
            default => [$now->copy()->subDays(29)->startOfDay(), 'week'],
        };
        $end = $now->copy();

        [$title, $summary, $sections] = match ($type) {
            'revenue' => $this->revenueExportData($start, $end, $bucket),
            'order-status' => $this->orderStatusExportData($start, $end, $bucket),
            'customers' => $this->customersExportData($start, $end, $bucket),
            'best-sellers' => $this->bestSellersExportData($start, $end, $bucket),
            default => abort(404),
        };

        $periodLabel = self::PERIOD_LABELS[$period]
            . ' (' . $start->format('d/m/Y') . ' – ' . $end->format('d/m/Y') . ')';

        $fileName = sprintf('bao-cao-%s-%s-%s.xlsx', $type, $period, $now->format('Ymd-Hi'));

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ReportExport($title, $periodLabel, $summary, $sections),
            $fileName,
        );
    }

    private function revenueExportData(Carbon $start, Carbon $end, string $bucket): array
    {
        $data = $this->revenueForRange($start, $end, $bucket);

        $summary = [
            'Doanh thu thuần' => $data['revenue'],
            'Số đơn hàng' => $data['orders'],
            'Giá trị đơn trung bình' => $data['aov'],
            'Tỷ lệ giảm giá' => $data['discountRate'],
        ];

        $sections = [
            [
                'title' => 'CHI TIẾT DOANH SỐ BÁN HÀNG',
                'headings' => ['Thời gian', 'Số đơn hàng', 'Doanh thu gộp', 'Giảm giá', 'Doanh thu thuần'],
                'rows' => array_map(fn ($r): array => [
                    $r['time'], $r['count'], $r['gross'], $r['discount'], $r['net'],
                ], $data['table']),
            ],
            [
                'title' => 'DOANH THU THEO DANH MỤC',
                'headings' => ['Danh mục', 'Tỷ trọng'],
                'rows' => array_map(fn ($c): array => [
                    $c['name'], $c['percentage'],
                ], $data['categories']),
            ],
        ];

        return ['Báo cáo doanh thu', $summary, $sections];
    }

    private function orderStatusExportData(Carbon $start, Carbon $end, string $bucket): array
    {
        $data = $this->orderStatusForRange($start, $end, $bucket);

        $summary = [
            'Tổng đơn hàng' => $data['total'],
            'Hoàn tất' => $data['completed'],
            'Chờ xác nhận' => $data['pending'],
            'Đã hủy' => $data['cancelled'],
        ];

        $sections = [
            [
                'title' => 'PHÂN BỔ TRẠNG THÁI ĐƠN',
                'headings' => ['Trạng thái', 'Số đơn', 'Tỷ trọng'],
                'rows' => array_map(fn ($s): array => [
                    $s['name'], $s['count'], $s['percentage'],
                ], $data['statuses']),
            ],
        ];

        return ['Báo cáo trạng thái đơn hàng', $summary, $sections];
    }

    private function customersExportData(Carbon $start, Carbon $end, string $bucket): array
    {
        $data = $this->customersForRange($start, $end, $bucket);

        $summary = [
            'Tổng khách hàng' => $data['total'],
            'Khách mới' => $data['new'],
        ];

        $sections = [
            [
                'title' => 'DANH SÁCH THỐNG KÊ KHÁCH HÀNG',
                'headings' => ['STT', 'Khách hàng', 'Email', 'Số đơn mua', 'Tổng chi tiêu'],
                'rows' => array_map(fn ($c): array => [
                    $c['position'], $c['name'], $c['email'], $c['count'], $c['totalSpent'],
                ], $data['customers']),
            ],
        ];

        return ['Báo cáo khách hàng', $summary, $sections];
    }

    private function bestSellersExportData(Carbon $start, Carbon $end, string $bucket): array
    {
        $data = $this->bestSellersForRange($start, $end, $bucket);

        $summary = [
            'Tổng sản phẩm bán ra' => $data['totalSold'],
            'Sản phẩm bán chạy nhất' => $data['topProduct'],
            'Danh mục bán chạy nhất' => $data['topCategory'],
            'Thương hiệu bán chạy nhất' => $data['topBrand'],
        ];

        // Mỗi sản phẩm kèm các biến thể đã bán, thụt lề để dễ đọc.
        $rows = [];
        foreach ($data['sellers'] as $s) {
            $rows[] = [$s['rank'], $s['name'], $s['cat'], $s['brand'], $s['units'], $s['revenue']];

            foreach ($s['variants'] ?? [] as $v) {
                $rows[] = ['', '    → ' . $v['name'], '', '', $v['units'], $v['revenue']];
            }
        }

        $sections = [
            [
                'title' => 'DANH SÁCH THỐNG KÊ SẢN PHẨM',
                'headings' => ['STT', 'Sản phẩm', 'Danh mục', 'Thương hiệu', 'Số lượng bán', 'Tổng doanh thu'],
                'rows' => $rows,
            ],
            [
                'title' => 'TỶ TRỌNG THEO DANH MỤC',
                'headings' => ['Danh mục', 'Số lượng', 'Tỷ trọng'],
                'rows' => array_map(fn ($c): array => [
                    $c['name'], $c['count'], $c['percentage'],
                ], $data['categories']),
            ],
        ];

        return ['Báo cáo sản phẩm bán chạy', $summary, $sections];
    }
}

