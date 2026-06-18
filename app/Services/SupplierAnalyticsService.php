<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\SupplierPriceHistory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SupplierAnalyticsService
{
    public function getDashboardStats(): array
    {
        return Cache::remember('purchasing.analytics.dashboard', 300, function () {
            $completedPOs = PurchaseOrder::whereIn('status', ['completed', 'partially_received']);

            $avgLeadTime = $completedPOs->whereNotNull('expected_date')
                ->selectRaw('AVG(DATEDIFF(COALESCE(updated_at, created_at), order_date)) as avg_lead')
                ->value('avg_lead');

            return [
                'total_pos' => PurchaseOrder::count(),
                'completed_pos' => PurchaseOrder::where('status', 'completed')->count(),
                'pending_pos' => PurchaseOrder::whereIn('status', ['pending_approval', 'approved', 'sent'])->count(),
                'total_spent' => PurchaseOrder::whereIn('status', ['completed', 'partially_received'])->sum('total'),
                'avg_lead_time' => round($avgLeadTime ?? 0, 1),
                'active_suppliers' => PurchaseOrder::distinct('supplier_id')->count('supplier_id'),
            ];
        });
    }

    public function getSupplierRankings(): array
    {
        return Cache::remember('purchasing.analytics.supplier_rankings', 300, function () {
            return PurchaseOrder::select(
                'supplier_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as total_spent'),
                DB::raw('AVG(DATEDIFF(COALESCE(updated_at, created_at), order_date)) as avg_lead_days')
            )
                ->whereIn('status', ['completed', 'partially_received'])
                ->groupBy('supplier_id')
                ->with('supplier')
                ->orderByDesc('total_spent')
                ->get()
                ->toArray();
        });
    }

    public function getPurchaseTrends(int $months = 6): array
    {
        $start = now()->subMonths($months)->startOfMonth();

        return Cache::remember("purchasing.analytics.trends.{$months}", 300, function () use ($start) {
            return PurchaseOrder::select(
                DB::raw("DATE_FORMAT(order_date, '%Y-%m') as month"),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as total_amount')
            )
                ->where('order_date', '>=', $start)
                ->whereIn('status', ['completed', 'partially_received', 'sent'])
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->toArray();
        });
    }

    public function invalidateCache(): void
    {
        Cache::forget('purchasing.analytics.dashboard');
        Cache::forget('purchasing.analytics.supplier_rankings');
    }
}
