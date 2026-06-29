<?php

namespace App\Jobs;

use App\Services\SupplierReportService;
use App\Models\ScheduledReport;
use App\Models\ReportLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateSupplierReportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ScheduledReport $scheduledReport,
    ) {}

    public function handle(SupplierReportService $supplierReportService): void
    {
        try {
            $supplierReportService->getSupplierPerformance();
            $supplierReportService->getLeadTimeAnalysis();

            ReportLog::create([
                'type' => 'scheduled',
                'report_name' => $this->scheduledReport->name,
                'format' => implode(',', $this->scheduledReport->format),
                'user_id' => $this->scheduledReport->created_by,
                'ip_address' => 'queue',
                'user_agent' => 'Job',
            ]);
        } catch (\Throwable $e) {
            Log::error('GenerateSupplierReportJob failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
