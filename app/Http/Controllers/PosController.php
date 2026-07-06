<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\PosService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        protected PosService $posService,
    ) {}

    public function index(): View
    {
        return view('pos.index');
    }

    public function dashboard(): View
    {
        $stats = $this->posService->getDashboardStats();

        $chartLabels = range(1, now()->daysInMonth);
        $dayExpr = DB::getDriverName() === 'sqlite'
            ? "strftime('%d', created_at)"
            : 'DAY(created_at)';
        $chartData = Invoice::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw("{$dayExpr} as day, SUM(total) as total")
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();
        $chartData = array_map(fn($d) => $chartData[$d] ?? 0, $chartLabels);

        return view('pos.dashboard', compact('stats', 'chartLabels', 'chartData'));
    }

    public function lookupBarcode(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['barcode' => 'required|string']);

        $product = $this->posService->lookupBarcode($request->barcode);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $units = $product->units()->with('unit')->get();
        $stock = $product->current_stock;

        return response()->json([
            'product' => $product,
            'units' => $units,
            'stock' => $stock,
        ]);
    }

    public function lookupSku(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['sku' => 'required|string']);

        $product = $this->posService->lookupSku($request->sku);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $units = $product->units()->with('unit')->get();

        return response()->json([
            'product' => $product,
            'units' => $units,
            'stock' => $product->current_stock,
        ]);
    }

    public function getCustomer(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['customer_id' => 'required|exists:customers,id']);

        $customer = Customer::findOrFail($request->customer_id);
        $data = $this->posService->getCustomerWithCredit($customer);

        return response()->json($data);
    }

    public function getPrice(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'unit_id' => 'required|exists:units,id',
            'quantity' => 'required|numeric|min:0.001',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
        ]);

        $price = $this->posService->getProductPrice(
            $request->product_id,
            $request->unit_id,
            $request->quantity,
            $request->customer_group_id
        );

        return response()->json(['price' => $price]);
    }

    public function validateCredit(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $customer = Customer::findOrFail($request->customer_id);
        $result = $this->posService->validateCreditForTransaction($customer, $request->amount);

        return response()->json($result);
    }

    public function checkout(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment.amount' => 'required|numeric|min:0',
            'payment.payment_method' => 'required|string|in:cash,credit,bank_transfer,mobile_money,cheque,mixed',
        ]);

        try {
            $customer = Customer::findOrFail($request->customer_id);
            $result = $this->posService->checkout($request->all(), $customer);

            return response()->json([
                'success' => true,
                'invoice' => $result['invoice'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
