<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CustomerAnalyticsService;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function __construct(
        protected CustomerAnalyticsService $analyticsService
    ) {}

    public function index(): View
    {
        $stats = $this->analyticsService->getDashboardStats();
        $recentTransactions = $this->analyticsService->getRecentTransactions(10);
        $utilization = $this->analyticsService->getCreditUtilizationStats();
        $recentCustomers = Customer::with('group')->latest()->take(5)->get();

        return view('customers.dashboard', compact(
            'stats', 'recentTransactions', 'utilization', 'recentCustomers'
        ));
    }
}
