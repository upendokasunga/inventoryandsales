<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Supplier Analytics') }}</h2>
            <form action="{{ route('purchasing.analytics.recalculate') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                    Recalculate Performance
                </button>
            </form>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-5 gap-4 mb-6">
            <x-stat-card label="Total POs" :value="$stats['total_pos']" />
            <x-stat-card label="Completed" :value="$stats['completed_pos']" />
            <x-stat-card label="Pending" :value="$stats['pending_pos']" />
            <x-stat-card label="Total Spent" :value="number_format($stats['total_spent'], 2)" />
            <x-stat-card label="Active Suppliers" :value="$stats['active_suppliers']" />
        </div>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <x-stat-card label="Avg Lead Time" :value="$stats['avg_lead_time'] . ' days'" />
            <x-stat-card label="Items Received" :value="$stats['total_items_received']" />
            <x-stat-card label="Avg Quality Rate" :value="$stats['avg_quality_rate'] . '%'" />
        </div>

        @if($stats['total_damaged'] > 0 || $stats['total_returned'] > 0)
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Damaged Items</p>
                <p class="text-2xl font-bold text-red-600">{{ $stats['total_damaged'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Returned Items</p>
                <p class="text-2xl font-bold text-orange-600">{{ $stats['total_returned'] }}</p>
            </div>
        </div>
        @endif

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
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">On-Time Rate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Accuracy</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Quality</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($rankings as $ranking)
                            @php
                                $perf = $performances[$ranking['supplier_id']] ?? null;
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $ranking['supplier']['name'] ?? 'Supplier #' . $ranking['supplier_id'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $ranking['order_count'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ number_format($ranking['total_spent'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ round($ranking['avg_lead_days'] ?? 0, 1) }} days</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        {{ ($perf['on_time_rate'] ?? 0) >= 90 ? 'bg-green-100 text-green-700' : (($perf['on_time_rate'] ?? 0) >= 70 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                        {{ $perf['on_time_rate'] ?? 0 }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        {{ ($perf['order_accuracy_rate'] ?? 0) >= 90 ? 'bg-green-100 text-green-700' : (($perf['order_accuracy_rate'] ?? 0) >= 70 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                        {{ $perf['order_accuracy_rate'] ?? 0 }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($perf)
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                            {{ ($perf['quality_rate'] ?? 0) >= 95 ? 'bg-green-100 text-green-700' : (($perf['quality_rate'] ?? 0) >= 80 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                            {{ $perf['quality_rate'] ?? 0 }}%
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-slate-500">No data yet.</td>
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
