<x-app-layout>
    <x-slot name="header">
        {{ __('Create Purchase Order') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <form action="{{ route('purchasing.orders.store') }}" method="POST">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Order Details</h3>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-slate-700">Supplier *</label>
                            <select name="supplier_id" id="supplier_id" required
                                class="mt-1 block w-full erp-input">
                                <option value="">Select Supplier</option>
                                @foreach ($suppliers as $id => $name)
                                    <option value="{{ $id }}" {{ old('supplier_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="order_date" class="block text-sm font-medium text-slate-700">Order Date *</label>
                            <input type="date" name="order_date" id="order_date"
                                value="{{ old('order_date', date('Y-m-d')) }}" required
                                class="mt-1 block w-full erp-input">
                            @error('order_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="expected_date" class="block text-sm font-medium text-slate-700">Expected Date</label>
                            <input type="date" name="expected_date" id="expected_date"
                                value="{{ old('expected_date') }}"
                                class="mt-1 block w-full erp-input">
                            @error('expected_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="tax" class="block text-sm font-medium text-slate-700">Tax</label>
                            <input type="number" step="0.01" name="tax" id="tax"
                                value="{{ old('tax', '0.00') }}"
                                class="mt-1 block w-full erp-input">
                            @error('tax') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="notes" class="block text-sm font-medium text-slate-700">Notes</label>
                        <textarea name="notes" id="notes" rows="2"
                            class="mt-1 block w-full erp-input">{{ old('notes') }}</textarea>
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
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Subtotal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
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
                                        class="erp-input item-price" style="width:120px">
                                </td>
                                <td class="px-4 py-2 text-sm text-slate-700 item-subtotal">0.00</td>
                                <td class="px-4 py-2">
                                    <button type="button" class="remove-item text-red-500 hover:text-red-700 text-sm">Remove</button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-slate-700">Total:</td>
                                <td class="px-4 py-3 text-sm font-bold text-slate-800" id="order-total">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('purchasing.orders.index') }}" class="mr-4 erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Create Purchase Order</button>
            </div>
        </form>
    </div>

    <script>
        let itemIndex = 1;
        document.getElementById('add-item').addEventListener('click', function() {
            const tbody = document.querySelector('#items-table tbody');
            const row = document.querySelector('.item-row').cloneNode(true);
            row.querySelectorAll('input, select').forEach(el => {
                el.name = el.name.replace(/items\[\d+\]/, `items[${itemIndex}]`);
                el.value = '';
            });
            row.querySelector('.item-subtotal').textContent = '0.00';
            tbody.appendChild(row);
            itemIndex++;
        });

        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('item-qty') || e.target.classList.contains('item-price')) {
                const row = e.target.closest('tr');
                const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                const subtotal = qty * price;
                row.querySelector('.item-subtotal').textContent = subtotal.toFixed(2);
                calcTotal();
            }
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item')) {
                const tbody = document.querySelector('#items-table tbody');
                if (tbody.querySelectorAll('.item-row').length > 1) {
                    e.target.closest('tr').remove();
                    calcTotal();
                }
            }
        });

        function calcTotal() {
            let total = 0;
            document.querySelectorAll('.item-subtotal').forEach(el => {
                total += parseFloat(el.textContent) || 0;
            });
            const tax = parseFloat(document.getElementById('tax').value) || 0;
            document.getElementById('order-total').textContent = (total + tax).toFixed(2);
        }
    </script>
</x-app-layout>
