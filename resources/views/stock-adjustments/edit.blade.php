<x-app-layout>
    <x-slot name="header">{{ __('Edit Stock Adjustment') }}: {{ $stockAdjustment->adjustment_number }}</x-slot>

    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('stock-adjustments.update', $stockAdjustment) }}" x-data="adjustmentForm()">
            @csrf
            @method('PATCH')

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                        <select name="type" class="erp-input w-full" required>
                            <option value="positive" {{ old('type', $stockAdjustment->type) == 'positive' ? 'selected' : '' }}>Positive (Add Stock)</option>
                            <option value="negative" {{ old('type', $stockAdjustment->type) == 'negative' ? 'selected' : '' }}>Negative (Remove Stock)</option>
                            <option value="transfer" {{ old('type', $stockAdjustment->type) == 'transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Reason</label>
                        <select name="reason" class="erp-input w-full" required>
                            <option value="damaged" {{ old('reason', $stockAdjustment->reason) == 'damaged' ? 'selected' : '' }}>Damaged</option>
                            <option value="lost" {{ old('reason', $stockAdjustment->reason) == 'lost' ? 'selected' : '' }}>Lost</option>
                            <option value="found" {{ old('reason', $stockAdjustment->reason) == 'found' ? 'selected' : '' }}>Found</option>
                            <option value="expired" {{ old('reason', $stockAdjustment->reason) == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="recount" {{ old('reason', $stockAdjustment->reason) == 'recount' ? 'selected' : '' }}>Recount</option>
                            <option value="other" {{ old('reason', $stockAdjustment->reason) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <textarea name="description" class="erp-input w-full" rows="2">{{ old('description', $stockAdjustment->description) }}</textarea>
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Product</label>
                                <select :name="'items[' + index + '][product_id]'" class="erp-input w-full" required
                                    @change="fetchStockInfo($event.target.value, index)">
                                    @foreach ($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                                    @endforeach
                                </select>
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
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('stock-adjustments.show', $stockAdjustment) }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Update Adjustment</button>
            </div>
        </form>
    </div>

    <script>
        function adjustmentForm() {
            return {
                items: @json($stockAdjustment->items->map(fn($i) => [
                    'product_id' => $i->product_id,
                    'expected_quantity' => $i->expected_quantity,
                    'actual_quantity' => $i->actual_quantity,
                    'unit_cost' => $i->unit_cost,
                ])->values()),
                addItem() {
                    this.items.push({ expected_quantity: '', unit_cost: '' });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                async fetchStockInfo(productId, index) {
                    if (!productId) return;
                    try {
                        const res = await fetch(`{{ route('stock-adjustments.stock-info') }}?product_id=${productId}`);
                        const data = await res.json();
                        this.items[index].expected_quantity = data.current_stock ?? 0;
                        this.items[index].unit_cost = data.unit_cost ?? 0;
                    } catch (e) {
                        console.error('Failed to fetch stock info', e);
                    }
                }
            }
        }
    </script>
</x-app-layout>
