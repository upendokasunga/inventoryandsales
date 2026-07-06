<x-app-layout>
    <x-slot name="header">{{ __('Inventory Dashboard') }}</x-slot>
    <x-slot name="headerDescription">Monitor stock levels, values, and transaction activity across your warehouse.</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-stats-card title="Products with Stock" :value="$stats['products_with_stock']" color="primary" />
            <x-stats-card title="Total Value" :value="number_format($stats['total_value'], 2)" color="success" />
            <x-stats-card title="Low Stock Items" :value="$stats['low_stock_count']" color="warning" />
            <x-stats-card title="Active Batches" :value="$stats['active_batches']" color="info" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="erp-card">
                <h3 class="text-sm font-semibold text-slate-800 mb-4">Stock Status Distribution</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">In Stock</span>
                        <span class="text-sm font-semibold text-green-600">{{ $stockDistribution['in_stock'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Low Stock</span>
                        <span class="text-sm font-semibold text-amber-600">{{ $stockDistribution['low_stock'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Out of Stock</span>
                        <span class="text-sm font-semibold text-red-600">{{ $stockDistribution['out_of_stock'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Not Tracked</span>
                        <span class="text-sm font-semibold text-slate-600">{{ $stockDistribution['not_tracked'] }}</span>
                    </div>
                </div>
            </div>

            <div class="erp-card">
                <h3 class="text-sm font-semibold text-slate-800 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('inventory.transactions') }}" class="px-4 py-3 text-sm bg-slate-50 rounded-xl border border-slate-200 hover:border-primary hover:text-primary hover:bg-primary-50/30 transition-all text-slate-700 font-medium">View Transactions</a>
                    <a href="{{ route('inventory.valuation') }}" class="px-4 py-3 text-sm bg-slate-50 rounded-xl border border-slate-200 hover:border-primary hover:text-primary hover:bg-primary-50/30 transition-all text-slate-700 font-medium">Stock Valuation</a>
                    <a href="{{ route('inventory.batches') }}" class="px-4 py-3 text-sm bg-slate-50 rounded-xl border border-slate-200 hover:border-primary hover:text-primary hover:bg-primary-50/30 transition-all text-slate-700 font-medium">Batch Tracking</a>
                    <a href="{{ route('stock-adjustments.create') }}" class="px-4 py-3 text-sm bg-slate-50 rounded-xl border border-slate-200 hover:border-primary hover:text-primary hover:bg-primary-50/30 transition-all text-slate-700 font-medium">New Adjustment</a>
                </div>
            </div>
        </div>

        <x-table-card title="Recent Transactions" :empty="count($recentTransactions) === 0" emptyMessage="No transactions yet." colspan="5">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($recentTransactions as $tx)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $tx['created_at'] ? \Carbon\Carbon::parse($tx['created_at'])->format('M d, H:i') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $tx['product']['name'] ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $tx['type'] === 'purchase_receipt' ? 'bg-green-50 text-green-700' : ($tx['type'] === 'sales_order' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700') }}">
                                {{ ucfirst(str_replace('_', ' ', $tx['type'])) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 font-medium">{{ $tx['quantity'] > 0 ? '+' : '' }}{{ number_format($tx['quantity'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ number_format($tx['balance_after'], 2) }}</td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
    </div>
</x-app-layout>
