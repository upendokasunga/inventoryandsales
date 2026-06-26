<x-app-layout>
    <x-slot name="header">{{ __('Inventory Analytics') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Total Products</p>
                <p class="text-2xl font-bold text-slate-800">{{ $stats['total_products'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Tracked Products</p>
                <p class="text-2xl font-bold text-slate-800">{{ $stats['tracked_products'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Total Qty On Hand</p>
                <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['total_quantity_on_hand'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Today's Transactions</p>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['today_transactions'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                <h3 class="text-sm font-medium text-slate-500 mb-4">Stock Status</h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-600">In Stock</span>
                            <span class="font-semibold text-green-600">{{ $stockDistribution['in_stock'] }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            @php $total = max(1, array_sum($stockDistribution)); @endphp
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ ($stockDistribution['in_stock'] / $total) * 100 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-600">Low Stock</span>
                            <span class="font-semibold text-amber-600">{{ $stockDistribution['low_stock'] }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-amber-500 h-2 rounded-full" style="width: {{ ($stockDistribution['low_stock'] / $total) * 100 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-600">Out of Stock</span>
                            <span class="font-semibold text-red-600">{{ $stockDistribution['out_of_stock'] }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-red-500 h-2 rounded-full" style="width: {{ ($stockDistribution['out_of_stock'] / $total) * 100 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-600">Not Tracked</span>
                            <span class="font-semibold text-slate-600">{{ $stockDistribution['not_tracked'] }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-slate-400 h-2 rounded-full" style="width: {{ ($stockDistribution['not_tracked'] / $total) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                <h3 class="text-sm font-medium text-slate-500 mb-4">Key Metrics</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-slate-500">Reserved Quantity</dt>
                        <dd class="text-sm font-semibold text-slate-800">{{ number_format($stats['total_reserved'], 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-slate-500">Low Stock Count</dt>
                        <dd class="text-sm font-semibold text-amber-600">{{ $stats['low_stock_count'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-slate-500">Out of Stock Count</dt>
                        <dd class="text-sm font-semibold text-red-600">{{ $stats['out_of_stock_count'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-slate-500">Active Batches</dt>
                        <dd class="text-sm font-semibold text-blue-600">{{ $stats['active_batches'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-slate-500">Total Inventory Value</dt>
                        <dd class="text-sm font-semibold text-slate-800">{{ number_format($stats['total_value'], 2) }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
