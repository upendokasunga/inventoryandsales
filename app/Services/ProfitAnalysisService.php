<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProfitAnalysisService
{
    public function __construct(
        protected InventoryValuationService $valuationService,
    ) {}

    public function getGrossProfit(string $startDate, string $endDate): array
    {
        $key = "report.profit.gross.{$startDate}.{$endDate}";
        return Cache::remember($key, 3600, function () use ($startDate, $endDate) {
            $items = InvoiceItem::whereHas('invoice', fn($q) => $q
                ->whereIn('status', ['paid', 'approved'])
                ->whereBetween('created_at', [$startDate, $endDate]))
                ->select(
                    DB::raw('COALESCE(SUM(line_total), 0) as revenue'),
                    DB::raw('COALESCE(SUM(unit_price * quantity), 0) as cost'),
                    DB::raw('COALESCE(SUM(line_total - (unit_price * quantity)), 0) as gross_profit'),
                )->first();

            $revenue = (float) ($items->revenue ?? 0);
            $cost = (float) ($items->cost ?? 0);
            $profit = (float) ($items->gross_profit ?? 0);

            return [
                'revenue' => $revenue,
                'cost_of_goods_sold' => $cost,
                'gross_profit' => $profit,
                'gross_margin_percent' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
            ];
        });
    }

    public function getNetProfit(string $startDate, string $endDate): array
    {
        $gross = $this->getGrossProfit($startDate, $endDate);
        $returns = \App\Models\SalesReturn::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['approved', 'completed'])
            ->sum('total_amount');

        $discounts = Invoice::whereIn('status', ['paid', 'approved'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('discount');

        return [
            'gross_profit' => $gross['gross_profit'],
            'returns_deducted' => (float) $returns,
            'discounts_given' => (float) $discounts,
            'net_profit' => $gross['gross_profit'] - $returns - $discounts,
        ];
    }

    public function getProfitMargin(string $startDate, string $endDate): float
    {
        $gross = $this->getGrossProfit($startDate, $endDate);
        return $gross['gross_margin_percent'];
    }

    public function getProductProfitability(string $startDate, string $endDate, int $limit = 20): array
    {
        $key = "report.profit.products.{$startDate}.{$endDate}.{$limit}";
        return Cache::remember($key, 3600, function () use ($startDate, $endDate, $limit) {
            return InvoiceItem::select(
                'product_id',
                DB::raw('SUM(quantity) as quantity_sold'),
                DB::raw('SUM(line_total) as revenue'),
                DB::raw('SUM(unit_price * quantity) as cost'),
                DB::raw('SUM(line_total - (unit_price * quantity)) as profit'),
            )
                ->whereHas('invoice', fn($q) => $q
                    ->whereIn('status', ['paid', 'approved'])
                    ->whereBetween('created_at', [$startDate, $endDate]))
                ->groupBy('product_id')
                ->orderByDesc('profit')
                ->limit($limit)
                ->with('product:id,name,sku,category_id')
                ->get()
                ->map(fn($i) => [
                    'product_id' => $i->product_id,
                    'product_name' => $i->product?->name,
                    'sku' => $i->product?->sku,
                    'quantity_sold' => (int) $i->quantity_sold,
                    'revenue' => (float) $i->revenue,
                    'cost' => (float) $i->cost,
                    'profit' => (float) $i->profit,
                    'margin_percent' => $i->revenue > 0 ? round(($i->profit / $i->revenue) * 100, 2) : 0,
                ])->toArray();
        });
    }

    public function getCategoryProfitability(string $startDate, string $endDate): array
    {
        $key = "report.profit.categories.{$startDate}.{$endDate}";
        return Cache::remember($key, 3600, function () use ($startDate, $endDate) {
            return InvoiceItem::select(
                'products.category_id',
                DB::raw('SUM(sales_invoice_items.line_total) as revenue'),
                DB::raw('SUM(sales_invoice_items.unit_price * sales_invoice_items.quantity) as cost'),
                DB::raw('SUM(sales_invoice_items.line_total - (sales_invoice_items.unit_price * sales_invoice_items.quantity)) as profit'),
            )
                ->join('products', 'products.id', '=', 'sales_invoice_items.product_id')
                ->whereHas('invoice', fn($q) => $q
                    ->whereIn('status', ['paid', 'approved'])
                    ->whereBetween('created_at', [$startDate, $endDate]))
                ->groupBy('products.category_id')
                ->with('product.category:id,name')
                ->get()
                ->map(fn($i) => [
                    'category_id' => $i->category_id,
                    'category_name' => $i->product?->category?->name,
                    'revenue' => (float) $i->revenue,
                    'cost' => (float) $i->cost,
                    'profit' => (float) $i->profit,
                    'margin_percent' => $i->revenue > 0 ? round(($i->profit / $i->revenue) * 100, 2) : 0,
                ])->toArray();
        });
    }

    public function getCustomerProfitability(string $startDate, string $endDate, int $limit = 20): array
    {
        $key = "report.profit.customers.{$startDate}.{$endDate}.{$limit}";
        return Cache::remember($key, 3600, function () use ($startDate, $endDate, $limit) {
            return Invoice::select(
                'customer_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as revenue'),
            )
                ->whereIn('status', ['paid', 'approved'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('customer_id')
                ->orderByDesc('revenue')
                ->limit($limit)
                ->with('customer:id,name,email')
                ->get()
                ->map(fn($i) => [
                    'customer_id' => $i->customer_id,
                    'customer_name' => $i->customer?->name,
                    'order_count' => (int) $i->order_count,
                    'revenue' => (float) $i->revenue,
                ])->toArray();
        });
    }

    public function getTopMarginProducts(int $limit = 10): array
    {
        return Cache::remember("report.profit.top_margin.{$limit}", 3600, function () use ($limit) {
            return Product::whereHas('invoiceItems', fn($q) => $q->whereHas('invoice', fn($i) => $i->whereIn('status', ['paid', 'approved'])))
                ->withCount(['invoiceItems as total_revenue' => fn($q) => $q->select(DB::raw('COALESCE(SUM(line_total), 0)'))
                    ->whereHas('invoice', fn($i) => $i->whereIn('status', ['paid', 'approved']))])
                ->limit($limit)
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'revenue' => (float) $p->total_revenue,
                ])->toArray();
        });
    }

    public function getLowMarginProducts(int $limit = 10): array
    {
        return Cache::remember("report.profit.low_margin.{$limit}", 3600, function () use ($limit) {
            return InvoiceItem::select(
                'product_id',
                DB::raw('SUM(line_total) as revenue'),
                DB::raw('SUM(unit_price * quantity) as cost'),
                DB::raw('SUM(line_total - (unit_price * quantity)) as profit'),
            )
                ->whereHas('invoice', fn($q) => $q->whereIn('status', ['paid', 'approved']))
                ->groupBy('product_id')
                ->havingRaw('SUM(line_total) > 0')
                ->orderByRaw('(SUM(line_total - (unit_price * quantity)) / SUM(line_total)) ASC')
                ->limit($limit)
                ->with('product:id,name,sku')
                ->get()
                ->map(fn($i) => [
                    'product_id' => $i->product_id,
                    'product_name' => $i->product?->name,
                    'sku' => $i->product?->sku,
                    'revenue' => (float) $i->revenue,
                    'cost' => (float) $i->cost,
                    'profit' => (float) $i->profit,
                    'margin_percent' => $i->revenue > 0 ? round(($i->profit / $i->revenue) * 100, 2) : 0,
                ])->toArray();
        });
    }

    public function invalidateCache(): void
    {
        Cache::forget('report.profit.gross');
        Cache::forget('report.profit.products');
        Cache::forget('report.profit.categories');
        Cache::forget('report.profit.customers');
        Cache::forget('report.profit.top_margin');
        Cache::forget('report.profit.low_margin');
    }
}
