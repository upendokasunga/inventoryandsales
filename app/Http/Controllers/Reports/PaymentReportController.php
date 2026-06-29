<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\PaymentReportService;
use App\Services\ExportService;
use App\Models\ReportLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentReportController extends Controller
{
    public function __construct(
        protected PaymentReportService $paymentReportService,
        protected ExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $cashPayments = $this->paymentReportService->getCashPayments($startDate, $endDate);
        $creditPayments = $this->paymentReportService->getCreditPayments($startDate, $endDate);
        $bankTransfers = $this->paymentReportService->getBankTransfers($startDate, $endDate);
        $mobileMoney = $this->paymentReportService->getMobileMoney($startDate, $endDate);
        $outstandingReceivables = $this->paymentReportService->getOutstandingReceivables();
        $overdueInvoices = $this->paymentReportService->getOverdueInvoices();
        $paymentTrends = $this->paymentReportService->getPaymentTrends($startDate, $endDate);

        ReportLog::create(['type' => 'dashboard_viewed', 'report_name' => 'Payment Report', 'user_id' => auth()->id(), 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);

        return view('reports.payments.index', compact(
            'cashPayments', 'creditPayments', 'bankTransfers', 'mobileMoney',
            'outstandingReceivables', 'overdueInvoices', 'paymentTrends',
            'startDate', 'endDate', 'year', 'month'
        ));
    }
}
