<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\DashboardAnalyticsService;
use App\Services\KpiService;
use App\Models\ReportLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsDashboardController extends Controller
{
    public function __construct(
        protected DashboardAnalyticsService $dashboardAnalyticsService,
        protected KpiService $kpiService,
    ) {}

    public function index(Request $request): View
    {
        $summary = $this->dashboardAnalyticsService->getSummaryCards();
        $salesTrend = $this->dashboardAnalyticsService->getSalesTrend();
        $profitTrend = $this->dashboardAnalyticsService->getProfitTrend();
        $paymentMethods = $this->dashboardAnalyticsService->getPaymentMethodDistribution(now()->startOfYear(), now());
        $categoryPerformance = $this->dashboardAnalyticsService->getCategoryPerformance(now()->startOfYear(), now());
        $topCustomers = $this->dashboardAnalyticsService->getSummaryCards(); // reuse
        $debtExposure = $this->dashboardAnalyticsService->getDebtExposure();
        $returnTrends = $this->dashboardAnalyticsService->getReturnTrends();

        $dailyKpis = $this->kpiService->getDailyKpis();

        ReportLog::create(['type' => 'dashboard_viewed', 'report_name' => 'Executive Dashboard', 'user_id' => auth()->id(), 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);

        return view('reports.executive-dashboard', compact(
            'summary', 'salesTrend', 'profitTrend', 'paymentMethods',
            'categoryPerformance', 'debtExposure', 'returnTrends', 'dailyKpis'
        ));
    }
}
