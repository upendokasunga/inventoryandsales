<?php

namespace App\Http\Controllers;

use App\Http\Requests\PriceList\StorePriceListRequest;
use App\Http\Requests\PriceList\UpdatePriceListRequest;
use App\Models\PriceList;
use App\Services\CategoryService;
use App\Services\CustomerGroupService;
use App\Services\PriceListService;
use App\Services\ProductService;
use App\Services\UnitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class PriceListController extends Controller
{
    protected PriceListService $priceListService;
    protected CustomerGroupService $customerGroupService;
    protected ProductService $productService;
    protected UnitService $unitService;

    public function __construct(
        PriceListService $priceListService,
        CustomerGroupService $customerGroupService,
        ProductService $productService,
        UnitService $unitService
    ) {
        $this->priceListService = $priceListService;
        $this->customerGroupService = $customerGroupService;
        $this->productService = $productService;
        $this->unitService = $unitService;
    }

    public function dashboard(): View
    {
        $stats = $this->priceListService->getDashboardStats();
        $recentLists = \App\Models\PriceList::with('customerGroup')
            ->latest()
            ->take(5)
            ->get();

        return view('price-lists.dashboard', compact('stats', 'recentLists'));
    }

    public function index(Request $request): View
    {
        $search = $request->get('search');
        $filters = [];

        if ($request->get('status')) {
            $filters['status'] = $request->get('status');
        }
        if ($request->get('customer_group_id')) {
            $filters['customer_group_id'] = $request->get('customer_group_id');
        }

        $priceLists = $search
            ? $this->priceListService->search($search)
            : $this->priceListService->getAllPaginated(20, $filters);

        $customerGroups = $this->customerGroupService->getAllPaginated(100);

        return view('price-lists.index', compact('priceLists', 'search', 'customerGroups'));
    }

    public function create(): View
    {
        $customerGroups = $this->customerGroupService->getAllPaginated(100);
        $products = $this->productService->getAllPaginated(100);
        $units = $this->unitService->getAllPaginated(100);

        return view('price-lists.create', compact('customerGroups', 'products', 'units'));
    }

    public function store(StorePriceListRequest $request): RedirectResponse
    {
        try {
            $priceList = $this->priceListService->create($request->validated());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('price-lists.show', $priceList)
            ->with('success', 'Price list created successfully.');
    }

    public function show(PriceList $priceList): View
    {
        $priceList->load('customerGroup', 'items.product', 'items.unit');
        return view('price-lists.show', compact('priceList'));
    }

    public function edit(PriceList $priceList): View
    {
        $priceList->load('items.product', 'items.unit');
        $customerGroups = $this->customerGroupService->getAllPaginated(100);
        $products = $this->productService->getAllPaginated(100);
        $units = $this->unitService->getAllPaginated(100);

        return view('price-lists.edit', compact('priceList', 'customerGroups', 'products', 'units'));
    }

    public function update(UpdatePriceListRequest $request, PriceList $priceList): RedirectResponse
    {
        try {
            $this->priceListService->update($priceList, $request->validated());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('price-lists.show', $priceList)
            ->with('success', 'Price list updated successfully.');
    }

    public function destroy(PriceList $priceList): RedirectResponse
    {
        $this->priceListService->delete($priceList);

        return redirect()->route('price-lists.index')
            ->with('success', 'Price list deleted successfully.');
    }

    public function exportCsv()
    {
        $priceLists = \App\Models\PriceList::with('customerGroup', 'items.product', 'items.unit')->latest()->get();

        $csv = "Name,Customer Group,Currency,Status,Valid From,Valid Until,Items\n";
        foreach ($priceLists as $list) {
            $group = $list->customerGroup ? $list->customerGroup->name : 'General';
            $status = $list->isExpired() ? 'Expired' : ($list->is_active ? 'Active' : 'Inactive');
            $items = $list->items->map(function ($i) {
                $unitCode = $i->unit ? $i->unit->short_code : '';
                $maxQty = $i->max_quantity !== null ? $i->max_quantity : "\u{221E}";
                return "{$i->product->name} ({$unitCode}): {$i->min_quantity}-{$maxQty} @{$i->price}";
            })->implode('; ');
            $validFrom = $list->valid_from ? $list->valid_from->format('Y-m-d') : '';
            $validUntil = $list->valid_until ? $list->valid_until->format('Y-m-d') : '';
            $csv .= "\"{$list->name}\",\"{$group}\",{$list->currency},{$status},{$validFrom},{$validUntil},\"{$items}\"\n";
        }

        $filename = 'price-lists-' . now()->format('Y-m-d') . '.csv';
        return Response::stream(function () use ($csv) {
            echo $csv;
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
