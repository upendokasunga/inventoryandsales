<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\SalesReturn;
use App\Models\CustomerAdvance;
use App\Services\AdvanceService;
use App\Services\BarcodeService;
use App\Services\CentralApprovalService;
use App\Services\InvoiceService;
use App\Services\PrintDocumentService;
use App\Services\ReceiptService;
use App\Services\SalesReturnService;
use App\Support\Approvals;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService,
        protected ReceiptService $receiptService,
        protected BarcodeService $barcodeService,
        protected CentralApprovalService $centralApproval,
        protected SalesReturnService $salesReturnService,
        protected AdvanceService $advanceService,
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
        $customers = \App\Models\Customer::where('is_active', true)->orderBy('name')->get();
        $stores = \App\Models\Warehouse::orderBy('name')->get();
        $paymentAccounts = \App\Models\Account::where('type', 'asset')
            ->whereHas('parent', fn($q) => $q->where('code', 'like', '110%'))
            ->orWhere('code', 'like', '110%')
            ->orderBy('name')
            ->get();
        $costCenters = \App\Models\CostCenter::all();
        $currencies = ['TZS', 'USD', 'EUR'];

        return view('invoices.create', compact(
            'customers', 'stores', 'paymentAccounts', 'costCenters', 'currencies'
        ));
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        if ($request->has('save_draft')) {
            return $this->storeDraft($request);
        }

        $data = $request->validated();
        $data['status'] = 'draft';
        $invoice = $this->invoiceService->create($data);

        if (Approvals::isLevelZero('invoice')) {
            $this->centralApproval->submit($invoice);
        }

        if (!empty($data['customer_advance_id']) && !empty($data['advance_amount'])) {
            try {
                $advance = CustomerAdvance::findOrFail($data['customer_advance_id']);
                $this->advanceService->applyToInvoice($advance, $invoice, (float) $data['advance_amount']);
                Cache::forget('customer.advances.stats');
            } catch (\InvalidArgumentException $e) {
                return redirect()->route('invoices.show', $invoice)
                    ->with('warning', 'Invoice created but advance application failed: ' . $e->getMessage());
            }
        }

        Cache::forget('invoices.stats');

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function storeDraft(StoreInvoiceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = 'draft';
        $data['amount_paid'] = 0;

        $invoice = $this->invoiceService->create($data);

        Cache::forget('invoices.stats');

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Draft invoice saved.');
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
            if ($invoice->status !== 'posted') {
                $this->centralApproval->approve($invoice);
            }
            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice approved and posted successfully.');
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

    public function returnCreate(Invoice $invoice): View
    {
        $invoice->load(['customer', 'items.product', 'items.subProduct', 'items.store', 'items.unit']);
        return view('invoices.return-create', compact('invoice'));
    }

    public function returnStore(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_unit_id' => 'nullable|exists:product_units,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.reason' => 'required|string|in:damaged,wrong_item,expired,customer_dissatisfaction,pricing_error,duplicate_order',
        ]);

        try {
            $data = array_merge($validated, [
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
            ]);

            $return = $this->salesReturnService->create($data);

            return redirect()->route('sales-returns.show', $return)
                ->with('success', 'Return created from invoice successfully.');
        } catch (\Exception $e) {
            return redirect()->route('invoices.return-create', $invoice)
                ->with('error', $e->getMessage());
        }
    }

    public function discountCreate(Invoice $invoice): View
    {
        $invoice->load(['customer', 'items.product', 'items.subProduct', 'items.store', 'items.unit']);
        return view('invoices.discount-create', compact('invoice'));
    }

    public function discountStore(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'discount_amount' => 'required|numeric|min:0|max:' . $invoice->total,
            'reason' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($invoice, $validated) {
            $discount = $validated['discount_amount'];
            $newTotal = max(0, $invoice->total - $discount);

            CreditNote::create([
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'amount' => $discount,
                'notes' => $validated['reason'] ?? 'Discount adjustment',
                'status' => 'issued',
                'issued_date' => now(),
                'created_by' => auth()->id(),
            ]);

            $invoice->update([
                'discount' => $invoice->discount + $discount,
                'total' => $newTotal,
                'balance_due' => max(0, $invoice->balance_due - $discount),
            ]);
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Discount applied successfully.');
    }

    public function creditNotes(Invoice $invoice): View
    {
        $creditNotes = CreditNote::where('invoice_id', $invoice->id)
            ->with(['creator'])
            ->latest()
            ->get();

        $invoice->load(['customer']);
        return view('invoices.credit-notes', compact('invoice', 'creditNotes'));
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->invoiceService->delete($invoice);
        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }
}
