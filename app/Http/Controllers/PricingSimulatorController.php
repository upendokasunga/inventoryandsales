<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pricing\PricingSimulationRequest;
use App\Models\AuditLog;
use App\Services\CustomerGroupService;
use App\Services\PricingSimulationService;
use App\Services\ProductService;
use App\Services\UnitService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\View\View;

class PricingSimulatorController extends Controller
{
    protected PricingSimulationService $simulationService;
    protected CustomerGroupService $customerGroupService;
    protected ProductService $productService;
    protected UnitService $unitService;

    public function __construct(
        PricingSimulationService $simulationService,
        CustomerGroupService $customerGroupService,
        ProductService $productService,
        UnitService $unitService
    ) {
        $this->simulationService = $simulationService;
        $this->customerGroupService = $customerGroupService;
        $this->productService = $productService;
        $this->unitService = $unitService;
    }

    public function index(): View
    {
        $customerGroups = $this->customerGroupService->getAllPaginated(100);
        $products = $this->productService->getAllPaginated(100);
        $units = $this->unitService->getAllPaginated(100);

        return view('pricing.simulator', compact('customerGroups', 'products', 'units'));
    }

    public function simulate(PricingSimulationRequest $request): View
    {
        $data = $request->validated();
        $results = $this->simulationService->simulate(
            $data['customer_group_id'] ?? null,
            $data['product_id'],
            $data['unit_id'],
            $data['quantity']
        );

        $customerGroups = $this->customerGroupService->getAllPaginated(100);
        $products = $this->productService->getAllPaginated(100);
        $units = $this->unitService->getAllPaginated(100);

        try {
            AuditLog::create([
                'auditable_type' => 'PricingSimulation',
                'auditable_id' => 0,
                'user_id' => Auth::id(),
                'event' => 'simulation_run',
                'old_values' => null,
                'new_values' => $data + ['result_count' => $results['best'] ? 1 : 0],
                'ip_address' => app()->runningInConsole() ? 'console' : Request::ip(),
                'user_agent' => app()->runningInConsole() ? 'CLI' : Request::userAgent(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return view('pricing.simulator', array_merge(
            compact('customerGroups', 'products', 'units'),
            ['simulation' => $results],
            ['inputs' => $data]
        ));
    }
}
