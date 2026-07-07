<x-app-layout>
    <x-slot name="header">{{ __('Stock Transfers') }}</x-slot>
    <x-slot name="headerDescription">Manage stock transfers between warehouses — create, approve, issue, and receive transfers.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('stock-transfers.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Stock Transfer
        </a>
    </x-slot>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 border-b border-slate-200">
            <nav class="flex space-x-4 -mb-px">
                @foreach (['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'issued' => 'Issued', 'received' => 'Received', 'rejected' => 'Rejected'] as $key => $label)
                    <a href="{{ route('stock-transfers.index', ['tab' => $key]) }}" class="pb-3 px-1 text-sm font-medium border-b-2 whitespace-nowrap {{ $tab === $key ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </div>
        <x-table-card :empty="count($stockTransfers) === 0" emptyMessage="No stock transfers found." colspan="8">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Transfer #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Source</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Destination</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Created By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($stockTransfers as $st)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-800">{{ $st->transfer_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $st->sourceWarehouse?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $st->destinationWarehouse?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php $c = ['pending' => 'erp-badge-pending', 'approved' => 'erp-badge-approved', 'issued' => 'erp-badge-info', 'received' => 'erp-badge-fulfilled', 'rejected' => 'erp-badge-cancelled']; @endphp
                            <span class="{{ $c[$st->status] ?? 'erp-badge-draft' }}">{{ ucfirst($st->status) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $st->items->count() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $st->creator?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $st->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links :view="route('stock-transfers.show', $st)" :edit="route('stock-transfers.edit', $st)" :delete="route('stock-transfers.destroy', $st)" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $stockTransfers->appends(['tab' => $tab])->links() }}</div>
    </div>
</x-app-layout>
