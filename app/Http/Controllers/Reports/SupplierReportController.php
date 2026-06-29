<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\SupplierReportService;
use App\Services\ExportService;
use App\Models\ReportLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierReportController extends Controller
{
    public function __construct(
        protected SupplierReportService $supplierReportService,
        protected ExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $purchaseTrends = $this->supplierReportService->getPurchaseTrends($startDate, $endDate);
        $performance = $this->supplierReportService->getSupplierPerformance();
        $topSuppliers = $this->supplierReportService->getTopSuppliers();
        $leadTime = $this->supplierReportService->getLeadTimeAnalysis();

        ReportLog::create(['type' => 'dashboard_viewed', 'report_name' => 'Supplier Report', 'user_id' => auth()->id(), 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);

        return view('reports.suppliers.index', compact('purchaseTrends', 'performance', 'topSuppliers', 'leadTime'));
    }
}
