<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseReturn\StorePurchaseReturnRequest;
use App\Models\PurchaseReturn;
use App\Services\PurchaseReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseReturnController extends Controller
{
    public function __construct(
        protected PurchaseReturnService $purchaseReturnService,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'supplier_id', 'date_from', 'date_to']);
        $returns = $this->purchaseReturnService->getAllPaginated(20, $filters);
        $stats = $this->purchaseReturnService->getStats();
        return view('purchase-returns.index', compact('returns', 'stats'));
    }

    public function create(): View
    {
        return view('purchase-returns.create');
    }

    public function store(StorePurchaseReturnRequest $request): RedirectResponse
    {
        $return = $this->purchaseReturnService->create($request->validated());

        return redirect()->route('purchase-returns.show', $return)
            ->with('success', 'Purchase return created successfully.');
    }

    public function show(PurchaseReturn $purchaseReturn): View
    {
        $purchaseReturn->load(['supplier', 'items.product', 'items.unit', 'purchaseOrder', 'creator', 'approver']);
        return view('purchase-returns.show', compact('purchaseReturn'));
    }

    public function approve(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        try {
            $this->purchaseReturnService->approve($purchaseReturn);
            return redirect()->route('purchase-returns.show', $purchaseReturn)
                ->with('success', 'Purchase return approved. Inventory deducted.');
        } catch (\Exception $e) {
            return redirect()->route('purchase-returns.show', $purchaseReturn)
                ->with('error', $e->getMessage());
        }
    }

    public function reject(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        $this->purchaseReturnService->reject($purchaseReturn);
        return redirect()->route('purchase-returns.show', $purchaseReturn)
            ->with('success', 'Purchase return rejected.');
    }

    public function complete(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        $this->purchaseReturnService->complete($purchaseReturn);
        return redirect()->route('purchase-returns.show', $purchaseReturn)
            ->with('success', 'Purchase return completed.');
    }
}
