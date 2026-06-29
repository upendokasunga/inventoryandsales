<?php

namespace App\Services;

use App\Models\ReportLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    public function generatePdf(string $view, array $data, string $filename): DomPDF
    {
        $pdf = Pdf::loadView($view, $data);

        return $pdf;
    }

    public function generateExcel(array $headers, array $rows, string $filename): void
    {
        $writer = new Writer();
        $writer->openToBrowser("{$filename}.xlsx");
        $writer->addRow(Row::fromValues($headers));

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();
    }

    public function generateCsv(array $headers, array $rows, string $filename): void
    {
        $response = new StreamedResponse(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}.csv\"");

        $response->send();
    }

    public function logExport(string $type, string $reportName, string $format): void
    {
        ReportLog::create([
            'type' => $type,
            'report_name' => $reportName,
            'format' => $format,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
