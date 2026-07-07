<x-app-layout>
    <x-slot name="header">{{ __('Create Store Request') }}</x-slot>
    <div class="max-w-7xl mx-auto">
        <form action="{{ route('store-requests.store') }}" method="POST">
            @csrf
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60"><h3 class="text-lg font-semibold text-slate-800">Request Details</h3></div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Source Warehouse</label>
                        <select name="source_warehouse_id" required class="mt-1 block w-full erp-input">
                            <option value="">Select</option>
                            @foreach ($warehouses as $w)
                                <option value="{{ $w->id }}" {{ old('source_warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->code }})</option>
                            @endforeach
                        </select>
                        @error('source_warehouse_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Destination Warehouse</label>
                        <select name="destination_warehouse_id" required class="mt-1 block w-full erp-input">
                            <option value="">Select</option>
                            @foreach ($warehouses as $w)
                                <option value="{{ $w->id }}" {{ old('destination_warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->code }})</option>
                            @endforeach
                        </select>
                        @error('destination_warehouse_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Reason</label>
                        <textarea name="reason" rows="3" class="mt-1 block w-full erp-input">{{ old('reason') }}</textarea>
                        @error('reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800">Items</h3>
                    <button type="button" id="add-item" class="erp-btn-primary text-xs">Add Item</button>
                </div>
                <div class="p-6">
                    <div id="items-container">
                        <div class="item-row grid grid-cols-12 gap-3 mb-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                            <div class="col-span-8">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Product</label>
                                <select name="items[0][product_id]" required class="block w-full erp-input text-sm">
                                    <option value="">Select Product</option>
                                    @foreach ($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->sku }} - {{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-3">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Quantity</label>
                                <input type="number" step="0.001" min="0.001" name="items[0][quantity_requested]" value="1" required class="block w-full erp-input text-sm">
                            </div>
                            <div class="col-span-1 pt-5">
                                <button type="button" class="remove-item text-red-500 hover:text-red-700" title="Remove"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div>
                        </div>
                    </div>
                    @error('items') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex justify-end mb-8">
                <a href="{{ route('store-requests.index') }}" class="mr-4 inline-flex items-center px-4 py-2 erp-btn-secondary">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 erp-btn-primary">Create Store Request</button>
            </div>
        </form>
    </div>
    @push('scripts')
    <script>
        let itemIndex = 1;
        document.getElementById('add-item').addEventListener('click', function() {
            const template = document.querySelector('.item-row').cloneNode(true);
            template.querySelectorAll('[name]').forEach(input => {
                const name = input.getAttribute('name');
                if (name) input.setAttribute('name', name.replace(/\[\d+\]/, '[' + itemIndex + ']'));
                if (input.type !== 'checkbox') input.value = '';
            });
            template.querySelector('.remove-item').addEventListener('click', function() { template.remove(); });
            document.getElementById('items-container').appendChild(template);
            itemIndex++;
        });
        document.querySelectorAll('.remove-item').forEach(btn => btn.addEventListener('click', function() { this.closest('.item-row').remove(); }));
    </script>
    @endpush
</x-app-layout>
