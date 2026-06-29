<x-app-layout>
    <x-slot name="header">KPI Dashboard</x-slot>
    <x-breadcrumbs :items="[['label' => 'Reports'], ['label' => 'KPI Dashboard']]" />

    <div class="mb-4 flex gap-2">
        @foreach(['daily', 'weekly', 'monthly', 'quarterly', 'annual'] as $p)
            <a href="{{ route('reports.kpi', ['period' => $p]) }}" class="px-4 py-2 text-sm rounded-lg transition {{ $period === $p ? 'bg-primary text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                {{ ucfirst($p) }}
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Revenue</p>
            <p class="text-2xl font-bold text-primary">{{ number_format($kpis['total_revenue'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Gross Profit</p>
            <p class="text-2xl font-bold text-success">{{ number_format($kpis['gross_profit'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Profit Margin</p>
            <p class="text-2xl font-bold text-blue-600">{{ $kpis['profit_margin'] ?? 0 }}%</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Sales Growth</p>
            <p class="text-2xl font-bold text-{{ ($kpis['sales_growth'] ?? 0) >= 0 ? 'success' : 'danger' }}">{{ number_format($kpis['sales_growth'] ?? 0, 1) }}%</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Inventory Turnover</p>
            <p class="text-2xl font-bold text-warning">{{ number_format($kpis['inventory_turnover'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Customer Growth</p>
            <p class="text-2xl font-bold text-{{ ($kpis['customer_growth'] ?? 0) >= 0 ? 'success' : 'danger' }}">{{ number_format($kpis['customer_growth'] ?? 0, 1) }}%</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Debt Ratio</p>
            <p class="text-2xl font-bold text-{{ ($kpis['debt_ratio'] ?? 0) < 50 ? 'success' : ($kpis['debt_ratio'] < 75 ? 'warning' : 'danger') }}">{{ number_format($kpis['debt_ratio'] ?? 0, 1) }}%</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Total Sales</p>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($kpis['total_sales'] ?? 0) }}</p>
        </div>
    </div>
</x-app-layout>
