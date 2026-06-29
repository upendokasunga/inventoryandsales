<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\CustomerReportService;
use App\Services\ExportService;
use App\Models\ReportLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerReportController extends Controller
{
    public function __construct(
        protected CustomerReportService $customerReportService,
        protected ExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        $outstanding = $this->customerReportService->getOutstandingDebts();
        $creditExposure = $this->customerReportService->getCreditExposure();
        $topCustomers = $this->customerReportService->getTopCustomers();
        $overdue = $this->customerReportService->getOverdueCustomers();

        ReportLog::create(['type' => 'dashboard_viewed', 'report_name' => 'Customer Report', 'user_id' => auth()->id(), 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);

        return view('reports.customers.index', compact('outstanding', 'creditExposure', 'topCustomers', 'overdue'));
    }

    public function statement(Request $request, int $customerId): View
    {
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $data = $this->customerReportService->getCustomerStatement($customerId, $startDate, $endDate);
        return view('reports.customers.statement', compact('data'));
    }
}
