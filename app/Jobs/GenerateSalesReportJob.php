<?php

namespace App\Jobs;

use App\Services\SalesReportService;
use App\Services\ExportService;
use App\Models\ScheduledReport;
use App\Models\ReportLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateSalesReportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ScheduledReport $scheduledReport,
        public string $startDate,
        public string $endDate,
    ) {}

    public function handle(SalesReportService $salesReportService, ExportService $exportService): void
    {
        try {
            $data = ['summary' => $salesReportService->getCustomRange($this->startDate, $this->endDate)];

            foreach ($this->scheduledReport->format as $format) {
                match ($format) {
                    'pdf' => $exportService->generatePdf('reports.sales.pdf', $data, "sales-report-{$this->startDate}.pdf"),
                    default => null,
                };
            }

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
            Log::error('GenerateSalesReportJob failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
