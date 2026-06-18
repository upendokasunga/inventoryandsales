<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseOrder\StorePurchaseOrderRequest;
use App\Http\Requests\PurchaseOrder\UpdatePurchaseOrderRequest;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PurchaseApprovalService;
use App\Services\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected PurchaseOrderService $orderService,
        protected PurchaseApprovalService $approvalService,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'supplier_id', 'date_from', 'date_to', 'search']);
        $orders = $this->orderService->getAllPaginated(20, $filters);
        $stats = $this->orderService->getStats();
        $suppliers = Supplier::where('is_active', true)->pluck('name', 'id');

        return view('purchasing.orders.index', compact('orders', 'stats', 'suppliers'));
    }

    public function create(): View
    {
        $suppliers = Supplier::where('is_active', true)->pluck('name', 'id');
        $products = Product::active()->pluck('name', 'id');
        return view('purchasing.orders.create', compact('suppliers', 'products'));
    }

    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('items');
        $items = $request->input('items', []);

        $this->orderService->create($data, $items);

        return redirect()->route('purchasing.orders.index')
            ->with('success', 'Purchase order created.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'items.product', 'creator', 'approver']);
        return view('purchasing.orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load('items');
        $suppliers = Supplier::where('is_active', true)->pluck('name', 'id');
        $products = Product::active()->pluck('name', 'id');
        return view('purchasing.orders.edit', compact('purchaseOrder', 'suppliers', 'products'));
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $data = $request->safe()->except('items');
        $items = $request->input('items', []);

        $this->orderService->update($purchaseOrder, $data, $items);

        return redirect()->route('purchasing.orders.show', $purchaseOrder)
            ->with('success', 'Purchase order updated.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->orderService->delete($purchaseOrder);

        return redirect()->route('purchasing.orders.index')
            ->with('success', 'Purchase order deleted.');
    }

    public function submitForApproval(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $this->approvalService->submitForApproval($purchaseOrder);
            return redirect()->route('purchasing.orders.show', $purchaseOrder)
                ->with('success', 'Order submitted for approval.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $this->approvalService->approve($purchaseOrder);
            return redirect()->route('purchasing.orders.show', $purchaseOrder)
                ->with('success', 'Order approved.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $this->approvalService->reject($purchaseOrder);
            return redirect()->route('purchasing.orders.show', $purchaseOrder)
                ->with('success', 'Order returned to draft.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function send(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $this->approvalService->send($purchaseOrder);
            return redirect()->route('purchasing.orders.show', $purchaseOrder)
                ->with('success', 'Order sent to supplier.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $this->approvalService->cancel($purchaseOrder);
            return redirect()->route('purchasing.orders.show', $purchaseOrder)
                ->with('success', 'Order cancelled.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
