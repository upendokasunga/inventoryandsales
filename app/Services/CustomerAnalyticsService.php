<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CustomerAnalyticsService
{
    public function getDashboardStats(): array
    {
        return Cache::remember('customer.dashboard.stats', 300, function () {
            return [
                'total' => Customer::count(),
                'active' => Customer::where('is_active', true)->count(),
                'on_hold' => Customer::where('credit_status', 'suspended')->count(),
                'overdue' => Customer::where('credit_status', 'overdue')->count(),
                'total_credit_limit' => Customer::sum('credit_limit'),
                'total_outstanding' => Customer::sum('outstanding_balance'),
                'total_available' => Customer::sum('available_credit'),
            ];
        });
    }

    public function getRecentTransactions(int $limit = 10): array
    {
        return CustomerCreditTransaction::with('customer')
            ->latest()
            ->take($limit)
            ->get()
            ->toArray();
    }

    public function getCreditUtilizationStats(): array
    {
        return Customer::select(
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(credit_limit) as total_limit'),
            DB::raw('SUM(outstanding_balance) as total_outstanding'),
            DB::raw('CASE WHEN SUM(credit_limit) > 0 THEN (SUM(outstanding_balance) / SUM(credit_limit)) * 100 ELSE 0 END as avg_utilization')
        )->where('is_active', true)->first()->toArray();
    }

    public function invalidateCache(): void
    {
        Cache::forget('customer.dashboard.stats');
    }
}
