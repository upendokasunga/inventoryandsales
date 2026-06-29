<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockAdjustmentRequest;
use App\Http\Requests\UpdateStockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    public function index(Request $request)
    {
        $query = StockAdjustment::with(['creator', 'items.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $adjustments = $query->latest()->paginate(20);

        return view('stock-adjustments.index', compact('adjustments'));
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
                'status' => 'draft',
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

    public function edit(StockAdjustment $stockAdjustment)
    {
        if ($stockAdjustment->status !== 'draft') {
            return redirect()->route('stock-adjustments.show', $stockAdjustment)
                ->with('error', 'Only draft adjustments can be edited.');
        }

        $products = Product::where('track_stock', true)->orderBy('name')->get();
        $stockAdjustment->load('items.product');
        return view('stock-adjustments.edit', compact('stockAdjustment', 'products'));
    }

    public function update(UpdateStockAdjustmentRequest $request, StockAdjustment $stockAdjustment)
    {
        if ($stockAdjustment->status !== 'draft') {
            return redirect()->route('stock-adjustments.show', $stockAdjustment)
                ->with('error', 'Only draft adjustments can be edited.');
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
        if ($stockAdjustment->status !== 'draft') {
            return redirect()->route('stock-adjustments.show', $stockAdjustment)
                ->with('error', 'Only draft adjustments can be submitted.');
        }

        $stockAdjustment->update(['status' => 'pending_approval']);

        return redirect()->route('stock-adjustments.show', $stockAdjustment)
            ->with('success', 'Stock adjustment submitted for approval.');
    }

    public function approve(StockAdjustment $stockAdjustment)
    {
        if ($stockAdjustment->status !== 'pending_approval') {
            return redirect()->route('stock-adjustments.show', $stockAdjustment)
                ->with('error', 'Adjustment is not pending approval.');
        }

        $stockAdjustment->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('stock-adjustments.show', $stockAdjustment)
            ->with('success', 'Stock adjustment approved.');
    }

    public function complete(StockAdjustment $stockAdjustment)
    {
        if (!in_array($stockAdjustment->status, ['draft', 'approved'])) {
            return redirect()->route('stock-adjustments.show', $stockAdjustment)
                ->with('error', 'Adjustment cannot be completed in its current status.');
        }

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

        return redirect()->route('stock-adjustments.show', $stockAdjustment)
            ->with('success', 'Stock adjustment completed.');
    }

    public function destroy(StockAdjustment $stockAdjustment)
    {
        if (!in_array($stockAdjustment->status, ['draft', 'cancelled'])) {
            return redirect()->route('stock-adjustments.show', $stockAdjustment)
                ->with('error', 'Cannot delete a completed adjustment.');
        }

        $stockAdjustment->delete();

        return redirect()->route('stock-adjustments.index')
            ->with('success', 'Stock adjustment deleted.');
    }
}
