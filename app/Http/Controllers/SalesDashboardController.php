<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Services\SalesOrderService;

class SalesDashboardController extends Controller
{
    public function __construct(
        protected SalesOrderService $salesOrderService,
    ) {}

    public function index()
    {
        $stats = $this->salesOrderService->getStats();
        $recentOrders = SalesOrder::with('customer')
            ->latest()
            ->limit(10)
            ->get();

        return view('sales.dashboard', compact('stats', 'recentOrders'));
    }
}
