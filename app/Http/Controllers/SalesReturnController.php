<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalesReturn\StoreSalesReturnRequest;
use App\Models\SalesReturn;
use App\Services\SalesReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesReturnController extends Controller
{
    public function __construct(
        protected SalesReturnService $salesReturnService,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'customer_id', 'date_from', 'date_to']);
        $returns = $this->salesReturnService->getAllPaginated(20, $filters);
        $stats = $this->salesReturnService->getStats();
        return view('sales-returns.index', compact('returns', 'stats'));
    }

    public function create(): View
    {
        return view('sales-returns.create');
    }

    public function store(StoreSalesReturnRequest $request): RedirectResponse
    {
        $return = $this->salesReturnService->create($request->validated());

        return redirect()->route('sales-returns.show', $return)
            ->with('success', 'Sales return created successfully.');
    }

    public function show(SalesReturn $salesReturn): View
    {
        $salesReturn->load(['customer', 'items.product', 'items.unit', 'invoice', 'creator', 'approver', 'creditNote']);
        return view('sales-returns.show', compact('salesReturn'));
    }

    public function approve(SalesReturn $salesReturn): RedirectResponse
    {
        try {
            $this->salesReturnService->approve($salesReturn);
            return redirect()->route('sales-returns.show', $salesReturn)
                ->with('success', 'Sales return approved. Credit note generated.');
        } catch (\Exception $e) {
            return redirect()->route('sales-returns.show', $salesReturn)
                ->with('error', $e->getMessage());
        }
    }

    public function reject(SalesReturn $salesReturn): RedirectResponse
    {
        $this->salesReturnService->reject($salesReturn);
        return redirect()->route('sales-returns.show', $salesReturn)
            ->with('success', 'Sales return rejected.');
    }

    public function complete(SalesReturn $salesReturn): RedirectResponse
    {
        $this->salesReturnService->complete($salesReturn);
        return redirect()->route('sales-returns.show', $salesReturn)
            ->with('success', 'Sales return completed.');
    }
}
