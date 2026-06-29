<?php

namespace App\Services;

use App\Models\Invoice;

class ReceiptService
{
    public function __construct(
        protected SettingsService $settings
    ) {}

    public function getReceiptData(Invoice $invoice): array
    {
        $invoice->load(['customer', 'items.product', 'payments', 'creator']);

        return [
            'invoice' => $invoice,
            'business' => $this->getBusinessInfo(),
            'is_thermal' => true,
        ];
    }

    public function getInvoicePrintData(Invoice $invoice): array
    {
        $invoice->load(['customer', 'items.product', 'items.unit', 'payments', 'creator', 'approver']);

        return [
            'invoice' => $invoice,
            'business' => $this->getBusinessInfo(),
            'is_thermal' => false,
        ];
    }

    protected function getBusinessInfo(): array
    {
        return [
            'name' => $this->settings->get('business_name', config('app.name')),
            'address' => $this->settings->get('business_address', ''),
            'phone' => $this->settings->get('business_phone', ''),
            'email' => $this->settings->get('business_email', ''),
        ];
    }
}
