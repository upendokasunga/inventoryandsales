<x-app-layout>
    <x-slot name="header">{{ __('Pricing Simulator') }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Pricing Dashboard', 'url' => route('price-lists.dashboard')], ['label' => 'Simulator']]" />

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 h-fit">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">Simulation Inputs</h2>
                <form action="{{ route('price-lists.simulate') }}" method="POST">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Customer Group</label>
                            <select name="customer_group_id" class="erp-input w-full">
                                <option value="">General (All Customers)</option>
                                @foreach ($customerGroups as $group)
                                    <option value="{{ $group->id }}" {{ ($inputs['customer_group_id'] ?? '') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Product</label>
                            <select name="product_id" class="erp-input w-full" required>
                                <option value="">Select Product</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" {{ ($inputs['product_id'] ?? '') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                @endforeach
                            </select>
                            @error('product_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Unit</label>
                            <select name="unit_id" class="erp-input w-full" required>
                                <option value="">Select Unit</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" {{ ($inputs['unit_id'] ?? '') == $unit->id ? 'selected' : '' }}>{{ $unit->short_code ?? $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('unit_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Quantity</label>
                            <input type="number" step="0.001" min="0.001" name="quantity" value="{{ $inputs['quantity'] ?? 1 }}" class="erp-input w-full" required>
                            @error('quantity') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 erp-btn-primary">Simulate</button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-2">
                @if (isset($simulation))
                    @if ($simulation['best'])
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                            <div class="erp-card">
                                <p class="text-sm font-medium text-slate-500">Selected Price List</p>
                                <p class="mt-1 text-lg font-bold text-slate-800">{{ $simulation['best']['price_list_name'] }}</p>
                            </div>
                            <div class="erp-card">
                                <p class="text-sm font-medium text-slate-500">Matched Tier</p>
                                <p class="mt-1 text-lg font-bold text-slate-800">{{ $simulation['best']['matched_tier'] }}</p>
                            </div>
                            <div class="erp-card">
                                <p class="text-sm font-medium text-slate-500">Unit Price</p>
                                <p class="mt-1 text-lg font-bold text-slate-800">{{ number_format($simulation['best']['unit_price'], 2) }}</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
                            <h3 class="text-md font-semibold text-slate-800 mb-4">Summary</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div><span class="text-slate-500">Quantity:</span> <span class="font-medium">{{ $inputs['quantity'] }}</span></div>
                                <div><span class="text-slate-500">Unit Price:</span> <span class="font-medium">{{ number_format($simulation['best']['unit_price'], 2) }}</span></div>
                                <div><span class="text-slate-500">Total Amount:</span> <span class="font-medium text-lg text-primary">{{ number_format($simulation['best']['total_amount'], 2) }}</span></div>
                            </div>
                        </div>

                        @if (count($simulation['results']) > 1)
                            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                                <h3 class="text-md font-semibold text-slate-800 mb-4">All Applicable Prices</h3>
                                <table class="min-w-full divide-y divide-slate-100">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Price List</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Customer Group</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Tier</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Unit Price</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @foreach ($simulation['results'] as $result)
                                            <tr class="{{ $result['price_list_id'] === $simulation['best']['price_list_id'] ? 'bg-success-50' : '' }}">
                                                <td class="px-4 py-3 text-sm text-slate-800">{{ $result['price_list_name'] }}</td>
                                                <td class="px-4 py-3 text-sm text-slate-500">{{ $result['customer_group'] ?? 'General' }}</td>
                                                <td class="px-4 py-3 text-sm text-right text-slate-500">{{ $result['matched_tier'] }}</td>
                                                <td class="px-4 py-3 text-sm text-right font-mono">{{ number_format($result['unit_price'], 2) }}</td>
                                                <td class="px-4 py-3 text-sm text-right font-mono">{{ number_format($result['total_amount'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @else
                        <div class="erp-card">
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                <p class="text-sm text-slate-500">No applicable price list found for the given inputs.</p>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="erp-card">
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <p class="text-sm text-slate-500">Select a product and quantity to simulate pricing.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
