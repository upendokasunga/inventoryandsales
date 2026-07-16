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
        protected \App\Services\PricingService $pricingService,
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

    public function searchProducts(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['q' => 'required|string|min:1']);

        $products = Product::where(function ($q) use ($request) {
                $term = $request->input('q');
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('sku', 'LIKE', "%{$term}%")
                  ->orWhere('barcode', 'LIKE', "%{$term}%")
                  ->orWhere('product_code', 'LIKE', "%{$term}%");
            })
            ->where('is_active', true)
            ->with(['units.unit'])
            ->limit(20)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'product_code' => $p->product_code,
                'image_url' => $p->image_url,
                'unit_price' => $p->price,
                'current_stock' => $p->current_stock,
                'units' => $p->units->map(fn($u) => ['id' => $u->id, 'unit_id' => $u->unit_id, 'name' => $u->unit?->name]),
            ]);

        return response()->json(['products' => $products]);
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

    public function getPriceSimple(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.001',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        $product = \App\Models\Product::findOrFail($request->product_id);
        $unitId = $request->input('unit_id') ?: $product->productUnits()->where('is_default_sale', true)->first()?->unit_id ?? $product->units()->first()?->id ?? 1;

        $customerGroupId = $request->input('customer_group_id');
        if (!$customerGroupId && $request->input('customer_id')) {
            $customerGroupId = \App\Models\Customer::find($request->input('customer_id'))?->customer_group_id;
        }

        $price = $this->pricingService->getPrice(
            $request->product_id,
            $unitId,
            $request->quantity,
            $customerGroupId
        );

        $unitPrice = $price['price'] ?? null;

        if ($unitPrice === null) {
            $pu = $product->productUnits()->where('is_default_sale', true)->first();
            if ($pu && $pu->wholesale_price > 0) {
                $unitPrice = $pu->wholesale_price;
            }
        }

        if ($unitPrice === null) {
            $latestPoItem = \App\Models\PurchaseOrderItem::where('product_id', $product->id)
                ->whereHas('purchaseOrder', fn($q) => $q->whereIn('status', ['approved', 'completed']))
                ->latest()
                ->first();
            if ($latestPoItem) {
                $unitPrice = $latestPoItem->selling_price ?? $latestPoItem->unit_price;
            }
        }

        if ($unitPrice === null) {
            $pu = $product->productUnits()->where('is_default_sale', true)->first();
            if ($pu && $pu->selling_price > 0) {
                $unitPrice = $pu->selling_price;
            }
        }

        if ($unitPrice === null) {
            $unitPrice = $product->productUnits()->min('selling_price') ?? $product->price ?? 0;
        }

        return response()->json([
            'price' => $price,
            'unit_price' => $unitPrice,
        ]);
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
