<x-app-layout>
    <x-slot name="header">Customer Advances</x-slot>

    <x-breadcrumbs :items="[['label' => 'Customer Advances']]" />

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Total Advances" :value="number_format($stats['total_advances'], 0)" />
        <x-stat-card label="Total Applied" :value="number_format($stats['total_applied'], 0)" />
        <x-stat-card label="Remaining Balance" :value="number_format($stats['total_balance'], 0)" />
        <x-stat-card label="Active Advances" :value="$stats['pending_count']" />
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Customer</label>
                <select name="customer_id" class="text-sm border border-slate-200 rounded-lg px-3 py-2">
                    <option value="">All Customers</option>
                    @foreach($customers as $id => $name)
                        <option value="{{ $id }}" @selected(request('customer_id') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="text-sm border border-slate-200 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="text-sm border border-slate-200 rounded-lg px-3 py-2">
            </div>
            <button type="submit" class="px-3 py-2 bg-slate-100 text-slate-700 text-sm rounded-lg hover:bg-slate-200 transition">Filter</button>
            <a href="{{ route('customer-advances.create') }}" class="px-3 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-600 transition ml-auto">Record Advance</a>
        </form>
    </div>

    {{-- Tabs --}}
    <x-status-tabs :tabs="[
        'all' => ['label' => 'All', 'count' => $advances->total()],
        'completed' => ['label' => 'Completed'],
        'partially_applied' => ['label' => 'Partially Applied'],
        'applied' => ['label' => 'Applied'],
        'cancelled' => ['label' => 'Cancelled'],
    ]" :current="$tab" />

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Advance #</th>
                    <th class="text-left px-4 py-3">Customer</th>
                    <th class="text-right px-4 py-3">Amount</th>
                    <th class="text-right px-4 py-3">Balance</th>
                    <th class="text-left px-4 py-3">Method</th>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($advances as $advance)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium">{{ $advance->advance_number }}</td>
                        <td class="px-4 py-3">{{ $advance->customer->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($advance->amount, 0) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($advance->balance, 0) }}</td>
                        <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $advance->payment_method) }}</td>
                        <td class="px-4 py-3">{{ $advance->advance_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-xs rounded-full font-medium
                                @if($advance->status === 'completed') bg-blue-100 text-blue-700
                                @elseif($advance->status === 'partially_applied') bg-purple-100 text-purple-700
                                @elseif($advance->status === 'applied') bg-green-100 text-green-700
                                @elseif($advance->status === 'cancelled') bg-red-100 text-red-700
                                @else bg-slate-100 text-slate-600 @endif">
                                {{ str_replace('_', ' ', ucfirst($advance->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('customer-advances.show', $advance) }}" class="text-primary hover:underline text-xs">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-400">No advances recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $advances->links() }}</div>
</x-app-layout>
