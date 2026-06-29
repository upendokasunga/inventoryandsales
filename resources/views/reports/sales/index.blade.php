<x-app-layout>
    <x-slot name="header">Sales Report</x-slot>
    <x-breadcrumbs :items="[['label' => 'Reports'], ['label' => 'Sales Report']]" />

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
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-600">Filter</button>
                <a href="{{ route('reports.sales.pdf', request()->query()) }}" class="px-4 py-2 bg-danger text-white text-sm rounded-lg hover:bg-danger-600">PDF</a>
                <a href="{{ route('reports.sales.excel', request()->query()) }}" class="px-4 py-2 bg-success text-white text-sm rounded-lg hover:bg-success-600">Excel</a>
                <a href="{{ route('reports.sales.csv', request()->query()) }}" class="px-4 py-2 bg-slate-600 text-white text-sm rounded-lg hover:bg-slate-700">CSV</a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Total Sales</p>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($summary['total_sales'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Revenue</p>
            <p class="text-2xl font-bold text-primary">{{ number_format($summary['total_revenue'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Gross Profit</p>
            <p class="text-2xl font-bold text-success">{{ number_format($summary['gross_profit'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Avg Order Value</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($summary['average_order_value'] ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Top Products</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Product</th><th class="py-2 font-medium text-right">Qty</th><th class="py-2 font-medium text-right">Revenue</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($topProducts as $p)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 text-slate-700">{{ $p['product_name'] ?? $p['product']['name'] ?? 'N/A' }}</td>
                            <td class="py-2.5 text-right text-slate-700">{{ number_format($p['total_quantity'] ?? 0) }}</td>
                            <td class="py-2.5 text-right text-slate-700">{{ number_format($p['total_revenue'] ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Top Customers</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Customer</th><th class="py-2 font-medium text-right">Orders</th><th class="py-2 font-medium text-right">Total</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($topCustomers as $c)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 text-slate-700">{{ $c['customer_name'] ?? $c['customer']['name'] ?? 'N/A' }}</td>
                            <td class="py-2.5 text-right text-slate-700">{{ number_format($c['total_orders'] ?? 0) }}</td>
                            <td class="py-2.5 text-right text-slate-700">{{ number_format($c['total_spent'] ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <h3 class="text-sm font-semibold text-slate-700 mb-3">Payment Method Breakdown</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Method</th><th class="py-2 font-medium text-right">Count</th><th class="py-2 font-medium text-right">Total</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($paymentMethods as $pm)
                    <tr class="hover:bg-slate-50">
                        <td class="py-2.5 text-slate-700 capitalize">{{ str_replace('_', ' ', $pm['payment_method']) }}</td>
                        <td class="py-2.5 text-right text-slate-700">{{ number_format($pm['count']) }}</td>
                        <td class="py-2.5 text-right text-slate-700">{{ number_format($pm['total'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
