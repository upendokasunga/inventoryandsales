<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProcurementReportService
{
    public function getMonthlySpend(int $year, int $month): array
    {
        return Cache::remember("report.procurement.monthly_spend.{$year}.{$month}", 3600, function () use ($year, $month) {
            $start = "{$year}-{$month}-01";
            $end = date('Y-m-t', strtotime($start));

            $orders = PurchaseOrder::select(
                'supplier_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as total_spent')
            )
                ->whereBetween('order_date', [$start, $end])
                ->whereIn('status', ['completed', 'partially_received'])
                ->groupBy('supplier_id')
                ->with('supplier')
                ->get();

            $total = $orders->sum('total_spent');

            return [
                'year' => $year,
                'month' => $month,
                'total_spend' => round($total, 2),
                'order_count' => $orders->sum('order_count'),
                'supplier_count' => $orders->count(),
                'by_supplier' => $orders->toArray(),
            ];
        });
    }

    public function getPendingApprovals(): array
    {
        return Cache::remember('report.procurement.pending_approvals', 3600, function () {
            return PurchaseOrder::with(['supplier', 'creator'])
                ->where('status', 'pending_approval')
                ->latest()
                ->take(1000)->get()
                ->toArray();
        });
    }

    public function getPurchaseOrderAnalysis(string $startDate, string $endDate): array
    {
        return Cache::remember("report.procurement.po_analysis.{$startDate}.{$endDate}", 3600, function () use ($startDate, $endDate) {
            $orders = PurchaseOrder::with(['supplier', 'items.product'])
                ->whereBetween('order_date', [$startDate, $endDate])
                ->take(1000)->get();

            $totalOrders = $orders->count();
            $totalAmount = $orders->sum('total');
            $averageOrderValue = $totalOrders > 0 ? $totalAmount / $totalOrders : 0;

            $byStatus = $orders->groupBy('status')->map(fn($group) => [
                'count' => $group->count(),
                'total' => round($group->sum('total'), 2),
            ]);

            $totalItems = PurchaseOrderItem::whereHas('purchaseOrder', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('order_date', [$startDate, $endDate]);
            })->sum('quantity');

            return [
                'total_orders' => $totalOrders,
                'total_amount' => round($totalAmount, 2),
                'average_order_value' => round($averageOrderValue, 2),
                'total_items_ordered' => round($totalItems, 2),
                'by_status' => $byStatus,
            ];
        });
    }

    public function getProcurementSavings(string $startDate, string $endDate): array
    {
        return Cache::remember("report.procurement.savings.{$startDate}.{$endDate}", 3600, function () use ($startDate, $endDate) {
            $items = PurchaseOrderItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('AVG(unit_price) as avg_price'),
                DB::raw('SUM(subtotal) as total_spent')
            )
                ->whereHas('purchaseOrder', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('order_date', [$startDate, $endDate])
                        ->whereIn('status', ['completed', 'partially_received']);
                })
                ->groupBy('product_id')
                ->with('product')
                ->get();

            $previousItems = PurchaseOrderItem::select(
                'product_id',
                DB::raw('AVG(unit_price) as avg_price')
            )
                ->whereHas('purchaseOrder', function ($q) use ($startDate) {
                    $q->where('order_date', '<', $startDate)
                        ->whereIn('status', ['completed', 'partially_received']);
                })
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');

            $savings = 0;
            $results = [];

            foreach ($items as $item) {
                $prev = $previousItems->get($item->product_id);
                $prevPrice = $prev ? (float) $prev->avg_price : 0;
                $currentPrice = (float) $item->avg_price;
                $lineSavings = $prevPrice > 0 ? ($prevPrice - $currentPrice) * (float) $item->total_qty : 0;
                $savings += $lineSavings;

                $results[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name,
                    'total_quantity' => (float) $item->total_qty,
                    'previous_avg_price' => round($prevPrice, 2),
                    'current_avg_price' => round($currentPrice, 2),
                    'savings' => round($lineSavings, 2),
                ];
            }

            return [
                'total_savings' => round($savings, 2),
                'total_spend' => round($items->sum('total_spent'), 2),
                'savings_percentage' => $items->sum('total_spent') > 0
                    ? round(($savings / $items->sum('total_spent')) * 100, 2) : 0,
                'items_analyzed' => count($results),
                'details' => $results,
            ];
        });
    }

    public function getPurchaseTrends(string $startDate, string $endDate): array
    {
        return Cache::remember("report.procurement.trends.{$startDate}.{$endDate}", 3600, function () use ($startDate, $endDate) {
            $monthly = PurchaseOrder::select(
                DB::raw("DATE_FORMAT(order_date, '%Y-%m') as month"),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as total_amount'),
                DB::raw('AVG(total) as average_amount')
            )
                ->whereBetween('order_date', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'partially_received'])
                ->groupBy(DB::raw("DATE_FORMAT(order_date, '%Y-%m')"))
                ->orderBy('month')
                ->get();

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'months' => $monthly->toArray(),
                'total_orders' => $monthly->sum('order_count'),
                'total_amount' => round($monthly->sum('total_amount'), 2),
                'average_monthly' => $monthly->count() > 0
                    ? round($monthly->sum('total_amount') / $monthly->count(), 2) : 0,
            ];
        });
    }

    public function invalidateCache(): void
    {
        Cache::forget('report.procurement.pending_approvals');
    }
}
