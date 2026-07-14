<x-app-layout>
    <x-slot name="header">{{ __('Sales') }}</x-slot>
    <x-slot name="headerDescription">Manage sales invoices.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('sales.new') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Sale
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Total</p>
                <p class="text-2xl font-bold text-slate-800">{{ $stats['total_invoices'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Paid</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($stats['total_paid'], 0) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Pending</p>
                <p class="text-2xl font-bold text-amber-600">{{ number_format($stats['total_pending'], 0) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Monthly</p>
                <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['monthly_total'], 0) }}</p>
            </div>
        </div>

        <x-table-card :empty="count($invoices) === 0" emptyMessage="No invoices found." colspan="8">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Invoice #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Paid</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Payment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($invoices as $invoice)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('invoices.show', $invoice) }}" class="text-primary">{{ $invoice->invoice_number }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $invoice->customer?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $invoice->invoice_date?->format('M d, Y') ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-800">{{ number_format($invoice->total, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-800">{{ number_format($invoice->amount_paid, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $c = ['draft' => 'erp-badge-draft', 'proforma' => 'erp-badge-info', 'pending_approval' => 'erp-badge-warning', 'approved' => 'erp-badge-info', 'posted' => 'erp-badge-fulfilled', 'cancelled' => 'erp-badge-cancelled', 'reversed' => 'erp-badge-cancelled'];
                            @endphp
                            <span class="{{ $c[$invoice->status] ?? 'erp-badge-draft' }}">{{ str_replace('_', ' ', ucfirst($invoice->status)) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $p = ['pending' => 'erp-badge-draft', 'partial' => 'erp-badge-warning', 'paid' => 'erp-badge-fulfilled', 'overdue' => 'erp-badge-cancelled'];
                            @endphp
                            <span class="{{ $p[$invoice->payment_status] ?? 'erp-badge-draft' }}">{{ ucfirst($invoice->payment_status) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links :view="route('invoices.show', $invoice)" />
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $invoices->links() }}</div>
    </div>
</x-app-layout>
