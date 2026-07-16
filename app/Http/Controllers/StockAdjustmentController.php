<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockAdjustmentRequest;
use App\Http\Requests\UpdateStockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Services\CentralApprovalService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected CentralApprovalService $centralApproval,
    ) {}

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'all');

        $query = StockAdjustment::with(['creator', 'items.product']);

        if ($tab !== 'all') {
            $query->where('status', $tab);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $adjustments = $query->latest()->paginate(20);

        $counts = [
            'all' => StockAdjustment::count(),
                'pending_approval' => StockAdjustment::where('status', 'pending_approval')->count(),
            'approved' => StockAdjustment::where('status', 'approved')->count(),
            'completed' => StockAdjustment::where('status', 'completed')->count(),
            'cancelled' => StockAdjustment::where('status', 'cancelled')->count(),
        ];

        return view('stock-adjustments.index', compact('adjustments', 'tab', 'counts'));
    }

    public function create()
    {
        $products = Product::where('track_stock', true)->orderBy('name')->get();
        return view('stock-adjustments.create', compact('products'));
    }

    public function store(StoreStockAdjustmentRequest $request)
    {
        $data = $request->validated();

        $adjustment = DB::transaction(function () use ($data) {
            $adjustment = StockAdjustment::create([
                'adjustment_number' => 'ADJ-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'type' => $data['type'],
                'reason' => $data['reason'],
                'description' => $data['description'] ?? null,
                'status' => 'pending_approval',
                'created_by' => auth()->id(),
            ]);

            $items = [];
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $balance = $this->inventoryService->getOrCreateBalance($product->id);

                $items[] = $adjustment->items()->create([
                    'product_id' => $item['product_id'],
                    'inventory_batch_id' => $item['inventory_batch_id'] ?? null,
                    'expected_quantity' => $item['expected_quantity'],
                    'actual_quantity' => $item['actual_quantity'],
                    'difference' => $item['actual_quantity'] - $item['expected_quantity'],
                    'unit_cost' => $item['unit_cost'] ?? $balance->average_cost,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $adjustment->fresh(['items.product']);
        });

        return redirect()->route('stock-adjustments.show', $adjustment)
            ->with('success', 'Stock adjustment created.');
    }

    public function show(StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->load(['items.product', 'creator', 'approver']);
        return view('stock-adjustments.show', compact('stockAdjustment'));
    }

    public function print(StockAdjustment $stockAdjustment): Response
    {
        $stockAdjustment->load(['items.product', 'creator', 'approver']);
        $data = app(\App\Services\PrintDocumentService::class)->getLetterheadData();
        $data['stockAdjustment'] = $stockAdjustment;
        return app(\App\Services\PrintDocumentService::class)->streamPdf('print.stock-adjustment', $data, "adj-{$stockAdjustment->adjustment_number}.pdf");
    }

    public function edit(StockAdjustment $stockAdjustment)
    {
        if ($stockAdjustment->status !== 'pending_approval') {
            return redirect()->route('stock-adjustments.show', $stockAdjustment)
                ->with('error', 'Only pending adjustments can be edited.');
        }

        $products = Product::where('track_stock', true)->orderBy('name')->get();
        $stockAdjustment->load('items.product');
        return view('stock-adjustments.edit', compact('stockAdjustment', 'products'));
    }

    public function update(UpdateStockAdjustmentRequest $request, StockAdjustment $stockAdjustment)
    {
        if ($stockAdjustment->status !== 'pending_approval') {
            return redirect()->route('stock-adjustments.show', $stockAdjustment)
                ->with('error', 'Only pending adjustments can be edited.');
        }

        $data = $request->validated();

        DB::transaction(function () use ($stockAdjustment, $data) {
            $stockAdjustment->update([
                'type' => $data['type'],
                'reason' => $data['reason'],
                'description' => $data['description'] ?? null,
            ]);

            $stockAdjustment->items()->delete();

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $balance = $this->inventoryService->getOrCreateBalance($product->id);

                $stockAdjustment->items()->create([
                    'product_id' => $item['product_id'],
                    'inventory_batch_id' => $item['inventory_batch_id'] ?? null,
                    'expected_quantity' => $item['expected_quantity'],
                    'actual_quantity' => $item['actual_quantity'],
                    'difference' => $item['actual_quantity'] - $item['expected_quantity'],
                    'unit_cost' => $item['unit_cost'] ?? $balance->average_cost,
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('stock-adjustments.show', $stockAdjustment)
            ->with('success', 'Stock adjustment updated.');
    }

    public function submitForApproval(StockAdjustment $stockAdjustment)
    {
        try {
            $this->centralApproval->submit($stockAdjustment);
            return redirect()->route('stock-adjustments.show', $stockAdjustment)
                ->with('success', 'Stock adjustment submitted for approval.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('stock-adjustments.show', $stockAdjustment)
                ->with('error', $e->getMessage());
        }
    }

    public function approve(StockAdjustment $stockAdjustment)
    {
        try {
            $this->centralApproval->approve($stockAdjustment);
            return redirect()->route('stock-adjustments.show', $stockAdjustment)
                ->with('success', 'Stock adjustment approved.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('stock-adjustments.show', $stockAdjustment)
                ->with('error', $e->getMessage());
        }
    }

    public function complete(StockAdjustment $stockAdjustment)
    {
        if ($stockAdjustment->status !== 'approved') {
            return redirect()->route('stock-adjustments.show', $stockAdjustment)
                ->with('error', 'Only approved adjustments can be completed. Submit for approval first.');
        }

        try {
            DB::transaction(function () use ($stockAdjustment) {
                $stockAdjustment->update([
                    'status' => 'completed',
                    'approved_by' => $stockAdjustment->approved_by ?? auth()->id(),
                    'approved_at' => $stockAdjustment->approved_at ?? now(),
                ]);

                foreach ($stockAdjustment->items as $item) {
                    $this->inventoryService->adjustStock(
                        $item->product,
                        $item->expected_quantity,
                        $item->actual_quantity,
                        $item->unit_cost,
                        $stockAdjustment->reason . ': ' . ($item->notes ?? ''),
                        $stockAdjustment
                    );
                }
            });
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('stock-adjustments.show', $stockAdjustment)
                ->with('error', 'Cannot complete adjustment: ' . $e->getMessage());
        }

        return redirect()->route('stock-adjustments.show', $stockAdjustment)
            ->with('success', 'Stock adjustment completed.');
    }

    public function destroy(StockAdjustment $stockAdjustment)
    {
        if (!in_array($stockAdjustment->status, ['pending_approval', 'cancelled'])) {
            return redirect()->route('stock-adjustments.show', $stockAdjustment)
                ->with('error', 'Cannot delete a completed adjustment.');
        }

        $stockAdjustment->delete();

        return redirect()->route('stock-adjustments.index')
            ->with('success', 'Stock adjustment deleted.');
    }

    public function stockInfo(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::with('inventoryBalance')->findOrFail($request->product_id);

        return response()->json([
            'current_stock' => $product->inventoryBalance->quantity_on_hand ?? 0,
            'unit_cost' => $product->inventoryBalance->average_cost ?? $product->standard_cost ?? 0,
        ]);
    }
}
