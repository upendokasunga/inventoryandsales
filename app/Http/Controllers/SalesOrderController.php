<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalesOrderRequest;
use App\Http\Requests\UpdateSalesOrderRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Services\FulfillmentService;
use App\Services\ReservationService;
use App\Services\SalesOrderApprovalService;
use App\Services\SalesOrderService;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    public function __construct(
        protected SalesOrderService $salesOrderService,
        protected SalesOrderApprovalService $approvalService,
        protected ReservationService $reservationService,
        protected FulfillmentService $fulfillmentService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'customer_id', 'search', 'date_from', 'date_to']);
        $orders = $this->salesOrderService->getAllPaginated(20, $filters);
        $stats = $this->salesOrderService->getStats();
        $customers = Customer::orderBy('name')->pluck('name', 'id');

        return view('sales.orders.index', compact('orders', 'stats', 'filters', 'customers'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->where('track_stock', true)->orderBy('name')->get();

        return view('sales.orders.create', compact('customers', 'products'));
    }

    public function store(StoreSalesOrderRequest $request)
    {
        $data = $request->validated();
        $items = $data['items'];
        unset($data['items']);

        $salesOrder = $this->salesOrderService->create($data, $items);

        return redirect()->route('sales.orders.show', $salesOrder)
            ->with('success', 'Sales order created successfully.');
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['items.product', 'customer', 'creator', 'approver', 'reservist', 'fulfiller', 'reservations.items']);
        $fulfillmentStatus = $this->fulfillmentService->getFulfillmentStatus($salesOrder);

        return view('sales.orders.show', compact('salesOrder', 'fulfillmentStatus'));
    }

    public function edit(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'draft') {
            return redirect()->route('sales.orders.show', $salesOrder)
                ->with('error', 'Only draft orders can be edited.');
        }

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->where('track_stock', true)->orderBy('name')->get();
        $salesOrder->load('items.product');

        return view('sales.orders.edit', compact('salesOrder', 'customers', 'products'));
    }

    public function update(UpdateSalesOrderRequest $request, SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'draft') {
            return redirect()->route('sales.orders.show', $salesOrder)
                ->with('error', 'Only draft orders can be edited.');
        }

        $data = $request->validated();
        $items = $data['items'];
        unset($data['items']);

        $salesOrder = $this->salesOrderService->update($salesOrder, $data, $items);

        return redirect()->route('sales.orders.show', $salesOrder)
            ->with('success', 'Sales order updated.');
    }

    public function destroy(SalesOrder $salesOrder)
    {
        $this->salesOrderService->delete($salesOrder);

        return redirect()->route('sales.orders.index')
            ->with('success', 'Sales order deleted.');
    }

    public function submitForApproval(SalesOrder $salesOrder)
    {
        try {
            $this->approvalService->submitForApproval($salesOrder);
            return redirect()->route('sales.orders.show', $salesOrder)
                ->with('success', 'Order submitted for approval.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('sales.orders.show', $salesOrder)
                ->with('error', $e->getMessage());
        }
    }

    public function approve(SalesOrder $salesOrder)
    {
        try {
            $this->approvalService->approve($salesOrder);
            return redirect()->route('sales.orders.show', $salesOrder)
                ->with('success', 'Order approved.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('sales.orders.show', $salesOrder)
                ->with('error', $e->getMessage());
        }
    }

    public function reject(SalesOrder $salesOrder)
    {
        try {
            $this->approvalService->reject($salesOrder);
            return redirect()->route('sales.orders.show', $salesOrder)
                ->with('success', 'Order returned to draft.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('sales.orders.show', $salesOrder)
                ->with('error', $e->getMessage());
        }
    }

    public function reserve(SalesOrder $salesOrder)
    {
        try {
            if (!$this->reservationService->hasSufficientStock($salesOrder)) {
                $issues = $this->reservationService->validateAvailability($salesOrder);
                $msg = collect($issues)->map(fn($i) =>
                    "{$i['product_name']}: need {$i['requested']}, only {$i['available']} available"
                )->implode('; ');

                return redirect()->route('sales.orders.show', $salesOrder)
                    ->with('error', 'Insufficient stock: ' . $msg);
            }

            $this->reservationService->reserve($salesOrder);
            return redirect()->route('sales.orders.show', $salesOrder)
                ->with('success', 'Stock reserved for order.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('sales.orders.show', $salesOrder)
                ->with('error', $e->getMessage());
        }
    }

    public function fulfill(SalesOrder $salesOrder)
    {
        try {
            $this->fulfillmentService->fulfill($salesOrder);
            return redirect()->route('sales.orders.show', $salesOrder)
                ->with('success', 'Order fulfilled successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('sales.orders.show', $salesOrder)
                ->with('error', $e->getMessage());
        }
    }

    public function cancel(SalesOrder $salesOrder)
    {
        try {
            $this->approvalService->cancel($salesOrder);
            return redirect()->route('sales.orders.show', $salesOrder)
                ->with('success', 'Order cancelled.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('sales.orders.show', $salesOrder)
                ->with('error', $e->getMessage());
        }
    }
}
