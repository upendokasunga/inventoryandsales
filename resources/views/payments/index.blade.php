<x-app-layout>
    <x-slot name="header">Payments</x-slot>

    <x-breadcrumbs :items="[['label' => 'Payments']]" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs text-slate-500 block mb-1">Method</label>
                <select name="payment_method" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach($methods as $m)
                        <option value="{{ $m }}" @selected(request('payment_method') === $m)>{{ ucfirst(str_replace('_', ' ', $m)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-500 block mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs text-slate-500 block mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-primary text-white text-sm rounded-lg">Filter</button>
            <a href="{{ route('payments.index') }}" class="px-4 py-2 border border-slate-200 text-slate-600 text-sm rounded-lg">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">Date</th>
                        <th class="text-left px-4 py-3">Invoice</th>
                        <th class="text-left px-4 py-3">Customer</th>
                        <th class="text-left px-4 py-3">Method</th>
                        <th class="text-right px-4 py-3">Amount</th>
                        <th class="text-left px-4 py-3">Reference</th>
                        <th class="text-left px-4 py-3">Received By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($payments as $payment)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">{{ $payment->payment_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('invoices.show', $payment->invoice) }}" class="text-primary hover:underline">{{ $payment->invoice->invoice_number }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $payment->customer->name ?? 'Walk-in' }}</td>
                            <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-success">{{ number_format($payment->amount, 2) }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $payment->reference_number ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $payment->receiver?->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">No payments recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">{{ $payments->appends(request()->query())->links() }}</div>
    </div>
</x-app-layout>
