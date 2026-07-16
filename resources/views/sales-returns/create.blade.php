<x-app-layout>
    <x-slot name="header">Create Sales Return</x-slot>

    <x-breadcrumbs :items="[['label' => 'Returns', 'url' => route('sales-returns.index')], ['label' => 'New Return']]" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form action="{{ route('sales-returns.store') }}" method="POST" x-data="returnForm()">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Customer</label>
                    <select name="customer_id" required class="erp-input w-full">
                        <option value="">Select customer</option>
                        @foreach(\App\Models\Customer::all() as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Invoice (optional)</label>
                    <select name="invoice_id" class="erp-input w-full">
                        <option value="">No invoice</option>
                        @foreach(\App\Models\Invoice::where('status', '!=', 'cancelled')->get() as $inv)
                            <option value="{{ $inv->id }}">{{ $inv->invoice_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Overall Reason</label>
                    <select name="reason" class="erp-input w-full">
                        <option value="">Select reason</option>
                        @foreach(\App\Models\SalesReturn::REASONS as $r)
                            <option value="{{ $r }}">{{ ucfirst(str_replace('_', ' ', $r)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="erp-input w-full"></textarea>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Return Items</h3>
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="text-left px-3 py-2">Product</th>
                            <th class="text-center px-3 py-2">Qty</th>
                            <th class="text-right px-3 py-2">Unit Price</th>
                            <th class="text-left px-3 py-2">Reason</th>
                            <th class="text-center px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td class="px-3 py-2">
                                    <select x-model="item.product_id" required class="erp-input w-48">
                                        <option value="">Select product</option>
                                        @foreach(\App\Models\Product::all() as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <input type="number" x-model="item.quantity" step="0.001" min="0.001" required class="erp-input w-20 text-center">
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <input type="number" x-model="item.unit_price" step="0.01" min="0" required class="erp-input w-24 text-right">
                                </td>
                                <td class="px-3 py-2">
                                    <select x-model="item.reason" required class="erp-input">
                                        <option value="">Reason</option>
                                        @foreach(\App\Models\SalesReturn::REASONS as $r)
                                            <option value="{{ $r }}">{{ ucfirst(str_replace('_', ' ', $r)) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" @click="removeItem(index)" class="text-danger text-xs">Remove</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <button type="button" @click="addItem" class="mt-3 px-3 py-1.5 text-sm text-primary border border-primary rounded-lg hover:bg-primary-50">+ Add Item</button>
            </div>

            <div class="flex justify-end items-center gap-4 mb-6">
                <span class="text-sm text-slate-500">Total Return Value:</span>
                <span class="text-lg font-bold text-primary" x-text="formatPrice(totalAmount)"></span>
            </div>

            <template x-for="(item, index) in items" :key="index">
                <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                <input type="hidden" :name="`items[${index}][quantity]`" :value="item.quantity">
                <input type="hidden" :name="`items[${index}][unit_price]`" :value="item.unit_price">
                <input type="hidden" :name="`items[${index}][reason]`" :value="item.reason">
            </template>

            <div class="flex justify-end">
                <button type="submit" class="erp-btn-primary">Create Return</button>
            </div>
        </form>
    </div>

    @push("scripts")
    <script>
        function returnForm() {
            return {
                items: [{ product_id: "", quantity: 1, unit_price: 0, reason: "" }],
                get totalAmount() {
                    return this.items.reduce((sum, item) => sum + (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0), 0);
                },
                addItem() { this.items.push({ product_id: "", quantity: 1, unit_price: 0, reason: "" }); },
                removeItem(index) { if (this.items.length > 1) this.items.splice(index, 1); },
            };
        }
    </script>
    @endpush
</x-app-layout>
