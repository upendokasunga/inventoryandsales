<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\KpiService;
use App\Services\ScheduledReportService;
use App\Models\ReportLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KpiDashboardController extends Controller
{
    public function __construct(
        protected KpiService $kpiService,
        protected ScheduledReportService $scheduledReportService,
    ) {}

    public function index(Request $request): View
    {
        $period = $request->get('period', 'daily');
        $kpis = match ($period) {
            'weekly' => $this->kpiService->getWeeklyKpis(),
            'monthly' => $this->kpiService->getMonthlyKpis(),
            'quarterly' => $this->kpiService->getQuarterlyKpis(),
            'annual' => $this->kpiService->getAnnualKpis(),
            default => $this->kpiService->getDailyKpis(),
        };

        ReportLog::create(['type' => 'dashboard_viewed', 'report_name' => 'KPI Dashboard', 'user_id' => auth()->id(), 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);

        return view('reports.kpi-dashboard', compact('kpis', 'period'));
    }
}
