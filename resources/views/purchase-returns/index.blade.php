<x-app-layout>
    <x-slot name="header">Purchase Returns</x-slot>
    <x-slot name="headerDescription">Track returns to suppliers and monitor refund processing.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('purchase-returns.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Return
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-stats-card title="Total Returns" :value="number_format($stats['total_returns'] ?? 0)" color="primary" />
            <x-stats-card title="Pending" :value="number_format($stats['pending_returns'] ?? 0)" color="warning" />
            <x-stats-card title="Approved" :value="number_format($stats['approved_returns'] ?? 0)" color="info" />
            <x-stats-card title="Total Amount" :value="number_format($stats['total_amount'] ?? 0, 2)" color="success" />
        </div>

        <div class="erp-card mb-6">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="text-xs font-medium text-slate-500 block mb-1.5">Status</label>
                    <select name="status" class="erp-input">
                        <option value="">All</option>
                        @foreach(\App\Models\PurchaseReturn::STATUSES as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-500 block mb-1.5">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="erp-input">
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-500 block mb-1.5">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="erp-input">
                </div>
                <button type="submit" class="erp-btn-primary">Filter</button>
                <a href="{{ route('purchase-returns.index') }}" class="erp-btn-secondary">Reset</a>
            </form>
        </div>

        <x-table-card :empty="count($returns) === 0" emptyMessage="No purchase returns found." colspan="8">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Return #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">PO</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Reason</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($returns as $return)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">{{ $return->return_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $return->supplier->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $return->purchaseOrder->po_number ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">{{ number_format($return->total_amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm capitalize text-slate-500">{{ $return->reason ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php
                                $sc = ['draft' => 'erp-badge-draft', 'pending_approval' => 'erp-badge-pending', 'approved' => 'erp-badge-approved', 'rejected' => 'erp-badge-cancelled', 'completed' => 'erp-badge-fulfilled'];
                            @endphp
                            <span class="{{ $sc[$return->status] ?? 'erp-badge-draft' }}">{{ ucfirst(str_replace('_', ' ', $return->status)) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $return->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <x-action-links :view="route('purchase-returns.show', $return)" />
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $returns->appends(request()->query())->links() }}</div>
    </div>
</x-app-layout>
