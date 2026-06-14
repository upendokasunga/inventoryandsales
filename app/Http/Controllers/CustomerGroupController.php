<?php

namespace App\Http\Controllers;

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

    public function index(): View
    {
        $customerGroups = $this->customerGroupService->getAllPaginated();

        return view('customer-groups.index', compact('customerGroups'));
    }

    public function create(): View
    {
        return view('customer-groups.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:customer_groups',
            'description' => 'nullable|string|max:500',
            'default_credit_limit' => 'numeric|min:0',
            'default_payment_terms' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $this->customerGroupService->create($validated);

        return redirect()->route('customer-groups.index')
            ->with('success', 'Customer group created successfully.');
    }

    public function edit(CustomerGroup $customerGroup): View
    {
        return view('customer-groups.edit', compact('customerGroup'));
    }

    public function update(Request $request, CustomerGroup $customerGroup): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:customer_groups,name,' . $customerGroup->id,
            'description' => 'nullable|string|max:500',
            'default_credit_limit' => 'numeric|min:0',
            'default_payment_terms' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $this->customerGroupService->update($customerGroup, $validated);

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
