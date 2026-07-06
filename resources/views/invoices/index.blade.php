<x-app-layout>
    <x-slot name="header">Invoices</x-slot>
    <x-slot name="headerDescription">Manage all invoices, track payments, and monitor outstanding balances.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('invoices.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Invoice
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-stats-card title="Total Invoices" :value="$stats['total_invoices']" color="primary" />
            <x-stats-card title="Total Paid" :value="number_format($stats['total_paid'], 2)" color="success" />
            <x-stats-card title="Pending Balance" :value="number_format($stats['total_pending'], 2)" color="warning" />
            <x-stats-card title="Monthly Total" :value="number_format($stats['monthly_total'], 2)" color="info" />
        </div>

        <div class="erp-card mb-6">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="text-xs font-medium text-slate-500 block mb-1.5">Status</label>
                    <select name="status" class="erp-input">
                        <option value="">All</option>
                        @foreach(\App\Models\Invoice::STATUSES as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-500 block mb-1.5">Payment</label>
                    <select name="payment_status" class="erp-input">
                        <option value="">All</option>
                        @foreach(\App\Models\Invoice::PAYMENT_STATUSES as $ps)
                            <option value="{{ $ps }}" @selected(request('payment_status') === $ps)>{{ ucfirst($ps) }}</option>
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
                <div>
                    <label class="text-xs font-medium text-slate-500 block mb-1.5">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice # or customer..." class="erp-input w-56">
                </div>
                <button type="submit" class="erp-btn-primary">Filter</button>
                <a href="{{ route('invoices.index') }}" class="erp-btn-secondary">Reset</a>
            </form>
        </div>

        <x-table-card :empty="count($invoices) === 0" emptyMessage="No invoices found." colspan="9">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Invoice #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Paid</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Balance</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Payment</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($invoices as $invoice)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">{{ $invoice->invoice_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $invoice->customer->name ?? 'Walk-in' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $invoice->invoice_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 text-right font-medium">{{ number_format($invoice->total, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-success text-right">{{ number_format($invoice->amount_paid, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $invoice->balance_due > 0 ? 'text-warning-600 font-medium' : 'text-slate-400' }}">{{ number_format($invoice->balance_due, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php
                                $sc = ['draft' => 'erp-badge-draft', 'approved' => 'erp-badge-approved', 'completed' => 'erp-badge-fulfilled', 'cancelled' => 'erp-badge-cancelled'];
                            @endphp
                            <span class="{{ $sc[$invoice->status] ?? 'erp-badge-draft' }}">{{ ucfirst($invoice->status) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php
                                $pc = ['paid' => 'erp-badge-fulfilled', 'partial' => 'erp-badge-partial', 'overdue' => 'erp-badge-danger', 'pending' => 'erp-badge-draft'];
                            @endphp
                            <span class="{{ $pc[$invoice->payment_status] ?? 'erp-badge-draft' }}">{{ ucfirst($invoice->payment_status) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <x-action-links :view="route('invoices.show', $invoice)" />
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
        <div class="mt-4">
            {{ $invoices->appends(request()->query())->links() }}
        </div>
    </div>
</x-app-layout>
