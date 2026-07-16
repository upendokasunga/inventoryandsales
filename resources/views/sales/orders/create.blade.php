<x-app-layout>
    <x-slot name="header">{{ __('Create Proforma Invoice') }}</x-slot>

    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('sales.orders.store') }}" x-data="salesOrderForm()" x-init="init()">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Customer</label>
                        <x-create-inline selectId="customer_id" :createUrl="route('customers.store')" title="Create New Customer"
                            :fields="[['name'=>'name','label'=>'Customer Name','required'=>true],['name'=>'phone','label'=>'Phone'],['name'=>'email','label'=>'Email']]">
                            <select name="customer_id" id="customer_id" class="erp-input w-full" required>
                                <option value="">Select Customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }} ({{ $customer->code }})</option>
                                @endforeach
                                <option value="" disabled>---</option>
                                <option value="__create__">&plus; Not in the list? Create new</option>
                            </select>
                        </x-create-inline>
                        @error('customer_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Order Date</label>
                        <input type="date" name="order_date" value="{{ old('order_date', now()->format('Y-m-d')) }}" class="erp-input w-full" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Delivery Date (Optional)</label>
                        <input type="date" name="delivery_date" value="{{ old('delivery_date') }}" class="erp-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Payment Terms</label>
                        <input type="text" name="payment_terms" value="{{ old('payment_terms') }}" class="erp-input w-full" placeholder="e.g. Net 30">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" class="erp-input w-full" rows="2">{{ old('notes') }}</textarea>
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
                                <select x-model="item.product_id" @change="loadProduct(index)" class="erp-input w-full" required>
                                    <option value="">Select</option>
                                    @foreach ($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Quantity</label>
                                <input type="number" step="0.001" min="0.001" x-model="item.quantity" @input="updateLine(index)" class="erp-input w-full" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Unit Price (TSh)</label>
                                <input type="number" step="any" min="0" x-model="item.unit_price" @input="updateLine(index)" class="erp-input w-full" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Unit</label>
                                <input type="text" x-model="item.unit_id" class="erp-input w-full" placeholder="Optional">
                            </div>
                        </div>
                        <div class="mt-2 text-right text-sm text-slate-600">
                            Line Total: <span class="font-semibold" x-text="formatPrice(item.line_total)"></span>
                        </div>
                    </div>
                </template>

                <p x-show="items.length === 0" class="text-sm text-slate-400 text-center py-4">No items added.</p>

                <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-slate-100">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Discount</label>
                        <input type="number" step="0.01" name="discount" value="{{ old('discount', 0) }}" class="erp-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Discount Type</label>
                        <select name="discount_type" class="erp-input w-full">
                            <option value="fixed">Fixed</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tax</label>
                        <input type="number" step="0.01" name="tax" value="{{ old('tax', 0) }}" class="erp-input w-full">
                    </div>
                </div>

                <div class="mt-4 text-right text-sm">
                    Subtotal: <span class="font-semibold" x-text="formatPrice(subtotal)"></span>
                </div>
            </div>

            <template x-for="(item, index) in items" :key="'h'+index">
                <div>
                    <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                    <input type="hidden" :name="`items[${index}][quantity]`" :value="item.quantity">
                    <input type="hidden" :name="`items[${index}][unit_price]`" :value="item.unit_price">
                    <input type="hidden" :name="`items[${index}][unit_id]`" :value="item.unit_id">
                </div>
            </template>

            <div class="flex justify-end gap-2">
                <a href="{{ route('sales.orders.index') }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Create Proforma Invoice</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function salesOrderForm() {
            return {
                items: [],
                newItem() {
                    return { product_id: "", quantity: 1, unit_price: 0, unit_id: "", line_total: 0 };
                },
                addItem() {
                    this.items.push(this.newItem());
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                updateLine(index) {
                    const item = this.items[index];
                    const qty = parseFloat(item.quantity) || 0;
                    const price = parseFloat(item.unit_price) || 0;
                    item.line_total = qty * price;
                },
                loadProduct(index) {
                    const productId = this.items[index].product_id;
                    if (!productId) return;
                    const qty = parseFloat(this.items[index].quantity) || 1;
                    fetch(`/pos/price-simple?product_id=${productId}&quantity=${qty}`)
                        .then(r => r.json())
                        .then(data => {
                            this.items[index].unit_price = parseFloat(data.unit_price) || 0;
                            this.updateLine(index);
                        })
                        .catch(() => {});
                },
                get subtotal() {
                    return this.items.reduce((sum, item) => sum + (parseFloat(item.unit_price) || 0) * (parseFloat(item.quantity) || 0), 0);
                },
                init() {
                    this.addItem();
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
