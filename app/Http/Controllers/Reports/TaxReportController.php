<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\TaxReportService;
use App\Services\ExportService;
use App\Models\ReportLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxReportController extends Controller
{
    public function __construct(
        protected TaxReportService $taxReportService,
        protected ExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $salesTax = $this->taxReportService->getSalesTaxReport($startDate, $endDate);
        $purchaseTax = $this->taxReportService->getPurchaseTaxReport($startDate, $endDate);
        $vatSummary = $this->taxReportService->getVatSummary($startDate, $endDate);
        $taxPayable = $this->taxReportService->getTaxPayable($startDate, $endDate);
        $taxCollected = $this->taxReportService->getTaxCollected($startDate, $endDate);

        ReportLog::create(['type' => 'dashboard_viewed', 'report_name' => 'Tax Report', 'user_id' => auth()->id(), 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);

        return view('reports.tax.index', compact('salesTax', 'purchaseTax', 'vatSummary', 'taxPayable', 'taxCollected', 'startDate', 'endDate'));
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $data = [
            'salesTax' => $this->taxReportService->getSalesTaxReport($startDate, $endDate),
            'vatSummary' => $this->taxReportService->getVatSummary($startDate, $endDate),
        ];
        $pdf = $this->exportService->generatePdf('reports.tax.pdf', $data, 'tax-report.pdf');
        $this->exportService->logExport('report_generated', 'Tax Report', 'pdf');
        return $pdf->download('tax-report.pdf');
    }
}
