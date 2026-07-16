<?php

namespace App\Http\Controllers;

use App\Models\InventoryBalance;
use App\Models\Product;
use App\Services\InventoryAnalyticsService;
use App\Services\InventoryService;
use App\Services\InventoryValuationService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected InventoryAnalyticsService $analyticsService,
        protected InventoryValuationService $valuationService,
    ) {}

    public function index()
    {
        $stats = $this->analyticsService->getDashboardStats();
        $stockDistribution = $this->analyticsService->getStockStatusDistribution();
        $recentTransactions = $this->analyticsService->getRecentTransactions(10);

        return view('inventory.index', compact('stats', 'stockDistribution', 'recentTransactions'));
    }

    public function transactions(Request $request)
    {
        $filters = $request->only(['product_id', 'type', 'date_from', 'date_to']);
        $transactions = $this->inventoryService->getTransactionsPaginated(20, $filters);

        return view('inventory.transactions', compact('transactions', 'filters'));
    }

    public function valuation(Request $request)
    {
        $productId = $request->get('product_id');
        $valuation = $this->valuationService->getValuation($productId);
        $products = Product::where('track_stock', true)->orderBy('name')->get();

        return view('inventory.valuation', compact('valuation', 'products'));
    }

    public function analytics()
    {
        $stats = $this->analyticsService->getDashboardStats();
        $stockDistribution = $this->analyticsService->getStockStatusDistribution();
        $recentTransactions = $this->analyticsService->getRecentTransactions();

        return view('inventory.analytics', compact('stats', 'stockDistribution', 'recentTransactions'));
    }

    public function availableStock(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $query = InventoryBalance::with('product')->where('quantity_on_hand', '>', 0);

        if ($search) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($status === 'low') {
            $query->whereColumn('quantity_on_hand', '<=', 'product.reorder_level');
        } elseif ($status === 'overstocked') {
            $query->whereColumn('quantity_on_hand', '>', 'product.safety_stock * 3');
        }

        $balances = $query->orderBy('quantity_on_hand', 'desc')->paginate(25)->withQueryString();

        return view('inventory.available-stock', compact('balances', 'search', 'status'));
    }
}
