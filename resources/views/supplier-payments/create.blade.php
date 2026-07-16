<x-app-layout>
    <x-slot name="header">{{ __('Create Supplier Payment') }}</x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('supplier-payments.index') }}" class="erp-btn-secondary">Back to List</a>
        </div>

        <form action="{{ route('supplier-payments.store') }}" method="POST" id="payment-form">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60">
                    <h3 class="text-lg font-semibold text-slate-800">Payment Details</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="supplier_id" class="block text-sm font-medium text-slate-700">Supplier <span class="text-red-500">*</span></label>
                        <select name="supplier_id" id="supplier_id" required class="mt-1 block w-full erp-input">
                            <option value="">Select Supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="purchase_order_id" class="block text-sm font-medium text-slate-700">Purchase Order</label>
                        <select name="purchase_order_id" id="purchase_order_id" class="mt-1 block w-full erp-input">
                            <option value="">No linked PO</option>
                            @foreach ($purchaseOrders as $po)
                                @php
                                    $remaining = (float) ($po->total_amount ?: $po->total) - (float) $po->amount_paid;
                                @endphp
                                <option value="{{ $po->id }}" data-remaining="{{ $remaining }}" data-supplier="{{ $po->supplier_id }}"
                                    {{ old('purchase_order_id') == $po->id ? 'selected' : '' }}>
                                    {{ $po->po_number }} — {{ $po->supplier->name }} — Remaining: {{ number_format($remaining, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('purchase_order_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="amount" class="block text-sm font-medium text-slate-700">Amount (TZS) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="amount" value="{{ old('amount') }}" required
                            x-data="priceInput()" class="mt-1 block w-full erp-input">
                        @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-slate-700">Payment Date <span class="text-red-500">*</span></label>
                        <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required
                            class="mt-1 block w-full erp-input">
                        @error('payment_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-slate-700">Notes</label>
                        <textarea name="notes" id="notes" rows="2" class="mt-1 block w-full erp-input">{{ old('notes') }}</textarea>
                        @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('supplier-payments.index') }}" class="mr-4 erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Create Payment</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        const poSelect = document.getElementById('purchase_order_id');
        const supplierSelect = document.getElementById('supplier_id');
        const amountInput = document.getElementById('amount');

        poSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (selected.value) {
                supplierSelect.value = selected.dataset.supplier;
                amountInput.max = selected.dataset.remaining;
                amountInput.placeholder = 'Max: ' + formatPrice(selected.dataset.remaining);
            }
        });

        supplierSelect.addEventListener('change', function() {
            const supplierId = this.value;
            Array.from(poSelect.options).forEach(opt => {
                if (opt.value && opt.dataset.supplier !== supplierId) {
                    opt.disabled = true;
                } else {
                    opt.disabled = false;
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
