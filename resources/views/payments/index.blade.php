<x-app-layout>
    <x-slot name="header">Payments</x-slot>
    <x-slot name="headerDescription">Track all incoming payments across invoices and customers.</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="erp-card mb-6">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="text-xs font-medium text-slate-500 block mb-1.5">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="erp-input">
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-500 block mb-1.5">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="erp-input">
                </div>
                <button type="submit" class="erp-btn-primary">Filter</button>
                <a href="{{ route('payments.index') }}" class="erp-btn-secondary">Reset</a>
            </form>
        </div>

        <x-table-card :empty="count($payments) === 0" emptyMessage="No payments recorded." colspan="7">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Invoice</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Account</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Received By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($payments as $payment)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $payment->payment_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('invoices.show', $payment->invoice) }}" class="text-primary hover:text-primary/80 transition">{{ $payment->invoice->invoice_number }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $payment->customer->name ?? 'Walk-in' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm capitalize text-slate-500">{{ $payment->account?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-success">{{ number_format($payment->amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $payment->reference_number ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $payment->receiver?->name ?? 'System' }}</td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $payments->appends(request()->query())->links() }}</div>
    </div>
</x-app-layout>
