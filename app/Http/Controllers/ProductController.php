<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\BarcodePrintRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\BarcodeLabelService;
use App\Services\CategoryService;
use App\Services\ProductService;
use App\Services\ProductUnitService;
use App\Services\UnitService;
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

    public function show(Product $product): View
    {
        $product->load('category', 'productUnits.unit', 'priceListItems.priceList', 'priceListItems.unit');
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
