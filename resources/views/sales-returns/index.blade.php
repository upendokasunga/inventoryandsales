<x-app-layout>
    <x-slot name="header">Sales Returns</x-slot>
    <x-slot name="headerDescription">Track customer returns, approvals, and refund processing.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('sales-returns.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Return
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-stats-card title="Total Returns" :value="$stats['total_returns']" color="primary" />
            <x-stats-card title="Pending" :value="$stats['pending_returns']" color="warning" />
            <x-stats-card title="Completed" :value="$stats['completed_returns']" color="success" />
            <x-stats-card title="Total Refund Value" :value="number_format($stats['total_refund_value'], 2)" color="danger" />
        </div>

        <div class="erp-card mb-6">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="text-xs font-medium text-slate-500 block mb-1.5">Status</label>
                    <select name="status" class="erp-input">
                        <option value="">All</option>
                        @foreach(\App\Models\SalesReturn::STATUSES as $s)
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
                <a href="{{ route('sales-returns.index') }}" class="erp-btn-secondary">Reset</a>
            </form>
        </div>

        <x-table-card :empty="count($returns) === 0" emptyMessage="No sales returns found." colspan="8">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Return #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Invoice</th>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $return->customer->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $return->invoice->invoice_number ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">{{ number_format($return->total_amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm capitalize text-slate-500">{{ $return->reason ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php
                                $sc = ['pending_approval' => 'erp-badge-pending', 'approved' => 'erp-badge-approved', 'rejected' => 'erp-badge-cancelled', 'completed' => 'erp-badge-fulfilled'];
                            @endphp
                            <span class="{{ $sc[$return->status] ?? 'erp-badge-pending' }}">{{ ucfirst(str_replace('_', ' ', $return->status)) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $return->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <x-action-links :view="route('sales-returns.show', $return)" />
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $returns->appends(request()->query())->links() }}</div>
    </div>
</x-app-layout>
