<x-app-layout>
    <x-slot name="header">{{ __('Stock Adjustments') }}</x-slot>
    <x-slot name="headerDescription">Track inventory adjustments — positive, negative, and transfers between locations.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('stock-adjustments.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Adjustment
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-6 border-b border-slate-200/60">
            <nav class="flex gap-6 -mb-px overflow-x-auto">
                @foreach (['all' => 'All', 'draft' => 'Draft', 'pending_approval' => 'Pending', 'approved' => 'Approved', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $key => $label)
                    <a href="{{ route('stock-adjustments.index', ['tab' => $key, 'type' => request('type')]) }}"
                       class="whitespace-nowrap pb-3 px-1 text-sm font-medium border-b-2 transition
                       {{ $tab === $key ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                        {{ $label }}
                        <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-500">{{ $counts[$key] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
            <form method="GET" class="flex gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <select name="type" class="erp-input" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="positive" {{ request('type') == 'positive' ? 'selected' : '' }}>Positive</option>
                    <option value="negative" {{ request('type') == 'negative' ? 'selected' : '' }}>Negative</option>
                    <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                </select>
            </form>
        </div>

        <x-table-card :empty="count($adjustments) === 0" emptyMessage="No adjustments found." colspan="7">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Reason</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($adjustments as $adj)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-medium text-slate-800">
                            <a href="{{ route('stock-adjustments.show', $adj) }}" class="text-primary hover:text-primary/80 transition">{{ $adj->adjustment_number }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $adj->type === 'positive' ? 'bg-green-50 text-green-700' : ($adj->type === 'negative' ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700') }}">
                                {{ ucfirst($adj->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ ucfirst($adj->reason) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $adj->items->count() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $c = ['draft' => 'erp-badge-draft', 'pending_approval' => 'erp-badge-pending', 'approved' => 'erp-badge-approved', 'completed' => 'erp-badge-fulfilled', 'cancelled' => 'erp-badge-cancelled'];
                            @endphp
                            <span class="{{ $c[$adj->status] ?? 'erp-badge-draft' }}">{{ ucfirst(str_replace('_', ' ', $adj->status)) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $adj->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links
                                :view="route('stock-adjustments.show', $adj)"
                                :edit="$adj->status === 'draft' ? route('stock-adjustments.edit', $adj) : null"
                            />
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $adjustments->links() }}</div>
    </div>
</x-app-layout>
