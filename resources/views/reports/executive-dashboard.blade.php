<x-app-layout>
    <x-slot name="header">Executive Dashboard</x-slot>
    <x-breadcrumbs :items="[['label' => 'Reports'], ['label' => 'Executive Dashboard']]" />

    {{-- KPI Cards Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Revenue</p>
            <p class="text-2xl font-bold text-primary mt-1">{{ number_format($summary['total_revenue'] ?? 0, 2) }}</p>
            <p class="text-xs text-green-600 mt-1">Monthly: {{ number_format($summary['monthly_sales'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Gross Profit</p>
            <p class="text-2xl font-bold text-success mt-1">{{ number_format($summary['gross_profit'] ?? 0, 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">Margin: {{ $summary['profit_margin'] ?? 0 }}%</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Inventory Value</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($summary['inventory_value'] ?? 0, 2) }}</p>
            <p class="text-xs text-orange-600 mt-1">Low Stock: {{ $summary['low_stock_items'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Receivables</p>
            <p class="text-2xl font-bold text-warning mt-1">{{ number_format($summary['outstanding_receivables'] ?? 0, 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">Outstanding</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Payables</p>
            <p class="text-2xl font-bold text-danger mt-1">{{ number_format($summary['outstanding_payables'] ?? 0, 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">Monthly Purchases: {{ number_format($summary['monthly_purchases'] ?? 0, 2) }}</p>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Sales Trend (12 Months)</h3>
            <canvas id="salesTrendChart" height="200"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Profit Trend (12 Months)</h3>
            <canvas id="profitTrendChart" height="200"></canvas>
        </div>
    </div>

    {{-- Pie Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Payment Methods</h3>
            <canvas id="paymentMethodChart" height="200"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Debt Exposure</h3>
            <canvas id="debtExposureChart" height="200"></canvas>
        </div>
    </div>

    {{-- Bottom Tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Return Trends</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200">
                            <th class="py-2 font-medium">Period</th>
                            <th class="py-2 font-medium text-right">Sales Returns</th>
                            <th class="py-2 font-medium text-right">Purchase Returns</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($returnTrends as $trend)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 text-slate-700">{{ $trend['month'] ?? $trend['period'] ?? '-' }}</td>
                            <td class="py-2.5 text-right text-slate-700">{{ number_format($trend['sales_returns'] ?? 0, 2) }}</td>
                            <td class="py-2.5 text-right text-slate-700">{{ number_format($trend['purchase_returns'] ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Daily KPIs</h3>
            <div class="grid grid-cols-2 gap-3">
                <div class="p-3 bg-slate-50 rounded-lg">
                    <p class="text-xs text-slate-500">Revenue</p>
                    <p class="text-lg font-bold text-primary">{{ number_format($dailyKpis['total_revenue'] ?? 0, 2) }}</p>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg">
                    <p class="text-xs text-slate-500">Gross Profit</p>
                    <p class="text-lg font-bold text-success">{{ number_format($dailyKpis['gross_profit'] ?? 0, 2) }}</p>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg">
                    <p class="text-xs text-slate-500">Margin</p>
                    <p class="text-lg font-bold text-blue-600">{{ $dailyKpis['profit_margin'] ?? 0 }}%</p>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg">
                    <p class="text-xs text-slate-500">Sales Growth</p>
                    <p class="text-lg font-bold text-{{ ($dailyKpis['sales_growth'] ?? 0) >= 0 ? 'success' : 'danger' }}">{{ number_format($dailyKpis['sales_growth'] ?? 0, 1) }}%</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js" integrity="sha384-9nhczxUqK87bcKHh20fSQcTGD4qq5GhayNYSYWqwBkINBhOfQLg/P5HG5lF1urn4" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new Chart(document.getElementById('salesTrendChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($salesTrend, 'month')) !!},
            datasets: [{
                label: 'Revenue',
                data: {!! json_encode(array_column($salesTrend, 'total')) !!},
                borderColor: '#1E4A92',
                backgroundColor: 'rgba(30,74,146,0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('profitTrendChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($profitTrend, 'month')) !!},
            datasets: [{
                label: 'Gross Profit',
                data: {!! json_encode(array_column($profitTrend, 'gross_profit')) !!},
                borderColor: '#18B87A',
                backgroundColor: 'rgba(24,184,122,0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('paymentMethodChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_column($paymentMethods, 'payment_method')) !!},
            datasets: [{
                data: {!! json_encode(array_column($paymentMethods, 'total')) !!},
                backgroundColor: ['#1E4A92', '#18B87A', '#F5A623', '#EF4444', '#6366F1']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('debtExposureChart'), {
        type: 'doughnut',
        data: {
            labels: ['Used Credit', 'Available Credit'],
            datasets: [{
                data: [{{ $debtExposure['total_exposure'] ?? 0 }}, {{ ($debtExposure['total_credit_limit'] ?? 0) - ($debtExposure['total_exposure'] ?? 0) }}],
                backgroundColor: ['#EF4444', '#E5E7EB']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
});
</script>
@endpush
