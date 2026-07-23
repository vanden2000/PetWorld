<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /** Bảng màu cho danh mục (theo màu chủ đạo + phối bổ trợ). */
    private const CATEGORY_PALETTE = ['#ff782d', '#4b6b60', '#825736', '#2563eb', '#9333ea', '#0d9488', '#bdc7c2'];

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
                ->selectRaw('YEARWEEK(created_at, 3) as yw, MIN(DATE(created_at)) as wk_start, MAX(DATE(created_at)) as wk_end, COUNT(*) as cnt, COALESCE(SUM(total_amount), 0) as net, COALESCE(SUM(discount_amount), 0) as discount')
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
                $label = 'Tuần ' . Carbon::parse($row->wk_start)->format('d/m') . '–' . Carbon::parse($row->wk_end)->format('d/m');
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
        return view('Admin.reports.order_status');
    }

    public function customers()
    {
        return view('Admin.reports.customers');
    }

    public function bestSellers()
    {
        return view('Admin.reports.best_sellers');
    }

    public function lowStock()
    {
        return view('Admin.reports.low_stock');
    }

    public function latestOrders()
    {
        return view('Admin.reports.latest_orders');
    }
}
