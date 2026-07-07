<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;

class PrintDocumentService
{
    public function __construct(
        protected SettingsService $settings
    ) {}

    public function getBusinessInfo(): array
    {
        return [
            'name' => $this->settings->get('business_name', config('app.name')),
            'address' => $this->settings->get('business_address', ''),
            'phone' => $this->settings->get('business_phone', ''),
            'email' => $this->settings->get('business_email', ''),
            'tin' => $this->settings->get('business_tin', ''),
            'vat' => $this->settings->get('business_vat', ''),
            'logo' => $this->settings->get('business_logo', ''),
            'terms' => $this->settings->get('business_terms', ''),
            'signatory_name' => $this->settings->get('business_signatory_name', ''),
            'signatory_title' => $this->settings->get('business_signatory_title', ''),
        ];
    }

    public function getLetterheadData(): array
    {
        $business = $this->getBusinessInfo();
        return [
            'business' => $business,
            'showLetterhead' => true,
        ];
    }

    public function generatePdf(string $view, array $data, string $filename): DomPDF
    {
        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('a4', 'portrait');
        return $pdf;
    }

    public function streamPdf(string $view, array $data, string $filename): \Illuminate\Http\Response
    {
        return $this->generatePdf($view, $data, $filename)->stream($filename);
    }

    public function downloadPdf(string $view, array $data, string $filename): \Illuminate\Http\Response
    {
        return $this->generatePdf($view, $data, $filename)->download($filename);
    }
}
