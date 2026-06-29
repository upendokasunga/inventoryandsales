<?php

namespace App\Services;

use App\Models\ScheduledReport;
use App\Services\SalesReportService;
use App\Services\InventoryReportService;
use App\Services\CustomerReportService;
use App\Services\SupplierReportService;
use App\Services\ProfitAnalysisService;
use App\Services\PaymentReportService;
use App\Services\ProcurementReportService;
use App\Services\TaxReportService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ScheduledReportService
{
    public function schedule(array $data): ScheduledReport
    {
        $report = ScheduledReport::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'frequency' => $data['frequency'],
            'filters' => $data['filters'] ?? [],
            'recipients' => $data['recipients'] ?? [],
            'format' => $data['format'] ?? ['pdf'],
            'next_run_at' => $data['next_run_at'] ?? $this->calculateNextRun($data['frequency']),
            'is_active' => $data['is_active'] ?? true,
            'created_by' => $data['created_by'] ?? auth()->id(),
        ]);

        Cache::forget('scheduled_reports.all');

        return $report;
    }

    public function getAll(): Collection
    {
        return Cache::remember('scheduled_reports.all', 3600, function () {
            return ScheduledReport::with('creator')
                ->orderBy('next_run_at')
                ->get();
        });
    }

    public function getDue(): Collection
    {
        return ScheduledReport::with('creator')
            ->where('is_active', true)
            ->where('next_run_at', '<=', now())
            ->get();
    }

    public function execute(ScheduledReport $report): void
    {
        $report->load('creator');

        $data = match ($report->type) {
            'sales' => app(SalesReportService::class)->getMonthlySales(
                (int) ($report->filters['year'] ?? now()->year),
                (int) ($report->filters['month'] ?? now()->month)
            ),
            'inventory' => app(InventoryReportService::class)->getCurrentStockReport(
                $report->filters ?? []
            ),
            'customer' => app(CustomerReportService::class)->getTopCustomers(
                $report->filters['limit'] ?? 20
            ),
            'supplier' => app(SupplierReportService::class)->getTopSuppliers(
                $report->filters['limit'] ?? 20
            ),
            'profit' => app(ProfitAnalysisService::class)->getGrossProfit(
                $report->filters['start_date'] ?? now()->startOfMonth()->toDateString(),
                $report->filters['end_date'] ?? now()->endOfMonth()->toDateString()
            ),
            'payment' => app(PaymentReportService::class)->getPaymentTrends(
                $report->filters['start_date'] ?? now()->startOfMonth()->toDateString(),
                $report->filters['end_date'] ?? now()->endOfMonth()->toDateString()
            ),
            'procurement' => app(ProcurementReportService::class)->getPurchaseTrends(
                $report->filters['start_date'] ?? now()->startOfMonth()->toDateString(),
                $report->filters['end_date'] ?? now()->endOfMonth()->toDateString()
            ),
            'tax' => app(TaxReportService::class)->getVatSummary(
                (int) ($report->filters['year'] ?? now()->year),
                (int) ($report->filters['quarter'] ?? now()->quarter)
            ),
            default => [],
        };

        $this->updateLastRun($report);
    }

    public function updateLastRun(ScheduledReport $report): void
    {
        $frequency = $report->frequency;

        $report->update([
            'last_run_at' => now(),
            'next_run_at' => $this->calculateNextRun($frequency),
        ]);

        Cache::forget('scheduled_reports.all');
    }

    public function delete(string $id): bool
    {
        $result = ScheduledReport::destroy($id);

        Cache::forget('scheduled_reports.all');

        return (bool) $result;
    }

    protected function calculateNextRun(string $frequency): string
    {
        return (match ($frequency) {
            'hourly' => now()->addHour(),
            'daily' => now()->addDay()->startOfDay(),
            'weekly' => now()->addWeek()->startOfWeek(),
            'monthly' => now()->addMonth()->startOfMonth(),
            'quarterly' => now()->addQuarter()->startOfQuarter(),
            'yearly' => now()->addYear()->startOfYear(),
            default => now()->addDay()->startOfDay(),
        })->toDateTimeString();
    }

    public function invalidateCache(): void
    {
        Cache::forget('scheduled_reports.all');
    }
}
