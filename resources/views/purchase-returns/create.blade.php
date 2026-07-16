<x-app-layout>
    <x-slot name="header">Create Purchase Return</x-slot>

    <x-breadcrumbs :items="[['label' => 'Returns', 'url' => route('purchase-returns.index')], ['label' => 'New Return']]" />

    @if(!$selectedOrder)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Select a Purchase Order to Return</h3>
            @if(count($purchaseOrders) === 0)
                <div class="text-center py-12 text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25-2.25m-2.25 2.25V4.875M3.375 7.5h17.25"/></svg>
                    <p class="text-sm">No eligible purchase orders found.</p>
                    <p class="text-xs text-slate-400 mt-1">Only approved or partially received POs with received goods are shown.</p>
                </div>
            @else
                <x-table-card :empty="false">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">PO #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($purchaseOrders as $po)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">{{ $po->po_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $po->supplier->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $po->created_at->format('d M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">{{ number_format($po->total_amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="erp-badge-{{ $po->status === 'approved' ? 'approved' : 'fulfilled' }}">
                                        {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="{{ route('purchase-returns.create', ['purchase_order_id' => $po->id]) }}"
                                       class="px-3 py-1.5 text-sm text-primary border border-primary rounded-lg hover:bg-primary-50 transition">
                                        Return
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-table-card>
            @endif
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <form action="{{ route('purchase-returns.store') }}" method="POST" x-data="returnForm()">
                @csrf
                <input type="hidden" name="purchase_order_id" value="{{ $selectedOrder->id }}">
                <input type="hidden" name="supplier_id" value="{{ $selectedOrder->supplier_id }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Supplier</label>
                        <input type="text" value="{{ $selectedOrder->supplier->name }}" disabled class="erp-input w-full bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Purchase Order</label>
                        <div class="flex items-center gap-2">
                            <input type="text" value="{{ $selectedOrder->po_number }}" disabled class="erp-input flex-1 bg-slate-50">
                            <a href="{{ route('purchase-returns.create') }}" class="text-xs text-slate-400 hover:text-primary transition">Change</a>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                        <input type="text" name="notes" class="erp-input w-full" placeholder="Optional notes">
                        @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                @error('items') <p class="mb-4 text-sm text-red-600">{{ $message }}</p> @enderror

                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-slate-700 mb-3">Return Items</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                                <tr>
                                    <th class="text-left px-3 py-2">Product</th>
                                    <th class="text-center px-3 py-2">Returnable</th>
                                    <th class="text-center px-3 py-2">Qty to Return</th>
                                    <th class="text-right px-3 py-2">Unit Price</th>
                                    <th class="text-right px-3 py-2">Line Total</th>
                                    <th class="text-left px-3 py-2">Reason</th>
                                    <th class="text-center px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="px-3 py-2">
                                            <input type="text" :value="item.product_name" disabled class="erp-input w-48 bg-slate-50">
                                            <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                                            <input type="hidden" :name="`items[${index}][product_unit_id]`" :value="item.product_unit_id || ''">
                                        </td>
                                        <td class="px-3 py-2 text-center text-slate-500 text-xs" x-text="item.returnable"></td>
                                        <td class="px-3 py-2 text-center">
                                            <input type="number" :name="`items[${index}][quantity]`"
                                                   x-model.number="item.quantity"
                                                   :max="item.returnable"
                                                   step="0.001" min="0.001" required
                                                   class="erp-input w-20 text-center">
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <input type="number" :name="`items[${index}][unit_price]`"
                                                   x-model.number="item.unit_price"
                                                   step="0.01" min="0" required
                                                   class="erp-input w-24 text-right">
                                        </td>
                                        <td class="px-3 py-2 text-right font-medium" x-text="formatPrice(item.quantity * item.unit_price)"></td>
                                        <td class="px-3 py-2">
                                            <select :name="`items[${index}][reason]`" x-model="item.reason" required class="erp-input">
                                                <option value="">Reason</option>
                                                @foreach(\App\Models\PurchaseReturn::REASONS as $r)
                                                    <option value="{{ $r }}">{{ ucfirst(str_replace('_', ' ', $r)) }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" @click="removeItem(index)" class="text-danger text-xs hover:underline">Remove</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" @click="addItem" class="mt-3 px-3 py-1.5 text-sm text-primary border border-primary rounded-lg hover:bg-primary-50 transition">+ Add Item</button>
                </div>

                <div class="flex justify-between items-center">
                    <a href="{{ route('purchase-returns.create') }}" class="erp-btn-secondary">Back to PO List</a>
                    <div class="flex gap-3">
                        <a href="{{ route('purchase-returns.index') }}" class="erp-btn-ghost">Cancel</a>
                        <button type="submit" class="erp-btn-primary">Create Return</button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    @push("scripts")
    <script>
        function returnForm() {
            return {
                items: @js($poItems),
                addItem() {
                    this.items.push({ product_id: "", product_name: "", product_unit_id: null, quantity: 1, received_quantity: 0, unit_price: 0, returnable: 0, reason: "" });
                },
                removeItem(index) {
                    if (this.items.length > 1) this.items.splice(index, 1);
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
