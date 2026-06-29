<?php

use App\Http\Controllers\Reports\AnalyticsDashboardController;
use App\Http\Controllers\Reports\CustomerReportController;
use App\Http\Controllers\Reports\InventoryReportController;
use App\Http\Controllers\Reports\KpiDashboardController;
use App\Http\Controllers\Reports\PaymentReportController;
use App\Http\Controllers\Reports\ProcurementReportController;
use App\Http\Controllers\Reports\ProfitAnalysisController;
use App\Http\Controllers\Reports\SalesReportController;
use App\Http\Controllers\Reports\ScheduledReportController;
use App\Http\Controllers\Reports\SupplierReportController;
use App\Http\Controllers\Reports\TaxReportController;

Route::middleware(['auth', 'verified'])->group(function () {

    // --- Executive Dashboard ---
    Route::middleware('menu.access:can_view')->group(function () {
        Route::get('reports/analytics', [AnalyticsDashboardController::class, 'index'])->name('reports.analytics');
    });

    // --- KPI Dashboard ---
    Route::middleware('menu.access:can_view')->group(function () {
        Route::get('reports/kpi', [KpiDashboardController::class, 'index'])->name('reports.kpi');
    });

    // --- Sales Reports ---
    Route::middleware('menu.access:can_view')->group(function () {
        Route::get('reports/sales', [SalesReportController::class, 'index'])->name('reports.sales');
        Route::get('reports/sales/pdf', [SalesReportController::class, 'exportPdf'])->name('reports.sales.pdf');
        Route::get('reports/sales/excel', [SalesReportController::class, 'exportExcel'])->name('reports.sales.excel');
        Route::get('reports/sales/csv', [SalesReportController::class, 'exportCsv'])->name('reports.sales.csv');
    });

    // --- Profit Analysis ---
    Route::middleware('menu.access:can_view')->group(function () {
        Route::get('reports/profit', [ProfitAnalysisController::class, 'index'])->name('reports.profit');
    });

    // --- Inventory Reports ---
    Route::middleware('menu.access:can_view')->group(function () {
        Route::get('reports/inventory', [InventoryReportController::class, 'index'])->name('reports.inventory');
        Route::get('reports/inventory/movement', [InventoryReportController::class, 'movement'])->name('reports.inventory.movement');
    });

    // --- Customer Reports ---
    Route::middleware('menu.access:can_view')->group(function () {
        Route::get('reports/customers', [CustomerReportController::class, 'index'])->name('reports.customers');
        Route::get('reports/customers/statement/{customer}', [CustomerReportController::class, 'statement'])->name('reports.customers.statement');
    });

    // --- Supplier Reports ---
    Route::middleware('menu.access:can_view')->group(function () {
        Route::get('reports/suppliers', [SupplierReportController::class, 'index'])->name('reports.suppliers');
    });

    // --- Procurement Reports ---
    Route::middleware('menu.access:can_view')->group(function () {
        Route::get('reports/procurement', [ProcurementReportController::class, 'index'])->name('reports.procurement');
    });

    // --- Tax Reports ---
    Route::middleware('menu.access:can_view')->group(function () {
        Route::get('reports/tax', [TaxReportController::class, 'index'])->name('reports.tax');
        Route::get('reports/tax/pdf', [TaxReportController::class, 'exportPdf'])->name('reports.tax.pdf');
    });

    // --- Payment Reports ---
    Route::middleware('menu.access:can_view')->group(function () {
        Route::get('reports/payments', [PaymentReportController::class, 'index'])->name('reports.payments');
    });

    // --- Scheduled Reports ---
    Route::middleware('menu.access:can_view')->group(function () {
        Route::get('reports/scheduled', [ScheduledReportController::class, 'index'])->name('reports.scheduled.index');
    });
    Route::middleware('menu.access:can_create')->group(function () {
        Route::post('reports/scheduled', [ScheduledReportController::class, 'store'])->name('reports.scheduled.store');
        Route::post('reports/scheduled/{id}/trigger', [ScheduledReportController::class, 'trigger'])->name('reports.scheduled.trigger');
    });
    Route::middleware('menu.access:can_delete')->group(function () {
        Route::delete('reports/scheduled/{id}', [ScheduledReportController::class, 'destroy'])->name('reports.scheduled.destroy');
    });
});
