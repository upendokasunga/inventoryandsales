<?php

namespace App\Jobs;

use App\Services\TaxReportService;
use App\Models\ScheduledReport;
use App\Models\ReportLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateTaxReportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ScheduledReport $scheduledReport,
        public string $startDate,
        public string $endDate,
    ) {}

    public function handle(TaxReportService $taxReportService): void
    {
        try {
            $taxReportService->getVatSummary($this->startDate, $this->endDate);

            ReportLog::create([
                'type' => 'scheduled',
                'report_name' => $this->scheduledReport->name,
                'format' => implode(',', $this->scheduledReport->format),
                'meta' => ['start_date' => $this->startDate, 'end_date' => $this->endDate],
                'user_id' => $this->scheduledReport->created_by,
                'ip_address' => 'queue',
                'user_agent' => 'Job',
            ]);
        } catch (\Throwable $e) {
            Log::error('GenerateTaxReportJob failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
