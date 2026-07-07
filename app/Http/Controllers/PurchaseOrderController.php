<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseOrder\StorePurchaseOrderRequest;
use App\Http\Requests\PurchaseOrder\UpdatePurchaseOrderRequest;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\CentralApprovalService;
use App\Services\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected PurchaseOrderService $orderService,
        protected CentralApprovalService $centralApproval,
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'all');
        $filters = $request->only(['supplier_id', 'date_from', 'date_to', 'search']);

        if ($tab !== 'all') {
            $filters['status'] = $tab;
        }

        $orders = $this->orderService->getAllPaginated(20, $filters);
        $stats = $this->orderService->getStats();
        $suppliers = Supplier::where('is_active', true)->pluck('name', 'id');

        return view('purchasing.orders.index', compact('orders', 'stats', 'suppliers', 'tab'));
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
        $purchaseOrder->load(['supplier', 'items.product', 'creator', 'approver', 'receipts']);
        return view('purchasing.orders.show', compact('purchaseOrder'));
    }

    public function print(PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->load(['supplier', 'items.product', 'creator', 'approver']);
        $data = app(\App\Services\PrintDocumentService::class)->getLetterheadData();
        $data['purchaseOrder'] = $purchaseOrder;
        return app(\App\Services\PrintDocumentService::class)->streamPdf('print.purchase-order', $data, "po-{$purchaseOrder->po_number}.pdf");
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
            $this->centralApproval->submit($purchaseOrder);
            return redirect()->route('purchasing.orders.show', $purchaseOrder)
                ->with('success', 'Order submitted for approval.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $this->centralApproval->approve($purchaseOrder);
            return redirect()->route('purchasing.orders.show', $purchaseOrder)
                ->with('success', 'Order approved.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $this->centralApproval->reject($purchaseOrder);
            return redirect()->route('purchasing.orders.show', $purchaseOrder)
                ->with('success', 'Order returned to draft.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('purchasing.orders.show', $purchaseOrder)
                ->with('error', $e->getMessage());
        }
    }

    public function send(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status !== 'approved') {
            return redirect()->route('purchasing.orders.show', $purchaseOrder)
                ->with('error', 'Only approved orders can be sent.');
        }
        $purchaseOrder->update(['status' => 'sent']);
        return redirect()->route('purchasing.orders.show', $purchaseOrder)
            ->with('success', 'Order sent to supplier.');
    }

    public function cancel(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $this->centralApproval->cancel($purchaseOrder);
            return redirect()->route('purchasing.orders.show', $purchaseOrder)
                ->with('success', 'Order cancelled.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reverse(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            DB::transaction(function () use ($purchaseOrder) {
                if (!in_array($purchaseOrder->status, ['completed', 'approved'])) {
                    throw new \InvalidArgumentException('Only completed or approved orders can be reversed.');
                }

                $hasReceipts = GoodsReceipt::where('purchase_order_id', $purchaseOrder->id)
                    ->where('status', 'completed')
                    ->exists();

                if ($hasReceipts) {
                    throw new \InvalidArgumentException('Cannot reverse order. Goods have already been received. Reverse the goods receipt first.');
                }

                $purchaseOrder->update(['status' => 'reversed']);
            });

            return redirect()->route('purchasing.orders.show', $purchaseOrder)
                ->with('success', 'Order reversed successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
