<x-app-layout>
    <x-slot name="header">{{ __('Create Stock Adjustment') }}</x-slot>

    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('stock-adjustments.store') }}" x-data="adjustmentForm()">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                        <select name="type" class="erp-input w-full" required>
                            <option value="">Select Type</option>
                            <option value="positive" {{ old('type') == 'positive' ? 'selected' : '' }}>Positive (Add Stock)</option>
                            <option value="negative" {{ old('type') == 'negative' ? 'selected' : '' }}>Negative (Remove Stock)</option>
                            <option value="transfer" {{ old('type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>
                        @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Reason</label>
                        <select name="reason" class="erp-input w-full" required>
                            <option value="">Select Reason</option>
                            <option value="damaged" {{ old('reason') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                            <option value="lost" {{ old('reason') == 'lost' ? 'selected' : '' }}>Lost</option>
                            <option value="found" {{ old('reason') == 'found' ? 'selected' : '' }}>Found</option>
                            <option value="expired" {{ old('reason') == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="recount" {{ old('reason') == 'recount' ? 'selected' : '' }}>Recount</option>
                            <option value="other" {{ old('reason') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('reason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description (Optional)</label>
                    <textarea name="description" class="erp-input w-full" rows="2">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-slate-500">Adjustment Items</h3>
                    <button type="button" @click="addItem()" class="erp-btn-secondary text-sm">Add Item</button>
                </div>

                <template x-for="(item, index) in items" :key="index">
                    <div class="border border-slate-200 rounded-lg p-4 mb-3">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-slate-700" x-text="'Item ' + (index + 1)"></span>
                            <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Product</label>
                                <select :name="'items[' + index + '][product_id]'" class="erp-input w-full" required>
                                    <option value="">Select Product</option>
                                    @foreach ($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Batch (Optional)</label>
                                <input type="text" :name="'items[' + index + '][inventory_batch_id]'" class="erp-input w-full" placeholder="Batch ID">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Expected Qty</label>
                                <input type="number" step="0.001" :name="'items[' + index + '][expected_quantity]'" class="erp-input w-full" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Actual Qty</label>
                                <input type="number" step="0.001" :name="'items[' + index + '][actual_quantity]'" class="erp-input w-full" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Unit Cost</label>
                                <input type="number" step="0.01" :name="'items[' + index + '][unit_cost]'" class="erp-input w-full">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Notes</label>
                                <input type="text" :name="'items[' + index + '][notes]'" class="erp-input w-full">
                            </div>
                        </div>
                    </div>
                </template>

                <p x-show="items.length === 0" class="text-sm text-slate-400 text-center py-4">No items added. Click "Add Item" to begin.</p>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('stock-adjustments.index') }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Create Adjustment</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function adjustmentForm() {
            return {
                items: [],
                addItem() {
                    this.items.push({});
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
