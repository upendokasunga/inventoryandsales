<x-app-layout>
    <x-slot name="header">
        {{ __('Edit Product') }}: {{ $product->name }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PATCH')

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60">
                    <h3 class="text-lg font-semibold text-slate-800">Basic Information</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                            class="mt-1 block w-full erp-input">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-slate-700">Category</label>
                        <select name="category_id" id="category_id" required
                            class="mt-1 block w-full erp-input">
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="weight" class="block text-sm font-medium text-slate-700">Weight (kg)</label>
                        <input type="number" step="0.001" min="0" name="weight" id="weight" value="{{ old('weight', $product->weight) }}"
                            class="mt-1 block w-full erp-input">
                        @error('weight') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="mt-1 block w-full erp-input">{{ old('description', $product->description) }}</textarea>
                        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="image" class="block text-sm font-medium text-slate-700">Product Image</label>
                        @if ($product->image)
                            <div class="mt-1 mb-2">
                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="h-20 w-20 rounded object-cover">
                            </div>
                        @endif
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
                        <input type="number" step="0.01" min="0" max="100" name="tax_rate" id="tax_rate" value="{{ old('tax_rate', $product->tax_rate) }}"
                            class="mt-1 block w-full erp-input">
                        @error('tax_rate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="reorder_level" class="block text-sm font-medium text-slate-700">Reorder Level</label>
                        <input type="number" step="0.001" min="0" name="reorder_level" id="reorder_level" value="{{ old('reorder_level', $product->reorder_level) }}"
                            class="mt-1 block w-full erp-input">
                        @error('reorder_level') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-3 pt-6">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="tax_inclusive" value="1" {{ old('tax_inclusive', $product->tax_inclusive) ? 'checked' : '' }}
                                class="erp-input">
                            <span class="ml-2 text-sm text-slate-700">Tax Inclusive</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="track_stock" value="1" {{ old('track_stock', $product->track_stock) ? 'checked' : '' }}
                                class="erp-input">
                            <span class="ml-2 text-sm text-slate-700">Track Stock</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                                class="erp-input">
                            <span class="ml-2 text-sm text-slate-700">Active</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="has_variants" value="1" {{ old('has_variants', $product->has_variants) ? 'checked' : '' }}
                                class="erp-input">
                            <span class="ml-2 text-sm text-slate-700">Has Variants</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6" id="variant-section" style="{{ old('has_variants', $product->has_variants) ? '' : 'display:none' }}">
                <div class="px-6 py-4 border-b border-slate-200/60 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800">Variants / Sub-Products</h3>
                    <button type="button" id="generate-variants" class="erp-btn-primary text-xs">
                        Generate Variants
                    </button>
                </div>
                <div class="p-6">
                    <p class="text-sm text-slate-500 mb-3">Define attribute keys (e.g., Size, Color) and their comma-separated values. Click <strong>Generate Variants</strong> to create all combinations.</p>
                    <div id="variant-attributes-container">
                        @php $attrs = old('variant_attributes', $product->variant_attributes ?? []); @endphp
                        @if (is_array($attrs) && count($attrs) > 0)
                            @foreach ($attrs as $key => $value)
                                <div class="attribute-row grid grid-cols-12 gap-3 mb-3">
                                    <div class="col-span-5">
                                        <input type="text" name="variant_attributes[{{ $loop->index }}][key]" value="{{ $key }}" placeholder="Attribute name" class="block w-full erp-input text-sm">
                                    </div>
                                    <div class="col-span-5">
                                        <input type="text" name="variant_attributes[{{ $loop->index }}][value]" value="{{ is_array($value) ? implode(',', $value) : $value }}" placeholder="Values (comma-separated)" class="block w-full erp-input text-sm">
                                    </div>
                                    <div class="col-span-2 pt-1">
                                        <button type="button" class="remove-attribute text-red-500 hover:text-red-700 text-sm">Remove</button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="attribute-row grid grid-cols-12 gap-3 mb-3">
                                <div class="col-span-5">
                                    <input type="text" name="variant_attributes[0][key]" placeholder="Attribute name (e.g. Size)" class="block w-full erp-input text-sm">
                                </div>
                                <div class="col-span-5">
                                    <input type="text" name="variant_attributes[0][value]" placeholder="Values (comma-separated)" class="block w-full erp-input text-sm">
                                </div>
                                <div class="col-span-2 pt-1">
                                    <button type="button" class="remove-attribute text-red-500 hover:text-red-700 text-sm">Remove</button>
                                </div>
                            </div>
                        @endif
                    </div>
                    <button type="button" id="add-attribute" class="mt-2 text-sm text-primary hover:text-primary/80">+ Add Attribute</button>
                </div>

                <div class="px-6 pb-6" id="variants-table-wrapper" style="display:none;">
                    <h4 class="text-sm font-semibold text-slate-700 mb-3">Generated Variants</h4>
                    <div class="overflow-x-auto border border-slate-200 rounded-lg">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase">SKU</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase">Barcode</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 uppercase">Purchase Price</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 uppercase">Selling Price</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-slate-500 uppercase">Active</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-slate-500 uppercase"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50" id="variants-tbody">
                                @if($product->has_variants && $product->variants->isNotEmpty())
                                    @foreach($product->variants as $vi => $variant)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="px-3 py-2">
                                            <input type="hidden" name="variants[{{ $vi }}][id]" value="{{ $variant->id }}">
                                            <input type="hidden" name="variants[{{ $vi }}][attributes]" value='{{ json_encode($variant->variant_attributes ?? []) }}'>
                                            <input type="text" name="variants[{{ $vi }}][name]" value="{{ $variant->name }}" class="erp-input text-xs w-48" required>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" name="variants[{{ $vi }}][sku]" value="{{ $variant->sku }}" class="erp-input text-xs font-mono w-32">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" name="variants[{{ $vi }}][barcode]" value="{{ $variant->barcode }}" class="erp-input text-xs font-mono w-28">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" name="variants[{{ $vi }}][purchase_price]" value="{{ $variant->productUnits->first()?->purchase_price }}" placeholder="0.00" class="erp-input text-xs w-24 text-right">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" name="variants[{{ $vi }}][selling_price]" value="{{ $variant->productUnits->first()?->selling_price }}" placeholder="0.00" class="erp-input text-xs w-24 text-right">
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <input type="hidden" name="variants[{{ $vi }}][is_active]" value="0">
                                            <input type="checkbox" name="variants[{{ $vi }}][is_active]" value="1" {{ $variant->is_active ? 'checked' : '' }} class="erp-input">
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" class="remove-variant text-red-400 hover:text-red-600" title="Remove variant">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                    @php $existingVariantCount = $vi + 1; @endphp
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($product->has_variants && $product->variants->isNotEmpty())
                <script>document.addEventListener('DOMContentLoaded', function() { document.getElementById('variants-table-wrapper').style.display = ''; });</script>
                @endif
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
                        @forelse ($product->productUnits as $i => $pu)
                            <div class="unit-row grid grid-cols-12 gap-3 mb-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                                <input type="hidden" name="units[{{ $i }}][id]" value="{{ $pu->id }}">
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Unit</label>
                                    <select name="units[{{ $i }}][unit_id]" required
                                        class="block w-full erp-input text-sm">
                                        <option value="">Select</option>
                                        @foreach ($allUnits as $unit)
                                            <option value="{{ $unit->id }}" {{ $pu->unit_id == $unit->id ? 'selected' : '' }}>{{ $unit->short_code ?? $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Conversion Factor</label>
                                    <input type="number" step="0.001" min="0.001" name="units[{{ $i }}][conversion_factor]" value="{{ $pu->conversion_factor }}" required
                                        class="block w-full erp-input text-sm">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Purchase Price</label>
                                    <input type="number" step="0.01" min="0" name="units[{{ $i }}][purchase_price]" value="{{ $pu->purchase_price }}"
                                        class="block w-full erp-input text-sm">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Selling Price</label>
                                    <input type="number" step="0.01" min="0" name="units[{{ $i }}][selling_price]" value="{{ $pu->selling_price }}"
                                        class="block w-full erp-input text-sm">
                                </div>
                                <div class="col-span-1">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Wholesale</label>
                                    <input type="number" step="0.01" min="0" name="units[{{ $i }}][wholesale_price]" value="{{ $pu->wholesale_price }}"
                                        class="block w-full erp-input text-sm">
                                </div>
                                <div class="col-span-1">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Bulk</label>
                                    <input type="number" step="0.01" min="0" name="units[{{ $i }}][bulk_price]" value="{{ $pu->bulk_price }}"
                                        class="block w-full erp-input text-sm">
                                </div>
                                <div class="col-span-1 pt-5 space-y-1">
                                    <label class="inline-flex items-center text-xs">
                                        <input type="checkbox" name="units[{{ $i }}][is_default_sale]" value="1" {{ $pu->is_default_sale ? 'checked' : '' }}
                                            class="erp-input">
                                        <span class="ml-1 text-slate-600">Sale</span>
                                    </label>
                                    <label class="inline-flex items-center text-xs">
                                        <input type="checkbox" name="units[{{ $i }}][is_default_purchase]" value="1" {{ $pu->is_default_purchase ? 'checked' : '' }}
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
                        @empty
                            <p class="text-sm text-slate-500">No units added yet.</p>
                        @endforelse
                    </div>
                    @error('units') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end mb-8">
                <a href="{{ route('products.show', $product) }}" class="mr-4 inline-flex items-center px-4 py-2 erp-btn-secondary">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 erp-btn-primary">Update Product</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        let unitIndex = {{ count($product->productUnits) }};
        document.getElementById('add-unit').addEventListener('click', function() {
            const template = document.querySelector('.unit-row').cloneNode(true);
            template.querySelector('input[name*="[id]"]')?.remove();
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
            });
            document.getElementById('units-container').appendChild(template);
            unitIndex++;
        });

        document.querySelectorAll('.remove-unit').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.unit-row').remove();
            });
        });

        const hasVariantsCb = document.querySelector('input[name="has_variants"]');
        const variantSection = document.getElementById('variant-section');
        if (hasVariantsCb) {
            hasVariantsCb.addEventListener('change', function() {
                variantSection.style.display = this.checked ? '' : 'none';
            });
        }

        let attrIndex = {{ max($product->variant_attributes ? count($product->variant_attributes) : 1, 1) }};
        document.getElementById('add-attribute')?.addEventListener('click', function() {
            const container = document.getElementById('variant-attributes-container');
            const template = document.querySelector('.attribute-row').cloneNode(true);
            template.querySelectorAll('[name]').forEach(input => {
                const name = input.getAttribute('name');
                if (name) input.setAttribute('name', name.replace(/\[\d+\]/, '[' + attrIndex + ']'));
                if (input.type !== 'checkbox') input.value = '';
            });
            template.querySelector('.remove-attribute').addEventListener('click', function() { template.remove(); });
            container.appendChild(template);
            attrIndex++;
        });
        document.querySelectorAll('.remove-attribute').forEach(btn => {
            btn.addEventListener('click', function() { this.closest('.attribute-row').remove(); });
        });
        document.querySelectorAll('.remove-variant').forEach(btn => {
            btn.addEventListener('click', function() { this.closest('tr').remove(); });
        });

        function getVariantAttributes() {
            const rows = document.querySelectorAll('#variant-attributes-container .attribute-row');
            const attrs = [];
            rows.forEach(row => {
                const key = row.querySelector('input[name*="[key]"]')?.value?.trim();
                const value = row.querySelector('input[name*="[value]"]')?.value?.trim();
                if (key && value) attrs.push({ key, value });
            });
            return attrs;
        }

        function cartesianProduct(attrs) {
            if (attrs.length === 0) return [];
            const keys = attrs.map(a => a.key);
            const values = attrs.map(a => a.value.split(',').map(v => v.trim()).filter(v => v));
            if (values.some(v => v.length === 0)) return [];

            function combine(arrays, prefix = []) {
                if (arrays.length === 0) return [prefix];
                const [first, ...rest] = arrays;
                const result = [];
                for (const val of first) {
                    result.push(...combine(rest, [...prefix, val]));
                }
                return result;
            }

            return combine(values).map(combo => {
                const obj = {};
                keys.forEach((k, i) => { obj[k] = combo[i]; });
                return obj;
            });
        }

        let variantIndex = {{ $existingVariantCount ?? 0 }};

        function renderVariants(combinations, parentName) {
            const tbody = document.getElementById('variants-tbody');
            const wrapper = document.getElementById('variants-table-wrapper');
            if (combinations.length === 0) {
                if (tbody.children.length === 0) wrapper.style.display = 'none';
                return;
            }
            wrapper.style.display = '';

            combinations.forEach((combo, idx) => {
                const parts = Object.values(combo);
                const varName = parentName + ' - ' + parts.join(' / ');
                const attrStr = JSON.stringify(combo);
                const skuHint = parentName.substring(0, 3).toUpperCase() + '-' + parts.map(p => p.substring(0, 3).toUpperCase()).join('-');

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50/50';
                tr.innerHTML = `
                    <td class="px-3 py-2">
                        <input type="hidden" name="variants[${variantIndex}][attributes]" value='${attrStr}'>
                        <input type="text" name="variants[${variantIndex}][name]" value="${varName}" class="erp-input text-xs w-48" required>
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="variants[${variantIndex}][sku]" value="${skuHint}" class="erp-input text-xs font-mono w-32">
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="variants[${variantIndex}][barcode]" value="" class="erp-input text-xs font-mono w-28">
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" step="0.01" min="0" name="variants[${variantIndex}][purchase_price]" value="" placeholder="0.00" class="erp-input text-xs w-24 text-right">
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" step="0.01" min="0" name="variants[${variantIndex}][selling_price]" value="" placeholder="0.00" class="erp-input text-xs w-24 text-right">
                    </td>
                    <td class="px-3 py-2 text-center">
                        <input type="hidden" name="variants[${variantIndex}][is_active]" value="0">
                        <input type="checkbox" name="variants[${variantIndex}][is_active]" value="1" checked class="erp-input">
                    </td>
                    <td class="px-3 py-2 text-center">
                        <button type="button" class="remove-variant text-red-400 hover:text-red-600" title="Remove variant">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </td>
                `;
                tr.querySelector('.remove-variant')?.addEventListener('click', function() {
                    tr.remove();
                    if (tbody.children.length === 0) wrapper.style.display = 'none';
                });
                tbody.appendChild(tr);
                variantIndex++;
            });
        }

        document.getElementById('generate-variants')?.addEventListener('click', function() {
            const attrs = getVariantAttributes();
            const combos = cartesianProduct(attrs);
            const parentName = document.getElementById('name')?.value?.trim() || 'Product';
            renderVariants(combos, parentName);
        });

        document.getElementById('name')?.addEventListener('input', function() {
            const parentName = this.value?.trim() || 'Product';
            const rows = document.querySelectorAll('#variants-tbody tr');
            rows.forEach((row, idx) => {
                const nameInput = row.querySelector('input[name*="[name]"]');
                const attrsInput = row.querySelector('input[name*="[attributes]"]');
                if (nameInput && attrsInput) {
                    try {
                        const combo = JSON.parse(attrsInput.value);
                        const parts = Object.values(combo);
                        nameInput.value = parentName + ' - ' + parts.join(' / ');
                    } catch(e) {}
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
