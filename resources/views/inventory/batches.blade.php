<x-app-layout>
    <x-slot name="header">{{ __('Batch Tracking') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        @if ($expiryData['total_quantity'] > 0)
            <div class="mb-4 px-4 py-3 text-amber-700 bg-amber-50 border border-amber-100 rounded-lg text-sm">
                <strong>{{ $expiryData['total_products'] }} product(s)</strong> have batches expiring within 30 days.
                Total quantity: <strong>{{ number_format($expiryData['total_quantity'], 2) }}</strong>.
                @if ($expiryData['critical_count'] > 0)
                    <span class="text-red-600">{{ number_format($expiryData['critical_count'], 2) }} units expiring within 7 days!</span>
                @endif
            </div>
        @endif

        <div class="mb-4">
            <form method="GET" class="flex flex-wrap gap-2">
                <select name="status" class="erp-input" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ ($filters['status'] ?? '') == 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="depleted" {{ ($filters['status'] ?? '') == 'depleted' ? 'selected' : '' }}>Depleted</option>
                    <option value="quarantined" {{ ($filters['status'] ?? '') == 'quarantined' ? 'selected' : '' }}>Quarantined</option>
                </select>
                <button type="submit" class="erp-btn-primary">Filter</button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Batch #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Qty Remaining</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Unit Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Expiry</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($batches as $batch)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-medium text-slate-800">{{ $batch->batch_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $batch->product?->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ number_format($batch->quantity_remaining, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ number_format($batch->unit_cost, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $batch->expiry_date && $batch->expiry_date->isPast() ? 'text-red-600 font-semibold' : 'text-slate-500' }}">
                                    {{ $batch->expiry_date ? $batch->expiry_date->format('M d, Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $c = ['active' => 'bg-green-100 text-green-700', 'expired' => 'bg-red-100 text-red-700', 'depleted' => 'bg-slate-100 text-slate-600', 'quarantined' => 'bg-amber-100 text-amber-700'];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $c[$batch->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($batch->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500">No batches found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">{{ $batches->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
