<?php

namespace App\Http\Controllers;

use App\Models\ApprovalConfiguration;
use App\Models\Group;
use App\Models\ApprovalLevel;
use App\Http\Requests\ApprovalConfiguration\StoreApprovalConfigurationRequest;
use App\Http\Requests\ApprovalConfiguration\UpdateApprovalConfigurationRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ApprovalConfigurationController extends Controller
{
    public function index(): View
    {
        $configs = ApprovalConfiguration::with('levels.group')->latest()->paginate(20);
        return view('approval-configurations.index', compact('configs'));
    }

    public function create(): View
    {
        $moduleKeys = [
            'sales_order', 'purchase_order', 'store_request',
            'stock_transfer', 'expense', 'stock_adjustment', 'journal_entry'
        ];
        $groups = Group::orderBy('name')->get();
        return view('approval-configurations.create', compact('moduleKeys', 'groups'));
    }

    public function store(StoreApprovalConfigurationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $levels = $data['levels'] ?? [];
        unset($data['levels']);

        $config = ApprovalConfiguration::create($data);

        foreach ($levels as $level) {
            $config->levels()->create($level);
        }

        return redirect()->route('approval-configurations.index')
            ->with('success', 'Approval configuration created successfully.');
    }

    public function show(ApprovalConfiguration $approvalConfiguration): View
    {
        $approvalConfiguration->load('levels.group');
        return view('approval-configurations.show', compact('approvalConfiguration'));
    }

    public function edit(ApprovalConfiguration $approvalConfiguration): View
    {
        $approvalConfiguration->load('levels');
        $moduleKeys = [
            'sales_order', 'purchase_order', 'store_request',
            'stock_transfer', 'expense', 'stock_adjustment', 'journal_entry'
        ];
        $groups = Group::orderBy('name')->get();
        return view('approval-configurations.edit', compact('approvalConfiguration', 'moduleKeys', 'groups'));
    }

    public function update(UpdateApprovalConfigurationRequest $request, ApprovalConfiguration $approvalConfiguration): RedirectResponse
    {
        $data = $request->validated();
        $levels = $data['levels'] ?? [];
        unset($data['levels']);

        $approvalConfiguration->update($data);
        $approvalConfiguration->levels()->delete();
        foreach ($levels as $level) {
            $approvalConfiguration->levels()->create($level);
        }

        return redirect()->route('approval-configurations.index')
            ->with('success', 'Approval configuration updated successfully.');
    }

    public function destroy(ApprovalConfiguration $approvalConfiguration): RedirectResponse
    {
        $approvalConfiguration->levels()->delete();
        $approvalConfiguration->delete();
        return redirect()->route('approval-configurations.index')
            ->with('success', 'Approval configuration deleted successfully.');
    }
}
