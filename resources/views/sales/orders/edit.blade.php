<x-app-layout>
    <x-slot name="header">{{ __('Edit Sales Order') }}: {{ $salesOrder->so_number }}</x-slot>

    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('sales.orders.update', $salesOrder) }}" x-data="salesOrderForm()">
            @csrf
            @method('PATCH')

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Customer</label>
                        <select name="customer_id" class="erp-input w-full" required>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $salesOrder->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Order Date</label>
                        <input type="date" name="order_date" value="{{ old('order_date', $salesOrder->order_date?->format('Y-m-d')) }}" class="erp-input w-full" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Delivery Date</label>
                        <input type="date" name="delivery_date" value="{{ old('delivery_date', $salesOrder->delivery_date?->format('Y-m-d')) }}" class="erp-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Payment Terms</label>
                        <input type="text" name="payment_terms" value="{{ old('payment_terms', $salesOrder->payment_terms) }}" class="erp-input w-full">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" class="erp-input w-full" rows="2">{{ old('notes', $salesOrder->notes) }}</textarea>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-slate-500">Order Items</h3>
                    <button type="button" @click="addItem()" class="erp-btn-secondary text-sm">Add Item</button>
                </div>

                <template x-for="(item, index) in items" :key="index">
                    <div class="border border-slate-200 rounded-lg p-4 mb-3">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-slate-700" x-text="'Item ' + (index + 1)"></span>
                            <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                        </div>
                        <div class="grid grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Product</label>
                                <select :name="'items[' + index + '][product_id]'" class="erp-input w-full" required>
                                    @foreach ($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Quantity</label>
                                <input type="number" step="0.001" :name="'items[' + index + '][quantity]'" class="erp-input w-full" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Unit Price</label>
                                <input type="number" step="0.01" :name="'items[' + index + '][unit_price]'" class="erp-input w-full" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Unit</label>
                                <input type="text" :name="'items[' + index + '][unit_id]'" class="erp-input w-full">
                            </div>
                        </div>
                    </div>
                </template>

                <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-slate-100">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Discount</label>
                        <input type="number" step="0.01" name="discount" value="{{ old('discount', $salesOrder->discount) }}" class="erp-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Discount Type</label>
                        <select name="discount_type" class="erp-input w-full">
                            <option value="fixed" {{ $salesOrder->discount_type == 'fixed' ? 'selected' : '' }}>Fixed</option>
                            <option value="percentage" {{ $salesOrder->discount_type == 'percentage' ? 'selected' : '' }}>Percentage</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tax</label>
                        <input type="number" step="0.01" name="tax" value="{{ old('tax', $salesOrder->tax) }}" class="erp-input w-full">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('sales.orders.show', $salesOrder) }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Update Order</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function salesOrderForm() {
            return {
                items: {{ Illuminate\Support\Js::from($salesOrder->items->map(fn($i) => ['product_id' => $i->product_id, 'quantity' => $i->quantity, 'unit_price' => $i->unit_price, 'unit_id' => $i->unit_id])->values()) }},
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
