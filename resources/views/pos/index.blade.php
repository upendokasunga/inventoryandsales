<x-app-layout>
    <x-slot name="header">Point of Sale</x-slot>

    <div x-data="posApp()">
    <div x-show="toast.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium"
         :class="toast.type === 'error' ? 'bg-red-600 text-white' : toast.type === 'success' ? 'bg-green-600 text-white' : 'bg-slate-800 text-white'"
         x-text="toast.message"></div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Left Panel: Scanner & Product Info --}}
        <div class="lg:col-span-3 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Scan Barcode</h3>
                <div class="relative">
                    <input type="text" x-ref="barcodeInput" x-model="barcodeInput" @keydown.enter.prevent="lookupBarcode"
                           placeholder="Scan or enter barcode / SKU..."
                           class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary pr-10">
                    <div x-show="barcodeLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
                        <svg class="animate-spin h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-1.5">USB scanner: type barcode + Enter. Manual: type name/SKU + Enter.</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Search Product</h3>
                <input type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts"
                       placeholder="Search by name, SKU, or code..."
                       class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary">
                <template x-if="searchResults.length > 0">
                    <div class="mt-2 max-h-48 overflow-y-auto border border-slate-200 rounded-lg divide-y divide-slate-100">
                        <template x-for="p in searchResults" :key="p.id">
                            <div @click="selectSearchResult(p)" class="px-3 py-2 hover:bg-slate-50 cursor-pointer">
                                <p class="text-sm font-medium text-slate-700" x-text="p.name"></p>
                                <p class="text-xs text-slate-400">
                                    <span x-text="p.sku || p.product_code || ''"></span>
                                    <span x-show="p.current_stock !== undefined"> &middot; Stock: <span x-text="p.current_stock"></span></span>
                                </p>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="searchQuery.length >= 2 && searchResults.length === 0 && !searchLoading">
                    <p class="mt-2 text-xs text-slate-400 text-center">No products found</p>
                </template>
            </div>

            <template x-if="lastScanned">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <h3 class="text-sm font-semibold text-slate-700 mb-3">Last Added</h3>
                    <p class="text-sm text-slate-600" x-text="lastScanned.name"></p>
                    <p class="text-xs text-slate-400">SKU: <span x-text="lastScanned.sku"></span></p>
                </div>
            </template>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Cart Summary</h3>
                <p class="text-sm text-slate-600">Items: <span class="font-medium" x-text="cart.length"></span></p>
                <p class="text-sm text-slate-600">Total: <span class="font-medium text-primary" x-text="formatPrice(cartTotal)"></span></p>
            </div>

            {{-- Product Preview (for search results) --}}
            <template x-if="previewProduct">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-2">
                    <h3 class="text-sm font-semibold text-slate-700" x-text="previewProduct.name"></h3>
                    <img x-bind:src="previewProduct.image_url" class="w-full h-32 object-cover rounded-lg" x-show="previewProduct.image_url">
                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-500">
                        <span>Barcode: <span class="font-medium" x-text="previewProduct.barcode"></span></span>
                        <span>SKU: <span class="font-medium" x-text="previewProduct.sku"></span></span>
                        <span>Stock: <span class="font-medium" x-text="previewStock"></span></span>
                        <span>Price: <span class="font-medium text-primary" x-text="formatPrice(previewPrice)"></span></span>
                    </div>
                    <button @click="addToCart" class="w-full mt-2 px-3 py-2 bg-success text-white text-sm rounded-lg hover:bg-success-600 transition">
                        Add to Cart
                    </button>
                </div>
            </template>
        </div>

        {{-- Center Panel: Cart --}}
        <div class="lg:col-span-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                <div class="p-4 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-700">Shopping Cart</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                            <tr>
                                <th class="text-left px-4 py-3">Product</th>
                                <th class="text-left px-4 py-3">Unit</th>
                                <th class="text-center px-4 py-3">Qty</th>
                                <th class="text-right px-4 py-3">Price</th>
                                <th class="text-right px-4 py-3">Discount</th>
                                <th class="text-right px-4 py-3">Total</th>
                                <th class="text-center px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in cart" :key="index">
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-700" x-text="item.name"></td>
                                    <td class="px-4 py-3 text-slate-500">
                                        <select x-model="item.product_unit_id" @change="updateItem(index)"
                                                class="text-xs border border-slate-200 rounded px-2 py-1">
                                            <template x-for="unit in item.units" :key="unit.id">
                                                <option :value="unit.id" x-text="unit.name"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="number" x-model="item.quantity" @input.debounce="updateItem(index)"
                                               class="w-20 text-center border border-slate-200 rounded px-2 py-1 text-xs" min="0.001" step="1">
                                    </td>
                                    <td class="px-4 py-3 text-right text-slate-600" x-text="formatPrice(item.unit_price)"></td>
                                    <td class="px-4 py-3 text-right">
                                        <input type="number" x-model="item.discount" @input.debounce="updateItem(index)"
                                               class="w-20 text-right border border-slate-200 rounded px-2 py-1 text-xs" min="0">
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-slate-700" x-text="formatPrice(item.lineTotal)"></td>
                                    <td class="px-4 py-3 text-center">
                                        <button @click="removeItem(index)" class="text-danger hover:text-danger-600 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="cart.length === 0">
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center text-slate-400 text-sm">Cart is empty. Scan or search products to begin.</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right Panel: Checkout --}}
        <div class="lg:col-span-3 space-y-4">
            {{-- Customer Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Customer</h3>
                <select x-model="customerId" @change="loadCustomer" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm mb-2">
                    <option value="">Walk-in Customer</option>
                    <template x-for="customer in customers" :key="customer.id">
                        <option :value="customer.id" x-text="customer.name"></option>
                    </template>
                </select>
                <template x-if="customerData">
                    <div class="text-xs space-y-1 text-slate-500 mt-2 p-2 bg-slate-50 rounded-lg">
                        <p>Limit: <span class="font-medium text-slate-700" x-text="formatPrice(customerData.credit_limit)"></span></p>
                        <p>Outstanding: <span class="font-medium text-slate-700" x-text="formatPrice(customerData.outstanding_balance)"></span></p>
                        <p>Available: <span class="font-medium" :class="customerData.available_credit > 0 ? 'text-success' : 'text-danger'" x-text="formatPrice(customerData.available_credit)"></span></p>
                    </div>
                </template>
            </div>

            {{-- Payment Account Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Payment Account</h3>
                <select x-model="paymentAccountId" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm">
                    @if(count($paymentAccounts) !== 1)
                        <option value="">Select Account</option>
                    @endif
                    @foreach($paymentAccounts as $acct)
                        <option value="{{ $acct->id }}" @selected(count($paymentAccounts) === 1)>{{ $acct->name }} ({{ $acct->code }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Totals Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-3">
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Subtotal</span>
                    <span class="font-medium" x-text="formatPrice(subtotal)"></span>
                </div>
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Discount</span>
                    <span class="font-medium text-danger" x-text="formatPrice(discountTotal)"></span>
                </div>
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Tax (<span x-text="taxRateDisplay"></span>)</span>
                    <span class="font-medium" x-text="formatPrice(taxAmount)"></span>
                </div>
                <hr class="border-slate-200">
                <div class="flex justify-between text-base font-bold text-slate-800">
                    <span>Grand Total</span>
                    <span class="text-primary" x-text="formatPrice(grandTotal)"></span>
                </div>

                <div class="pt-2">
                    <label class="text-xs text-slate-500 block mb-1">Amount Tendered</label>
                    <input type="number" x-model="amountTendered" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm" min="0">
                </div>

                <button @click="checkout" :disabled="cart.length === 0 || processing"
                        class="w-full px-4 py-3 bg-primary text-white font-medium rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition">
                    <span x-text="processing ? 'Processing...' : 'Complete Sale'"></span>
                </button>
            </div>
        </div>
    </div>
    </div>

    <script>
        function posApp() {
            return {
                barcodeInput: "",
                barcodeLoading: false,
                customerId: "",
                customers: [],
                customerData: null,
                cart: [],
                previewProduct: null,
                previewStock: 0,
                previewPrice: 0,
                lastScanned: null,
                paymentMethod: "cash",
                paymentAccountId: "",
                amountTendered: 0,
                processing: false,
                searchQuery: "",
                searchResults: [],
                searchLoading: false,
                toast: { show: false, message: '', type: 'info' },

                init() {
                    fetch("/customers?per_page=1000")
                        .then(r => r.json())
                        .then(data => { this.customers = data.data || []; });
                    this.$nextTick(() => this.$refs.barcodeInput?.focus());
                },

                showToast(message, type = 'info') {
                    this.toast = { show: true, message, type };
                    setTimeout(() => { this.toast.show = false; }, 2500);
                },

                focusBarcode() {
                    this.$nextTick(() => this.$refs.barcodeInput?.focus());
                },

                async lookupBarcode() {
                    if (!this.barcodeInput || this.barcodeLoading) return;
                    const input = this.barcodeInput;
                    this.barcodeInput = "";
                    this.barcodeLoading = true;
                    try {
                        let res = await fetch(`/pos/barcode?barcode=${encodeURIComponent(input)}`);
                        if (!res.ok) {
                            res = await fetch(`/pos/sku?sku=${encodeURIComponent(input)}`);
                        }
                        if (!res.ok) {
                            this.showToast("Product not found: " + input, "error");
                            this.focusBarcode();
                            return;
                        }
                        const data = await res.json();
                        this.addToCartDirect(data.product, data.stock);
                        this.lastScanned = data.product;
                        this.showToast(data.product.name + " added to cart", "success");
                    } catch (e) {
                        this.showToast("Error looking up product", "error");
                    } finally {
                        this.barcodeLoading = false;
                        this.focusBarcode();
                    }
                },

                addToCartDirect(product, stock) {
                    const existing = this.cart.find(i => i.product_id === product.id);
                    if (existing) {
                        existing.quantity = parseFloat(existing.quantity) + 1;
                        this.fetchItemPrice(existing);
                    } else {
                        const item = {
                            product_id: product.id,
                            name: product.name,
                            product_unit_id: product.units?.[0]?.id || null,
                            units: (product.units || []).map(u => ({ id: u.unit_id || u.id, name: u.unit?.name || u.name })),
                            quantity: 1,
                            unit_price: parseFloat(product.unit_price),
                            discount: 0,
                            tax: 0,
                            lineTotal: parseFloat(product.unit_price),
                        };
                        this.cart.push(item);
                        this.fetchItemPrice(item);
                    }
                    this.updateTotals();
                },

                fetchItemPrice(item) {
                    const customerId = this.customerId || '';
                    fetch(`/pos/price-simple?product_id=${item.product_id}&quantity=${item.quantity}&customer_id=${customerId}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.unit_price && parseFloat(data.unit_price) > 0) {
                                item.unit_price = parseFloat(data.unit_price);
                                this.updateItem(this.cart.indexOf(item));
                            }
                        })
                        .catch(() => {});
                },

                async searchProducts() {
                    const q = this.searchQuery.trim();
                    if (q.length < 2) { this.searchResults = []; return; }
                    this.searchLoading = true;
                    try {
                        const res = await fetch(`/pos/search?q=${encodeURIComponent(q)}`);
                        const data = await res.json();
                        this.searchResults = data.products || [];
                    } catch (e) {
                        this.searchResults = [];
                    } finally {
                        this.searchLoading = false;
                    }
                },

                selectSearchResult(product) {
                    this.previewProduct = product;
                    this.previewStock = product.current_stock;
                    this.previewPrice = product.unit_price;
                    this.lastScanned = product;
                    this.searchQuery = "";
                    this.searchResults = [];
                },

                loadCustomer() {
                    if (!this.customerId) { this.customerData = null; return; }
                    fetch(`/pos/customer?customer_id=${this.customerId}`)
                        .then(r => r.json())
                        .then(data => { this.customerData = data; });
                    this.cart.forEach(item => this.fetchItemPrice(item));
                },

                addToCart() {
                    if (!this.previewProduct) return;
                    this.addToCartDirect(this.previewProduct, this.previewStock);
                    this.showToast(this.previewProduct.name + " added to cart", "success");
                    this.previewProduct = null;
                    this.focusBarcode();
                },

                updateItem(index) {
                    const item = this.cart[index];
                    const qty = parseFloat(item.quantity) || 0;
                    const price = parseFloat(item.unit_price) || 0;
                    const disc = parseFloat(item.discount) || 0;
                    item.lineTotal = (qty * price) - disc;
                    this.updateTotals();
                },

                removeItem(index) {
                    this.cart.splice(index, 1);
                    this.updateTotals();
                },

                updateTotals() {},

                get subtotal() {
                    return this.cart.reduce((sum, i) => sum + (parseFloat(i.unit_price) * parseFloat(i.quantity || 0)), 0);
                },

                get discountTotal() {
                    return this.cart.reduce((sum, i) => sum + (parseFloat(i.discount) || 0), 0);
                },

                get taxRate() {
                    return parseFloat("{{ app(\App\Services\SettingsService::class)->get('tax_rate', '0') }}") || 0;
                },

                get taxRateDisplay() {
                    return this.taxRate > 0 ? this.taxRate + '%' : '0%';
                },

                get taxAmount() {
                    const rate = this.taxRate > 0 ? this.taxRate / 100 : 0;
                    return (this.subtotal - this.discountTotal) * rate;
                },

                get grandTotal() {
                    return this.subtotal - this.discountTotal + this.taxAmount;
                },

                get cartTotal() {
                    return this.grandTotal;
                },

                async checkout() {
                    if (this.cart.length === 0) return;
                    this.processing = true;
                    try {
                        const res = await fetch("/pos/checkout", {
                            method: "POST",
                            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
                            body: JSON.stringify({
                                customer_id: this.customerId || null,
                                items: this.cart.map(i => ({
                                    product_id: i.product_id,
                                    product_unit_id: i.product_unit_id,
                                    quantity: parseFloat(i.quantity),
                                    unit_price: parseFloat(i.unit_price),
                                    discount: parseFloat(i.discount) || 0,
                                    tax: 0,
                                })),
                                payment: {
                                    amount: parseFloat(this.amountTendered) || this.grandTotal,
                                    payment_method: "cash",
                                    payment_account_id: this.paymentAccountId || null,
                                },
                                discount: this.discountTotal,
                                discount_type: "fixed",
                            }),
                        });
                        const data = await res.json();
                        if (data.success) {
                            window.location.href = `/invoices/${data.invoice.id}`;
                        } else {
                            this.showToast(data.error || "Checkout failed", "error");
                        }
                    } catch (e) {
                        this.showToast("Error processing sale", "error");
                    } finally {
                        this.processing = false;
                    }
                },
            };
        }
    </script>
</x-app-layout>
