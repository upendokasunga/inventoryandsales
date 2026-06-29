<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\ProcurementReportService;
use App\Services\ExportService;
use App\Models\ReportLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcurementReportController extends Controller
{
    public function __construct(
        protected ProcurementReportService $procurementReportService,
        protected ExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $monthlySpend = $this->procurementReportService->getMonthlySpend($year, $month);
        $pendingApprovals = $this->procurementReportService->getPendingApprovals();
        $orderAnalysis = $this->procurementReportService->getPurchaseOrderAnalysis($startDate, $endDate);
        $trends = $this->procurementReportService->getPurchaseTrends($startDate, $endDate);

        ReportLog::create(['type' => 'dashboard_viewed', 'report_name' => 'Procurement Report', 'user_id' => auth()->id(), 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);

        return view('reports.procurement.index', compact('monthlySpend', 'pendingApprovals', 'orderAnalysis', 'trends', 'year', 'month', 'startDate', 'endDate'));
    }
}
