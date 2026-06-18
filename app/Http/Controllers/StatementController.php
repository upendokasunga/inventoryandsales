<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\StatementService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatementController extends Controller
{
    public function __construct(
        protected StatementService $statementService
    ) {}

    public function index(Request $request): View
    {
        $customers = Customer::active()->pluck('name', 'id');
        $selectedCustomer = null;
        $statement = null;

        if ($request->filled('customer_id')) {
            $customer = Customer::with('group')->findOrFail($request->customer_id);
            $selectedCustomer = $customer;

            $statement = $this->statementService->generateStatementData(
                $customer,
                $request->from,
                $request->to
            );
        }

        return view('customers.statement', compact(
            'customers', 'selectedCustomer', 'statement'
        ));
    }

    public function pdf(Request $request, Customer $customer): View
    {
        $statement = $this->statementService->generateStatementData(
            $customer,
            $request->from,
            $request->to
        );

        return view('customers.statement-pdf', compact('statement'));
    }
}
