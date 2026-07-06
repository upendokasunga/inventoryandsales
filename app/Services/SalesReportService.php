<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    public function getDailySales(string $date): array
    {
        return Cache::remember("report.sales.daily.{$date}", 3600, function () use ($date) {
            return $this->baseQuery(
                Invoice::whereDate('created_at', $date)
                    ->whereIn('status', ['paid', 'approved'])
            );
        });
    }

    public function getWeeklySales(string $weekStart, string $weekEnd): array
    {
        $key = "report.sales.weekly.{$weekStart}.{$weekEnd}";
        return Cache::remember($key, 3600, function () use ($weekStart, $weekEnd) {
            $invoices = Invoice::whereBetween('created_at', [$weekStart, $weekEnd])
                ->whereIn('status', ['paid', 'approved']);
            return $this->baseQuery(clone $invoices);
        });
    }

    public function getMonthlySales(int $year, int $month): array
    {
        $key = "report.sales.monthly.{$year}.{$month}";
        return Cache::remember($key, 3600, function () use ($year, $month) {
            $invoices = Invoice::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereIn('status', ['paid', 'approved']);
            return $this->baseQuery(clone $invoices);
        });
    }

    public function getAnnualSales(int $year): array
    {
        $key = "report.sales.annual.{$year}";
        return Cache::remember($key, 3600, function () use ($year) {
            $invoices = Invoice::whereYear('created_at', $year)
                ->whereIn('status', ['paid', 'approved']);
            return $this->baseQuery(clone $invoices);
        });
    }

    public function getCustomRange(string $startDate, string $endDate, array $filters = []): array
    {
        $filterHash = md5(json_encode($filters));
        $key = "report.sales.custom.{$startDate}.{$endDate}.{$filterHash}";
        return Cache::remember($key, 3600, function () use ($startDate, $endDate, $filters) {
            $query = Invoice::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['paid', 'approved']);

            if (!empty($filters['customer_id'])) {
                $query->where('customer_id', $filters['customer_id']);
            }
            if (!empty($filters['payment_method'])) {
                $query->whereHas('payments', fn($q) => $q->where('payment_method', $filters['payment_method']));
            }
            if (!empty($filters['created_by'])) {
                $query->where('created_by', $filters['created_by']);
            }

            $result = $this->baseQuery(clone $query);
            $byDay = $query->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as revenue')
            )->groupBy('date')->orderBy('date')->get()->toArray();

            $result['sales_by_day'] = $byDay;
            return $result;
        });
    }

    public function getTopProducts(string $startDate, string $endDate, int $limit = 10): array
    {
        return Cache::remember("report.sales.top_products.{$startDate}.{$endDate}.{$limit}", 3600, function () use ($startDate, $endDate, $limit) {
            return InvoiceItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(line_total) as total_revenue'),
                DB::raw('SUM(line_total - (unit_price * quantity)) as gross_profit')
            )
                ->whereHas('invoice', fn($q) => $q->whereIn('status', ['paid', 'approved'])
                    ->whereBetween('created_at', [$startDate, $endDate]))
                ->groupBy('product_id')
                ->orderByDesc('total_revenue')
                ->limit($limit)
                ->with('product:id,name,sku')
                ->get()
                ->toArray();
        });
    }

    public function getTopCategories(string $startDate, string $endDate, int $limit = 10): array
    {
        return Cache::remember("report.sales.top_categories.{$startDate}.{$endDate}.{$limit}", 3600, function () use ($startDate, $endDate, $limit) {
            return InvoiceItem::select(
                'products.category_id',
                DB::raw('SUM(sales_invoice_items.line_total) as total_revenue'),
                DB::raw('SUM(sales_invoice_items.quantity) as total_quantity'),
                DB::raw('COUNT(DISTINCT sales_invoice_items.invoice_id) as order_count')
            )
                ->join('products', 'products.id', '=', 'sales_invoice_items.product_id')
                ->whereHas('invoice', fn($q) => $q->whereIn('status', ['paid', 'approved'])
                    ->whereBetween('created_at', [$startDate, $endDate]))
                ->groupBy('products.category_id')
                ->orderByDesc('total_revenue')
                ->limit($limit)
                ->with('product.category:id,name')
                ->get()
                ->toArray();
        });
    }

    public function getTopCustomers(string $startDate, string $endDate, int $limit = 10): array
    {
        return Cache::remember("report.sales.top_customers.{$startDate}.{$endDate}.{$limit}", 3600, function () use ($startDate, $endDate, $limit) {
            return Invoice::select(
                'customer_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_spent'),
                DB::raw('AVG(total_amount) as avg_order_value')
            )
                ->whereIn('status', ['paid', 'approved'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('customer_id')
                ->orderByDesc('total_spent')
                ->limit($limit)
                ->with('customer:id,name,email')
                ->get()
                ->toArray();
        });
    }

    public function getPaymentMethodBreakdown(string $startDate, string $endDate): array
    {
        return Cache::remember("report.sales.payment_methods.{$startDate}.{$endDate}", 3600, function () use ($startDate, $endDate) {
            return Payment::select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total')
            )
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->groupBy('payment_method')
                ->get()
                ->toArray();
        });
    }

    public function invalidateCache(): void
    {
        Cache::forget('report.sales.daily');
        Cache::forget('report.sales.weekly');
        Cache::forget('report.sales.monthly');
        Cache::forget('report.sales.annual');
        Cache::forget('report.sales.custom');
        Cache::forget('report.sales.top_products');
        Cache::forget('report.sales.top_categories');
        Cache::forget('report.sales.top_customers');
        Cache::forget('report.sales.payment_methods');
    }

    protected function baseQuery($query): array
    {
        $stats = (clone $query)->select(
            DB::raw('COUNT(*) as total_sales'),
            DB::raw('COALESCE(SUM(total_amount), 0) as total_revenue'),
            DB::raw('COALESCE(SUM(discount), 0) as total_discounts'),
            DB::raw('COALESCE(SUM(tax), 0) as total_taxes'),
        )->first();

        $cost = InvoiceItem::whereHas('invoice', fn($q) => $q->whereIn('status', ['paid', 'approved']))
            ->whereIn('invoice_id', (clone $query)->pluck('id'))
            ->select(DB::raw('COALESCE(SUM(unit_price * quantity), 0) as total_cost'))
            ->value('total_cost');

        $revenue = $stats->total_revenue ?? 0;
        $count = $stats->total_sales ?? 0;

        return [
            'total_sales' => (int) $count,
            'total_revenue' => (float) $revenue,
            'total_discounts' => (float) ($stats->total_discounts ?? 0),
            'total_taxes' => (float) ($stats->total_taxes ?? 0),
            'total_cost' => (float) $cost,
            'gross_profit' => (float) ($revenue - $cost),
            'average_order_value' => $count > 0 ? round($revenue / $count, 2) : 0,
        ];
    }
}
