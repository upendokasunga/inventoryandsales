<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierPerformance;
use App\Models\SupplierPriceHistory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SupplierReportService
{
    public function getPurchaseTrends(string $startDate, string $endDate): array
    {
        $key = "report.suppliers.purchase_trends.{$startDate}.{$endDate}";
        return Cache::remember($key, 3600, function () use ($startDate, $endDate) {
            $trends = PurchaseOrder::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['approved', 'received', 'completed'])
                ->select(
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(total_amount) as total'),
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->toArray();

            return [
                'total_orders' => array_sum(array_column($trends, 'order_count')),
                'total_spend' => array_sum(array_column($trends, 'total')),
                'monthly_trends' => $trends,
            ];
        });
    }

    public function getSupplierPerformance(?int $supplierId = null): array
    {
        $key = $supplierId ? "report.suppliers.performance.{$supplierId}" : 'report.suppliers.performance';
        return Cache::remember($key, 3600, function () use ($supplierId) {
            $query = SupplierPerformance::select(
                'supplier_id',
                'total_orders',
                DB::raw('on_time_orders as on_time_deliveries'),
                DB::raw('late_orders as late_deliveries'),
                DB::raw('avg_lead_time_days as avg_delay_days'),
                'on_time_rate',
            );

            if ($supplierId) {
                $query->where('supplier_id', $supplierId);
            }

            $performances = $query->with('supplier:id,name,email,phone')
                ->take(1000)->get()
                ->map(fn($p) => [
                    'supplier_id' => $p->supplier_id,
                    'supplier_name' => $p->supplier?->name,
                    'total_orders' => (int) $p->total_orders,
                    'on_time_deliveries' => (int) $p->on_time_deliveries,
                    'late_deliveries' => (int) $p->late_deliveries,
                    'on_time_rate' => (float) $p->on_time_rate,
                    'avg_delay_days' => round((float) ($p->avg_delay_days ?? 0), 1),
                ])->toArray();

            return ['suppliers' => $performances];
        });
    }

    public function getOnTimeDeliveries(?int $supplierId = null): array
    {
        $key = $supplierId ? "report.suppliers.ontime.{$supplierId}" : 'report.suppliers.ontime';
        return Cache::remember($key, 3600, function () use ($supplierId) {
            $query = GoodsReceipt::whereHas('purchaseOrder', fn($q) => $q->where('status', 'received'))
                ->where('status', 'received')
                ->with('purchaseOrder.supplier:id,name');

            if ($supplierId) {
                $query->whereHas('purchaseOrder', fn($q) => $q->where('supplier_id', $supplierId));
            }

            return $query->take(1000)->get()
                ->filter(fn($r) => $r->created_at->diffInDays($r->received_date ?? $r->created_at) <= 3)
                ->values()
                ->toArray();
        });
    }

    public function getLateDeliveries(?int $supplierId = null): array
    {
        $key = $supplierId ? "report.suppliers.late.{$supplierId}" : 'report.suppliers.late';
        return Cache::remember($key, 3600, function () use ($supplierId) {
            $query = GoodsReceipt::whereHas('purchaseOrder', fn($q) => $q->where('status', 'received'))
                ->where('status', 'received')
                ->with('purchaseOrder.supplier:id,name');

            if ($supplierId) {
                $query->whereHas('purchaseOrder', fn($q) => $q->where('supplier_id', $supplierId));
            }

            return $query->take(1000)->get()
                ->filter(fn($r) => $r->created_at->diffInDays($r->received_date ?? $r->created_at) > 3)
                ->values()
                ->toArray();
        });
    }

    public function getSupplierProfitability(int $supplierId, string $startDate, string $endDate): array
    {
        $key = "report.suppliers.profitability.{$supplierId}.{$startDate}.{$endDate}";
        return Cache::remember($key, 3600, function () use ($supplierId, $startDate, $endDate) {
            $purchaseTotal = PurchaseOrder::where('supplier_id', $supplierId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['received', 'completed'])
                ->sum('total_amount');

            $supplier = Supplier::find($supplierId, ['id', 'name']);

            return [
                'supplier_id' => $supplierId,
                'supplier_name' => $supplier?->name,
                'total_purchases' => (float) $purchaseTotal,
            ];
        });
    }

    public function getPriceChanges(int $supplierId): array
    {
        $key = "report.suppliers.price_changes.{$supplierId}";
        return Cache::remember($key, 3600, function () use ($supplierId) {
            return SupplierPriceHistory::where('supplier_id', $supplierId)
                ->with('product:id,name,sku')
                ->orderByDesc('changed_at')
                ->limit(50)
                ->get()
                ->map(fn($h) => [
                    'id' => $h->id,
                    'product_name' => $h->product?->name,
                    'previous_price' => (float) $h->previous_price,
                    'new_price' => (float) $h->price,
                    'change_percent' => $h->previous_price > 0 ? round((($h->price - $h->previous_price) / $h->previous_price) * 100, 2) : 0,
                    'changed_at' => $h->changed_at,
                ])->toArray();
        });
    }

    public function getTopSuppliers(int $limit = 20): array
    {
        return Cache::remember("report.suppliers.top.{$limit}", 3600, function () use ($limit) {
            return Supplier::withCount(['purchaseOrders as total_spend' => fn($q) => $q
                ->whereIn('status', ['approved', 'received', 'completed'])
                ->select(DB::raw('COALESCE(SUM(total_amount), 0)'))])
                ->withCount('purchaseOrders as order_count')
                ->orderByDesc('total_spend')
                ->limit($limit)
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'email' => $s->email,
                    'phone' => $s->phone,
                    'order_count' => $s->order_count,
                    'total_spend' => (float) $s->total_spend,
                ])->toArray();
        });
    }

    public function getLeadTimeAnalysis(?int $supplierId = null): array
    {
        $key = $supplierId ? "report.suppliers.lead_time.{$supplierId}" : 'report.suppliers.lead_time';
        return Cache::remember($key, 3600, function () use ($supplierId) {
            $query = DB::table('purchase_orders as po')
                ->join('goods_receipts as gr', 'gr.purchase_order_id', '=', 'po.id')
                ->select(
                    'po.supplier_id',
                    DB::raw('AVG(DATEDIFF(gr.received_date, po.created_at)) as avg_lead_time_days'),
                    DB::raw('MIN(DATEDIFF(gr.received_date, po.created_at)) as min_lead_time_days'),
                    DB::raw('MAX(DATEDIFF(gr.received_date, po.created_at)) as max_lead_time_days'),
                    DB::raw('COUNT(*) as total_receipts'),
                )
                ->whereNotNull('gr.received_date')
                ->where('po.status', 'received');

            if ($supplierId) {
                $query->where('po.supplier_id', $supplierId);
            }

            $results = $query->groupBy('po.supplier_id')->take(1000)->get();

            $supplierIds = $results->pluck('supplier_id');
            $suppliers = Supplier::whereIn('id', $supplierIds)->pluck('name', 'id');

            return $results->map(fn($r) => [
                'supplier_id' => $r->supplier_id,
                'supplier_name' => $suppliers[$r->supplier_id] ?? 'Unknown',
                'avg_lead_time_days' => round((float) $r->avg_lead_time_days, 1),
                'min_lead_time_days' => (int) $r->min_lead_time_days,
                'max_lead_time_days' => (int) $r->max_lead_time_days,
                'total_receipts' => (int) $r->total_receipts,
            ])->toArray();
        });
    }

    public function invalidateCache(): void
    {
        Cache::forget('report.suppliers.purchase_trends');
        Cache::forget('report.suppliers.performance');
        Cache::forget('report.suppliers.ontime');
        Cache::forget('report.suppliers.late');
        Cache::forget('report.suppliers.profitability');
        Cache::forget('report.suppliers.price_changes');
        Cache::forget('report.suppliers.top');
        Cache::forget('report.suppliers.lead_time');
    }
}
