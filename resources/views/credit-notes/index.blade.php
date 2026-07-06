<x-app-layout>
    <x-slot name="header">Credit Notes</x-slot>
    <x-slot name="headerDescription">Manage credit notes issued to customers for returns and adjustments.</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-stats-card title="Issued" :value="$stats['total_issued']" color="primary" />
            <x-stats-card title="Applied" :value="$stats['total_applied']" color="success" />
            <x-stats-card title="Total Amount" :value="number_format($stats['total_amount'], 2)" color="warning" />
        </div>

        <div class="erp-card mb-6">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="text-xs font-medium text-slate-500 block mb-1.5">Status</label>
                    <select name="status" class="erp-input">
                        <option value="">All</option>
                        @foreach(\App\Models\CreditNote::STATUSES as $s)
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
                <a href="{{ route('credit-notes.index') }}" class="erp-btn-secondary">Reset</a>
            </form>
        </div>

        <x-table-card :empty="count($creditNotes) === 0" emptyMessage="No credit notes found." colspan="8">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">CN #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Return</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Method</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($creditNotes as $cn)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">{{ $cn->credit_note_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $cn->customer->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $cn->salesReturn->return_number ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-success">{{ number_format($cn->amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm capitalize text-slate-500">{{ $cn->refund_method ? str_replace('_', ' ', $cn->refund_method) : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php
                                $sc = ['issued' => 'erp-badge-approved', 'applied' => 'erp-badge-fulfilled', 'cancelled' => 'erp-badge-cancelled'];
                            @endphp
                            <span class="{{ $sc[$cn->status] ?? 'erp-badge-draft' }}">{{ ucfirst($cn->status) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $cn->issued_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <x-action-links :view="route('credit-notes.show', $cn)" />
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $creditNotes->appends(request()->query())->links() }}</div>
    </div>
</x-app-layout>
