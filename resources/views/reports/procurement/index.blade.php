<x-app-layout>
    <x-slot name="header">Procurement Reports</x-slot>
    <x-breadcrumbs :items="[['label' => 'Reports'], ['label' => 'Procurement Reports']]" />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Monthly Spend</p>
            <p class="text-2xl font-bold text-primary">{{ number_format($monthlySpend['total_spend'] ?? 0, 2) }}</p>
            <p class="text-xs text-slate-400">{{ $monthlySpend['order_count'] ?? 0 }} orders</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Pending Approvals</p>
            <p class="text-2xl font-bold text-{{ ($pendingApprovals['pending_count'] ?? 0) > 0 ? 'warning' : 'success' }}">{{ $pendingApprovals['pending_count'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Total Orders</p>
            <p class="text-2xl font-bold text-slate-800">{{ $orderAnalysis['total_orders'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase font-semibold">Avg Order Value</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($orderAnalysis['average_order_value'] ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Purchase Trends</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Month</th><th class="py-2 font-medium text-right">Orders</th><th class="py-2 font-medium text-right">Total</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach(($trends['monthly_trends'] ?? $trends) as $t)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 text-slate-700">{{ $t['month'] ?? '-' }}</td>
                            <td class="py-2.5 text-right text-slate-700">{{ $t['order_count'] ?? 0 }}</td>
                            <td class="py-2.5 text-right font-medium text-primary">{{ number_format($t['total'] ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Pending Approvals</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200"><th class="py-2 font-medium">Reference</th><th class="py-2 font-medium text-right">Total</th><th class="py-2 font-medium">Type</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach(($pendingApprovals['items'] ?? []) as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 text-slate-700">{{ $item['reference'] ?? $item['order_number'] ?? '-' }}</td>
                            <td class="py-2.5 text-right text-slate-700">{{ number_format($item['total_amount'] ?? $item['total'] ?? 0, 2) }}</td>
                            <td class="py-2.5 text-slate-600">{{ $item['type'] ?? 'PO' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
