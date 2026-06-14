<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerGroup\StoreCustomerGroupRequest;
use App\Http\Requests\CustomerGroup\UpdateCustomerGroupRequest;
use App\Models\CustomerGroup;
use App\Services\CustomerGroupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerGroupController extends Controller
{
    protected CustomerGroupService $customerGroupService;

    public function __construct(CustomerGroupService $customerGroupService)
    {
        $this->customerGroupService = $customerGroupService;
    }

    public function index(Request $request): View
    {
        $status = $request->get('status');

        $customerGroups = $this->customerGroupService->getAllPaginated();

        if ($status === 'active') {
            $customerGroups = $this->customerGroupService->getAllPaginated(null, ['is_active' => true]);
        } elseif ($status === 'inactive') {
            $customerGroups = $this->customerGroupService->getAllPaginated(null, ['is_active' => false]);
        }

        return view('customer-groups.index', compact('customerGroups'));
    }

    public function create(): View
    {
        return view('customer-groups.create');
    }

    public function store(StoreCustomerGroupRequest $request): RedirectResponse
    {
        $this->customerGroupService->create($request->validated());

        return redirect()->route('customer-groups.index')
            ->with('success', 'Customer group created successfully.');
    }

    public function edit(CustomerGroup $customerGroup): View
    {
        return view('customer-groups.edit', compact('customerGroup'));
    }

    public function update(UpdateCustomerGroupRequest $request, CustomerGroup $customerGroup): RedirectResponse
    {
        $this->customerGroupService->update($customerGroup, $request->validated());

        return redirect()->route('customer-groups.index')
            ->with('success', 'Customer group updated successfully.');
    }

    public function destroy(CustomerGroup $customerGroup): RedirectResponse
    {
        $this->customerGroupService->delete($customerGroup);

        return redirect()->route('customer-groups.index')
            ->with('success', 'Customer group deleted successfully.');
    }
}
