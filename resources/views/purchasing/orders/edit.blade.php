<x-app-layout>
    <x-slot name="header">
        {{ __('Edit Purchase Order') }}: {{ $purchaseOrder->po_number }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <form action="{{ route('purchasing.orders.update', $purchaseOrder) }}" method="POST">
            @csrf @method('PATCH')

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Order Details</h3>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-slate-700">Supplier *</label>
                            <x-create-inline selectId="supplier_id" :createUrl="route('suppliers.store')" title="Create New Supplier"
                                :fields="[['name'=>'name','label'=>'Supplier Name','required'=>true],['name'=>'phone','label'=>'Phone'],['name'=>'email','label'=>'Email']]">
                                <select name="supplier_id" id="supplier_id" required
                                    class="mt-1 block w-full erp-input">
                                    <option value="">Select Supplier</option>
                                    @foreach ($suppliers as $id => $name)
                                        <option value="{{ $id }}" {{ old('supplier_id', $purchaseOrder->supplier_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                    <option value="" disabled>---</option>
                                    <option value="__create__">&plus; Not in the list? Create new</option>
                                </select>
                            </x-create-inline>
                            @error('supplier_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="order_date" class="block text-sm font-medium text-slate-700">Order Date *</label>
                            <input type="date" name="order_date" id="order_date"
                                value="{{ old('order_date', $purchaseOrder->order_date?->format('Y-m-d')) }}" required
                                class="mt-1 block w-full erp-input">
                            @error('order_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-4 gap-4 mb-4">
                        <div>
                            <label for="expected_date" class="block text-sm font-medium text-slate-700">Expected Date</label>
                            <input type="date" name="expected_date" id="expected_date"
                                value="{{ old('expected_date', $purchaseOrder->expected_date?->format('Y-m-d')) }}"
                                class="mt-1 block w-full erp-input">
                            @error('expected_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="currency_code" class="block text-sm font-medium text-slate-700">Currency</label>
                            <select name="currency_code" id="currency_code" class="mt-1 block w-full erp-input">
                                <option value="TZS" {{ old('currency_code', $purchaseOrder->currency_code ?? 'TZS') === 'TZS' ? 'selected' : '' }}>TZS</option>
                                <option value="USD" {{ old('currency_code') === 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ old('currency_code') === 'EUR' ? 'selected' : '' }}>EUR</option>
                            </select>
                        </div>
                        <div>
                            <label for="exchange_rate" class="block text-sm font-medium text-slate-700">Exchange Rate</label>
                            <input type="number" step="0.00000001" min="0" name="exchange_rate" id="exchange_rate"
                                value="{{ old('exchange_rate', $purchaseOrder->exchange_rate ?? '1') }}" class="mt-1 block w-full erp-input">
                        </div>
                        <div>
                            <label for="cost_center_id" class="block text-sm font-medium text-slate-700">Cost Center</label>
                            <select name="cost_center_id" id="cost_center_id" class="mt-1 block w-full erp-input">
                                <option value="">Select</option>
                                @foreach (\App\Models\CostCenter::all() as $cc)
                                    <option value="{{ $cc->id }}" {{ old('cost_center_id', $purchaseOrder->cost_center_id) == $cc->id ? 'selected' : '' }}>{{ $cc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="tax" class="block text-sm font-medium text-slate-700">Tax</label>
                            <input type="number" step="0.01" name="tax" id="tax"
                                value="{{ old('tax', $purchaseOrder->tax) }}"
                                class="mt-1 block w-full erp-input">
                            @error('tax') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="discount" class="block text-sm font-medium text-slate-700">Discount</label>
                            <div class="flex gap-2 mt-1">
                                <input type="number" step="0.01" name="discount" id="discount"
                                    value="{{ old('discount', $purchaseOrder->discount) }}"
                                    class="block w-full erp-input">
                                <select name="discount_type" id="discount_type" class="erp-input w-32">
                                    <option value="fixed" {{ old('discount_type', $purchaseOrder->discount_type) === 'fixed' ? 'selected' : '' }}>Fixed</option>
                                    <option value="percentage" {{ old('discount_type', $purchaseOrder->discount_type) === 'percentage' ? 'selected' : '' }}>%</option>
                                </select>
                            </div>
                            @error('discount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="notes" class="block text-sm font-medium text-slate-700">Notes</label>
                        <textarea name="notes" id="notes" rows="2"
                            class="mt-1 block w-full erp-input">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                        @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-slate-500">Order Items</h3>
                        <button type="button" id="add-item" class="erp-btn-secondary text-sm">+ Add Item</button>
                    </div>
                    <table class="min-w-full divide-y divide-slate-100" id="items-table">
                        <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Product *</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Quantity *</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Unit Price *</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Selling Price</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Subtotal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase"></th>
                    </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse ($purchaseOrder->items as $i => $item)
                            <tr class="item-row">
                                <td class="px-4 py-2">
                                    <select name="items[{{ $i }}][product_id]" required class="erp-input product-select">
                                        <option value="">Select</option>
                                        @foreach ($products as $id => $name)
                                            <option value="{{ $id }}" {{ $item->product_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" step="0.01" name="items[{{ $i }}][quantity]" required
                                        value="{{ $item->quantity }}"
                                        class="erp-input item-qty" style="width:100px">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" step="0.01" name="items[{{ $i }}][unit_price]" required
                                        value="{{ $item->unit_price }}"
                                        class="erp-input item-price" data-price-input style="width:120px">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" step="0.01" name="items[{{ $i }}][selling_price]"
                                        value="{{ $item->selling_price }}"
                                        class="erp-input item-selling-price" data-price-input style="width:120px" placeholder="Resale price">
                                </td>
                                <td class="px-4 py-2 text-sm text-slate-700 item-subtotal">{{ number_format($item->subtotal, 2) }}</td>
                                <td class="px-4 py-2">
                                    <button type="button" class="remove-item text-red-500 hover:text-red-700 text-sm">Remove</button>
                                </td>
                            </tr>
                            @empty
                            <tr class="item-row">
                                <td class="px-4 py-2">
                                    <select name="items[0][product_id]" required class="erp-input product-select">
                                        <option value="">Select</option>
                                        @foreach ($products as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" step="0.01" name="items[0][quantity]" required
                                        class="erp-input item-qty" style="width:100px">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" step="0.01" name="items[0][unit_price]" required
                                        class="erp-input item-price" data-price-input style="width:120px">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" step="0.01" name="items[0][selling_price]"
                                        class="erp-input item-selling-price" data-price-input style="width:120px" placeholder="Resale price">
                                </td>
                                <td class="px-4 py-2 text-sm text-slate-700 item-subtotal">0.00</td>
                                <td class="px-4 py-2">
                                    <button type="button" class="remove-item text-red-500 hover:text-red-700 text-sm">Remove</button>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="flex justify-end mt-4">
                        <div class="min-w-[280px] max-w-[420px] space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">Subtotal:</span>
                                <span class="font-medium text-slate-700 whitespace-nowrap" id="order-subtotal">TSh {{ number_format($purchaseOrder->subtotal, 0) }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">Discount:</span>
                                <span class="text-slate-500 whitespace-nowrap" id="order-discount">-TSh {{ number_format($purchaseOrder->discount ?? 0, 0) }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">Tax:</span>
                                <span class="text-slate-500 whitespace-nowrap" id="order-tax-display">TSh {{ number_format($purchaseOrder->tax, 0) }}</span>
                            </div>
                            <div class="flex justify-between gap-4 border-t border-slate-200 pt-2">
                                <span class="font-medium text-slate-700">Total:</span>
                                <span class="font-bold text-slate-800 text-base whitespace-nowrap" id="order-total">TSh {{ number_format($purchaseOrder->total, 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('purchasing.orders.show', $purchaseOrder) }}" class="mr-4 erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Update Purchase Order</button>
            </div>
        </form>
    </div>

    <script>
        function initPriceInput(el) {
            if (el.dataset.priceInit) return;
            el.dataset.priceInit = '1';

            let raw = parseFloat(el.value) || 0;
            el.value = raw || '';
            el.type = 'hidden';

            const wrapper = document.createElement('div');
            wrapper.className = 'relative';
            el.parentNode.insertBefore(wrapper, el);
            wrapper.appendChild(el);

            const visible = document.createElement('input');
            visible.type = 'text';
            visible.className = el.className.replace('item-price', '').replace('item-selling-price', '').trim();
            visible.style.width = el.style.width;
            visible.placeholder = 'TSh 0';
            visible.value = raw ? raw.toLocaleString('en-US', { maximumFractionDigits: 0 }) : '';
            wrapper.insertBefore(visible, el);

            visible.addEventListener('input', function () {
                const cleaned = this.value.replace(/[^0-9.]/g, '');
                raw = parseFloat(cleaned) || 0;
                el.value = raw || '';
                const pos = this.selectionStart;
                const oldLen = this.value.length;
                this.value = raw ? raw.toLocaleString('en-US', { maximumFractionDigits: 0 }) : '';
                this.setSelectionRange(pos + (this.value.length - oldLen), pos + (this.value.length - oldLen));
                recalcRow(el);
            });

            visible.addEventListener('focus', function () {
                this.value = raw || '';
                this.setSelectionRange(this.value.length, this.value.length);
            });

            visible.addEventListener('blur', function () {
                this.value = raw ? raw.toLocaleString('en-US', { maximumFractionDigits: 0 }) : '';
            });
        }

        function initAllPriceInputs(container) {
            container.querySelectorAll('[data-price-input]').forEach(initPriceInput);
        }

        function recalcRow(changedEl) {
            const row = changedEl.closest('tr');
            if (!row) return;
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const priceInput = row.querySelector('.item-price');
            const price = priceInput ? (parseFloat(priceInput.value) || 0) : 0;
            const subtotal = qty * price;
            row.querySelector('.item-subtotal').textContent = 'TSh ' + subtotal.toLocaleString('en-US', { maximumFractionDigits: 0 });
            calcTotal();
        }

        function calcTotal() {
            let subtotal = 0;
            document.querySelectorAll('.item-subtotal').forEach(el => {
                subtotal += parseFloat(el.textContent.replace(/[^0-9.-]/g, '')) || 0;
            });
            const tax = parseFloat(document.getElementById('tax').value) || 0;
            const discount = parseFloat(document.getElementById('discount').value) || 0;
            const discountType = document.getElementById('discount_type').value;

            let afterDiscount = subtotal;
            if (discountType === 'percentage' && discount > 0) {
                afterDiscount = subtotal - (subtotal * discount / 100);
            } else if (discount > 0) {
                afterDiscount = Math.max(0, subtotal - discount);
            }

            const total = afterDiscount + tax;

            document.getElementById('order-subtotal').textContent = 'TSh ' + subtotal.toLocaleString('en-US', { maximumFractionDigits: 0 });
            document.getElementById('order-discount').textContent = '-TSh ' + (subtotal - afterDiscount).toLocaleString('en-US', { maximumFractionDigits: 0 });
            document.getElementById('order-tax-display').textContent = 'TSh ' + tax.toLocaleString('en-US', { maximumFractionDigits: 0 });
            document.getElementById('order-total').textContent = 'TSh ' + total.toLocaleString('en-US', { maximumFractionDigits: 0 });
        }

        function fetchLatestPrice(productSelect) {
            const productId = productSelect.value;
            if (!productId) return;

            const row = productSelect.closest('tr');
            const priceInput = row.querySelector('.item-price');
            const sellingInput = row.querySelector('.item-selling-price');

            fetch(`{{ route('purchasing.orders.latest-price') }}?product_id=${productId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.unit_price !== null) {
                        setPriceInputValue(priceInput, data.unit_price);
                        recalcRow(priceInput);
                    }
                    if (data.selling_price !== null && sellingInput) {
                        setPriceInputValue(sellingInput, data.selling_price);
                    }
                })
                .catch(() => {});
        }

        function setPriceInputValue(el, value) {
            if (!el) return;
            el.value = value || '';
            const wrapper = el.parentNode;
            if (wrapper && wrapper.classList.contains('relative')) {
                const visible = wrapper.querySelector('input[type="text"]');
                if (visible) {
                    visible.value = value ? value.toLocaleString('en-US', { maximumFractionDigits: 0 }) : '';
                }
            }
        }

        let itemIndex = {{ count($purchaseOrder->items) }};

        document.getElementById('add-item').addEventListener('click', function () {
            const tbody = document.querySelector('#items-table tbody');
            const row = document.querySelector('.item-row').cloneNode(true);
            row.querySelectorAll('input, select').forEach(el => {
                el.name = el.name.replace(/items\[\d+\]/, `items[${itemIndex}]`);
                el.value = '';
                delete el.dataset.priceInit;
                const wrapper = el.parentNode;
                if (wrapper && wrapper.classList.contains('relative') && el.type === 'hidden') {
                    const visible = wrapper.querySelector('input[type="text"]');
                    if (visible) visible.remove();
                    el.type = 'number';
                    el.className = el.className;
                    el.style.width = el.style.width;
                    wrapper.parentNode.insertBefore(el, wrapper);
                    wrapper.remove();
                }
            });
            row.querySelector('.item-subtotal').textContent = '0.00';
            tbody.appendChild(row);
            initAllPriceInputs(row);
            itemIndex++;
        });

        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('item-qty')) {
                recalcRow(e.target);
            }
            if (e.target.id === 'tax' || e.target.id === 'discount' || e.target.id === 'discount_type') {
                calcTotal();
            }
        });

        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('product-select')) {
                fetchLatestPrice(e.target);
            }
            if (e.target.id === 'tax' || e.target.id === 'discount' || e.target.id === 'discount_type') {
                calcTotal();
            }
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-item')) {
                const tbody = document.querySelector('#items-table tbody');
                if (tbody.querySelectorAll('.item-row').length > 1) {
                    e.target.closest('tr').remove();
                    calcTotal();
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            initAllPriceInputs(document);
        });
    </script>
</x-app-layout>
