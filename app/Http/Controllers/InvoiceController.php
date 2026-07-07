<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Models\Invoice;
use App\Services\BarcodeService;
use App\Services\CentralApprovalService;
use App\Services\InvoiceService;
use App\Services\PrintDocumentService;
use App\Services\ReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService,
        protected ReceiptService $receiptService,
        protected BarcodeService $barcodeService,
        protected CentralApprovalService $centralApproval,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['tab', 'status', 'payment_status', 'customer_id', 'date_from', 'date_to', 'search']);
        $tab = $request->get('tab', 'all');
        $invoices = $this->invoiceService->getAllPaginated(20, $filters);
        $stats = $this->invoiceService->getStats();
        $customers = \App\Models\Customer::orderBy('name')->pluck('name', 'id');
        return view('invoices.index', compact('invoices', 'stats', 'tab', 'customers'));
    }

    public function create(): View
    {
        return view('invoices.create');
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $invoice = $this->invoiceService->create($request->validated());

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['customer', 'items.product', 'items.subProduct', 'items.store', 'items.unit', 'payments', 'creator', 'approver']);
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice): View
    {
        $invoice->load(['customer', 'items.product', 'items.subProduct', 'items.store', 'items.unit']);
        return view('invoices.edit', compact('invoice'));
    }

    public function update(StoreInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->invoiceService->update($invoice, $request->validated());

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function convertToProforma(Invoice $invoice): RedirectResponse
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be converted to proforma.');
        }

        $invoice->update(['status' => 'proforma']);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice converted to proforma successfully.');
    }

    public function revertProformaToDraft(Invoice $invoice): RedirectResponse
    {
        if ($invoice->status !== 'proforma') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only proforma invoices can be reverted to draft.');
        }

        $invoice->update(['status' => 'draft']);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Proforma reverted to draft successfully.');
    }

    public function approve(Invoice $invoice): RedirectResponse
    {
        try {
            if (in_array($invoice->status, ['draft', 'proforma'])) {
                $this->centralApproval->submit($invoice);
            }
            $this->centralApproval->approve($invoice);
            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice approved successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', $e->getMessage());
        }
    }

    public function print(Invoice $invoice): View
    {
        $data = $this->receiptService->getInvoicePrintData($invoice);
        $data['barcodeSvg'] = $this->barcodeService->getBarcodeSvg($invoice->invoice_number);
        return view('invoices.print', $data);
    }

    public function pdf(Invoice $invoice): \Illuminate\Http\Response
    {
        $invoice->load(['customer', 'items.product', 'items.unit', 'payments', 'creator', 'approver']);
        $data = app(PrintDocumentService::class)->getLetterheadData();
        $data['invoice'] = $invoice;
        return app(PrintDocumentService::class)->streamPdf('print.invoice', $data, "invoice-{$invoice->invoice_number}.pdf");
    }

    public function receipt(Invoice $invoice): View
    {
        $data = $this->receiptService->getReceiptData($invoice);
        $data['barcodeSvg'] = $this->barcodeService->getBarcodeSvg($invoice->invoice_number);
        return view('invoices.receipt', $data);
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->invoiceService->delete($invoice);
        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }
}
