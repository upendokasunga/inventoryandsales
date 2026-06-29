<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\InventoryReportService;
use App\Services\ExportService;
use App\Models\ReportLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryReportController extends Controller
{
    public function __construct(
        protected InventoryReportService $inventoryReportService,
        protected ExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['product_id', 'warehouse_id', 'category_id']);
        $currentStock = $this->inventoryReportService->getCurrentStockReport($filters);
        $valuation = $this->inventoryReportService->getValuationReport();
        $fastMoving = $this->inventoryReportService->getFastMovingProducts();
        $slowMoving = $this->inventoryReportService->getSlowMovingProducts();
        $deadStock = $this->inventoryReportService->getDeadStock();
        $expiry = $this->inventoryReportService->getExpiryReport();
        $lowStock = $this->inventoryReportService->getLowStockReport();

        ReportLog::create(['type' => 'dashboard_viewed', 'report_name' => 'Inventory Report', 'user_id' => auth()->id(), 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);

        return view('reports.inventory.index', compact(
            'currentStock', 'valuation', 'fastMoving', 'slowMoving',
            'deadStock', 'expiry', 'lowStock'
        ));
    }

    public function movement(Request $request): View
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $movement = $this->inventoryReportService->getStockMovementReport($startDate, $endDate);
        return view('reports.inventory.movement', compact('movement', 'startDate', 'endDate'));
    }
}
