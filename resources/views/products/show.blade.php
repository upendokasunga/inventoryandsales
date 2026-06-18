<x-app-layout>
    <x-slot name="header">
        {{ $product->name }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="mb-4 flex gap-2">
            <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 erp-btn-secondary">
                Back to List
            </a>
            <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center px-4 py-2 erp-btn-primary">
                Edit Product
            </a>
            <a href="{{ route('products.print-barcode', $product) }}" target="_blank" class="inline-flex items-center px-4 py-2 erp-btn-secondary">
                Print Barcode
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="px-6 py-4 border-b border-blue-100">
                        <h3 class="text-lg font-semibold text-slate-800">Basic Information</h3>
                    </div>
                    <div class="p-6 grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">SKU</span>
                            <p class="mt-1 text-sm font-mono text-slate-800">{{ $product->sku }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Barcode</span>
                            <p class="mt-1 text-sm font-mono text-slate-800">
                                {{ $product->barcode ?? '-' }}
                                @if ($product->barcode_image)
                                    <br><img src="{{ Storage::url($product->barcode_image) }}" alt="{{ $product->barcode }}" class="mt-1 h-10">
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Category</span>
                            <p class="mt-1 text-sm text-slate-800">{{ $product->category?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Weight</span>
                            <p class="mt-1 text-sm text-slate-800">{{ $product->weight ? $product->weight . ' kg' : '-' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Tax Rate</span>
                            <p class="mt-1 text-sm text-slate-800">{{ $product->tax_rate }}% {{ $product->tax_inclusive ? '(inclusive)' : '(exclusive)' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Status</span>
                            <p class="mt-1">
                                @if ($product->is_active)
                                    <span class="erp-badge-active">Active</span>
                                @else
                                    <span class="erp-badge-inactive">Inactive</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Track Stock</span>
                            <p class="mt-1 text-sm text-slate-800">{{ $product->track_stock ? 'Yes' : 'No' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Reorder Level</span>
                            <p class="mt-1 text-sm text-slate-800">{{ $product->reorder_level }}</p>
                        </div>
                    </div>
                </div>

                @if ($product->description)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="px-6 py-4 border-b border-blue-100">
                        <h3 class="text-lg font-semibold text-slate-800">Description</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-slate-700">{{ $product->description }}</p>
                    </div>
                </div>
                @endif

                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="px-6 py-4 border-b border-blue-100">
                        <h3 class="text-lg font-semibold text-slate-800">Units & Pricing</h3>
                    </div>
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Unit</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Conversion Factor</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Purchase Price</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Selling Price</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Wholesale</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Bulk</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Default</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse ($product->productUnits as $pu)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $pu->unit?->short_code ?? $pu->unit?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $pu->conversion_factor }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $pu->purchase_price ? '$' . number_format($pu->purchase_price, 2) : '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $pu->selling_price ? '$' . number_format($pu->selling_price, 2) : '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $pu->wholesale_price ? '$' . number_format($pu->wholesale_price, 2) : '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $pu->bulk_price ? '$' . number_format($pu->bulk_price, 2) : '-' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($pu->is_default_sale) <span class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">Sale</span> @endif
                                            @if ($pu->is_default_purchase) <span class="text-xs bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded">Buy</span> @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-3 text-center text-sm text-slate-500">No units configured.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="px-6 py-4 border-b border-blue-100">
                        <h3 class="text-lg font-semibold text-slate-800">Price Lists</h3>
                    </div>
                    <div class="p-6">
                        @php $grouped = $product->priceListItems->groupBy(fn($i) => $i->priceList?->name ?? 'Unknown'); @endphp
                        @forelse ($grouped as $listName => $items)
                            <div class="mb-4 last:mb-0">
                                <h4 class="text-sm font-semibold text-slate-700 mb-2">{{ $listName }}</h4>
                                <table class="min-w-full divide-y divide-slate-100 text-sm">
                                    <thead>
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase">Unit</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 uppercase">Min Qty</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 uppercase">Max Qty</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 uppercase">Price</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @foreach ($items as $item)
                                            <tr>
                                                <td class="px-3 py-2 text-slate-800">{{ $item->unit?->short_code ?? $item->unit?->name ?? '-' }}</td>
                                                <td class="px-3 py-2 text-right text-slate-600">{{ $item->min_quantity }}</td>
                                                <td class="px-3 py-2 text-right text-slate-600">{{ $item->max_quantity ?? '∞' }}</td>
                                                <td class="px-3 py-2 text-right font-mono">{{ number_format($item->price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">No price list entries for this product.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                @if ($product->image)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="px-6 py-4 border-b border-blue-100">
                        <h3 class="text-lg font-semibold text-slate-800">Image</h3>
                    </div>
                    <div class="p-6">
                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full rounded-lg">
                    </div>
                </div>
                @endif

                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="px-6 py-4 border-b border-blue-100">
                        <h3 class="text-lg font-semibold text-slate-800">Barcode</h3>
                    </div>
                    <div class="p-6 text-center">
                        @if ($product->barcode_image)
                            <img src="{{ Storage::url($product->barcode_image) }}" alt="{{ $product->barcode }}" class="mx-auto mb-2">
                        @endif
                        <p class="text-sm font-mono text-slate-600">{{ $product->barcode }}</p>
                        <a href="{{ route('products.print-barcode', $product) }}" target="_blank" class="mt-3 inline-flex items-center px-3 py-1.5 erp-btn-primary text-xs">
                            Print Label
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="px-6 py-4 border-b border-blue-100">
                        <h3 class="text-lg font-semibold text-slate-800">Quick Actions</h3>
                    </div>
                    <div class="p-6 space-y-2">
                        <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full erp-btn-secondary text-xs text-red-600 border-red-200 hover:bg-red-50">
                                Delete Product
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
