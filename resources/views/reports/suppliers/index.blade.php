<x-app-layout>
    <x-slot name="header">Supplier Reports</x-slot>
    <x-breadcrumbs :items="[['label' => 'Reports'], ['label' => 'Supplier Reports']]" />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Total Spend</p>
            <p class="text-2xl font-bold text-primary">{{ number_format($purchaseTrends['total_spend'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Total Orders</p>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($purchaseTrends['total_orders'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Suppliers</p>
            <p class="text-2xl font-bold text-blue-600">{{ count($topSuppliers) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Avg Lead Time</p>
            <p class="text-2xl font-bold text-warning">{{ count($leadTime) > 0 ? number_format(array_sum(array_column($leadTime, 'avg_lead_time_days')) / count($leadTime), 1) : 0 }} days</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Supplier Performance</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Supplier</th><th class="py-2 font-medium text-right">On-Time %</th><th class="py-2 font-medium text-right">Avg Delay</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach(($performance['suppliers'] ?? []) as $s)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 text-slate-700">{{ $s['supplier_name'] }}</td>
                            <td class="py-2.5 text-right text-{{ ($s['on_time_rate'] ?? 0) >= 80 ? 'success' : 'danger' }}">{{ $s['on_time_rate'] ?? 0 }}%</td>
                            <td class="py-2.5 text-right text-slate-700">{{ $s['avg_delay_days'] ?? 0 }}d</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Lead Time Analysis</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Supplier</th><th class="py-2 font-medium text-right">Avg Days</th><th class="py-2 font-medium text-right">Min</th><th class="py-2 font-medium text-right">Max</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($leadTime as $l)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 text-slate-700">{{ $l['supplier_name'] }}</td>
                            <td class="py-2.5 text-right text-slate-700">{{ $l['avg_lead_time_days'] }}</td>
                            <td class="py-2.5 text-right text-green-600">{{ $l['min_lead_time_days'] }}</td>
                            <td class="py-2.5 text-right text-danger">{{ $l['max_lead_time_days'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <h3 class="text-sm font-semibold text-slate-700 mb-3">Top Suppliers</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Supplier</th><th class="py-2 font-medium text-right">Orders</th><th class="py-2 font-medium text-right">Total Spend</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($topSuppliers as $s)
                    <tr class="hover:bg-slate-50">
                        <td class="py-2.5 text-slate-700">{{ $s['name'] }}</td>
                        <td class="py-2.5 text-right text-slate-700">{{ $s['order_count'] ?? 0 }}</td>
                        <td class="py-2.5 text-right font-medium text-primary">{{ number_format($s['total_spend'] ?? 0, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
