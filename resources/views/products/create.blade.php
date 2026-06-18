<x-app-layout>
    <x-slot name="header">
        {{ __('Create Product') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60">
                    <h3 class="text-lg font-semibold text-slate-800">Basic Information</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="mt-1 block w-full erp-input">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-slate-700">Category</label>
                        <select name="category_id" id="category_id" required
                            class="mt-1 block w-full erp-input">
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="weight" class="block text-sm font-medium text-slate-700">Weight (kg)</label>
                        <input type="number" step="0.001" min="0" name="weight" id="weight" value="{{ old('weight') }}"
                            class="mt-1 block w-full erp-input">
                        @error('weight') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="mt-1 block w-full erp-input">{{ old('description') }}</textarea>
                        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="image" class="block text-sm font-medium text-slate-700">Product Image</label>
                        <input type="file" name="image" id="image" accept="image/*"
                            class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60">
                    <h3 class="text-lg font-semibold text-slate-800">Inventory & Tax</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="tax_rate" class="block text-sm font-medium text-slate-700">Tax Rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="tax_rate" id="tax_rate" value="{{ old('tax_rate', 0) }}"
                            class="mt-1 block w-full erp-input">
                        @error('tax_rate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="reorder_level" class="block text-sm font-medium text-slate-700">Reorder Level</label>
                        <input type="number" step="0.001" min="0" name="reorder_level" id="reorder_level" value="{{ old('reorder_level', 0) }}"
                            class="mt-1 block w-full erp-input">
                        @error('reorder_level') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-3 pt-6">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="tax_inclusive" value="1" {{ old('tax_inclusive', true) ? 'checked' : '' }}
                                class="erp-input">
                            <span class="ml-2 text-sm text-slate-700">Tax Inclusive</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="track_stock" value="1" {{ old('track_stock', true) ? 'checked' : '' }}
                                class="erp-input">
                            <span class="ml-2 text-sm text-slate-700">Track Stock</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                class="erp-input">
                            <span class="ml-2 text-sm text-slate-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800">Units & Pricing</h3>
                    <button type="button" id="add-unit" class="erp-btn-primary text-xs">
                        Add Unit
                    </button>
                </div>
                <div class="p-6">
                    <div id="units-container">
                        <div class="unit-row grid grid-cols-12 gap-3 mb-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Unit</label>
                                <select name="units[0][unit_id]" required
                                    class="block w-full erp-input text-sm">
                                    <option value="">Select</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->short_code ?? $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Conversion Factor</label>
                                <input type="number" step="0.001" min="0.001" name="units[0][conversion_factor]" value="1" required
                                    class="block w-full erp-input text-sm">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Purchase Price</label>
                                <input type="number" step="0.01" min="0" name="units[0][purchase_price]" placeholder="0.00"
                                    class="block w-full erp-input text-sm">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Selling Price</label>
                                <input type="number" step="0.01" min="0" name="units[0][selling_price]" placeholder="0.00"
                                    class="block w-full erp-input text-sm">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Wholesale</label>
                                <input type="number" step="0.01" min="0" name="units[0][wholesale_price]" placeholder="0.00"
                                    class="block w-full erp-input text-sm">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Bulk</label>
                                <input type="number" step="0.01" min="0" name="units[0][bulk_price]" placeholder="0.00"
                                    class="block w-full erp-input text-sm">
                            </div>
                            <div class="col-span-1 pt-5 space-y-1">
                                <label class="inline-flex items-center text-xs">
                                    <input type="checkbox" name="units[0][is_default_sale]" value="1"
                                        class="erp-input">
                                    <span class="ml-1 text-slate-600">Sale</span>
                                </label>
                                <label class="inline-flex items-center text-xs">
                                    <input type="checkbox" name="units[0][is_default_purchase]" value="1"
                                        class="erp-input">
                                    <span class="ml-1 text-slate-600">Buy</span>
                                </label>
                            </div>
                            <div class="col-span-1 pt-5">
                                <button type="button" class="remove-unit text-red-500 hover:text-red-700" title="Remove">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p id="no-units" class="text-sm text-slate-500 hidden">Add at least one unit.</p>
                    @error('units') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end mb-8">
                <a href="{{ route('products.index') }}" class="mr-4 inline-flex items-center px-4 py-2 erp-btn-secondary">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 erp-btn-primary">Create Product</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        let unitIndex = 1;
        document.getElementById('add-unit').addEventListener('click', function() {
            const template = document.querySelector('.unit-row').cloneNode(true);
            const inputs = template.querySelectorAll('[name]');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/\[\d+\]/, '[' + unitIndex + ']'));
                    if (input.type !== 'checkbox') input.value = '';
                    else input.checked = false;
                }
            });
            template.querySelector('.remove-unit').addEventListener('click', function() {
                template.remove();
                checkUnits();
            });
            document.getElementById('units-container').appendChild(template);
            unitIndex++;
            checkUnits();
        });

        document.querySelectorAll('.remove-unit').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.unit-row').remove();
                checkUnits();
            });
        });

        function checkUnits() {
            const count = document.querySelectorAll('.unit-row').length;
            document.getElementById('no-units').classList.toggle('hidden', count > 0);
        }
    </script>
    @endpush
</x-app-layout>
