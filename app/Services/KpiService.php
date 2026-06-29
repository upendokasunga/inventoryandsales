<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\InventoryBalance;
use App\Models\InventoryTransaction;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\KpiSnapshot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KpiService
{
    public function getDailyKpis(?string $date = null): array
    {
        $date = $date ?: today()->toDateString();

        return Cache::remember("kpi.daily.{$date}", 1800, function () use ($date) {
            $revenue = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereDate('invoice_date', $date)
                ->sum('total');

            $transactions = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereDate('invoice_date', $date)
                ->count();

            $orders = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereDate('invoice_date', $date)
                ->count();

            $cogs = InventoryTransaction::where('type', 'sales_order')
                ->whereDate('created_at', $date)
                ->sum(DB::raw('ABS(total_cost)'));

            return [
                'date' => $date,
                'revenue' => round($revenue, 2),
                'cost_of_goods' => round($cogs, 2),
                'profit' => round($revenue - $cogs, 2),
                'transaction_count' => $transactions,
                'order_count' => $orders,
                'average_order_value' => $orders > 0 ? round($revenue / $orders, 2) : 0,
            ];
        });
    }

    public function getWeeklyKpis(?string $weekStart = null, ?string $weekEnd = null): array
    {
        $weekStart = $weekStart ?: now()->startOfWeek()->toDateString();
        $weekEnd = $weekEnd ?: now()->endOfWeek()->toDateString();

        return Cache::remember("kpi.weekly.{$weekStart}.{$weekEnd}", 1800, function () use ($weekStart, $weekEnd) {
            $revenue = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereBetween('invoice_date', [$weekStart, $weekEnd])
                ->sum('total');

            $orders = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereBetween('invoice_date', [$weekStart, $weekEnd])
                ->count();

            $cogs = InventoryTransaction::where('type', 'sales_order')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->sum(DB::raw('ABS(total_cost)'));

            return [
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
                'revenue' => round($revenue, 2),
                'cost_of_goods' => round($cogs, 2),
                'profit' => round($revenue - $cogs, 2),
                'order_count' => $orders,
                'average_order_value' => $orders > 0 ? round($revenue / $orders, 2) : 0,
            ];
        });
    }

    public function getMonthlyKpis(?int $year = null, ?int $month = null): array
    {
        $year = $year ?: now()->year;
        $month = $month ?: now()->month;

        return Cache::remember("kpi.monthly.{$year}.{$month}", 1800, function () use ($year, $month) {
            $start = "{$year}-{$month}-01";
            $end = date('Y-m-t', strtotime($start));
            $daysInMonth = date('t', strtotime($start));

            $revenue = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereBetween('invoice_date', [$start, $end])
                ->sum('total');

            $orders = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereBetween('invoice_date', [$start, $end])
                ->count();

            $cogs = InventoryTransaction::where('type', 'sales_order')
                ->whereBetween('created_at', [$start, $end])
                ->sum(DB::raw('ABS(total_cost)'));

            $previousStart = date('Y-m-d', strtotime("-1 month", strtotime($start)));
            $previousEnd = date('Y-m-t', strtotime($previousStart));
            $previousRevenue = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereBetween('invoice_date', [$previousStart, $previousEnd])
                ->sum('total');

            $salesGrowth = $previousRevenue > 0
                ? round((($revenue - $previousRevenue) / $previousRevenue) * 100, 2) : 0;

            $customers = Customer::where('created_at', '<=', $end)->count();
            $previousCustomers = Customer::where('created_at', '<', $start)->count();

            $avgInventory = InventoryBalance::sum(DB::raw('(total_value)'));
            $inventoryTurnover = $avgInventory > 0
                ? round($cogs / ($avgInventory / $daysInMonth), 2) : 0;

            return [
                'year' => $year,
                'month' => $month,
                'revenue' => round($revenue, 2),
                'cost_of_goods' => round($cogs, 2),
                'profit' => round($revenue - $cogs, 2),
                'margin' => $revenue > 0 ? round((($revenue - $cogs) / $revenue) * 100, 2) : 0,
                'order_count' => $orders,
                'average_order_value' => $orders > 0 ? round($revenue / $orders, 2) : 0,
                'daily_average_revenue' => round($revenue / $daysInMonth, 2),
                'sales_growth' => $salesGrowth,
                'total_customers' => $customers,
                'new_customers' => $customers - $previousCustomers,
                'inventory_turnover' => $inventoryTurnover,
            ];
        });
    }

    public function getQuarterlyKpis(?int $year = null, ?int $quarter = null): array
    {
        $year = $year ?: now()->year;
        $quarter = $quarter ?: now()->quarter;
        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $startMonth + 2;
        $start = "{$year}-{$startMonth}-01";
        $end = date('Y-m-t', strtotime("{$year}-{$endMonth}-01"));

        return Cache::remember("kpi.quarterly.{$year}.{$quarter}", 1800, function () use ($start, $end, $year, $quarter) {
            $revenue = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereBetween('invoice_date', [$start, $end])
                ->sum('total');

            $orders = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereBetween('invoice_date', [$start, $end])
                ->count();

            $cogs = InventoryTransaction::where('type', 'sales_order')
                ->whereBetween('created_at', [$start, $end])
                ->sum(DB::raw('ABS(total_cost)'));

            return [
                'year' => $year,
                'quarter' => $quarter,
                'revenue' => round($revenue, 2),
                'cost_of_goods' => round($cogs, 2),
                'profit' => round($revenue - $cogs, 2),
                'margin' => $revenue > 0 ? round((($revenue - $cogs) / $revenue) * 100, 2) : 0,
                'order_count' => $orders,
                'average_order_value' => $orders > 0 ? round($revenue / $orders, 2) : 0,
            ];
        });
    }

    public function getAnnualKpis(?int $year = null): array
    {
        $year = $year ?: now()->year;
        $start = "{$year}-01-01";
        $end = "{$year}-12-31";

        return Cache::remember("kpi.annual.{$year}", 1800, function () use ($start, $end, $year) {
            $revenue = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereBetween('invoice_date', [$start, $end])
                ->sum('total');

            $orders = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereBetween('invoice_date', [$start, $end])
                ->count();

            $cogs = InventoryTransaction::where('type', 'sales_order')
                ->whereBetween('created_at', [$start, $end])
                ->sum(DB::raw('ABS(total_cost)'));

            $previousRevenue = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereBetween('invoice_date', [($year - 1) . '-01-01', ($year - 1) . '-12-31'])
                ->sum('total');

            $salesGrowth = $previousRevenue > 0
                ? round((($revenue - $previousRevenue) / $previousRevenue) * 100, 2) : 0;

            $customers = Customer::whereYear('created_at', '<=', $year)->count();
            $previousCustomers = Customer::whereYear('created_at', '<', $year)->count();

            $avgInventory = InventoryBalance::sum(DB::raw('(total_value)'));

            return [
                'year' => $year,
                'revenue' => round($revenue, 2),
                'cost_of_goods' => round($cogs, 2),
                'profit' => round($revenue - $cogs, 2),
                'margin' => $revenue > 0 ? round((($revenue - $cogs) / $revenue) * 100, 2) : 0,
                'order_count' => $orders,
                'average_order_value' => $orders > 0 ? round($revenue / $orders, 2) : 0,
                'monthly_average_revenue' => round($revenue / 12, 2),
                'sales_growth' => $salesGrowth,
                'total_customers' => $customers,
                'new_customers' => $customers - $previousCustomers,
            ];
        });
    }

    public function getRevenueKpi(): float
    {
        return Cache::remember('kpi.revenue', 1800, function () {
            return round(
                Invoice::whereIn('status', ['approved', 'completed'])
                    ->whereMonth('invoice_date', now()->month)
                    ->whereYear('invoice_date', now()->year)
                    ->sum('total'),
                2
            );
        });
    }

    public function getProfitKpi(): float
    {
        return Cache::remember('kpi.profit', 1800, function () {
            $revenue = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereMonth('invoice_date', now()->month)
                ->whereYear('invoice_date', now()->year)
                ->sum('total');

            $cogs = InventoryTransaction::where('type', 'sales_order')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum(DB::raw('ABS(total_cost)'));

            return round($revenue - $cogs, 2);
        });
    }

    public function getMarginKpi(): float
    {
        return Cache::remember('kpi.margin', 1800, function () {
            $revenue = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereMonth('invoice_date', now()->month)
                ->whereYear('invoice_date', now()->year)
                ->sum('total');

            $cogs = InventoryTransaction::where('type', 'sales_order')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum(DB::raw('ABS(total_cost)'));

            if ($revenue <= 0) {
                return 0;
            }

            return round((($revenue - $cogs) / $revenue) * 100, 2);
        });
    }

    public function getSalesGrowthKpi(): float
    {
        return Cache::remember('kpi.sales_growth', 1800, function () {
            $currentRevenue = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereMonth('invoice_date', now()->month)
                ->whereYear('invoice_date', now()->year)
                ->sum('total');

            $lastMonth = now()->subMonth();
            $previousRevenue = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereMonth('invoice_date', $lastMonth->month)
                ->whereYear('invoice_date', $lastMonth->year)
                ->sum('total');

            if ($previousRevenue <= 0) {
                return $currentRevenue > 0 ? 100 : 0;
            }

            return round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 2);
        });
    }

    public function getInventoryTurnoverKpi(): float
    {
        return Cache::remember('kpi.inventory_turnover', 1800, function () {
            $cogs = InventoryTransaction::where('type', 'sales_order')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum(DB::raw('ABS(total_cost)'));

            $avgInventory = InventoryBalance::sum(DB::raw('(total_value)'));

            if ($avgInventory <= 0) {
                return 0;
            }

            return round($cogs / ($avgInventory / 12), 2);
        });
    }

    public function getCustomerGrowthKpi(): float
    {
        return Cache::remember('kpi.customer_growth', 1800, function () {
            $currentMonth = Customer::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            $lastMonth = now()->subMonth();
            $previousMonth = Customer::whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->count();

            if ($previousMonth <= 0) {
                return $currentMonth > 0 ? 100 : 0;
            }

            return round((($currentMonth - $previousMonth) / $previousMonth) * 100, 2);
        });
    }

    public function getDebtRatioKpi(): float
    {
        return Cache::remember('kpi.debt_ratio', 1800, function () {
            $outstanding = Customer::sum('outstanding_balance');
            $totalLimit = Customer::where('credit_limit', '>', 0)->sum('credit_limit');

            if ($totalLimit <= 0) {
                return 0;
            }

            return round(($outstanding / $totalLimit) * 100, 2);
        });
    }

    public function invalidateCache(): void
    {
        Cache::forget('kpi.revenue');
        Cache::forget('kpi.profit');
        Cache::forget('kpi.margin');
        Cache::forget('kpi.sales_growth');
        Cache::forget('kpi.inventory_turnover');
        Cache::forget('kpi.customer_growth');
        Cache::forget('kpi.debt_ratio');
    }
}
