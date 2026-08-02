<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /** Bảng màu cho danh mục (theo màu chủ đạo + phối bổ trợ). */
    private const CATEGORY_PALETTE = ['#ff782d', '#4b6b60', '#825736', '#2563eb', '#9333ea', '#0d9488', '#bdc7c2'];

    /**
     * Ngưỡng hạng thành viên theo chi tiêu trong kỳ.
     * Hệ thống chưa có bảng hạng thành viên nên tạm quy ước ở đây; sửa hai số này
     * là đổi được cách xếp hạng trên báo cáo khách hàng.
     */
    private const RANK_VIP_FROM = 10_000_000;
    private const RANK_GOLD_FROM = 5_000_000;

    /** Màu hạng thành viên, khớp với legend trên giao diện. */
    private const RANK_COLORS = ['VIP' => '#ff782d', 'GOLD' => '#f59e0b', 'SILVER' => '#94a3b8'];

    /** Còn từ ngần này trở xuống (nhưng chưa hết) thì tính là "sắp hết hàng". */
    private const LOW_STOCK_THRESHOLD = 10;

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

    /**
     * Gom một truy vấn theo mốc thời gian (giờ / ngày / tuần) thành chuỗi cho biểu đồ cột.
     * Trả về [['label' => ..., 'value' => ...]] theo thứ tự thời gian tăng dần.
     */
    private function bucketSeries(Builder $base, string $column, string $bucket, string $valueExpr = 'COUNT(*)'): array
    {
        if ($bucket === 'hour') {
            $rows = (clone $base)
                ->selectRaw("DATE_FORMAT({$column}, '%H:00') as label, MIN({$column}) as sort_key, {$valueExpr} as value")
                ->groupByRaw("DATE_FORMAT({$column}, '%H:00')")
                ->orderByRaw("MIN({$column})")
                ->get();
        } elseif ($bucket === 'day') {
            $rows = (clone $base)
                ->selectRaw("DATE({$column}) as day, {$valueExpr} as value")
                ->groupByRaw("DATE({$column})")
                ->orderByRaw("DATE({$column})")
                ->get();
        } else { // week
            $rows = (clone $base)
                ->selectRaw("YEARWEEK({$column}, 3) as yw, MIN(DATE({$column})) as wk_start, MAX(DATE({$column})) as wk_end, {$valueExpr} as value")
                ->groupByRaw("YEARWEEK({$column}, 3)")
                ->orderByRaw("YEARWEEK({$column}, 3)")
                ->get();
        }

        return $rows->map(function ($row) use ($bucket): array {
            if ($bucket === 'hour') {
                $label = $row->label;
            } elseif ($bucket === 'day') {
                $label = Carbon::parse($row->day)->format('d/m');
            } else {
                $label = 'Tuần ' . Carbon::parse($row->wk_start)->format('d/m') . '–' . Carbon::parse($row->wk_end)->format('d/m');
            }

            return ['label' => $label, 'value' => (float) $row->value];
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
        $now = Carbon::now();

        $periods = [
            'today' => $this->orderStatusForRange($now->copy()->startOfDay(), $now->copy(), 'hour'),
            '7days' => $this->orderStatusForRange($now->copy()->subDays(6)->startOfDay(), $now->copy(), 'day'),
            '30days' => $this->orderStatusForRange($now->copy()->subDays(29)->startOfDay(), $now->copy(), 'week'),
        ];

        return view('Admin.reports.order_status', compact('periods'));
    }

    /**
     * Nhóm trạng thái đơn hiển thị trên báo cáo.
     * 'pending' và 'confirmed' gộp làm "Chờ xử lý" vì với người xem báo cáo thì cả
     * hai đều là đơn chưa rời kho.
     */
    private const STATUS_GROUPS = [
        'Hoàn tất' => ['statuses' => ['completed'], 'color' => '#10b981', 'badge' => 'badge-completed', 'goodWhenHigh' => true],
        'Đang giao' => ['statuses' => ['shipping'], 'color' => '#3b82f6', 'badge' => 'badge-shipping', 'goodWhenHigh' => true],
        'Chờ xử lý' => ['statuses' => ['pending', 'confirmed'], 'color' => '#f59e0b', 'badge' => 'badge-pending', 'goodWhenHigh' => false],
        'Đã hủy' => ['statuses' => ['cancelled'], 'color' => '#ef4444', 'badge' => 'badge-cancelled', 'goodWhenHigh' => false],
    ];

    private function orderStatusForRange(Carbon $start, Carbon $end, string $bucket): array
    {
        $lengthSeconds = max(1, $end->getTimestamp() - $start->getTimestamp());
        $prevEnd = $start->copy();
        $prevStart = $start->copy()->subSeconds($lengthSeconds);

        $current = $this->orderStatusCounts($start, $end);
        $previous = $this->orderStatusCounts($prevStart, $prevEnd);

        $total = array_sum($current);
        $prevTotal = array_sum($previous);

        $groupCount = function (array $counts, string $group): int {
            $sum = 0;
            foreach (self::STATUS_GROUPS[$group]['statuses'] as $status) {
                $sum += $counts[$status] ?? 0;
            }

            return $sum;
        };

        $statuses = [];
        foreach (self::STATUS_GROUPS as $name => $config) {
            $count = $groupCount($current, $name);
            $share = $total > 0 ? $count / $total * 100 : 0;
            $prevShare = $prevTotal > 0 ? $groupCount($previous, $name) / $prevTotal * 100 : 0;

            // "Tốt lên" tùy nhóm: hoàn tất/đang giao tăng là tốt, chờ xử lý/đã hủy giảm mới tốt.
            $improving = $config['goodWhenHigh'] ? $share >= $prevShare : $share <= $prevShare;

            $statuses[] = [
                'name' => $name,
                'count' => $count,
                'percentage' => $this->percent($share),
                'color' => $config['color'],
                'badge' => $config['badge'],
                'note' => $this->statusNote($share, $prevShare, $config['goodWhenHigh']),
                'noteUp' => $improving,
            ];
        }

        $completed = $groupCount($current, 'Hoàn tất');
        $pending = $groupCount($current, 'Chờ xử lý');
        $cancelled = $groupCount($current, 'Đã hủy');

        return [
            'total' => number_format($total, 0, ',', '.') . ' đơn',
            'totalTrend' => $this->trend($total, $prevTotal),
            'completed' => number_format($completed, 0, ',', '.') . ' đơn',
            'completedTrend' => $this->trend($completed, $groupCount($previous, 'Hoàn tất')),
            'pending' => number_format($pending, 0, ',', '.') . ' đơn',
            'pendingTrend' => $this->trend($pending, $groupCount($previous, 'Chờ xử lý')),
            'cancelled' => number_format($cancelled, 0, ',', '.') . ' đơn',
            'cancelledTrend' => $this->trend($cancelled, $groupCount($previous, 'Đã hủy')),
            'statuses' => $statuses,
            'chart' => $this->bucketSeries(
                Order::query()->whereBetween('created_at', [$start, $end]),
                'created_at',
                $bucket,
            ),
        ];
    }

    /** Đếm đơn theo từng giá trị order_status trong khoảng. */
    private function orderStatusCounts(Carbon $start, Carbon $end): array
    {
        return Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('order_status')
            ->selectRaw('order_status, COUNT(*) as cnt')
            ->pluck('cnt', 'order_status')
            ->map(fn ($value): int => (int) $value)
            ->all();
    }

    /** Ghi chú ngắn cạnh mỗi nhóm trạng thái, suy ra từ tỷ trọng so với kỳ trước. */
    private function statusNote(float $share, float $prevShare, bool $goodWhenHigh): string
    {
        $diff = $share - $prevShare;

        if (abs($diff) < 0.05) {
            return 'Không đổi so với kỳ trước';
        }

        $direction = $diff > 0 ? 'Tăng' : 'Giảm';
        $good = $goodWhenHigh ? $diff > 0 : $diff < 0;

        return sprintf(
            '%s %s tỷ trọng so với kỳ trước%s',
            $direction,
            $this->percent(abs($diff)),
            $good ? '' : ' (cần chú ý)',
        );
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

    /**
     * Số liệu khách hàng cho một khoảng thời gian.
     * Mọi chỉ số đều tính trong khoảng đang chọn, trừ "Tổng khách hàng" là tích lũy
     * tới cuối kỳ và "khách quay lại" phải nhìn ngược về trước kỳ.
     */
    private function customersForRange(Carbon $start, Carbon $end, string $bucket): array
    {
        $lengthSeconds = max(1, $end->getTimestamp() - $start->getTimestamp());
        $prevEnd = $start->copy();
        $prevStart = $start->copy()->subSeconds($lengthSeconds);

        $total = User::query()->where('role', 'user')->where('created_at', '<=', $end)->count();
        $prevTotal = User::query()->where('role', 'user')->where('created_at', '<=', $prevEnd)->count();

        $new = User::query()->where('role', 'user')->whereBetween('created_at', [$start, $end])->count();
        $prevNew = User::query()->where('role', 'user')->whereBetween('created_at', [$prevStart, $prevEnd])->count();

        $current = $this->customerSpendStats($start, $end);
        $previous = $this->customerSpendStats($prevStart, $prevEnd);

        return [
            'total' => number_format($total, 0, ',', '.'),
            'totalTrend' => $this->trend($total, $prevTotal),
            'new' => number_format($new, 0, ',', '.') . ' thành viên',
            'newTrend' => $this->trend($new, $prevNew),
            'returning' => $this->percent($current['returningRate']),
            'returningTrend' => $this->trend($current['returningRate'], $previous['returningRate']),
            'spent' => $this->money($current['avgSpent']),
            'spentTrend' => $this->trend($current['avgSpent'], $previous['avgSpent']),
            'customers' => $current['table'],
            'ranks' => $current['ranks'],
            'chart' => $this->bucketSeries(
                User::query()->where('role', 'user')->whereBetween('created_at', [$start, $end]),
                'created_at',
                $bucket,
            ),
        ];
    }

    /** Chi tiêu, hạng thành viên và tỷ lệ quay lại của khách mua hàng trong khoảng. */
    private function customerSpendStats(Carbon $start, Carbon $end): array
    {
        $rows = DB::table('orders as o')
            ->join('users as u', 'u.id', '=', 'o.user_id')
            ->where('o.payment_status', 'paid')
            ->where('o.order_status', '!=', 'cancelled')
            ->whereBetween('o.created_at', [$start, $end])
            ->groupBy('u.id', 'u.name', 'u.email')
            ->selectRaw('u.id as id, u.name as name, u.email as email, COUNT(*) as orders_count, COALESCE(SUM(o.total_amount), 0) as spent')
            ->orderByDesc('spent')
            ->get();

        $buyerCount = $rows->count();
        $avgSpent = $buyerCount > 0 ? (float) $rows->sum('spent') / $buyerCount : 0.0;

        // Khách quay lại = khách mua trong kỳ này và đã từng mua trước đó.
        $returningCount = 0;
        if ($buyerCount > 0) {
            $returningCount = DB::table('orders')
                ->where('payment_status', 'paid')
                ->where('order_status', '!=', 'cancelled')
                ->whereIn('user_id', $rows->pluck('id')->all())
                ->where('created_at', '<', $start)
                ->distinct()
                ->count('user_id');
        }

        $rankCounts = ['VIP' => 0, 'GOLD' => 0, 'SILVER' => 0];
        $table = [];

        foreach ($rows as $index => $row) {
            $spent = (float) $row->spent;
            [$rankName, $rankClass] = $this->memberRank($spent);
            $rankCounts[strtoupper($rankName)]++;

            if ($index < 8) {
                $table[] = [
                    'name' => $row->name,
                    'email' => $row->email,
                    'count' => (int) $row->orders_count,
                    'totalSpent' => $this->money($spent),
                    'rank' => $rankName,
                    'rankClass' => $rankClass,
                ];
            }
        }

        $ranks = [];
        foreach (self::RANK_COLORS as $name => $color) {
            $count = $rankCounts[$name];
            $ranks[] = [
                'name' => $name,
                'count' => $count,
                'percentage' => $this->percent($buyerCount > 0 ? $count / $buyerCount * 100 : 0),
                'color' => $color,
            ];
        }

        return [
            'avgSpent' => $avgSpent,
            'returningRate' => $buyerCount > 0 ? $returningCount / $buyerCount * 100 : 0.0,
            'table' => $table,
            'ranks' => $ranks,
        ];
    }

    /** Hạng thành viên suy ra từ chi tiêu trong kỳ (ngưỡng ở hằng số phía trên). */
    private function memberRank(float $spent): array
    {
        if ($spent >= self::RANK_VIP_FROM) {
            return ['VIP', 'member-vip'];
        }

        if ($spent >= self::RANK_GOLD_FROM) {
            return ['Gold', 'member-gold'];
        }

        return ['Silver', 'member-silver'];
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

    /** Sản phẩm bán chạy trong khoảng, xếp theo số lượng đã bán. */
    private function bestSellersForRange(Carbon $start, Carbon $end, string $bucket): array
    {
        $lengthSeconds = max(1, $end->getTimestamp() - $start->getTimestamp());
        $prevEnd = $start->copy();
        $prevStart = $start->copy()->subSeconds($lengthSeconds);

        $rows = $this->soldProductRows($start, $end);
        $totalSold = (int) $rows->sum('units');
        $prevSold = (int) $this->soldProductRows($prevStart, $prevEnd)->sum('units');

        // Ảnh đại diện lấy một lượt cho các sản phẩm trong bảng, tránh N+1.
        $images = ProductImage::query()
            ->whereIn('product_id', $rows->take(10)->pluck('id')->all())
            ->where('is_primary', true)
            ->pluck('image_url', 'product_id');

        $badges = ['badge-pate', 'badge-food', 'badge-toys', 'badge-accessories', 'badge-category'];

        $sellers = $rows->take(10)->values()->map(fn ($row, $index): array => [
            'rank' => $index + 1,
            'name' => $row->name,
            'cat' => $row->cat ?? 'Chưa phân loại',
            'brand' => $row->brand ?? '—',
            'units' => (int) $row->units,
            'revenue' => $this->money((float) $row->revenue),
            'image' => $this->publicImageUrl($images[$row->id] ?? null),
            'badgeClass' => $badges[$index % count($badges)],
        ])->all();

        // Cơ cấu số lượng bán theo danh mục.
        $byCategory = $rows->groupBy(fn ($row) => $row->cat ?? 'Chưa phân loại')
            ->map(fn ($group) => (int) $group->sum('units'))
            ->sortDesc();

        $categories = [];
        $index = 0;
        foreach ($byCategory as $name => $count) {
            $categories[] = [
                'name' => $name,
                'count' => $count,
                'percentage' => $this->percent($totalSold > 0 ? $count / $totalSold * 100 : 0),
                'color' => self::CATEGORY_PALETTE[$index % count(self::CATEGORY_PALETTE)],
            ];
            $index++;
        }

        $top = $rows->first();

        return [
            'totalSold' => number_format($totalSold, 0, ',', '.') . ' sản phẩm',
            'soldTrend' => $this->trend($totalSold, $prevSold),
            'topProduct' => $top->name ?? 'Chưa có dữ liệu',
            'topCategory' => $byCategory->keys()->first() ?? 'Chưa có dữ liệu',
            'topBrand' => $rows->groupBy(fn ($row) => $row->brand ?? '—')
                ->map(fn ($group) => (int) $group->sum('units'))
                ->sortDesc()->keys()->first() ?? 'Chưa có dữ liệu',
            'sellers' => $sellers,
            'categories' => $categories,
            'chart' => $this->bucketSeries(
                Order::query()
                    ->join('order_items as oi', 'oi.order_id', '=', 'orders.id')
                    ->where('orders.payment_status', 'paid')
                    ->where('orders.order_status', '!=', 'cancelled')
                    ->whereBetween('orders.created_at', [$start, $end]),
                'orders.created_at',
                $bucket,
                'COALESCE(SUM(oi.quantity), 0)',
            ),
        ];
    }

    /** Số lượng bán và doanh thu theo từng sản phẩm trong khoảng. */
    private function soldProductRows(Carbon $start, Carbon $end)
    {
        return DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('product_variants as pv', 'pv.id', '=', 'oi.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('brands as b', 'b.id', '=', 'p.brand_id')
            ->where('o.payment_status', 'paid')
            ->where('o.order_status', '!=', 'cancelled')
            ->whereBetween('o.created_at', [$start, $end])
            ->groupBy('p.id', 'p.name', 'c.name', 'b.name')
            ->selectRaw('p.id as id, p.name as name, c.name as cat, b.name as brand, COALESCE(SUM(oi.quantity), 0) as units, COALESCE(SUM(oi.price * oi.quantity), 0) as revenue')
            ->orderByDesc('units')
            ->get();
    }

    /** Đường dẫn ảnh công khai; trả về null để giao diện dùng ảnh mặc định. */
    private function publicImageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(str_starts_with($path, 'storage/') ? $path : 'storage/' . ltrim($path, '/'));
    }

    public function lowStock()
    {
        // Tồn kho là số liệu tại thời điểm hiện tại, không phụ thuộc khoảng thời gian,
        // nên cả ba lựa chọn lọc đều nhận cùng một bộ số.
        $snapshot = $this->lowStockSnapshot();

        $periods = [
            'today' => $snapshot,
            '7days' => $snapshot,
            '30days' => $snapshot,
        ];

        return view('Admin.reports.low_stock', compact('periods'));
    }

    /** Ảnh chụp tồn kho hiện tại theo từng biến thể sản phẩm đang bán. */
    private function lowStockSnapshot(): array
    {
        $rows = DB::table('product_variants as pv')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->whereNull('pv.deleted_at')
            ->selectRaw('pv.id, pv.sku, pv.quantity, p.name as product_name, c.name as cat')
            ->orderBy('pv.quantity')
            ->get();

        $outOfStock = $rows->where('quantity', '<=', 0);
        $lowStock = $rows->filter(fn ($row) => $row->quantity > 0 && $row->quantity <= self::LOW_STOCK_THRESHOLD);
        $safe = $rows->filter(fn ($row) => $row->quantity > self::LOW_STOCK_THRESHOLD);
        $totalUnits = (int) $rows->sum('quantity');
        $variantCount = max(1, $rows->count());

        // Bảng chỉ liệt kê hàng cần chú ý: hết trước, sắp hết sau.
        $attention = $outOfStock->concat($lowStock)->take(30);
        $variantLabels = $this->variantLabels($attention->pluck('id')->all());

        $items = $attention->map(fn ($row): array => [
            'name' => $row->product_name,
            'variant' => $variantLabels[$row->id] ?? 'Mặc định',
            'sku' => $row->sku ?? '—',
            'cat' => $row->cat ?? 'Chưa phân loại',
            'stock' => (int) $row->quantity,
            'status' => $row->quantity <= 0 ? 'HẾT HÀNG' : 'SẮP HẾT',
            'statusClass' => $row->quantity <= 0 ? 'badge-cancelled' : 'badge-pending',
        ])->values()->all();

        $categoriesBreakdown = $attention
            ->groupBy(fn ($row) => $row->cat ?? 'Chưa phân loại')
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        return [
            'lowStock' => number_format($lowStock->count(), 0, ',', '.') . ' sản phẩm',
            'outOfStock' => number_format($outOfStock->count(), 0, ',', '.') . ' sản phẩm',
            'safety' => $this->percent($safe->count() / $variantCount * 100),
            'total' => number_format($totalUnits, 0, ',', '.') . ' đơn vị',
            'items' => $items,
            'statusBreakdown' => [
                ['name' => 'Hết hàng', 'count' => $outOfStock->count(), 'color' => '#ef4444', 'percentage' => $this->percent($outOfStock->count() / $variantCount * 100)],
                ['name' => 'Sắp hết hàng', 'count' => $lowStock->count(), 'color' => '#f59e0b', 'percentage' => $this->percent($lowStock->count() / $variantCount * 100)],
                ['name' => 'Tồn kho an toàn', 'count' => $safe->count(), 'color' => '#10b981', 'percentage' => $this->percent($safe->count() / $variantCount * 100)],
            ],
            'categoriesBreakdown' => [
                'labels' => $categoriesBreakdown->keys()->all(),
                'values' => $categoriesBreakdown->values()->all(),
            ],
        ];
    }

    /**
     * Tên biến thể dạng "S / Đỏ" ghép từ các giá trị thuộc tính.
     * Lấy một lượt cho cả danh sách để không truy vấn lặp theo từng dòng.
     *
     * @param  array<int>  $variantIds
     * @return array<int, string>
     */
    private function variantLabels(array $variantIds): array
    {
        if ($variantIds === []) {
            return [];
        }

        return ProductVariant::query()
            ->with('variantValues')
            ->whereKey($variantIds)
            ->get()
            ->mapWithKeys(function (ProductVariant $variant): array {
                $label = $variant->variantValues
                    ->map(fn ($value): string => trim((string) $value->value))
                    ->filter()
                    ->implode(' / ');

                return [$variant->id => $label !== '' ? $label : 'Mặc định'];
            })
            ->all();
    }

    public function latestOrders()
    {
        $now = Carbon::now();

        $periods = [
            'today' => $this->latestOrdersForRange($now->copy()->startOfDay(), $now->copy(), 'hour'),
            '7days' => $this->latestOrdersForRange($now->copy()->subDays(6)->startOfDay(), $now->copy(), 'day'),
            '30days' => $this->latestOrdersForRange($now->copy()->subDays(29)->startOfDay(), $now->copy(), 'week'),
        ];

        return view('Admin.reports.latest_orders', compact('periods'));
    }

    /** Đơn phát sinh trong khoảng: KPI, danh sách gần nhất và cơ cấu trạng thái. */
    private function latestOrdersForRange(Carbon $start, Carbon $end, string $bucket): array
    {
        $lengthSeconds = max(1, $end->getTimestamp() - $start->getTimestamp());
        $prevEnd = $start->copy();
        $prevStart = $start->copy()->subSeconds($lengthSeconds);

        $total = Order::query()->whereBetween('created_at', [$start, $end])->count();
        $prevTotal = Order::query()->whereBetween('created_at', [$prevStart, $prevEnd])->count();

        // Doanh thu chỉ tính đơn đã thu được tiền, giống báo cáo Doanh Thu.
        $current = $this->rangeAggregate($start, $end);
        $previous = $this->rangeAggregate($prevStart, $prevEnd);

        $counts = $this->orderStatusCounts($start, $end);
        $pending = ($counts['pending'] ?? 0) + ($counts['confirmed'] ?? 0);

        $statusBreakdown = [];
        foreach (self::STATUS_GROUPS as $name => $config) {
            $count = 0;
            foreach ($config['statuses'] as $status) {
                $count += $counts[$status] ?? 0;
            }

            if ($count === 0) {
                continue; // không vẽ nhóm rỗng cho đỡ rối biểu đồ
            }

            $statusBreakdown[] = [
                'name' => $name,
                'count' => $count,
                'color' => $config['color'],
                'percentage' => $this->percent($total > 0 ? $count / $total * 100 : 0),
            ];
        }

        return [
            'total' => number_format($total, 0, ',', '.') . ' đơn',
            'totalTrend' => $this->trend($total, $prevTotal),
            'revenue' => $this->money($current['net']),
            'revenueTrend' => $this->trend($current['net'], $previous['net']),
            'aov' => $this->money($current['aov']),
            'pending' => number_format($pending, 0, ',', '.') . ' đơn',
            'orders' => $this->recentOrderRows($start, $end),
            'statusBreakdown' => $statusBreakdown,
            'chart' => $this->bucketSeries(
                Order::query()->whereBetween('created_at', [$start, $end]),
                'created_at',
                $bucket,
            ),
        ];
    }

    /** Nhãn hiển thị + lớp badge cho từng trạng thái đơn. */
    private const STATUS_LABELS = [
        'completed' => ['HOÀN TẤT', 'badge-completed'],
        'shipping' => ['ĐANG GIAO', 'badge-shipping'],
        'confirmed' => ['ĐÃ XÁC NHẬN', 'badge-pending'],
        'pending' => ['CHỜ XỬ LÝ', 'badge-pending'],
        'cancelled' => ['ĐÃ HỦY', 'badge-cancelled'],
    ];

    /** 15 đơn gần nhất trong khoảng, kèm tên khách và các mặt hàng đã mua. */
    private function recentOrderRows(Carbon $start, Carbon $end): array
    {
        $orders = Order::query()
            ->with(['user:id,name', 'items:id,order_id,product_name,quantity'])
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->limit(15)
            ->get();

        return $orders->map(function (Order $order): array {
            [$label, $badge] = self::STATUS_LABELS[$order->order_status] ?? [strtoupper($order->order_status), 'badge-pending'];

            $names = $order->items->pluck('product_name')->filter();
            $items = $names->take(2)->implode(', ');
            if ($names->count() > 2) {
                $items .= ' +' . ($names->count() - 2) . ' sản phẩm';
            }

            return [
                'id' => '#' . ($order->payment_code ?: 'PW' . $order->id),
                'customer' => $order->user?->name ?: ($order->recipient_name ?: 'Khách lẻ'),
                'items' => $items !== '' ? $items : '—',
                'total' => $this->money((float) $order->total_amount),
                'status' => $label,
                'statusClass' => $badge,
            ];
        })->all();
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

        $totalProfit = 0;
        $categoriesData = [];

        foreach ($rows as $row) {
            $rev = (float) $row->revenue;
            $cst = (float) $row->cost;
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
                    MIN(DATE(o.created_at)) as wk_start,
                    MAX(DATE(o.created_at)) as wk_end,
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

        return $rows->map(function ($row) use ($bucket): array {
            $rev = (float) $row->revenue;
            $cst = (float) $row->cost;
            $prof = $rev - $cst;
            $marg = $rev > 0 ? ($prof / $rev) * 100 : 0;

            if ($bucket === 'hour') {
                $label = $row->label;
            } elseif ($bucket === 'day') {
                $label = Carbon::parse($row->day)->format('d/m');
            } else {
                $label = 'Tuần ' . Carbon::parse($row->wk_start)->format('d/m') . '–' . Carbon::parse($row->wk_end)->format('d/m');
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
}
