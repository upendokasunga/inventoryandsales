<x-app-layout>
    <x-slot name="header">{{ __('Create Price List') }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Pricing Dashboard', 'url' => route('price-lists.dashboard')], ['label' => 'Price Lists', 'url' => route('price-lists.index')], ['label' => 'Create']]" />

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('price-lists.store') }}" method="POST">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="erp-input w-full" required>
                        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea name="description" rows="3" class="erp-input w-full">{{ old('description') }}</textarea>
                        @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Customer Group</label>
                        <select name="customer_group_id" class="erp-input w-full">
                            <option value="">General (All Customers)</option>
                            @foreach ($customerGroups as $group)
                                <option value="{{ $group->id }}" {{ old('customer_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                        @error('customer_group_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Currency</label>
                        <input type="text" value="TZS" class="erp-input w-full bg-slate-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Valid From</label>
                        <input type="datetime-local" name="valid_from" value="{{ old('valid_from') }}" class="erp-input w-full">
                        @error('valid_from') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Valid Until</label>
                        <input type="datetime-local" name="valid_until" value="{{ old('valid_until') }}" class="erp-input w-full">
                        @error('valid_until') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="rounded border-slate-300">
                            <span class="text-sm font-medium text-slate-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-800">Price List Items</h2>
                    <button type="button" onclick="addItem()" class="inline-flex items-center px-3 py-1.5 erp-btn-secondary text-sm">Add Item</button>
                </div>

                <div id="items-container" class="space-y-3">
                    <template id="item-template">
                        <div class="item-row grid grid-cols-12 gap-2 items-end p-3 bg-slate-50 rounded-lg">
                            <div class="col-span-3">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Product</label>
                                <select name="items[INDEX][product_id]" class="erp-input w-full" required>
                                    <option value="">Select Product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Unit</label>
                                <select name="items[INDEX][unit_id]" class="erp-input w-full" required>
                                    <option value="">Select Unit</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->short_code ?? $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Min Qty</label>
                                <input type="number" step="0.001" min="0.001" name="items[INDEX][min_quantity]" value="1" class="erp-input w-full" required>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Max Qty</label>
                                <input type="number" step="0.001" min="0" name="items[INDEX][max_quantity]" class="erp-input w-full" placeholder="Unlimited">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Price</label>
                                <input type="number" step="0.01" min="0" name="items[INDEX][price]" class="erp-input w-full" required>
                            </div>
                            <div class="col-span-1">
                                <button type="button" onclick="this.closest('.item-row').remove()" class="text-red-500 hover:text-red-700 p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                @error('items') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center px-6 py-2 erp-btn-primary">Create Price List</button>
                <a href="{{ route('price-lists.index') }}" class="inline-flex items-center px-6 py-2 erp-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        let itemIndex = 0;
        function addItem() {
            const template = document.getElementById('item-template');
            const clone = template.content.cloneNode(true);
            const html = clone.querySelector('.item-row').outerHTML.replace(/INDEX/g, itemIndex++);
            document.getElementById('items-container').insertAdjacentHTML('beforeend', html);
        }
    </script>
    @endpush
</x-app-layout>
