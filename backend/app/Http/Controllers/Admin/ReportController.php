<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function revenue()
    {
        return view('Admin.reports.revenue');
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
