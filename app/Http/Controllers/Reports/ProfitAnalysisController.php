<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\ProfitAnalysisService;
use App\Services\ExportService;
use App\Models\ReportLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfitAnalysisController extends Controller
{
    public function __construct(
        protected ProfitAnalysisService $profitService,
        protected ExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $grossProfit = $this->profitService->getGrossProfit($startDate, $endDate);
        $netProfit = $this->profitService->getNetProfit($startDate, $endDate);
        $margin = $this->profitService->getProfitMargin($startDate, $endDate);
        $productProfitability = $this->profitService->getProductProfitability($startDate, $endDate);
        $categoryProfitability = $this->profitService->getCategoryProfitability($startDate, $endDate);
        $topMargin = $this->profitService->getTopMarginProducts();
        $lowMargin = $this->profitService->getLowMarginProducts();

        ReportLog::create(['type' => 'dashboard_viewed', 'report_name' => 'Profit Analysis', 'user_id' => auth()->id(), 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);

        return view('reports.profit.index', compact(
            'grossProfit', 'netProfit', 'margin', 'productProfitability',
            'categoryProfitability', 'topMargin', 'lowMargin', 'startDate', 'endDate'
        ));
    }
}
