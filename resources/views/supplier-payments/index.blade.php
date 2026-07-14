<x-app-layout>
    <x-slot name="header">{{ __('Supplier Payments') }}</x-slot>
    <x-slot name="headerDescription">Manage payments to suppliers.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('supplier-payments.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Payment
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Total Payments</p>
                <p class="text-2xl font-bold text-slate-800">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Pending</p>
                <p class="text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Approved</p>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['approved'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Paid</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['paid'] }}</p>
            </div>
        </div>

        <x-table-card :empty="count($payments) === 0" emptyMessage="No supplier payments found." colspan="7">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Payment #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">PO #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($payments as $payment)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">
                            <a href="{{ route('supplier-payments.show', $payment) }}">{{ $payment->id }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $payment->supplier?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-800">{{ number_format($payment->amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $payment->purchaseOrder?->po_number ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $c = ['pending' => 'erp-badge-draft', 'approved' => 'erp-badge-info', 'paid' => 'erp-badge-fulfilled', 'rejected' => 'erp-badge-cancelled'];
                            @endphp
                            <span class="{{ $c[$payment->status] ?? 'erp-badge-draft' }}">{{ ucfirst($payment->status) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $payment->payment_date?->format('M d, Y') ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links :view="route('supplier-payments.show', $payment)" />
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $payments->links() }}</div>
    </div>
</x-app-layout>
