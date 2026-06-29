<x-app-layout>
    <x-slot name="header">New Invoice</x-slot>

    <x-breadcrumbs :items="[['label' => 'Sales', 'url' => route('invoices.index')], ['label' => 'New Invoice']]" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form action="{{ route('invoices.store') }}" method="POST" x-data="invoiceForm()">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Customer</label>
                    <select name="customer_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm">
                        <option value="">Walk-in Customer</option>
                        @foreach(\App\Models\Customer::all() as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Invoice Date</label>
                    <input type="date" name="invoice_date" value="{{ now()->format('Y-m-d') }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Payment Type</label>
                    <select name="payment_type" class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm">
                        <option value="cash">Cash</option>
                        <option value="credit">Credit</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Discount</label>
                    <input type="number" name="discount" step="0.01" min="0" value="0" class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tax</label>
                    <input type="number" name="tax" step="0.01" min="0" value="0" class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm"></textarea>
                </div>
            </div>

            {{-- Items --}}
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Invoice Items</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                            <tr>
                                <th class="text-left px-3 py-2">Product</th>
                                <th class="text-center px-3 py-2">Qty</th>
                                <th class="text-right px-3 py-2">Unit Price</th>
                                <th class="text-right px-3 py-2">Discount</th>
                                <th class="text-right px-3 py-2">Line Total</th>
                                <th class="text-center px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in items" :key="index">
                                <tr>
                                    <td class="px-3 py-2">
                                        <select x-model="item.product_id" @change="loadProduct(index)" required class="w-48 border border-slate-200 rounded px-2 py-1.5 text-sm">
                                            <option value="">Select product</option>
                                            @foreach(\App\Models\Product::all() as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->barcode ?? $p->sku }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <input type="number" x-model="item.quantity" @input="updateLine(index)" step="0.001" min="0.001" required class="w-20 text-center border border-slate-200 rounded px-2 py-1.5 text-sm">
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <input type="number" x-model="item.unit_price" @input="updateLine(index)" step="0.01" min="0" required class="w-24 text-right border border-slate-200 rounded px-2 py-1.5 text-sm">
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <input type="number" x-model="item.discount" @input="updateLine(index)" step="0.01" min="0" class="w-20 text-right border border-slate-200 rounded px-2 py-1.5 text-sm">
                                    </td>
                                    <td class="px-3 py-2 text-right font-medium" x-text="formatCurrency(item.line_total)"></td>
                                    <td class="px-3 py-2 text-center">
                                        <button type="button" @click="removeItem(index)" class="text-danger text-xs">Remove</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <button type="button" @click="addItem" class="mt-3 px-3 py-1.5 text-sm text-primary border border-primary rounded-lg hover:bg-primary-50 transition">+ Add Item</button>
            </div>

            {{-- Hidden fields for items --}}
            <template x-for="(item, index) in items" :key="index">
                <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                <input type="hidden" :name="`items[${index}][quantity]`" :value="item.quantity">
                <input type="hidden" :name="`items[${index}][unit_price]`" :value="item.unit_price">
                <input type="hidden" :name="`items[${index}][discount]`" :value="item.discount">
                <input type="hidden" :name="`items[${index}][tax]`" :value="0">
            </template>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-primary text-white font-medium rounded-lg hover:bg-primary-600 transition">Create Invoice</button>
            </div>
        </form>
    </div>

    @push("scripts")
    <script>
        function invoiceForm() {
            return {
                items: [this.newItem()],
                newItem() {
                    return { product_id: "", quantity: 1, unit_price: 0, discount: 0, tax: 0, line_total: 0 };
                },
                addItem() { this.items.push(this.newItem()); },
                removeItem(index) { if (this.items.length > 1) this.items.splice(index, 1); },
                updateLine(index) {
                    const item = this.items[index];
                    const qty = parseFloat(item.quantity) || 0;
                    const price = parseFloat(item.unit_price) || 0;
                    const disc = parseFloat(item.discount) || 0;
                    item.line_total = (qty * price) - disc;
                },
                loadProduct(index) {
                    const productId = this.items[index].product_id;
                    if (!productId) return;
                    fetch(`/pos/barcode?barcode=${productId}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.product) {
                                this.items[index].unit_price = parseFloat(data.product.unit_price);
                                this.updateLine(index);
                            }
                        })
                        .catch(() => {});
                },
                formatCurrency(value) {
                    return new Intl.NumberFormat("en-TZ", { style: "currency", currency: "TZS", minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(value || 0);
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
