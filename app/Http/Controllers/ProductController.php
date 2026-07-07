<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\BarcodePrintRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\PurchaseOrderItem;
use App\Services\BarcodeLabelService;
use App\Services\CategoryService;
use App\Services\ProductService;
use App\Services\ProductUnitService;
use App\Services\SettingsService;
use App\Services\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ProductController extends Controller
{
    protected ProductService $productService;
    protected ProductUnitService $productUnitService;
    protected CategoryService $categoryService;
    protected UnitService $unitService;
    protected BarcodeLabelService $barcodeLabelService;

    public function __construct(
        ProductService $productService,
        ProductUnitService $productUnitService,
        CategoryService $categoryService,
        UnitService $unitService,
        BarcodeLabelService $barcodeLabelService,
        protected SettingsService $settingsService,
    ) {
        $this->productService = $productService;
        $this->productUnitService = $productUnitService;
        $this->categoryService = $categoryService;
        $this->unitService = $unitService;
        $this->barcodeLabelService = $barcodeLabelService;
    }

    public function index(Request $request): View
    {
        $search = $request->get('search');
        $filters = [];
        if ($request->get('status')) {
            $filters['status'] = $request->get('status');
        }
        if ($request->get('category_id')) {
            $filters['category_id'] = $request->get('category_id');
        }
        if ($request->get('product_type')) {
            $filters['product_type'] = $request->get('product_type');
        }

        $products = $search
            ? $this->productService->search($search)
            : $this->productService->getAllPaginated(20, $filters);

        $categories = $this->categoryService->getParentOptions();

        return view('products.index', compact('products', 'search', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::whereNull('parent_id')
            ->orWhere('id', '>', 0)
            ->orderBy('name')
            ->get();
        $units = $this->unitService->getAllPaginated(100);

        return view('products.create', compact('categories', 'units'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $units = $data['units'] ?? [];
        unset($data['units']);

        $product = $this->productService->create($data);

        if (!empty($units)) {
            $this->productUnitService->sync($product, $units);
        }

        return redirect()->route('products.show', $product)
            ->with('success', 'Product created successfully.');
    }

    public function priceJson(Request $request, Product $product): JsonResponse
    {
        $sellingPrice = null;

        $pu = $product->productUnits()->where('is_default_sale', true)->first();
        if ($pu && $pu->wholesale_price > 0) {
            $sellingPrice = $pu->wholesale_price;
        }

        if ($sellingPrice === null) {
            $latestPoItem = PurchaseOrderItem::where('product_id', $product->id)
                ->whereHas('purchaseOrder', fn($q) => $q->whereIn('status', ['approved', 'completed']))
                ->latest()
                ->first();

            if ($latestPoItem) {
                $sellingPrice = $latestPoItem->selling_price ?? $latestPoItem->unit_price;
            }
        }

        if ($sellingPrice === null && $pu) {
            $sellingPrice = $pu->selling_price;
        }

        if ($sellingPrice === null) {
            $sellingPrice = $product->productUnits()->min('selling_price') ?? 0;
        }

        return response()->json([
            'price' => $sellingPrice,
            'formatted' => number_format($sellingPrice, 2),
        ]);
    }

    public function show(Product $product): View
    {
        $product->load('category', 'productUnits.unit', 'priceListItems.priceList', 'priceListItems.unit', 'incomeAccount');
        return view('products.show', compact('product'));
    }

    public function edit(Request $request, Product $product): View
    {
        $product->load('productUnits.unit', 'incomeAccount');
        $categories = Category::orderBy('name')->get();
        $allUnits = $this->unitService->getAllPaginated(100);
        $canEditName = $request->user()?->hasMenuAccess('products.edit-name', 'can_edit') ?? false;
        $brandCodeField = Schema::hasColumn('products', 'brand_code') ? 'brand_code' : (Schema::hasColumn('products', 'brandcode') ? 'brandcode' : null);

        return view('products.edit', compact(
            'product', 'categories', 'allUnits',
            'canEditName', 'brandCodeField'
        ));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $units = $data['units'] ?? [];
        unset($data['units']);

        if (isset($data['name']) && $data['name'] !== $product->name) {
            if (!$request->user()?->hasMenuAccess('products.update', 'can_edit')) {
                return back()->with('error', 'You do not have permission to edit the product name.');
            }
        }

        $this->productService->update($product, $data);

        if (!empty($units)) {
            $this->productUnitService->sync($product, $units);
        }

        return redirect()->route('products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->delete($product);
        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function updatePrice(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate(['price' => 'required|numeric|min:0']);
        $product->update(['price' => $validated['price']]);
        return back()->with('success', 'Price updated successfully.');
    }

    public function updatePricesBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prices' => 'required|array',
            'prices.*.id' => 'required|exists:products,id',
            'prices.*.price' => 'required|numeric|min:0',
        ]);

        foreach ($validated['prices'] as $item) {
            Product::where('id', $item['id'])->update(['price' => $item['price']]);
        }

        return back()->with('success', 'Prices updated successfully.');
    }

    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return Response::stream(function () {
            $products = Product::with('category', 'productUnits.unit', 'incomeAccount')->get();
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Product Code', 'Product ID', 'Name', 'Type', 'Category', 'Price', 'Income Account', 'Status']);

            foreach ($products as $product) {
                fputcsv($output, [
                    $product->product_code,
                    $product->product_id,
                    $product->name,
                    $product->product_type,
                    $product->category?->name ?? $product->category,
                    $product->price,
                    $product->incomeAccount?->code . ' - ' . $product->incomeAccount?->name,
                    $product->is_active ? 'Active' : 'Inactive',
                ]);
            }
            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    public function barcodes(BarcodePrintRequest $request): View
    {
        $data = $this->barcodeLabelService->generateBulkLabels(
            $request->input('product_ids'),
            $request->input('format', '2x1')
        );

        return view('products.barcodes', $data);
    }

    public function printBarcode(Product $product): View
    {
        $data = $this->barcodeLabelService->generateLabels($product, '2x1');
        return view('products.barcodes', $data);
    }
}
