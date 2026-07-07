<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\StorePaymentRequest;
use App\Models\Invoice;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['payment_method', 'customer_id', 'date_from', 'date_to']);
        $payments = $this->paymentService->getAllPaginated(20, $filters);
        $methods = $this->paymentService->getPaymentMethods();
        return view('payments.index', compact('payments', 'methods'));
    }

    public function create(Invoice $invoice): View
    {
        return view('payments.create', compact('invoice'));
    }

    public function store(StorePaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        try {
            $this->paymentService->recordPayment($invoice, $request->validated());
            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Payment recorded successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show(\App\Models\Payment $payment): View
    {
        $payment->load(['invoice.customer', 'customer', 'receiver']);
        return view('payments.show', compact('payment'));
    }

    public function print(\App\Models\Payment $payment): Response
    {
        $payment->load(['invoice', 'customer', 'receiver']);
        $data = app(\App\Services\PrintDocumentService::class)->getLetterheadData();
        $data['payment'] = $payment;
        return app(\App\Services\PrintDocumentService::class)->streamPdf('print.payment-receipt', $data, "receipt-{$payment->uuid}.pdf");
    }
}
