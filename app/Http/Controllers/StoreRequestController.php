<?php

namespace App\Http\Controllers;

use App\Models\StoreRequest;
use App\Models\Warehouse;
use App\Models\Product;
use App\Http\Requests\StoreRequest\StoreStoreRequestRequest;
use App\Http\Requests\StoreRequest\UpdateStoreRequestRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StoreRequestController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'all');
        $query = StoreRequest::with('sourceWarehouse', 'destinationWarehouse', 'creator', 'items.product');

        if ($tab !== 'all') {
            $query->where('status', $tab);
        }

        $storeRequests = $query->latest()->paginate(20);
        return view('store-requests.index', compact('storeRequests', 'tab'));
    }

    public function create(): View
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('productUnits.unit')->where('is_active', true)->orderBy('name')->get();
        return view('store-requests.create', compact('warehouses', 'products'));
    }

    public function store(StoreStoreRequestRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'] ?? [];
        unset($data['items']);

        $data['request_number'] = 'SR-' . strtoupper(\Illuminate\Support\Str::random(8));
        $data['created_by'] = auth()->id();

        $storeRequest = StoreRequest::create($data);

        foreach ($items as $item) {
            $storeRequest->items()->create($item);
        }

        return redirect()->route('store-requests.show', $storeRequest)
            ->with('success', 'Store request created successfully.');
    }

    public function show(StoreRequest $storeRequest): View
    {
        $storeRequest->load('sourceWarehouse', 'destinationWarehouse', 'items.product', 'creator', 'approver', 'issuer', 'receiver');
        return view('store-requests.show', compact('storeRequest'));
    }

    public function edit(StoreRequest $storeRequest): View
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('productUnits.unit')->where('is_active', true)->orderBy('name')->get();
        return view('store-requests.edit', compact('storeRequest', 'warehouses', 'products'));
    }

    public function update(UpdateStoreRequestRequest $request, StoreRequest $storeRequest): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'] ?? [];
        unset($data['items']);

        $storeRequest->update($data);
        $storeRequest->items()->delete();
        foreach ($items as $item) {
            $storeRequest->items()->create($item);
        }

        return redirect()->route('store-requests.show', $storeRequest)
            ->with('success', 'Store request updated successfully.');
    }

    public function print(StoreRequest $storeRequest): Response
    {
        $storeRequest->load(['sourceWarehouse', 'destinationWarehouse', 'items.product', 'creator', 'approver', 'issuer', 'receiver']);
        $data = app(\App\Services\PrintDocumentService::class)->getLetterheadData();
        $data['storeRequest'] = $storeRequest;
        return app(\App\Services\PrintDocumentService::class)->streamPdf('print.store-request', $data, "srn-{$storeRequest->request_number}.pdf");
    }

    public function destroy(StoreRequest $storeRequest): RedirectResponse
    {
        $storeRequest->items()->delete();
        $storeRequest->delete();
        return redirect()->route('store-requests.index')->with('success', 'Store request deleted successfully.');
    }
}
