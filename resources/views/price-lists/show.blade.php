<x-app-layout>
    <x-slot name="header">{{ $priceList->name }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Pricing Dashboard', 'url' => route('price-lists.dashboard')], ['label' => 'Price Lists', 'url' => route('price-lists.index')], ['label' => $priceList->name]]" />

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                    <h2 class="text-lg font-semibold text-slate-800 mb-2">{{ $priceList->name }}</h2>
                    <p class="text-sm text-slate-500 mb-4">{{ $priceList->description ?? 'No description.' }}</p>

                    <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                        <div><span class="text-slate-500">Customer Group:</span> <span class="font-medium">{{ $priceList->customerGroup?->name ?? 'General' }}</span></div>
                        <div><span class="text-slate-500">Currency:</span> <span class="font-medium">TZS</span></div>
                        <div><span class="text-slate-500">Valid:</span> <span class="font-medium">{{ $priceList->valid_from?->format('Y-m-d') ?? '∞' }} - {{ $priceList->valid_until?->format('Y-m-d') ?? '∞' }}</span></div>
                        <div>
                            <span class="text-slate-500">Status:</span>
                            @if ($priceList->isExpired())
                                <span class="erp-badge-inactive">Expired</span>
                            @elseif ($priceList->is_active)
                                <span class="erp-badge-active">Active</span>
                            @else
                                <span class="erp-badge-inactive">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                    <h3 class="text-md font-semibold text-slate-800 mb-4">Items ({{ $priceList->items->count() }})</h3>
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Unit</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Min Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Max Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach ($priceList->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-slate-800">{{ $item->product->name }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-500">{{ $item->unit?->short_code ?? $item->unit?->name }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-slate-500">{{ $item->min_quantity }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-slate-500">{{ $item->max_quantity ?? '∞' }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-mono">{{ number_format($item->price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                    <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-3">Quick Actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('price-lists.edit', $priceList) }}" class="block w-full text-center px-4 py-2 erp-btn-primary">Edit Price List</a>
                        <form action="{{ route('price-lists.destroy', $priceList) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="block w-full text-center px-4 py-2 erp-btn-danger">Delete</button>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                    <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-3">Meta</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-slate-500">Items</span><span class="font-medium">{{ $priceList->items->count() }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Created</span><span class="font-medium">{{ $priceList->created_at->format('Y-m-d') }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Updated</span><span class="font-medium">{{ $priceList->updated_at->format('Y-m-d') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
