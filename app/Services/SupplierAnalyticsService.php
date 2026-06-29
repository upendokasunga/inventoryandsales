<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\SupplierPerformance;
use App\Models\SupplierPriceHistory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SupplierAnalyticsService
{
    protected function dateDiffSql(string $date1, string $date2): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return "julianday({$date1}) - julianday({$date2})";
        }

        return "DATEDIFF({$date1}, {$date2})";
    }

    protected function dateFormatSql(): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return "strftime('%Y-%m', order_date)";
        }

        return "DATE_FORMAT(order_date, '%Y-%m')";
    }

    public function getDashboardStats(): array
    {
        return Cache::remember('purchasing.analytics.dashboard', 3600, function () {
            $completedPOs = PurchaseOrder::whereIn('status', ['completed', 'partially_received']);

            $diffSql = $this->dateDiffSql('COALESCE(updated_at, created_at)', 'order_date');
            $avgLeadTime = $completedPOs->whereNotNull('expected_date')
                ->selectRaw("AVG({$diffSql}) as avg_lead")
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
        return Cache::remember('purchasing.analytics.supplier_rankings', 3600, function () {
            $diffSql = $this->dateDiffSql('COALESCE(updated_at, created_at)', 'order_date');

            return PurchaseOrder::select(
                'supplier_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as total_spent'),
                DB::raw("AVG({$diffSql}) as avg_lead_days")
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
        $formatSql = $this->dateFormatSql();

        return Cache::remember("purchasing.analytics.trends.{$months}", 3600, function () use ($start, $formatSql) {
            return PurchaseOrder::select(
                DB::raw("{$formatSql} as month"),
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

    public function recalculatePerformance(): void
    {
        $orders = PurchaseOrder::whereIn('status', ['completed', 'partially_received'])
            ->get()
            ->groupBy('supplier_id');

        foreach ($orders as $supplierId => $supplierOrders) {
            $totalOrders = $supplierOrders->count();
            $onTime = 0;
            $late = 0;
            $totalLeadDays = 0;

            foreach ($supplierOrders as $order) {
                $leadDays = $order->created_at->diffInDays($order->updated_at);
                $totalLeadDays += $leadDays;

                if ($order->expected_date && $order->updated_at->startOfDay() <= $order->expected_date) {
                    $onTime++;
                } else {
                    $late++;
                }
            }

            SupplierPerformance::updateOrCreate(
                ['supplier_id' => $supplierId],
                [
                    'total_orders' => $totalOrders,
                    'on_time_orders' => $onTime,
                    'late_orders' => $late,
                    'on_time_rate' => $totalOrders > 0 ? round(($onTime / $totalOrders) * 100, 2) : 0,
                    'avg_lead_time_days' => $totalOrders > 0 ? round($totalLeadDays / $totalOrders, 2) : 0,
                    'total_purchase_value' => $supplierOrders->sum('total'),
                    'order_accuracy_rate' => 0,
                    'calculated_at' => now(),
                ]
            );
        }

        Cache::forget('purchasing.analytics.dashboard');
        Cache::forget('purchasing.analytics.supplier_rankings');
    }

    public function invalidateCache(): void
    {
        Cache::forget('purchasing.analytics.dashboard');
        Cache::forget('purchasing.analytics.supplier_rankings');
    }
}
