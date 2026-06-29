<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\SalesReportService;
use App\Services\ExportService;
use App\Models\ReportLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesReportController extends Controller
{
    public function __construct(
        protected SalesReportService $salesReportService,
        protected ExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $filters = $request->only(['customer_id', 'payment_method', 'created_by']);

        $summary = $this->salesReportService->getCustomRange($startDate, $endDate, $filters);
        $topProducts = $this->salesReportService->getTopProducts($startDate, $endDate);
        $topCustomers = $this->salesReportService->getTopCustomers($startDate, $endDate);
        $paymentMethods = $this->salesReportService->getPaymentMethodBreakdown($startDate, $endDate);

        $this->logReportView('Sales Report');

        return view('reports.sales.index', compact(
            'summary', 'topProducts', 'topCustomers', 'paymentMethods', 'startDate', 'endDate'
        ));
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getExportData($request);
        $pdf = $this->exportService->generatePdf('reports.sales.pdf', $data, 'sales-report.pdf');
        $this->exportService->logExport('report_generated', 'Sales Report', 'pdf');
        return $pdf->download('sales-report.pdf');
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getExportData($request);
        $this->exportService->logExport('export', 'Sales Report', 'xlsx');
        return $this->exportService->generateExcel(
            ['Date', 'Invoice #', 'Customer', 'Subtotal', 'Tax', 'Discount', 'Total', 'Status'],
            $data['rows'] ?? [],
            'sales-report.xlsx'
        );
    }

    public function exportCsv(Request $request)
    {
        $data = $this->getExportData($request);
        $this->exportService->logExport('export', 'Sales Report', 'csv');
        return $this->exportService->generateCsv(
            ['Date', 'Invoice #', 'Customer', 'Subtotal', 'Tax', 'Discount', 'Total', 'Status'],
            $data['rows'] ?? [],
            'sales-report.csv'
        );
    }

    protected function getExportData(Request $request): array
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $summary = $this->salesReportService->getCustomRange($startDate, $endDate);
        return compact('summary', 'startDate', 'endDate');
    }

    protected function logReportView(string $name): void
    {
        try {
            ReportLog::create([
                'type' => 'dashboard_viewed',
                'report_name' => $name,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable) {}
    }
}
