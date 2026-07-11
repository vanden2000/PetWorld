<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class AdminController extends Controller
{
    public function index()
    {
        $recentOrders = Order::query()
            ->with('items:id,order_id,product_name')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard.index', [
            'recentOrders' => $recentOrders,
            'orderStatusLabels' => [
                'pending' => 'Chờ xác nhận',
                'confirmed' => 'Đã xác nhận',
                'shipping' => 'Đang giao',
                'completed' => 'Hoàn tất',
                'cancelled' => 'Đã hủy',
            ],
            'orderStatusClasses' => [
                'pending' => 'status-pending',
                'confirmed' => 'status-processing',
                'shipping' => 'status-shipping',
                'completed' => 'status-completed',
                'cancelled' => 'status-cancelled',
            ],
        ]);
    }
}
