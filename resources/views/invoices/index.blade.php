<x-app-layout>
    <x-slot name="header">Invoices</x-slot>

    <x-breadcrumbs :items="[['label' => 'Sales'], ['label' => 'Invoices']]" />

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Total Invoices</p>
            <p class="text-lg font-bold text-slate-800">{{ $stats['total_invoices'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Total Paid</p>
            <p class="text-lg font-bold text-success">{{ number_format($stats['total_paid'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Pending Balance</p>
            <p class="text-lg font-bold text-warning">{{ number_format($stats['total_pending'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Monthly Total</p>
            <p class="text-lg font-bold text-primary">{{ number_format($stats['monthly_total'], 2) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs text-slate-500 block mb-1">Status</label>
                <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach(\App\Models\Invoice::STATUSES as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-500 block mb-1">Payment</label>
                <select name="payment_status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach(\App\Models\Invoice::PAYMENT_STATUSES as $ps)
                        <option value="{{ $ps }}" @selected(request('payment_status') === $ps)>{{ ucfirst($ps) }}</option>
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
            <div>
                <label class="text-xs text-slate-500 block mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice # or customer..." class="border border-slate-300 rounded-lg px-3 py-2 text-sm w-48">
            </div>
            <button type="submit" class="px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-600 transition">Filter</button>
            <a href="{{ route('invoices.index') }}" class="px-4 py-2 border border-slate-200 text-slate-600 text-sm rounded-lg hover:bg-slate-50 transition">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-slate-700">All Invoices</h3>
            <a href="{{ route('invoices.create') }}" class="px-3 py-1.5 bg-primary text-white text-xs rounded-lg hover:bg-primary-600 transition">+ New</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">Invoice #</th>
                        <th class="text-left px-4 py-3">Customer</th>
                        <th class="text-left px-4 py-3">Date</th>
                        <th class="text-right px-4 py-3">Total</th>
                        <th class="text-right px-4 py-3">Paid</th>
                        <th class="text-right px-4 py-3">Balance</th>
                        <th class="text-center px-4 py-3">Status</th>
                        <th class="text-center px-4 py-3">Payment</th>
                        <th class="text-center px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($invoices as $invoice)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-primary">{{ $invoice->invoice_number }}</td>
                            <td class="px-4 py-3">{{ $invoice->customer->name ?? 'Walk-in' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $invoice->invoice_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($invoice->total, 2) }}</td>
                            <td class="px-4 py-3 text-right text-success">{{ number_format($invoice->amount_paid, 2) }}</td>
                            <td class="px-4 py-3 text-right {{ $invoice->balance_due > 0 ? 'text-warning' : 'text-slate-400' }}">{{ number_format($invoice->balance_due, 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs rounded-full font-medium
                                    @if($invoice->status === 'draft') bg-slate-100 text-slate-600
                                    @elseif($invoice->status === 'approved') bg-blue-100 text-blue-700
                                    @elseif($invoice->status === 'completed') bg-green-100 text-green-700
                                    @elseif($invoice->status === 'cancelled') bg-red-100 text-red-700
                                    @else bg-orange-100 text-orange-700 @endif">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs rounded-full font-medium
                                    @if($invoice->payment_status === 'paid') bg-green-100 text-green-700
                                    @elseif($invoice->payment_status === 'partial') bg-purple-100 text-purple-700
                                    @elseif($invoice->payment_status === 'overdue') bg-red-800 text-white
                                    @else bg-slate-100 text-slate-600 @endif">
                                    {{ ucfirst($invoice->payment_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-primary hover:text-primary-600 text-xs font-medium">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-8 text-center text-slate-400">No invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $invoices->appends(request()->query())->links() }}
        </div>
    </div>
</x-app-layout>
