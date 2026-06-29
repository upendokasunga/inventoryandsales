<x-app-layout>
    <x-slot name="header">Refunds</x-slot>

    <x-breadcrumbs :items="[['label' => 'Returns'], ['label' => 'Refunds']]" />

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Total Refunds</p>
            <p class="text-lg font-bold text-slate-800">{{ $stats['total_refunds'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Total Amount</p>
            <p class="text-lg font-bold text-danger">{{ number_format($stats['total_amount'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Cash Refunds</p>
            <p class="text-lg font-bold text-slate-800">{{ number_format($stats['cash_refunds'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Store Credits</p>
            <p class="text-lg font-bold text-success">{{ number_format($stats['store_credits'], 2) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs text-slate-500 block mb-1">Method</label>
                <select name="refund_method" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="cash" @selected(request('refund_method') === 'cash')>Cash</option>
                    <option value="store_credit" @selected(request('refund_method') === 'store_credit')>Store Credit</option>
                    <option value="bank_transfer" @selected(request('refund_method') === 'bank_transfer')>Bank Transfer</option>
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
            <a href="{{ route('refunds.index') }}" class="px-4 py-2 border border-slate-200 text-slate-600 text-sm rounded-lg">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">CN #</th>
                        <th class="text-left px-4 py-3">Customer</th>
                        <th class="text-right px-4 py-3">Amount</th>
                        <th class="text-left px-4 py-3">Method</th>
                        <th class="text-left px-4 py-3">Date</th>
                        <th class="text-center px-4 py-3">Status</th>
                        <th class="text-center px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($refunds as $refund)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-primary">{{ $refund->credit_note_number }}</td>
                            <td class="px-4 py-3">{{ $refund->customer->name }}</td>
                            <td class="px-4 py-3 text-right font-medium text-success">{{ number_format($refund->amount, 2) }}</td>
                            <td class="px-4 py-3 capitalize">{{ $refund->refund_method ? str_replace('_', ' ', $refund->refund_method) : '-' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $refund->issued_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs rounded-full font-medium @if($refund->status === 'applied') bg-green-100 text-green-700 @else bg-blue-100 text-blue-700 @endif">
                                    {{ ucfirst($refund->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('credit-notes.show', $refund) }}" class="text-primary hover:underline text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">No refunds processed.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">{{ $refunds->appends(request()->query())->links() }}</div>
    </div>
</x-app-layout>
