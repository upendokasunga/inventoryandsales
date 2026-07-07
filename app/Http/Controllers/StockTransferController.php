<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Models\Product;
use App\Http\Requests\StockTransfer\StoreStockTransferRequest;
use App\Http\Requests\StockTransfer\UpdateStockTransferRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StockTransferController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'all');
        $query = StockTransfer::with('sourceWarehouse', 'destinationWarehouse', 'creator', 'items.product');

        if ($tab !== 'all') {
            $query->where('status', $tab);
        }

        $stockTransfers = $query->latest()->paginate(20);
        return view('stock-transfers.index', compact('stockTransfers', 'tab'));
    }

    public function create(): View
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('productUnits.unit')->where('is_active', true)->orderBy('name')->get();
        return view('stock-transfers.create', compact('warehouses', 'products'));
    }

    public function store(StoreStockTransferRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'] ?? [];
        unset($data['items']);

        $data['transfer_number'] = 'ST-' . strtoupper(\Illuminate\Support\Str::random(8));
        $data['created_by'] = auth()->id();

        $stockTransfer = StockTransfer::create($data);

        foreach ($items as $item) {
            $stockTransfer->items()->create($item);
        }

        return redirect()->route('stock-transfers.show', $stockTransfer)
            ->with('success', 'Stock transfer created successfully.');
    }

    public function show(StockTransfer $stockTransfer): View
    {
        $stockTransfer->load('sourceWarehouse', 'destinationWarehouse', 'items.product', 'creator', 'approver', 'issuer', 'receiver');
        return view('stock-transfers.show', compact('stockTransfer'));
    }

    public function edit(StockTransfer $stockTransfer): View
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('productUnits.unit')->where('is_active', true)->orderBy('name')->get();
        return view('stock-transfers.edit', compact('stockTransfer', 'warehouses', 'products'));
    }

    public function update(UpdateStockTransferRequest $request, StockTransfer $stockTransfer): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'] ?? [];
        unset($data['items']);

        $stockTransfer->update($data);
        $stockTransfer->items()->delete();
        foreach ($items as $item) {
            $stockTransfer->items()->create($item);
        }

        return redirect()->route('stock-transfers.show', $stockTransfer)
            ->with('success', 'Stock transfer updated successfully.');
    }

    public function print(StockTransfer $stockTransfer): Response
    {
        $stockTransfer->load(['sourceWarehouse', 'destinationWarehouse', 'items.product', 'creator', 'approver', 'issuer', 'receiver']);
        $data = app(\App\Services\PrintDocumentService::class)->getLetterheadData();
        $data['stockTransfer'] = $stockTransfer;
        return app(\App\Services\PrintDocumentService::class)->streamPdf('print.stock-transfer', $data, "stn-{$stockTransfer->transfer_number}.pdf");
    }

    public function destroy(StockTransfer $stockTransfer): RedirectResponse
    {
        $stockTransfer->items()->delete();
        $stockTransfer->delete();
        return redirect()->route('stock-transfers.index')->with('success', 'Stock transfer deleted successfully.');
    }
}
