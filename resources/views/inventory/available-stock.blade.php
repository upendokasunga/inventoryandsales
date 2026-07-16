<x-app-layout>
    <x-slot name="header">{{ __('Available Stock') }}</x-slot>
    <x-slot name="headerDescription">Products with current stock on hand.</x-slot>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-slate-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Product name, SKU, or barcode..." class="erp-input">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Status</label>
                <select name="status" class="erp-input">
                    <option value="">All</option>
                    <option value="low" @selected($status === 'low')>Low Stock</option>
                    <option value="overstocked" @selected($status === 'overstocked')>Overstocked</option>
                </select>
            </div>
            <button type="submit" class="erp-btn-secondary erp-btn-sm">Filter</button>
            @if($search || $status)
                <a href="{{ route('inventory.available-stock') }}" class="erp-btn-ghost erp-btn-sm">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th class="text-right">On Hand</th>
                        <th class="text-right">Reserved</th>
                        <th class="text-right">Available</th>
                        <th class="text-right">Reorder Level</th>
                        <th>Status</th>
                        <th class="text-right">Unit Cost</th>
                        <th class="text-right">Total Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($balances as $balance)
                        @php
                            $product = $balance->product;
                            $onHand = (float) $balance->quantity_on_hand;
                            $reorder = (float) ($product->reorder_level ?? 0);
                            $safety = (float) ($product->safety_stock ?? 0);
                            $isLow = $reorder > 0 && $onHand <= $reorder;
                            $isOut = $onHand <= 0;
                            $isOver = $safety > 0 && $onHand > $safety * 3;
                        @endphp
                        <tr>
                            <td class="font-medium text-slate-800">{{ $product->name ?? '-' }}</td>
                            <td class="font-mono text-xs text-slate-500">{{ $product->sku ?? '-' }}</td>
                            <td class="text-right font-mono">{{ number_format($onHand, 2) }}</td>
                            <td class="text-right font-mono">{{ number_format((float) $balance->quantity_reserved, 2) }}</td>
                            <td class="text-right font-mono font-semibold">{{ number_format((float) $balance->quantity_available, 2) }}</td>
                            <td class="text-right font-mono text-slate-500">{{ $reorder > 0 ? number_format($reorder, 2) : '-' }}</td>
                            <td>
                                @if($isOut)
                                    <span class="erp-badge erp-badge-danger">Out of Stock</span>
                                @elseif($isLow)
                                    <span class="erp-badge erp-badge-warning">Low Stock</span>
                                @elseif($isOver)
                                    <span class="erp-badge erp-badge-info">Overstocked</span>
                                @else
                                    <span class="erp-badge erp-badge-active">In Stock</span>
                                @endif
                            </td>
                            <td class="text-right font-mono text-xs">TSh {{ number_format((float) ($balance->average_cost ?? 0), 0) }}</td>
                            <td class="text-right font-mono">TSh {{ number_format((float) ($balance->total_value ?? 0), 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-slate-400">No products with stock on hand.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $balances->links() }}</div>
</x-app-layout>
