<x-app-layout>
    <x-slot name="header">New Invoice</x-slot>

    <x-breadcrumbs :items="[['label' => 'Sales', 'url' => route('invoices.index')], ['label' => 'New Invoice']]" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form action="{{ route('invoices.store') }}" method="POST" x-data="invoiceForm()">
            @csrf
            <input type="hidden" name="_action" value="submit">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Customer</label>
                    <x-create-inline selectId="customer_id" :createUrl="route('customers.store')" title="Create New Customer"
                        :fields="[['name'=>'name','label'=>'Customer Name','required'=>true],['name'=>'phone','label'=>'Phone'],['name'=>'email','label'=>'Email']]">
                        <select name="customer_id" id="customer_id" x-model="selectedCustomer" required class="erp-input w-full">
                            <option value="">Walk-in Customer</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                            <option value="" disabled>---</option>
                            <option value="__create__">&plus; Not in the list? Create new</option>
                        </select>
                    </x-create-inline>
                    @error('customer_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Invoice Date</label>
                    <input type="date" name="invoice_date" value="{{ now()->format('Y-m-d') }}" required class="erp-input w-full">
                    @error('invoice_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Currency</label>
                    <select name="currency_code" class="erp-input w-full">
                        @foreach($currencies as $cur)
                            <option value="{{ $cur }}" {{ $cur === 'TZS' ? 'selected' : '' }}>{{ $cur }}</option>
                        @endforeach
                    </select>
                    @error('currency_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Exchange Rate</label>
                    <input type="number" name="exchange_rate" step="0.00000001" min="0" value="1" class="erp-input w-full">
                    @error('exchange_rate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Cost Center</label>
                    <select name="cost_center_id" class="erp-input w-full">
                        @if(count($costCenters) !== 1)<option value="">None</option>@endif
                        @foreach($costCenters as $cc)
                            <option value="{{ $cc->id }}" @selected(count($costCenters) === 1)>{{ $cc->name }}</option>
                        @endforeach
                    </select>
                    @error('cost_center_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Discount</label>
                    <input type="number" name="discount" step="0.01" min="0" value="0" class="erp-input w-full">
                    @error('discount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tax</label>
                    <input type="number" name="tax" step="0.01" min="0" value="0" class="erp-input w-full">
                    @error('tax') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Customer Advance Application --}}
                <div x-show="selectedCustomer">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Apply Customer Advance</label>
                    <select name="customer_advance_id" class="erp-input w-full">
                        <option value="">None</option>
                        <template x-for="adv in customerAdvances" :key="adv.id">
                            <option :value="adv.id" x-text="`${adv.advance_number} - ${adv.balance}`" :disabled="adv.balance <= 0"></option>
                        </template>
                    </select>
                    <div class="mt-1">
                        <input type="number" name="advance_amount" step="0.01" min="0" placeholder="Amount to apply" class="erp-input w-full">
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="erp-input w-full"></textarea>
                    @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
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
                                <th class="text-center px-3 py-2">Store</th>
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
                                        <select x-model="item.product_id" @change="loadProduct(index)" required class="erp-input w-40">
                                            <option value="">Select</option>
                                            @foreach(\App\Models\Product::all() as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->barcode ?? $p->sku }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <select x-model="item.store_id" class="erp-input w-32">
                                            @if(count($stores) !== 1)<option value="">Main Store</option>@endif
                                            @foreach($stores as $s)
                                                <option value="{{ $s->id }}" @selected(count($stores) === 1)>{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <input type="number" x-model="item.quantity" @input="updateLine(index)" step="0.001" min="0.001" required class="erp-input w-20 text-center">
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <input type="number" x-model="item.unit_price" @input="updateLine(index)" step="any" min="0" required class="erp-input w-24 text-right">
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <input type="number" x-model="item.discount" @input="updateLine(index)" step="0.01" min="0" class="erp-input w-20 text-right">
                                    </td>
                                    <td class="px-3 py-2 text-right font-medium" x-text="formatPrice(item.line_total)"></td>
                                    <td class="px-3 py-2 text-center">
                                        <button type="button" @click="removeItem(index)" class="text-danger text-xs">Remove</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 font-semibold">
                                <td colspan="5" class="px-3 py-2 text-right text-sm">Subtotal:</td>
                                <td class="px-3 py-2 text-right text-sm whitespace-nowrap" x-text="formatPrice(subtotal)"></td>
                                <td></td>
                            </tr>
                            <tr class="bg-slate-50 font-semibold">
                                <td colspan="5" class="px-3 py-2 text-right text-sm text-red-600">Total Discount:</td>
                                <td class="px-3 py-2 text-right text-sm text-red-600 whitespace-nowrap" x-text="formatPrice(totalDiscount)"></td>
                                <td></td>
                            </tr>
                            <tr class="bg-slate-50 font-semibold">
                                <td colspan="5" class="px-3 py-2 text-right text-sm">Total:</td>
                                <td class="px-3 py-2 text-right text-sm text-lg font-bold whitespace-nowrap" x-text="formatPrice(grandTotal)"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Payment Account (below totals) --}}
                <div class="mt-4 max-w-sm">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Payment Account</label>
                    <select name="payment_account_id" class="erp-input w-full">
                        <option value="">Select</option>
                        @if($bankAccounts->count())
                            <optgroup label="Bank Accounts">
                                @foreach($bankAccounts as $a)
                                    <option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if($cashAccounts->count())
                            <optgroup label="Cash Registers">
                                @foreach($cashAccounts as $a)
                                    <option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    @error('payment_account_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4 max-w-sm">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Amount Paid</label>
                    <input type="number" name="amount_paid" step="0.01" min="0" value="0" class="erp-input w-full">
                    @error('amount_paid') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="button" @click="addItem" class="mt-3 erp-btn-secondary">+ Add Item</button>
            </div>

            {{-- Hidden fields for items --}}
            <template x-for="(item, index) in items" :key="index">
                <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                <input type="hidden" :name="`items[${index}][store_id]`" :value="item.store_id">
                <input type="hidden" :name="`items[${index}][quantity]`" :value="item.quantity">
                <input type="hidden" :name="`items[${index}][unit_price]`" :value="item.unit_price">
                <input type="hidden" :name="`items[${index}][discount]`" :value="item.discount">
                <input type="hidden" :name="`items[${index}][tax]`" :value="0">
            </template>

            <div class="flex justify-end gap-3">
                <a href="{{ route('invoices.index') }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" name="save_draft" value="1" class="erp-btn-secondary border-primary text-primary hover:bg-primary-50">Save Draft</button>
                <button type="submit" class="erp-btn-primary">Submit Invoice</button>
            </div>
        </form>
    </div>

    <script>
        function invoiceForm() {
            return {
                items: [],
                selectedCustomer: '',
                customerAdvances: [],
                newItem() {
                    return { product_id: "", store_id: "", quantity: 1, unit_price: 0, discount: 0, tax: 0, line_total: 0 };
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
                    const qty = parseFloat(this.items[index].quantity) || 1;
                    let url = `/pos/price-simple?product_id=${productId}&quantity=${qty}&customer_id=${this.selectedCustomer || ''}`;
                    fetch(url)
                        .then(r => r.json())
                        .then(data => {
                            this.items[index].unit_price = parseFloat(data.unit_price);
                            this.updateLine(index);
                        })
                        .catch(() => {});
                },
                get subtotal() {
                    return this.items.reduce((sum, item) => sum + (parseFloat(item.unit_price) || 0) * (parseFloat(item.quantity) || 0), 0);
                },
                get totalDiscount() {
                    return this.items.reduce((sum, item) => sum + (parseFloat(item.discount) || 0), 0);
                },
                get grandTotal() {
                    return this.subtotal - this.totalDiscount;
                },
                init() {
                    this.items.push(this.newItem());
                    this.$watch('selectedCustomer', (val) => {
                        if (val) {
                            fetch(`/customer-advances/available?customer_id=${val}`)
                                .then(r => r.json())
                                .then(data => { this.customerAdvances = data.advances || []; })
                                .catch(() => { this.customerAdvances = []; });
                        } else {
                            this.customerAdvances = [];
                        }
                    });
                },
            };
        }
    </script>
</x-app-layout>
