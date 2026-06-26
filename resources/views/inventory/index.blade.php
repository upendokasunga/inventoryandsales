<x-app-layout>
    <x-slot name="header">{{ __('Inventory Dashboard') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Products with Stock</p>
                <p class="text-2xl font-bold text-slate-800">{{ $stats['products_with_stock'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Total Value</p>
                <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['total_value'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Low Stock Items</p>
                <p class="text-2xl font-bold text-amber-600">{{ $stats['low_stock_count'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Active Batches</p>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['active_batches'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                <h3 class="text-sm font-medium text-slate-500 mb-4">Stock Status Distribution</h3>
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

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                <h3 class="text-sm font-medium text-slate-500 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('inventory.transactions') }}" class="px-4 py-3 text-sm bg-slate-50 rounded-lg border border-slate-200 hover:border-primary hover:text-primary transition text-slate-700">View Transactions</a>
                    <a href="{{ route('inventory.valuation') }}" class="px-4 py-3 text-sm bg-slate-50 rounded-lg border border-slate-200 hover:border-primary hover:text-primary transition text-slate-700">Stock Valuation</a>
                    <a href="{{ route('inventory.batches') }}" class="px-4 py-3 text-sm bg-slate-50 rounded-lg border border-slate-200 hover:border-primary hover:text-primary transition text-slate-700">Batch Tracking</a>
                    <a href="{{ route('stock-adjustments.create') }}" class="px-4 py-3 text-sm bg-slate-50 rounded-lg border border-slate-200 hover:border-primary hover:text-primary transition text-slate-700">New Adjustment</a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <h3 class="text-sm font-medium text-slate-500 mb-4">Recent Transactions</h3>
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($recentTransactions as $tx)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $tx['created_at'] ? \Carbon\Carbon::parse($tx['created_at'])->format('M d, H:i') : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $tx['product']['name'] ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $tx['type'] === 'purchase_receipt' ? 'bg-green-100 text-green-700' : ($tx['type'] === 'sales_order' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ ucfirst(str_replace('_', ' ', $tx['type'])) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $tx['quantity'] > 0 ? '+' : '' }}{{ number_format($tx['quantity'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ number_format($tx['balance_after'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">No transactions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
