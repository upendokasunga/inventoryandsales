<?php

namespace App\Http\Controllers;

use App\Services\SupplierAnalyticsService;
use Illuminate\View\View;

class SupplierAnalyticsController extends Controller
{
    public function __construct(
        protected SupplierAnalyticsService $analyticsService
    ) {}

    public function index(): View
    {
        $stats = $this->analyticsService->getDashboardStats();
        $rankings = $this->analyticsService->getSupplierRankings();
        $trends = $this->analyticsService->getPurchaseTrends();

        return view('purchasing.analytics.index', compact('stats', 'rankings', 'trends'));
    }
}
