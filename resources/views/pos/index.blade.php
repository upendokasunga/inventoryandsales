<x-app-layout>
    <x-slot name="header">Point of Sale</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="posApp()">
        {{-- Left Panel: Scanner & Product Info --}}
        <div class="lg:col-span-3 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Scan Barcode</h3>
                <input type="text" x-model="barcodeInput" @keydown.enter.prevent="lookupBarcode"
                       placeholder="Scan or enter barcode..."
                       class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary">
                <div class="mt-3">
                    <button @click="startCamera" class="w-full px-3 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary-600 transition">
                        <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Camera Scan
                    </button>
                </div>
            </div>

            <template x-if="lastScanned">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <h3 class="text-sm font-semibold text-slate-700 mb-3">Last Scanned</h3>
                    <p class="text-sm text-slate-600" x-text="lastScanned.name"></p>
                    <p class="text-xs text-slate-400">SKU: <span x-text="lastScanned.sku"></span></p>
                </div>
            </template>

            {{-- Recent Scans --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Cart Summary</h3>
                <p class="text-sm text-slate-600">Items: <span class="font-medium" x-text="cart.length"></span></p>
                <p class="text-sm text-slate-600">Total: <span class="font-medium text-primary" x-text="formatCurrency(cartTotal)"></span></p>
            </div>

            {{-- Product Preview --}}
            <template x-if="previewProduct">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-2">
                    <h3 class="text-sm font-semibold text-slate-700" x-text="previewProduct.name"></h3>
                    <img x-bind:src="previewProduct.image_url" class="w-full h-32 object-cover rounded-lg" x-show="previewProduct.image_url">
                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-500">
                        <span>Barcode: <span class="font-medium" x-text="previewProduct.barcode"></span></span>
                        <span>SKU: <span class="font-medium" x-text="previewProduct.sku"></span></span>
                        <span>Stock: <span class="font-medium" x-text="previewStock"></span></span>
                        <span>Price: <span class="font-medium text-primary" x-text="formatCurrency(previewPrice)"></span></span>
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
                                    <td class="px-4 py-3 text-right text-slate-600" x-text="formatCurrency(item.unit_price)"></td>
                                    <td class="px-4 py-3 text-right">
                                        <input type="number" x-model="item.discount" @input.debounce="updateItem(index)"
                                               class="w-20 text-right border border-slate-200 rounded px-2 py-1 text-xs" min="0">
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-slate-700" x-text="formatCurrency(item.lineTotal)"></td>
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
                        <p>Limit: <span class="font-medium text-slate-700" x-text="formatCurrency(customerData.credit_limit)"></span></p>
                        <p>Outstanding: <span class="font-medium text-slate-700" x-text="formatCurrency(customerData.outstanding_balance)"></span></p>
                        <p>Available: <span class="font-medium" :class="customerData.available_credit > 0 ? 'text-success' : 'text-danger'" x-text="formatCurrency(customerData.available_credit)"></span></p>
                    </div>
                </template>
            </div>

            {{-- Totals Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-3">
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Subtotal</span>
                    <span class="font-medium" x-text="formatCurrency(subtotal)"></span>
                </div>
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Discount</span>
                    <span class="font-medium text-danger" x-text="formatCurrency(discountTotal)"></span>
                </div>
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Tax (<span x-text="taxRateDisplay"></span>)</span>
                    <span class="font-medium" x-text="formatCurrency(taxAmount)"></span>
                </div>
                <hr class="border-slate-200">
                <div class="flex justify-between text-base font-bold text-slate-800">
                    <span>Grand Total</span>
                    <span class="text-primary" x-text="formatCurrency(grandTotal)"></span>
                </div>

                <div class="pt-2">
                    <label class="text-xs text-slate-500 block mb-1">Payment Method</label>
                    <select x-model="paymentMethod" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm">
                        <option value="cash">Cash</option>
                        <option value="credit">Credit</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="cheque">Cheque</option>
                        <option value="mixed">Mixed</option>
                    </select>
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

    @push("scripts")
    <script>
        function posApp() {
            return {
                barcodeInput: "",
                customerId: "",
                customers: [],
                customerData: null,
                cart: [],
                previewProduct: null,
                lastScanned: null,
                paymentMethod: "cash",
                amountTendered: 0,
                processing: false,

                init() {
                    fetch("/customers?per_page=1000")
                        .then(r => r.json())
                        .then(data => { this.customers = data.data || []; });
                },

                async lookupBarcode() {
                    if (!this.barcodeInput) return;
                    try {
                        const res = await fetch(`/pos/barcode?barcode=${encodeURIComponent(this.barcodeInput)}`);
                        if (!res.ok) { alert("Product not found"); this.barcodeInput = ""; return; }
                        const data = await res.json();
                        this.previewProduct = data.product;
                        this.lastScanned = data.product;
                        this.previewStock = data.stock;
                        this.previewPrice = data.product.unit_price;
                        this.barcodeInput = "";
                    } catch (e) {
                        alert("Error looking up barcode");
                    }
                },

                async startCamera() {
                    if (!navigator.mediaDevices?.getUserMedia) {
                        alert("Camera access is not supported by your browser. Use a keyboard scanner or manual entry.");
                        return;
                    }
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } });
                        stream.getTracks().forEach(t => t.stop());
                        alert("Camera initialized. Point at a barcode and press Enter after scanning.");
                    } catch (e) {
                        if (e.name === "NotAllowedError" || e.name === "PermissionDeniedError") {
                            alert("Camera permission denied. Use keyboard scanner or manual entry.");
                        } else {
                            alert("Camera scanning requires a secure connection (HTTPS). Use keyboard scanner or manual entry.");
                        }
                    }
                },

                loadCustomer() {
                    if (!this.customerId) { this.customerData = null; return; }
                    fetch(`/pos/customer?customer_id=${this.customerId}`)
                        .then(r => r.json())
                        .then(data => { this.customerData = data; });
                },

                addToCart() {
                    if (!this.previewProduct) return;
                    const existing = this.cart.find(i => i.product_id === this.previewProduct.id);
                    if (existing) {
                        existing.quantity = parseFloat(existing.quantity) + 1;
                    } else {
                        this.cart.push({
                            product_id: this.previewProduct.id,
                            name: this.previewProduct.name,
                            product_unit_id: this.previewProduct.units?.[0]?.id || null,
                            units: (this.previewProduct.units || []).map(u => ({ id: u.unit_id, name: u.unit?.name })),
                            quantity: 1,
                            unit_price: parseFloat(this.previewProduct.unit_price),
                            discount: 0,
                            tax: 0,
                            lineTotal: parseFloat(this.previewProduct.unit_price),
                        });
                    }
                    this.updateTotals();
                    this.previewProduct = null;
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

                updateTotals() {
                    // Recalculate - computed properties handle this
                },

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
                                    payment_method: this.paymentMethod,
                                },
                                discount: this.discountTotal,
                                discount_type: "fixed",
                            }),
                        });
                        const data = await res.json();
                        if (data.success) {
                            window.location.href = `/invoices/${data.invoice.id}`;
                        } else {
                            alert(data.error || "Checkout failed");
                        }
                    } catch (e) {
                        alert("Error processing sale");
                    } finally {
                        this.processing = false;
                    }
                },

                formatCurrency(value) {
                    return new Intl.NumberFormat("en-TZ", { style: "currency", currency: "TZS", minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(value || 0);
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
