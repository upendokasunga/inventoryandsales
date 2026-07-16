<?php

namespace App\Http\Controllers;

use App\Http\Requests\CostCenter\StoreCostCenterRequest;
use App\Http\Requests\CostCenter\UpdateCostCenterRequest;
use App\Models\CostCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CostCenterController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $query = CostCenter::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $costCenters = $query->latest()->paginate(20)->withQueryString();

        return view('cost-centers.index', compact('costCenters', 'search'));
    }

    public function create(): View
    {
        return view('cost-centers.create');
    }

    public function store(StoreCostCenterRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['code'] = self::generateCode();
        CostCenter::create($data);

        return redirect()->route('cost-centers.index')
            ->with('success', 'Cost centre created successfully.');
    }

    private static function generateCode(): string
    {
        $last = CostCenter::orderByRaw("CAST(SUBSTRING(code, 4) AS UNSIGNED) DESC")->first();
        $nextNum = 1;
        if ($last && preg_match('/^CC-(\d+)$/', $last->code, $m)) {
            $nextNum = (int) $m[1] + 1;
        }
        return 'CC-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }

    public function edit(CostCenter $costCenter): View
    {
        return view('cost-centers.edit', compact('costCenter'));
    }

    public function update(UpdateCostCenterRequest $request, CostCenter $costCenter): RedirectResponse
    {
        $costCenter->update($request->validated());

        return redirect()->route('cost-centers.index')
            ->with('success', 'Cost centre updated successfully.');
    }

    public function destroy(CostCenter $costCenter): RedirectResponse
    {
        $costCenter->delete();

        return redirect()->route('cost-centers.index')
            ->with('success', 'Cost centre deleted successfully.');
    }
}
