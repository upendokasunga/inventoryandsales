<x-app-layout>
    <x-slot name="header">
        {{ __('Receive Goods') }}
    </x-slot>

    <div class="max-w-7xl mx-auto" x-data="receivingApp()">
        @if (!$selectedOrder)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60">
                    <h3 class="text-lg font-semibold text-slate-800">Purchase Orders Ready for Receiving</h3>
                    <p class="text-sm text-slate-500 mt-1">Select a purchase order to start receiving goods.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">PO Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Issued By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Order Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse ($orders as $order)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-slate-800">{{ $order->po_number }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $order->supplier?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $order->creator?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $order->order_date?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ 'TSh ' . number_format($order->total, 0) }}</td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusColors = [
                                                'approved' => 'bg-green-100 text-green-700',
                                                'partially_received' => 'bg-purple-100 text-purple-700',
                                            ];
                                        @endphp
                                        <span class="text-xs px-2 py-1 rounded-full {{ $statusColors[$order->status] ?? 'bg-slate-100 text-slate-600' }}">
                                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button type="button" @click="selectOrder({{ $order->id }})" class="erp-btn-primary text-sm">
                                            Receive
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-500">
                                        No purchase orders available for receiving.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end mb-8">
                <a href="{{ route('purchasing.receipts.index') }}" class="erp-btn-secondary">Back to Receipts</a>
            </div>
        @else
            <form action="{{ route('purchasing.receipts.store') }}" method="POST">
                @csrf
                <input type="hidden" name="purchase_order_id" value="{{ $selectedOrder->id }}">

                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                    <div class="px-6 py-4 border-b border-slate-200/60 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800">Receiving: {{ $selectedOrder->po_number }}</h3>
                            <p class="text-sm text-slate-500 mt-1">Supplier: {{ $selectedOrder->supplier?->name ?? '-' }}</p>
                        </div>
                        <button type="button" @click="selectedOrder = null" class="erp-btn-secondary text-sm">
                            Change PO
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="receipt_date" class="block text-sm font-medium text-slate-700">Receipt Date *</label>
                                <input type="date" name="receipt_date" id="receipt_date"
                                    value="{{ old('receipt_date', date('Y-m-d')) }}" required
                                    class="mt-1 block w-full erp-input">
                                @error('receipt_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="warehouse_id" class="block text-sm font-medium text-slate-700">Store *</label>
                                <select name="warehouse_id" id="warehouse_id" required class="mt-1 block w-full erp-input">
                                    <option value="">Select Store</option>
                                    @foreach ($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                            {{ $wh->name }} ({{ $wh->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('warehouse_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="notes" class="block text-sm font-medium text-slate-700">Notes</label>
                                <input type="text" name="notes" id="notes" value="{{ old('notes') }}"
                                    class="mt-1 block w-full erp-input">
                                @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                    <div class="px-6 py-4 border-b border-slate-200/60">
                        <h3 class="text-lg font-semibold text-slate-800">Items to Receive</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="erp-table">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Ordered</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Received</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Pending</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Now Receiving *</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Condition</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach ($selectedOrder->items as $i => $item)
                                    @php
                                        $pending = $item->quantity - ($item->received_quantity ?? 0);
                                    @endphp
                                    <tr>
                                        <input type="hidden" name="items[{{ $i }}][purchase_order_item_id]" value="{{ $item->id }}">
                                        <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item->product_id }}">
                                        <input type="hidden" name="items[{{ $i }}][expected_quantity]" value="{{ $item->quantity }}">
                                        <td class="px-6 py-4 text-sm text-slate-800">{{ $item->product?->name ?? '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-500">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-500">{{ number_format($item->received_quantity ?? 0, 2) }}</td>
                                        <td class="px-6 py-4 text-sm font-medium text-slate-700">{{ number_format($pending, 2) }}</td>
                                        <td class="px-6 py-4">
                                            <input type="number" step="0.01" min="0" max="{{ $pending }}"
                                                name="items[{{ $i }}][received_quantity]"
                                                value="{{ old("items.$i.received_quantity", $pending) }}"
                                                required class="erp-input" style="width:100px">
                                            @error("items.$i.received_quantity") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-6 py-4">
                                            <select name="items[{{ $i }}][condition]" class="erp-input">
                                                @foreach (['good', 'damaged', 'partial', 'return'] as $cond)
                                                    <option value="{{ $cond }}" {{ old("items.$i.condition", 'good') == $cond ? 'selected' : '' }}>{{ ucfirst($cond) }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="text" name="items[{{ $i }}][notes]"
                                                value="{{ old("items.$i.notes") }}"
                                                class="erp-input" style="width:120px">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mb-8">
                    <a href="{{ route('purchasing.receipts.index') }}" class="erp-btn-secondary">Cancel</a>
                    <button type="submit" class="erp-btn-primary">Complete Receipt</button>
                </div>
            </form>
        @endif
    </div>

    <script>
        function receivingApp() {
            return {
                selectedOrder: @js($selectedOrder ? $selectedOrder->id : null),
                selectOrder(id) {
                    window.location.href = '{{ route("purchasing.receipts.create") }}?purchase_order_id=' + id;
                }
            }
        }
    </script>
</x-app-layout>
