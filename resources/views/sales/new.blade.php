<x-app-layout>
    <x-slot name="header">{{ __('New Sale') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <form action="{{ route('sales.store') }}" method="POST" x-data="newSale()">
            @csrf
            <input type="hidden" name="items" x-model="JSON.stringify(lines)">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                        <h3 class="text-sm font-medium text-slate-500 mb-4">Sale Details</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Customer *</label>
                                <x-create-inline selectId="customer_id" :createUrl="route('customers.store')" title="Create New Customer"
                                    :fields="[['name'=>'name','label'=>'Customer Name','required'=>true],['name'=>'phone','label'=>'Phone'],['name'=>'email','label'=>'Email']]">
                                    <select name="customer_id" id="customer_id" required class="mt-1 block w-full erp-input"
                                        x-on:change="customerId = $event.target.value">
                                        <option value="">Select Customer</option>
                                        @foreach ($customers as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                        <option value="" disabled>---</option>
                                        <option value="__create__">&plus; Not in the list? Create new</option>
                                    </select>
                                </x-create-inline>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Date *</label>
                                <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" required class="mt-1 block w-full erp-input">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Store</label>
                                <select name="store_id" class="mt-1 block w-full erp-input"
                                    x-on:change="headerStore = $event.target.value">
                                    @if(count($stores) !== 1)<option value="">All Stores</option>@endif
                                    @foreach ($stores as $s)
                                        <option value="{{ $s->id }}" @selected(count($stores) === 1 || in_array($s->id, $userStores))>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Currency</label>
                                <select name="currency_code" class="mt-1 block w-full erp-input"
                                    x-on:change="currency = $event.target.value">
                                    <option value="TZS">TZS</option>
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                    <option value="GBP">GBP</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Cost Center</label>
                                <select name="cost_center_id" class="mt-1 block w-full erp-input">
                                    @if(count($costCenters) !== 1)<option value="">Select</option>@endif
                                    @foreach ($costCenters as $id => $name)
                                        <option value="{{ $id }}" @selected(count($costCenters) === 1)>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-slate-500">Products</h3>
                            <div class="relative">
                                <input type="text" placeholder="Search product..." class="erp-input pl-8 w-64"
                                    x-model="searchQuery"
                                    x-on:input="filterProducts()">
                                <svg class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>

                        <template x-if="filteredProducts.length > 0">
                            <div class="mb-4 max-h-40 overflow-y-auto border border-slate-200 rounded-lg">
                                <template x-for="p in filteredProducts" :key="p.id">
                                    <div class="px-4 py-2 hover:bg-slate-50 cursor-pointer flex justify-between items-center border-b border-slate-100 last:border-0"
                                        x-on:click="addLine(p)">
                                        <span class="text-sm text-slate-800" x-text="p.name"></span>
                                        <span class="text-xs text-slate-400" x-text="p.product_code"></span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <table class="min-w-full divide-y divide-slate-100">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase">Product</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase">Store</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 uppercase">Qty</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 uppercase">Price</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 uppercase">Total</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <template x-for="(line, i) in lines" :key="i">
                                    <tr>
                                        <td class="px-3 py-2 text-sm text-slate-800" x-text="line.product_name"></td>
                                        <td class="px-3 py-2">
                                            <select class="erp-input text-xs" x-model="line.store_id"
                                                x-show="headerStore === ''">
                                                @if(count($stores) !== 1)<option value="">Default</option>@endif
                                                @foreach ($stores as $s)
                                                    <option value="{{ $s->id }}" @selected(count($stores) === 1)>{{ $s->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-xs text-slate-400" x-show="headerStore !== ''" x-text="'Store set in header'"></span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0.01" class="erp-input text-sm w-20 text-right"
                                                x-model="line.quantity"
                                                x-on:input="recalcLine(i)">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" class="erp-input text-sm w-24 text-right"
                                                x-model="line.unit_price"
                                                x-on:input="recalcLine(i)">
                                        </td>
                                        <td class="px-3 py-2 text-sm font-mono text-right text-slate-800" x-text="line.line_total.toFixed(2)"></td>
                                        <td class="px-3 py-2 text-right">
                                            <button type="button" class="text-red-500 hover:text-red-700 text-xs" x-on:click="removeLine(i)">Remove</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>

                        <p class="text-sm text-slate-400 mt-2" x-show="lines.length === 0">Search and select products above to add line items.</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                        <h3 class="text-sm font-medium text-slate-500 mb-4">Totals</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Subtotal</dt>
                                <dd class="text-sm font-mono font-semibold text-slate-800" x-text="subtotal.toFixed(2)"></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Discount</dt>
                                <dd class="text-sm font-mono text-slate-600">
                                    <input type="number" name="discount" min="0" step="0.01" class="erp-input text-sm w-24 text-right"
                                        x-model="discountValue" x-on:input="recalcTotals()">
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">VAT</dt>
                                <dd class="text-sm font-mono text-slate-600">
                                    <input type="number" name="tax" min="0" step="0.01" class="erp-input text-sm w-24 text-right"
                                        x-model="vatValue" x-on:input="recalcTotals()">
                                </dd>
                            </div>
                            <hr class="border-slate-200">
                            <div class="flex justify-between text-lg">
                                <dt class="font-bold text-slate-800">Total</dt>
                                <dd class="font-bold font-mono text-primary" x-text="grandTotal.toFixed(2)"></dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                        <h3 class="text-sm font-medium text-slate-500 mb-4">Payment</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Payment Account</label>
                                <select name="payment_account_id" class="mt-1 block w-full erp-input">
                                    <option value="">Select</option>
                                    @foreach ($paymentAccounts as $acct)
                                        <option value="{{ $acct->id }}">{{ $acct->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Amount Paid</label>
                                <input type="number" name="amount_paid" min="0" step="0.01" value="0" class="mt-1 block w-full erp-input">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Notes</label>
                                <textarea name="notes" rows="2" class="mt-1 block w-full erp-input"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="erp-btn-primary flex-1" x-bind:disabled="lines.length === 0">
                            Save & Post
                        </button>
                        <button type="button" class="erp-btn-secondary"
                            x-on:click="document.querySelector('form').action = '{{ route('sales.drafts.store') }}'; document.querySelector('form').submit();">
                            Save Draft
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function newSale() {
            return {
                lines: [],
                searchQuery: '',
                filteredProducts: [],
                customerId: '',
                headerStore: '',
                currency: 'TZS',
                discountValue: 0,
                vatValue: 0,
                allProducts: @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'product_code' => $p->product_code])),

                filterProducts() {
                    const q = this.searchQuery.toLowerCase();
                    this.filteredProducts = q
                        ? this.allProducts.filter(p => p.name.toLowerCase().includes(q) || (p.product_code || '').toLowerCase().includes(q))
                        : [];
                },

                addLine(product) {
                    const line = {
                        product_id: product.id,
                        product_name: product.name,
                        quantity: 1,
                        unit_price: 0,
                        store_id: '',
                        line_total: 0,
                    };
                    this.lines.push(line);
                    this.searchQuery = '';
                    this.filteredProducts = [];
                    const qty = 1;
                    const customerId = this.customerId || '';
                    fetch(`/pos/price-simple?product_id=${product.id}&quantity=${qty}&customer_id=${customerId}`)
                        .then(r => r.json())
                        .then(data => {
                            line.unit_price = parseFloat(data.unit_price) || 0;
                            this.recalcLine(this.lines.indexOf(line));
                        })
                        .catch(() => {});
                    this.recalcTotals();
                },

                recalcLine(i) {
                    const line = this.lines[i];
                    const qty = parseFloat(line.quantity) || 0;
                    const price = parseFloat(line.unit_price) || 0;
                    line.line_total = qty * price;
                    this.recalcTotals();
                },

                removeLine(i) {
                    this.lines.splice(i, 1);
                    this.recalcTotals();
                },

                recalcTotals() {
                    this.lines = [...this.lines];
                },

                get subtotal() {
                    return this.lines.reduce((sum, l) => sum + (parseFloat(l.line_total) || 0), 0);
                },

                get grandTotal() {
                    const sub = this.subtotal;
                    const disc = parseFloat(this.discountValue) || 0;
                    const vat = parseFloat(this.vatValue) || 0;
                    return Math.max(0, sub - disc + vat);
                },
            };
        }
    </script>
</x-app-layout>
