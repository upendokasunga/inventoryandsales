<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\ScheduledReportService;
use App\Models\ReportLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduledReportController extends Controller
{
    public function __construct(
        protected ScheduledReportService $scheduledReportService,
    ) {}

    public function index(): View
    {
        $reports = $this->scheduledReportService->getAll();
        return view('reports.scheduled.index', compact('reports'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:sales,inventory,customer,supplier,tax,payment,kpi',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,annual',
            'recipients' => 'required|array',
            'recipients.*' => 'email',
            'format' => 'required|array',
            'format.*' => 'in:pdf,excel,csv',
            'filters' => 'nullable|array',
        ]);

        $validated['created_by'] = auth()->id();
        $this->scheduledReportService->schedule($validated);

        return redirect()->route('reports.scheduled.index')
            ->with('success', 'Scheduled report created successfully.');
    }

    public function destroy(int $id)
    {
        $this->scheduledReportService->delete($id);
        return redirect()->route('reports.scheduled.index')
            ->with('success', 'Scheduled report deleted.');
    }

    public function trigger(int $id)
    {
        $report = $this->scheduledReportService->getAll()->find($id);
        if ($report) {
            $this->scheduledReportService->execute($report);
            ReportLog::create(['type' => 'scheduled', 'report_name' => $report->name, 'format' => implode(',', $report->format), 'user_id' => auth()->id(), 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);
        }
        return redirect()->route('reports.scheduled.index')
            ->with('success', 'Report triggered for execution.');
    }
}
