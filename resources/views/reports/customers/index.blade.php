<x-app-layout>
    <x-slot name="header">Customer Reports</x-slot>
    <x-breadcrumbs :items="[['label' => 'Reports'], ['label' => 'Customer Reports']]" />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Outstanding Debts</p>
            <p class="text-2xl font-bold text-danger">{{ number_format($outstanding['total_outstanding'] ?? 0, 2) }}</p>
            <p class="text-xs text-slate-400">{{ $outstanding['customer_count'] ?? 0 }} customers</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Credit Utilization</p>
            <p class="text-2xl font-bold text-{{ ($outstanding['utilization_rate'] ?? 0) < 50 ? 'success' : 'warning' }}">{{ $outstanding['utilization_rate'] ?? 0 }}%</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Customers at Risk</p>
            <p class="text-2xl font-bold text-{{ ($creditExposure['at_risk_count'] ?? 0) > 0 ? 'danger' : 'success' }}">{{ $creditExposure['at_risk_count'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Overdue Customers</p>
            <p class="text-2xl font-bold text-danger">{{ count($overdue) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Top Customers</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Customer</th><th class="py-2 font-medium text-right">Orders</th><th class="py-2 font-medium text-right">Total</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($topCustomers as $c)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 text-slate-700">{{ $c['name'] }}</td>
                            <td class="py-2.5 text-right text-slate-700">{{ $c['order_count'] ?? 0 }}</td>
                            <td class="py-2.5 text-right font-medium text-primary">{{ number_format($c['total_purchases'] ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-danger mb-3">Overdue Customers</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Customer</th><th class="py-2 font-medium text-right">Invoices</th><th class="py-2 font-medium text-right">Total</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($overdue as $o)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 text-slate-700">{{ $o['customer_name'] }}</td>
                            <td class="py-2.5 text-right text-slate-700">{{ $o['overdue_count'] ?? 0 }}</td>
                            <td class="py-2.5 text-right text-danger font-medium">{{ number_format($o['total_overdue'] ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
