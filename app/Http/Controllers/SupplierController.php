<?php

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class SupplierController extends Controller
{
    protected SupplierService $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    public function index(Request $request): View
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $suppliers = $this->supplierService->search($search);

        if ($status === 'active') {
            $suppliers = $this->supplierService->getAllPaginated(null, ['is_active' => true]);
        } elseif ($status === 'inactive') {
            $suppliers = $this->supplierService->getAllPaginated(null, ['is_active' => false]);
        }

        return view('suppliers.index', compact('suppliers', 'search'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $supplier = $this->supplierService->create($request->validated());

        if ($request->ajax()) {
            return response()->json(['id' => $supplier->id, 'name' => $supplier->name]);
        }

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier): View
    {
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->supplierService->update($supplier, $request->validated());

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->supplierService->delete($supplier);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $suppliers = $this->supplierService->getAllPaginated(null, limit: 0)->get();

        $callback = function () use ($suppliers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Contact Person', 'Email', 'Phone 1', 'Phone 2', 'City', 'Address', 'Tax ID', 'Payment Terms', 'Status']);

            foreach ($suppliers as $supplier) {
                fputcsv($handle, [
                    $supplier->name,
                    $supplier->contact_person,
                    $supplier->email,
                    $supplier->phone1,
                    $supplier->phone2,
                    $supplier->city,
                    $supplier->address,
                    $supplier->tax_id,
                    $supplier->payment_terms,
                    $supplier->is_active ? 'Active' : 'Inactive',
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="suppliers.csv"',
        ]);
    }
}
