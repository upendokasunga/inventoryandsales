<x-app-layout>
    <x-slot name="header">{{ __('Invoices') }}</x-slot>
    <x-slot name="headerDescription">Manage all invoices, track payments, and monitor outstanding balances.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('invoices.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Invoice
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-stats-card title="Total Outstanding" :value="number_format($stats['total_pending'], 2)" color="warning" />
            <x-stats-card title="Overdue Amount" :value="number_format(\App\Models\Invoice::whereIn('payment_status', ['pending', 'partial'])->where('invoice_date', '<', now())->sum('balance_due'), 2)" color="danger" />
            <x-stats-card title="Paid This Month" :value="number_format($stats['monthly_total'], 2)" color="success" />
            <x-stats-card title="Invoice Count" :value="$stats['total_invoices']" color="info" />
        </div>

        <div class="mb-6 border-b border-slate-200">
            <nav class="flex space-x-4 -mb-px overflow-x-auto" role="tablist">
                @foreach ([
                    'all' => 'All', 'proforma' => 'Proforma',
                    'pending_approval' => 'Pending', 'posted' => 'Posted',
                    'paid' => 'Paid', 'partial' => 'Partial', 'overdue' => 'Overdue',
                    'cancelled' => 'Cancelled', 'reversed' => 'Reversed',
                ] as $key => $label)
                    <a href="{{ route('invoices.index', array_merge(request()->except('tab', 'page'), ['tab' => $key])) }}"
                       role="tab"
                       aria-selected="{{ $tab === $key ? 'true' : 'false' }}"
                       class="pb-3 px-2 text-sm font-medium border-b-2 whitespace-nowrap {{ $tab === $key ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="erp-card mb-6">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div>
                    <label class="text-xs font-medium text-slate-500 block mb-1.5">Customer</label>
                    <select name="customer_id" class="erp-input">
                        <option value="">All Customers</option>
                        @foreach ($customers as $id => $name)
                            <option value="{{ $id }}" {{ request('customer_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
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
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice # or customer..." class="erp-input w-48">
                </div>
                <button type="submit" class="erp-btn-primary">Filter</button>
                <a href="{{ route('invoices.index') }}" class="erp-btn-secondary">Reset</a>
            </form>
        </div>

        <x-table-card :empty="count($invoices) === 0" emptyMessage="No invoices found matching your criteria." colspan="10">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Invoice #</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Customer</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Total</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Paid</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Balance</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Payment</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Due Date</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($invoices as $invoice)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-primary">
                            <a href="{{ route('invoices.show', $invoice) }}" class="hover:underline">{{ $invoice->invoice_number }}</a>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-500">{{ $invoice->invoice_date->format('d M Y') }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-600">{{ $invoice->customer?->name ?? 'Walk-in' }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-800 text-right font-medium">{{ number_format($invoice->total, 2) }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-success-600 text-right">{{ number_format($invoice->amount_paid, 2) }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-right {{ $invoice->balance_due > 0 ? 'text-warning-600 font-medium' : 'text-slate-400' }}">{{ number_format($invoice->balance_due, 2) }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-center">
                            @php
                                $sc = [
                                    'proforma' => 'erp-badge-pending',
                                    'pending_approval' => 'erp-badge-pending', 'approved' => 'erp-badge-approved',
                                    'posted' => 'erp-badge-fulfilled', 'completed' => 'erp-badge-fulfilled',
                                    'cancelled' => 'erp-badge-cancelled', 'reversed' => 'erp-badge-cancelled',
                                ];
                            @endphp
                            <span class="{{ $sc[$invoice->status] ?? 'erp-badge-pending' }}">{{ str_replace('_', ' ', ucfirst($invoice->status)) }}</span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-center">
                            @php
                                    $pc = ['paid' => 'erp-badge-fulfilled', 'partial' => 'erp-badge-partial', 'overdue' => 'erp-badge-danger', 'pending' => 'erp-badge-pending', 'cancelled' => 'erp-badge-cancelled'];
                            @endphp
                            <span class="{{ $pc[$invoice->payment_status] ?? 'erp-badge-draft' }}">{{ ucfirst($invoice->payment_status) }}</span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-slate-500">
                            @if ($invoice->due_date)
                                {{ $invoice->due_date->format('d M Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-slate-400 hover:text-primary transition p-1" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="text-slate-400 hover:text-sky-600 transition p-1" title="Print">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                                @if ($invoice->payment_status !== 'paid' && $invoice->status !== 'cancelled')
                                    <a href="{{ route('payments.create', $invoice) }}" class="text-slate-400 hover:text-success-600 transition p-1" title="Record Payment">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table-card>
        <div class="mt-4">
            {{ $invoices->appends(request()->query())->links() }}
        </div>
    </div>
</x-app-layout>
