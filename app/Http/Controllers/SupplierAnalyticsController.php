<?php

namespace App\Http\Controllers;

use App\Models\SupplierPerformance;
use App\Services\SupplierAnalyticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupplierAnalyticsController extends Controller
{
    public function __construct(
        protected SupplierAnalyticsService $analyticsService
    ) {}

    public function index(): View
    {
        $stats = $this->analyticsService->getDashboardStats();
        $rankings = $this->analyticsService->getSupplierRankings();
        $trends = $this->analyticsService->getPurchaseTrends();
        $performances = SupplierPerformance::pluck(
            'quality_rate', 'supplier_id'
        )->toArray();

        $onTimeRates = SupplierPerformance::pluck('on_time_rate', 'supplier_id')->toArray();
        $accuracyRates = SupplierPerformance::pluck('order_accuracy_rate', 'supplier_id')->toArray();

        $performances = [];
        foreach ($rankings as $ranking) {
            $sid = $ranking['supplier_id'];
            $performances[$sid] = [
                'on_time_rate' => $onTimeRates[$sid] ?? 0,
                'order_accuracy_rate' => $accuracyRates[$sid] ?? 0,
                'quality_rate' => SupplierPerformance::where('supplier_id', $sid)->value('quality_rate') ?? 0,
            ];
        }

        return view('purchasing.analytics.index', compact('stats', 'rankings', 'trends', 'performances'));
    }

    public function recalculate(): RedirectResponse
    {
        $this->analyticsService->recalculatePerformance();

        return redirect()->route('purchasing.analytics')
            ->with('success', 'Supplier performance recalculated successfully.');
    }
}
