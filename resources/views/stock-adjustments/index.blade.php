<x-app-layout>
    <x-slot name="header">{{ __('Stock Adjustments') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('stock-adjustments.create') }}" class="erp-btn-primary">Create Adjustment</a>
            <form method="GET" class="flex gap-2">
                <select name="status" class="erp-input" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <select name="type" class="erp-input" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="positive" {{ request('type') == 'positive' ? 'selected' : '' }}>Positive</option>
                    <option value="negative" {{ request('type') == 'negative' ? 'selected' : '' }}>Negative</option>
                    <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                </select>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($adjustments as $adj)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-medium text-slate-800">
                                    <a href="{{ route('stock-adjustments.show', $adj) }}" class="text-blue-600 hover:text-blue-500">{{ $adj->adjustment_number }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $adj->type === 'positive' ? 'bg-green-100 text-green-700' : ($adj->type === 'negative' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                                        {{ ucfirst($adj->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ ucfirst($adj->reason) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $adj->items->count() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $c = ['draft' => 'bg-slate-100 text-slate-600', 'completed' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-700'];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $c[$adj->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($adj->status) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $adj->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('stock-adjustments.show', $adj) }}" class="text-blue-600 hover:text-blue-500">View</a>
                                    @if ($adj->status === 'draft')
                                        <a href="{{ route('stock-adjustments.edit', $adj) }}" class="text-blue-600 hover:text-blue-500 ml-2">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-slate-500">No adjustments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">{{ $adjustments->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
