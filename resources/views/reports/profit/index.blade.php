<x-app-layout>
    <x-slot name="header">Profit Analysis</x-slot>
    <x-breadcrumbs :items="[['label' => 'Reports'], ['label' => 'Profit Analysis']]" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-primary text-white text-sm rounded-lg">Filter</button>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Revenue</p>
            <p class="text-2xl font-bold text-primary">{{ number_format($grossProfit['revenue'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">COGS</p>
            <p class="text-2xl font-bold text-danger">{{ number_format($grossProfit['cost_of_goods_sold'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Gross Profit</p>
            <p class="text-2xl font-bold text-success">{{ number_format($grossProfit['gross_profit'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Margin</p>
            <p class="text-2xl font-bold text-{{ ($margin ?? 0) >= 20 ? 'success' : ($margin >= 10 ? 'warning' : 'danger') }}">{{ $margin ?? 0 }}%</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Top Margin Products</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Product</th><th class="py-2 font-medium text-right">Revenue</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($topMargin as $p)
                        <tr class="hover:bg-slate-50"><td class="py-2.5 text-slate-700">{{ $p['name'] ?? 'N/A' }}</td><td class="py-2.5 text-right text-slate-700">{{ number_format($p['revenue'] ?? 0, 2) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Low Margin Products</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Product</th><th class="py-2 font-medium text-right">Margin %</th><th class="py-2 font-medium text-right">Profit</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($lowMargin as $p)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 text-slate-700">{{ $p['product_name'] ?? 'N/A' }}</td>
                            <td class="py-2.5 text-right text-danger">{{ $p['margin_percent'] ?? 0 }}%</td>
                            <td class="py-2.5 text-right text-slate-700">{{ number_format($p['profit'] ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Product Profitability</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Product</th><th class="py-2 font-medium text-right">Profit</th><th class="py-2 font-medium text-right">Margin</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($productProfitability as $p)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 text-slate-700">{{ $p['product_name'] ?? 'N/A' }}</td>
                            <td class="py-2.5 text-right text-success">{{ number_format($p['profit'] ?? 0, 2) }}</td>
                            <td class="py-2.5 text-right text-slate-700">{{ $p['margin_percent'] ?? 0 }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Category Profitability</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Category</th><th class="py-2 font-medium text-right">Revenue</th><th class="py-2 font-medium text-right">Margin</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($categoryProfitability as $c)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 text-slate-700">{{ $c['category_name'] ?? 'N/A' }}</td>
                            <td class="py-2.5 text-right text-slate-700">{{ number_format($c['revenue'] ?? 0, 2) }}</td>
                            <td class="py-2.5 text-right text-{{ ($c['margin_percent'] ?? 0) >= 20 ? 'success' : 'warning' }}">{{ $c['margin_percent'] ?? 0 }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
