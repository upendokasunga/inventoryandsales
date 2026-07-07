<x-app-layout>
    <x-slot name="header">{{ __('Stock Adjustment') }}: {{ $stockAdjustment->adjustment_number }}</x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('stock-adjustments.index') }}" class="erp-btn-secondary">Back to List</a>
            <div class="flex gap-2">
                <a href="{{ route('stock-adjustments.print', $stockAdjustment) }}" class="erp-btn-secondary" target="_blank">Print PDF</a>
                @if ($stockAdjustment->status === 'draft')
                    <a href="{{ route('stock-adjustments.edit', $stockAdjustment) }}" class="erp-btn-primary">Edit</a>
                    <form action="{{ route('stock-adjustments.complete', $stockAdjustment) }}" method="POST" class="inline"
                        onsubmit="return confirm('Complete this adjustment? This will update inventory balances.');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="erp-btn-primary bg-green-600 hover:bg-green-700">Complete</button>
                    </form>
                @endif
                @if (in_array($stockAdjustment->status, ['draft', 'cancelled']))
                    <form action="{{ route('stock-adjustments.destroy', $stockAdjustment) }}" method="POST" class="inline"
                        onsubmit="return confirm('Delete this adjustment?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="erp-btn-danger">Delete</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Adjustment Information</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Adjustment #</dt>
                            <dd class="text-sm font-semibold text-slate-800">{{ $stockAdjustment->adjustment_number }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Type</dt>
                            <dd><span class="px-2 py-1 text-xs font-medium rounded-full {{ $stockAdjustment->type === 'positive' ? 'bg-green-100 text-green-700' : ($stockAdjustment->type === 'negative' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">{{ ucfirst($stockAdjustment->type) }}</span></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Reason</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ ucfirst($stockAdjustment->reason) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Status</dt>
                            <dd><span class="px-2 py-1 text-xs font-medium rounded-full {{ $stockAdjustment->status === 'completed' ? 'bg-green-100 text-green-700' : ($stockAdjustment->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600') }}">{{ ucfirst($stockAdjustment->status) }}</span></dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Audit</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Created By</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $stockAdjustment->creator?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Created At</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $stockAdjustment->created_at->format('M d, Y H:i') }}</dd>
                        </div>
                        @if ($stockAdjustment->approver)
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Approved By</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $stockAdjustment->approver->name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Approved At</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $stockAdjustment->approved_at?->format('M d, Y H:i') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
            @if ($stockAdjustment->description)
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <h3 class="text-sm font-medium text-slate-500 mb-2">Description</h3>
                    <p class="text-sm text-slate-700">{{ $stockAdjustment->description }}</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <h3 class="text-sm font-medium text-slate-500 mb-4">Items</h3>
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Expected</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actual</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Difference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Unit Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($stockAdjustment->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $item->product?->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ number_format($item->expected_quantity, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ number_format($item->actual_quantity, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $item->difference > 0 ? 'text-green-600' : ($item->difference < 0 ? 'text-red-600' : 'text-slate-500') }}">
                                    {{ $item->difference > 0 ? '+' : '' }}{{ number_format($item->difference, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ number_format($item->unit_cost, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $item->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500">No items.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
