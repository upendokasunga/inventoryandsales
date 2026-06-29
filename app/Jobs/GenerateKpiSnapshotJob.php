<?php

namespace App\Jobs;

use App\Services\KpiService;
use App\Models\KpiSnapshot;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateKpiSnapshotJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $period = 'daily',
    ) {}

    public function handle(KpiService $kpiService): void
    {
        try {
            $kpis = match ($this->period) {
                'weekly' => $kpiService->getWeeklyKpis(),
                'monthly' => $kpiService->getMonthlyKpis(),
                'quarterly' => $kpiService->getQuarterlyKpis(),
                'annual' => $kpiService->getAnnualKpis(),
                default => $kpiService->getDailyKpis(),
            };

            KpiSnapshot::create([
                'period' => $this->period,
                'snapshot_date' => now(),
                'metrics' => $kpis,
            ]);
        } catch (\Throwable $e) {
            Log::error('GenerateKpiSnapshotJob failed: ' . $e->getMessage());
        }
    }
}
