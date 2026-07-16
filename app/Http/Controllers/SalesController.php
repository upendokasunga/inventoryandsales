<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CostCenter;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\CentralApprovalService;
use App\Services\InvoiceService;
use App\Support\Approvals;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService,
        protected CentralApprovalService $centralApproval,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['tab', 'status', 'payment_status', 'customer_id', 'date_from', 'date_to', 'search']);
        $tab = $request->get('tab', 'all');
        $invoices = $this->invoiceService->getAllPaginated(20, $filters);
        $stats = $this->invoiceService->getStats();
        $customers = Customer::orderBy('name')->pluck('name', 'id');
        return view('sales.index', compact('invoices', 'stats', 'tab', 'customers'));
    }

    public function new(): View
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('product_type', 'goods')
            ->orderBy('name')
            ->get();
        $stores = Warehouse::active()->orderBy('name')->get();
        $paymentAccounts = Account::where('type', 'asset')
            ->where('is_active', true)
            ->whereNotNull('parent_id')
            ->get();
        $costCenters = CostCenter::pluck('name', 'id');
        $userStores = auth()->user()->stores()->pluck('id')->toArray();

        return view('sales.new', compact(
            'customers', 'products', 'stores', 'paymentAccounts',
            'costCenters', 'userStores'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateSaleRequest($request);
        $data['status'] = 'proforma';

        $invoice = $this->invoiceService->create($data);

        if (Approvals::isLevelZero('invoice')) {
            $this->centralApproval->submit($invoice);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function storeDraft(Request $request): RedirectResponse
    {
        $data = $this->validateSaleRequest($request);
        $data['status'] = 'proforma';
        $data['amount_paid'] = 0;

        $invoice = $this->invoiceService->create($data);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Draft invoice saved.');
    }

    protected function validateSaleRequest(Request $request): array
    {
        $data = $request->all();

        if (is_string($data['items'] ?? null)) {
            $decoded = json_decode($data['items'], true);
            if (!is_array($decoded)) {
                $decoded = [];
            }
            $request->merge(['items' => $decoded]);
        }

        return $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'currency_code' => 'nullable|string|max:10',
            'exchange_rate' => 'nullable|numeric|min:0',
            'store_id' => 'nullable|exists:warehouses,id',
            'payment_account_id' => 'nullable|exists:accounts,id',
            'amount_paid' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:amount,percentage',
            'tax' => 'nullable|numeric|min:0',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'payment_term_id' => 'nullable|exists:payment_terms,id',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_unit_id' => 'nullable|exists:product_units,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.store_id' => 'nullable|exists:warehouses,id',
        ]);
    }
}
