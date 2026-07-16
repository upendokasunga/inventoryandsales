<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Customer;
use App\Models\InventoryBalance;
use App\Models\InventoryTransaction;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesReturn;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    public function getSummaryCards(): array
    {
        return Cache::remember('analytics.dashboard.summary', 21600, function () {
            $revenue = Invoice::whereIn('status', ['approved', 'completed'])
                ->sum('total');

            $cogs = InventoryTransaction::where('type', 'sales_order')
                ->sum('total_cost');

            $inventoryValue = InventoryBalance::sum('total_value');

            $outstandingReceivables = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereIn('payment_status', ['pending', 'partial', 'overdue'])
                ->sum('balance_due');

            $outstandingPayables = PurchaseOrder::whereIn('status', ['partially_received'])
                ->sum('total');

            $monthlySales = Invoice::whereIn('status', ['approved', 'completed'])
                ->whereMonth('invoice_date', now()->month)
                ->whereYear('invoice_date', now()->year)
                ->sum('total');

            $monthlyPurchases = PurchaseOrder::whereIn('status', ['completed', 'partially_received'])
                ->whereMonth('order_date', now()->month)
                ->whereYear('order_date', now()->year)
                ->sum('total');

            $tracked = Product::where('track_stock', true)->get();
            $lowStockItems = $tracked->filter(fn($p) =>
                $p->current_stock <= $p->reorder_level && $p->current_stock > 0
            )->count();

            $deadStockValue = $this->calculateDeadStockValue();

            return [
                'total_revenue' => round($revenue, 2),
                'gross_profit' => round($revenue - abs($cogs), 2),
                'net_profit' => round($revenue - abs($cogs), 2),
                'inventory_value' => round($inventoryValue, 2),
                'outstanding_receivables' => round($outstandingReceivables, 2),
                'outstanding_payables' => round($outstandingPayables, 2),
                'monthly_sales' => round($monthlySales, 2),
                'monthly_purchases' => round($monthlyPurchases, 2),
                'low_stock_items' => $lowStockItems,
                'dead_stock_value' => round($deadStockValue, 2),
            ];
        });
    }

    public function getSalesTrend(int $months = 12): array
    {
        return Cache::remember("analytics.dashboard.sales_trend.{$months}", 21600, function () use ($months) {
            $start = now()->subMonths($months)->startOfMonth();

            $monthly = Invoice::select(
                DB::raw("DATE_FORMAT(invoice_date, '%Y-%m') as month"),
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('SUM(total) as total_sales'),
                DB::raw('SUM(subtotal) as subtotal'),
                DB::raw('SUM(tax) as tax')
            )
                ->where('invoice_date', '>=', $start)
                ->whereIn('status', ['approved', 'completed'])
                ->groupBy(DB::raw("DATE_FORMAT(invoice_date, '%Y-%m')"))
                ->orderBy('month')
                ->get();

            return [
                'months' => $monthly->toArray(),
                'total_sales' => round($monthly->sum('total_sales'), 2),
                'average_monthly' => $monthly->count() > 0
                    ? round($monthly->sum('total_sales') / $monthly->count(), 2) : 0,
            ];
        });
    }

    public function getRevenueTrend(int $months = 12): array
    {
        return Cache::remember("analytics.dashboard.revenue_trend.{$months}", 21600, function () use ($months) {
            $start = now()->subMonths($months)->startOfMonth();

            $revenue = Invoice::select(
                DB::raw("DATE_FORMAT(invoice_date, '%Y-%m') as month"),
                DB::raw('SUM(total) as revenue')
            )
                ->where('invoice_date', '>=', $start)
                ->whereIn('status', ['approved', 'completed'])
                ->groupBy(DB::raw("DATE_FORMAT(invoice_date, '%Y-%m')"))
                ->orderBy('month')
                ->get();

            $cogs = InventoryTransaction::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(total_cost) as cost')
            )
                ->where('type', 'sales_order')
                ->where('created_at', '>=', $start)
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
                ->orderBy('month')
                ->get()
                ->keyBy('month');

            $merged = $revenue->map(function ($item) use ($cogs) {
                $costRow = $cogs->get($item->month);
                $cost = $costRow ? abs((float) $costRow->cost) : 0;

                return [
                    'month' => $item->month,
                    'revenue' => round((float) $item->revenue, 2),
                    'cogs' => round($cost, 2),
                    'profit' => round((float) $item->revenue - $cost, 2),
                ];
            });

            return [
                'months' => $merged->values()->toArray(),
                'total_revenue' => round($revenue->sum('revenue'), 2),
            ];
        });
    }

    public function getProfitTrend(int $months = 12): array
    {
        return Cache::remember("analytics.dashboard.profit_trend.{$months}", 21600, function () use ($months) {
            $start = now()->subMonths($months)->startOfMonth();

            $revenue = Invoice::select(
                DB::raw("DATE_FORMAT(invoice_date, '%Y-%m') as month"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as transaction_count')
            )
                ->where('invoice_date', '>=', $start)
                ->whereIn('status', ['approved', 'completed'])
                ->groupBy(DB::raw("DATE_FORMAT(invoice_date, '%Y-%m')"))
                ->orderBy('month')
                ->get();

            $cogs = InventoryTransaction::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(total_cost) as cost')
            )
                ->where('type', 'sales_order')
                ->where('created_at', '>=', $start)
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
                ->orderBy('month')
                ->get()
                ->keyBy('month');

            $merged = $revenue->map(function ($item) use ($cogs) {
                $costRow = $cogs->get($item->month);
                $cost = $costRow ? abs((float) $costRow->cost) : 0;
                $rev = (float) $item->revenue;
                $profit = $rev - $cost;

                return [
                    'month' => $item->month,
                    'revenue' => round($rev, 2),
                    'cogs' => round($cost, 2),
                    'gross_profit' => round($profit, 2),
                    'margin_percentage' => $rev > 0 ? round(($profit / $rev) * 100, 2) : 0,
                ];
            });

            return [
                'months' => $merged->values()->toArray(),
            ];
        });
    }

    public function getInventoryValueTrend(int $months = 12): array
    {
        return Cache::remember("analytics.dashboard.inventory_value_trend.{$months}", 21600, function () use ($months) {
            $start = now()->subMonths($months)->startOfMonth();

            $transactions = InventoryTransaction::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(CASE WHEN type IN (\'purchase_receipt\', \'return\') THEN total_cost ELSE 0 END) as incoming'),
                DB::raw('SUM(CASE WHEN type IN (\'sales_order\', \'adjustment\') THEN ABS(total_cost) ELSE 0 END) as outgoing')
            )
                ->where('created_at', '>=', $start)
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
                ->orderBy('month')
                ->get();

            $currentValue = InventoryBalance::sum('total_value');

            return [
                'current_value' => round($currentValue, 2),
                'months' => $transactions->toArray(),
            ];
        });
    }

    public function getPaymentMethodDistribution(string $startDate, string $endDate): array
    {
        return Cache::remember("analytics.dashboard.payment_distribution.{$startDate}.{$endDate}", 21600, function () use ($startDate, $endDate) {
            $distribution = Payment::select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total')
            )
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->groupBy('payment_method')
                ->get();

            $total = $distribution->sum('total');

            return [
                'total_amount' => round($total, 2),
                'distribution' => $distribution->map(fn($item) => [
                    'method' => $item->payment_method,
                    'count' => $item->count,
                    'total' => round((float) $item->total, 2),
                    'percentage' => $total > 0 ? round(((float) $item->total / $total) * 100, 2) : 0,
                ])->toArray(),
            ];
        });
    }

    public function getCustomerGrowth(int $months = 12): array
    {
        return Cache::remember("analytics.dashboard.customer_growth.{$months}", 21600, function () use ($months) {
            $start = now()->subMonths($months)->startOfMonth();

            $monthly = Customer::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as new_customers')
            )
                ->where('created_at', '>=', $start)
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
                ->orderBy('month')
                ->get();

            $total = Customer::count();
            $active = Customer::where('is_active', true)->count();

            return [
                'total_customers' => $total,
                'active_customers' => $active,
                'months' => $monthly->toArray(),
            ];
        });
    }

    public function getCategoryPerformance(string $startDate, string $endDate): array
    {
        return Cache::remember("analytics.dashboard.category_performance.{$startDate}.{$endDate}", 21600, function () use ($startDate, $endDate) {
            $performance = InvoiceItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(line_total) as total_revenue')
            )
                ->whereHas('invoice', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('invoice_date', [$startDate, $endDate])
                        ->whereIn('status', ['approved', 'completed']);
                })
                ->groupBy('product_id')
                ->with('product.category')
                ->get()
                ->groupBy(fn($item) => $item->product?->category?->name ?? 'Uncategorized');

            $categories = $performance->map(function ($items, $categoryName) {
                return [
                    'category' => $categoryName,
                    'total_quantity' => round($items->sum('total_qty'), 2),
                    'total_revenue' => round($items->sum('total_revenue'), 2),
                    'product_count' => $items->count(),
                ];
            });

            return [
                'categories' => $categories->values()->toArray(),
                'total_revenue' => round($categories->sum('total_revenue'), 2),
            ];
        });
    }

    public function getDebtExposure(): array
    {
        return Cache::remember('analytics.dashboard.debt_exposure', 21600, function () {
            $customers = Customer::where('credit_limit', '>', 0)
                ->where('is_active', true)
                ->get();

            $totalLimit = $customers->sum('credit_limit');
            $totalOutstanding = $customers->sum('outstanding_balance');
            $overdueCount = Customer::where('credit_status', 'overdue')->count();
            $suspendedCount = Customer::where('credit_status', 'suspended')->count();

            $atRisk = $customers->filter(fn($c) =>
                $c->credit_limit > 0 && ($c->outstanding_balance / $c->credit_limit) > 0.8
            );

            return [
                'total_credit_limit' => round($totalLimit, 2),
                'total_outstanding' => round($totalOutstanding, 2),
                'utilization_rate' => $totalLimit > 0
                    ? round(($totalOutstanding / $totalLimit) * 100, 2) : 0,
                'overdue_customers' => $overdueCount,
                'suspended_customers' => $suspendedCount,
                'at_risk_customers' => $atRisk->count(),
                'available_credit' => round($totalLimit - $totalOutstanding, 2),
            ];
        });
    }

    public function getReturnTrends(int $months = 12): array
    {
        return Cache::remember("analytics.dashboard.return_trends.{$months}", 21600, function () use ($months) {
            $start = now()->subMonths($months)->startOfMonth();

            $monthly = SalesReturn::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as return_count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
                ->where('created_at', '>=', $start)
                ->whereIn('status', ['approved', 'completed'])
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
                ->orderBy('month')
                ->get();

            $totalReturns = $monthly->sum('total_amount');
            $totalSales = Invoice::whereIn('status', ['approved', 'completed'])
                ->where('created_at', '>=', $start)
                ->sum('total');

            return [
                'months' => $monthly->toArray(),
                'total_returns' => round($totalReturns, 2),
                'return_rate' => $totalSales > 0
                    ? round(($totalReturns / $totalSales) * 100, 2) : 0,
            ];
        });
    }

    protected function calculateDeadStockValue(): float
    {
        $threshold = now()->subDays(90);
        $productIds = InventoryTransaction::where('type', 'sales_order')
            ->where('created_at', '>=', $threshold)
            ->distinct('product_id')
            ->pluck('product_id');

        return InventoryBalance::whereNotIn('product_id', $productIds)
            ->where('quantity_on_hand', '>', 0)
            ->sum('total_value');
    }

    public function invalidateCache(): void
    {
        Cache::forget('analytics.dashboard.summary');
    }
}
