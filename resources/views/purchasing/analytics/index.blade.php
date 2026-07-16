<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">{{ __('Supplier Analytics') }}</h2>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="flex justify-end mb-4">
            <form action="{{ route('purchasing.analytics.recalculate') }}" method="POST">
                @csrf
                <button type="submit" class="erp-btn-primary">
                    Recalculate Performance
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
            <x-stat-card label="Total POs" :value="$stats['total_pos']" />
            <x-stat-card label="Completed" :value="$stats['completed_pos']" />
            <x-stat-card label="Pending" :value="$stats['pending_pos']" />
            <x-stat-card label="Total Spent" value="{{ 'TSh ' . number_format($stats['total_spent'], 0) }}" />
            <x-stat-card label="Active Suppliers" :value="$stats['active_suppliers']" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-stat-card label="Avg Lead Time" :value="$stats['avg_lead_time'] . ' days'" />
            <x-stat-card label="Items Received" :value="$stats['total_items_received']" />
            <x-stat-card label="Avg Quality Rate" :value="$stats['avg_quality_rate'] . '%'" />
        </div>

        @if($stats['total_damaged'] > 0 || $stats['total_returned'] > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
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
                <div class="overflow-x-auto">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Orders</th>
                            <th>Total Spent</th>
                            <th>Avg Lead Time</th>
                            <th>On-Time Rate</th>
                            <th>Accuracy</th>
                            <th>Quality</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rankings as $ranking)
                            @php
                                $perf = $performances[$ranking['supplier_id']] ?? null;
                            @endphp
                            <tr>
                                <td class="font-medium text-slate-800">{{ $ranking['supplier']['name'] ?? 'Supplier #' . $ranking['supplier_id'] }}</td>
                                <td class="text-slate-500">{{ $ranking['order_count'] }}</td>
                                <td class="text-slate-800">TSh {{ number_format($ranking['total_spent'], 0) }}</td>
                                <td class="text-slate-500">{{ round($ranking['avg_lead_days'] ?? 0, 1) }} days</td>
                                <td>
                                    <span class="erp-badge {{ ($perf['on_time_rate'] ?? 0) >= 90 ? 'bg-green-100 text-green-700' : (($perf['on_time_rate'] ?? 0) >= 70 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                        {{ $perf['on_time_rate'] ?? 0 }}%
                                    </span>
                                </td>
                                <td>
                                    <span class="erp-badge {{ ($perf['order_accuracy_rate'] ?? 0) >= 90 ? 'bg-green-100 text-green-700' : (($perf['order_accuracy_rate'] ?? 0) >= 70 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                        {{ $perf['order_accuracy_rate'] ?? 0 }}%
                                    </span>
                                </td>
                                <td>
                                    @if($perf)
                                        <span class="erp-badge {{ ($perf['quality_rate'] ?? 0) >= 95 ? 'bg-green-100 text-green-700' : (($perf['quality_rate'] ?? 0) >= 80 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                            {{ $perf['quality_rate'] ?? 0 }}%
                                        </span>
                                    @else
                                        <span class="text-slate-400">&mdash;</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-slate-500">No data yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <h3 class="text-sm font-medium text-slate-500 mb-4">Purchase Trends (Last 6 Months)</h3>
                <div class="overflow-x-auto">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Orders</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($trends as $trend)
                            <tr>
                                <td class="font-medium text-slate-800">{{ $trend['month'] }}</td>
                                <td class="text-slate-500">{{ $trend['order_count'] }}</td>
                                <td class="text-slate-800">TSh {{ number_format($trend['total_amount'], 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-slate-500">No trend data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
