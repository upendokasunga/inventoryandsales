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
use App\Services\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        BarcodeLabelService $barcodeLabelService
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

        $products = $search
            ? $this->productService->search($search)
            : $this->productService->getAllPaginated(20, $filters);

        $categories = $this->categoryService->getParentOptions();

        return view('products.index', compact('products', 'search', 'categories'));
    }

    public function subIndex(Request $request): View
    {
        $search = $request->get('search');
        $query = Product::with('category', 'parentProduct')
            ->whereNotNull('parent_product_id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(20);

        return view('products.sub-index', compact('products', 'search'));
    }

    public function create(): View
    {
        $categories = Category::whereNull('parent_id')
            ->orWhere('id', '>', 0)
            ->orderBy('name')
            ->get();
        $units = $this->unitService->getAllPaginated(100);
        $parentProducts = Product::whereNull('parent_product_id')->where('is_active', true)->orderBy('name')->pluck('name', 'id');
        $selectedParent = request('parent_id');

        return view('products.create', compact('categories', 'units', 'parentProducts', 'selectedParent'));
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

        if ($sellingPrice === null && $product->parent_product_id) {
            $parentPoItem = PurchaseOrderItem::where('product_id', $product->parent_product_id)
                ->where('product_make', $product->name)
                ->whereHas('purchaseOrder', fn($q) => $q->whereIn('status', ['approved', 'completed']))
                ->latest()
                ->first();

            if ($parentPoItem) {
                $sellingPrice = $parentPoItem->selling_price ?? $parentPoItem->unit_price;
            }
        }

        if ($sellingPrice === null && $product->parent_product_id) {
            $parentPoItem = PurchaseOrderItem::where('product_id', $product->parent_product_id)
                ->whereHas('purchaseOrder', fn($q) => $q->whereIn('status', ['approved', 'completed']))
                ->latest()
                ->first();

            if ($parentPoItem) {
                $sellingPrice = $parentPoItem->selling_price ?? $parentPoItem->unit_price;
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
        $product->load('category', 'productUnits.unit', 'priceListItems.priceList', 'priceListItems.unit', 'variants.productUnits.unit');
        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $product->load('productUnits.unit');
        $categories = Category::orderBy('name')->get();
        $allUnits = $this->unitService->getAllPaginated(100);

        return view('products.edit', compact('product', 'categories', 'allUnits'));
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

    public function exportCsv()
    {
        $content = $this->productService->exportCsv();
        $filename = 'products-' . now()->format('Y-m-d') . '.csv';

        return Response::stream(function () use ($content) {
            echo $content;
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
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
