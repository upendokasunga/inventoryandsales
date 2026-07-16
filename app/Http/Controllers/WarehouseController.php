<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Branch;
use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class WarehouseController extends Controller
{
    public function index(): View
    {
        $warehouses = Warehouse::with('branch')->latest()->paginate(20);
        return view('warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('warehouses.create', compact('branches'));
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['code'] = self::generateCode();
        Warehouse::create($data);
        return redirect()->route('warehouses.index')->with('success', 'Warehouse created successfully.');
    }

    private static function generateCode(): string
    {
        $last = Warehouse::orderByRaw("CAST(SUBSTRING(code, 4) AS UNSIGNED) DESC")->first();
        $nextNum = 1;
        if ($last && preg_match('/^WH-(\d+)$/', $last->code, $m)) {
            $nextNum = (int) $m[1] + 1;
        }
        return 'WH-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }

    public function show(Warehouse $warehouse): View
    {
        $warehouse->load('branch');
        return view('warehouses.show', compact('warehouse'));
    }

    public function edit(Warehouse $warehouse): View
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('warehouses.edit', compact('warehouse', 'branches'));
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update($request->validated());
        return redirect()->route('warehouses.index')->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->delete();
        return redirect()->route('warehouses.index')->with('success', 'Warehouse deleted successfully.');
    }
}
