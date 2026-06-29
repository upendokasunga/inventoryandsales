<x-app-layout>
    <x-slot name="header">Sales Returns</x-slot>

    <x-breadcrumbs :items="[['label' => 'Returns'], ['label' => 'Sales Returns']]" />

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Total Returns</p>
            <p class="text-lg font-bold text-slate-800">{{ $stats['total_returns'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Pending</p>
            <p class="text-lg font-bold text-warning">{{ $stats['pending_returns'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Completed</p>
            <p class="text-lg font-bold text-success">{{ $stats['completed_returns'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Total Refund Value</p>
            <p class="text-lg font-bold text-danger">{{ number_format($stats['total_refund_value'], 2) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs text-slate-500 block mb-1">Status</label>
                <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach(\App\Models\SalesReturn::STATUSES as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
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
            <a href="{{ route('sales-returns.index') }}" class="px-4 py-2 border border-slate-200 text-slate-600 text-sm rounded-lg">Reset</a>
            <a href="{{ route('sales-returns.create') }}" class="px-4 py-2 bg-success text-white text-sm rounded-lg">+ New Return</a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">Return #</th>
                        <th class="text-left px-4 py-3">Customer</th>
                        <th class="text-left px-4 py-3">Invoice</th>
                        <th class="text-right px-4 py-3">Amount</th>
                        <th class="text-left px-4 py-3">Reason</th>
                        <th class="text-center px-4 py-3">Status</th>
                        <th class="text-left px-4 py-3">Date</th>
                        <th class="text-center px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($returns as $return)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-primary">{{ $return->return_number }}</td>
                            <td class="px-4 py-3">{{ $return->customer->name }}</td>
                            <td class="px-4 py-3">{{ $return->invoice->invoice_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($return->total_amount, 2) }}</td>
                            <td class="px-4 py-3 capitalize text-slate-500">{{ $return->reason ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs rounded-full font-medium
                                    @if($return->status === 'draft') bg-slate-100 text-slate-600
                                    @elseif($return->status === 'pending_approval') bg-orange-100 text-orange-700
                                    @elseif($return->status === 'approved') bg-blue-100 text-blue-700
                                    @elseif($return->status === 'rejected') bg-red-100 text-red-700
                                    @else bg-green-100 text-green-700 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $return->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $return->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-center"><a href="{{ route('sales-returns.show', $return) }}" class="text-primary hover:underline text-xs">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">No sales returns found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">{{ $returns->appends(request()->query())->links() }}</div>
    </div>
</x-app-layout>
