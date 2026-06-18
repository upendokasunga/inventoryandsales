<x-app-layout>
    <x-slot name="header">
        {{ __('Supplier Analytics') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Total POs</p>
                <p class="text-2xl font-bold text-slate-800">{{ $stats['total_pos'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Completed</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['completed_pos'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Pending</p>
                <p class="text-2xl font-bold text-amber-600">{{ $stats['pending_pos'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Total Spent</p>
                <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['total_spent'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Active Suppliers</p>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['active_suppliers'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Avg Lead Time</p>
                <p class="text-2xl font-bold text-slate-800">{{ $stats['avg_lead_time'] }} days</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
            <div class="p-6">
                <h3 class="text-sm font-medium text-slate-500 mb-4">Supplier Rankings</h3>
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Orders</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Total Spent</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Avg Lead Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($rankings as $ranking)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $ranking['supplier']['name'] ?? 'Supplier #' . $ranking['supplier_id'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $ranking['order_count'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ number_format($ranking['total_spent'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ round($ranking['avg_lead_days'] ?? 0, 1) }} days</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-slate-500">No data yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <h3 class="text-sm font-medium text-slate-500 mb-4">Purchase Trends (Last 6 Months)</h3>
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Month</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Orders</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($trends as $trend)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $trend['month'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $trend['order_count'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ number_format($trend['total_amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-slate-500">No trend data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
