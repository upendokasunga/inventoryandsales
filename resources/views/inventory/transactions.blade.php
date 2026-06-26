<x-app-layout>
    <x-slot name="header">{{ __('Inventory Transactions') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-4">
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="product_id" value="{{ $filters['product_id'] ?? '' }}" placeholder="Product ID..." class="erp-input w-32">
                <select name="type" class="erp-input" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="purchase_receipt" {{ ($filters['type'] ?? '') == 'purchase_receipt' ? 'selected' : '' }}>Purchase Receipt</option>
                    <option value="sales_order" {{ ($filters['type'] ?? '') == 'sales_order' ? 'selected' : '' }}>Sales Order</option>
                    <option value="adjustment" {{ ($filters['type'] ?? '') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    <option value="transfer" {{ ($filters['type'] ?? '') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                </select>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="erp-input">
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="erp-input">
                <button type="submit" class="erp-btn-primary">Filter</button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Unit Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Total Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Balance After</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($transactions as $tx)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $tx->created_at->format('M d, H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $tx->product?->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $tx->type === 'purchase_receipt' ? 'bg-green-100 text-green-700' : ($tx->type === 'sales_order' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ ucfirst(str_replace('_', ' ', $tx->type)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $tx->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $tx->quantity > 0 ? '+' : '' }}{{ number_format($tx->quantity, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ number_format($tx->unit_cost, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ number_format($tx->total_cost, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ number_format($tx->balance_after, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $tx->creator?->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-slate-500">No transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">{{ $transactions->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
