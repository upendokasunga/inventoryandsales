<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Services\CreditService;
use App\Services\CustomerAnalyticsService;
use App\Services\CustomerService;
use App\Services\StatementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService,
        protected CreditService $creditService,
        protected StatementService $statementService,
        protected CustomerAnalyticsService $analyticsService
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'credit_status', 'is_active', 'customer_group_id']);
        $customers = $this->customerService->getAllPaginated(20, $filters);
        $customerGroups = CustomerGroup::active()->pluck('name', 'id');

        return view('customers.index', compact('customers', 'customerGroups'));
    }

    public function create(): View
    {
        $customerGroups = CustomerGroup::active()->pluck('name', 'id');
        return view('customers.create', compact('customerGroups'));
    }

    public function store(StoreCustomerRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $customer = $this->customerService->create($request->validated());

        if ($request->ajax()) {
            return response()->json(['id' => $customer->id, 'name' => $customer->name]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): View
    {
        $customer->load('group', 'creditTransactions.user');
        $creditInfo = $this->creditService->getCachedCredit($customer);

        return view('customers.show', compact('customer', 'creditInfo'));
    }

    public function edit(Customer $customer): View
    {
        $customerGroups = CustomerGroup::active()->pluck('name', 'id');
        return view('customers.edit', compact('customer', 'customerGroups'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->customerService->update($customer, $request->validated());

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $customers = $this->customerService->exportCsv();

        $callback = function () use ($customers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Code', 'Name', 'Email', 'Phone', 'Customer Group',
                'Credit Limit', 'Outstanding Balance', 'Available Credit',
                'Credit Status', 'Payment Terms', 'City', 'Region', 'Country',
                'Contact Person', 'Contact Phone', 'Contact Email',
                'Tax ID', 'Registration No', 'Status',
            ]);

            foreach ($customers as $c) {
                fputcsv($handle, [
                    $c->code, $c->name, $c->email, $c->phone,
                    $c->group?->name ?? '',
                    $c->credit_limit, $c->outstanding_balance, $c->available_credit,
                    $c->credit_status, $c->payment_terms,
                    $c->city, $c->region, $c->country,
                    $c->contact_person, $c->contact_phone, $c->contact_email,
                    $c->tax_id, $c->registration_number,
                    $c->is_active ? 'Active' : 'Inactive',
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customers.csv"',
        ]);
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->customerService->delete($customer);

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function profile(Customer $customer, string $tab = 'overview'): View
    {
        $customer->load('group', 'creditTransactions.user');

        $creditInfo = $this->creditService->getCachedCredit($customer);

        $transactions = match ($tab) {
            'credit' => $customer->creditTransactions()->latest()->paginate(20),
            'statements' => $this->statementService->getStatement($customer),
            default => collect(),
        };

        $auditLogs = match ($tab) {
            'audit-logs' => AuditLog::where('auditable_type', Customer::class)
                ->where('auditable_id', $customer->id)
                ->with('user')
                ->latest()
                ->paginate(20),
            default => collect(),
        };

        return view('customers.profile.index', compact(
            'customer', 'creditInfo', 'tab', 'transactions', 'auditLogs'
        ));
    }
}
