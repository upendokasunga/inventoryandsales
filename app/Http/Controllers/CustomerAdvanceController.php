<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerAdvance\ApplyAdvanceRequest;
use App\Http\Requests\CustomerAdvance\StoreCustomerAdvanceRequest;
use App\Models\Customer;
use App\Models\CustomerAdvance;
use App\Models\Invoice;
use App\Services\AdvanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerAdvanceController extends Controller
{
    public function __construct(
        protected AdvanceService $advanceService,
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'all');
        $filters = $request->only(['customer_id', 'date_from', 'date_to']);

        $advances = $this->advanceService->getAllPaginated(20, ['tab' => $tab, ...$filters]);
        $stats = $this->advanceService->getStats();
        $customers = Customer::where('is_active', true)->pluck('name', 'id');

        return view('customer-advances.index', compact('advances', 'stats', 'customers', 'tab'));
    }

    public function create(): View
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        return view('customer-advances.create', compact('customers'));
    }

    public function store(StoreCustomerAdvanceRequest $request): RedirectResponse
    {
        $this->advanceService->create($request->validated());

        return redirect()->route('customer-advances.index')
            ->with('success', 'Customer advance recorded successfully.');
    }

    public function show(CustomerAdvance $customerAdvance): View
    {
        $customerAdvance->load(['customer', 'creator', 'applications.invoice', 'applications.appliedBy']);
        return view('customer-advances.show', compact('customerAdvance'));
    }

    public function applyToInvoice(Request $request, CustomerAdvance $customerAdvance): RedirectResponse
    {
        $request->validate([
            'invoice_id' => 'required|exists:sales_invoices,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        try {
            $this->advanceService->applyToInvoice($customerAdvance, $invoice, (float) $request->amount);
            return redirect()->route('customer-advances.show', $customerAdvance)
                ->with('success', 'Advance applied to invoice successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('customer-advances.show', $customerAdvance)
                ->with('error', $e->getMessage());
        }
    }

    public function cancel(CustomerAdvance $customerAdvance): RedirectResponse
    {
        try {
            $this->advanceService->cancel($customerAdvance);
            return redirect()->route('customer-advances.show', $customerAdvance)
                ->with('success', 'Advance cancelled successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('customer-advances.show', $customerAdvance)
                ->with('error', $e->getMessage());
        }
    }
}
