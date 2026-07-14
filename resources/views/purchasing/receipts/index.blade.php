<x-app-layout>
    <x-slot name="header">{{ __('Goods Receipts') }}</x-slot>
    <x-slot name="headerDescription">Track goods receipts against purchase orders.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('purchasing.receipts.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Goods Receipt
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-6 border-b border-slate-200/60">
            <nav class="flex gap-6 -mb-px overflow-x-auto">
                @php $tabs = ['all' => 'All', 'draft' => 'Draft', 'completed' => 'Completed', 'cancelled' => 'Cancelled']; @endphp
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('purchasing.receipts.index', ['tab' => $key] + request()->except(['tab', 'status'])) }}"
                       class="whitespace-nowrap pb-3 px-1 text-sm font-medium border-b-2 transition
                       {{ ($tab ?? 'all') === $key ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                        {{ $label }}
                        <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-500">{{ $stats[$key === 'all' ? 'total' : $key] ?? 0 }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <x-table-card :empty="count($receipts) === 0" emptyMessage="No goods receipts found." colspan="8">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Receipt #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">PO #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Store</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Created By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($receipts as $receipt)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                            <a href="{{ route('purchasing.receipts.show', $receipt) }}" class="text-primary hover:text-primary/80 transition">{{ $receipt->receipt_number ?? $receipt->id }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $receipt->purchaseOrder?->po_number ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $receipt->purchaseOrder?->supplier?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $receipt->warehouse?->name ?? 'Main Store' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $receipt->receipt_date?->format('M d, Y') ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $c = ['draft' => 'erp-badge-draft', 'completed' => 'erp-badge-fulfilled', 'cancelled' => 'erp-badge-cancelled'];
                            @endphp
                            <span class="{{ $c[$receipt->status] ?? 'erp-badge-draft' }}">{{ ucfirst($receipt->status) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $receipt->creator?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links
                                :view="route('purchasing.receipts.show', $receipt)"
                            />
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $receipts->links() }}</div>
    </div>
</x-app-layout>
