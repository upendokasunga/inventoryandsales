<x-app-layout>
    <x-slot name="header">{{ __('Inventory Valuation') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Total Inventory Value</p>
                <p class="text-2xl font-bold text-slate-800">{{ number_format($valuation['total_value'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Total Products</p>
                <p class="text-2xl font-bold text-slate-800">{{ $valuation['total_products'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Weighted Avg Cost</p>
                <p class="text-2xl font-bold text-slate-800">{{ number_format($valuation['weighted_average_cost'], 2) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-slate-500">Stock Valuation Details</h3>
                    <form method="GET" class="flex gap-2">
                        <select name="product_id" class="erp-input" onchange="this.form.submit()">
                            <option value="">All Products</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->sku }})</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Qty On Hand</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Avg Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Total Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($valuation['details'] as $detail)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $detail['product_name'] ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $detail['sku'] ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ number_format($detail['quantity_on_hand'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ number_format($detail['average_cost'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ number_format($detail['total_value'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">No stock on hand.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
