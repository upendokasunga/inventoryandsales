<x-app-layout>
    <x-slot name="header">
        {{ __('Create Goods Receipt') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
            <div class="p-6">
                <h3 class="text-sm font-medium text-slate-500 mb-4">Select Purchase Order</h3>
                <form method="GET" action="{{ route('purchasing.receipts.create') }}" class="flex gap-2">
                    <select name="purchase_order_id" class="erp-input" onchange="this.form.submit()">
                        <option value="">Select PO to receive against</option>
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}" {{ ($purchaseOrder->id ?? '') == $order->id ? 'selected' : '' }}>
                                {{ $order->po_number }} - {{ $order->supplier?->name ?? '' }} ({{ $order->status }})
                            </option>
                        @endforeach
                    </select>
                    <noscript><button type="submit" class="erp-btn-primary">Load</button></noscript>
                </form>
            </div>
        </div>

        @if ($purchaseOrder)
            <form action="{{ route('purchasing.receipts.store') }}" method="POST">
                @csrf
                <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">

                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                    <div class="p-6">
                        <h3 class="text-sm font-medium text-slate-500 mb-4">Receipt Details</h3>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="receipt_date" class="block text-sm font-medium text-slate-700">Receipt Date *</label>
                                <input type="date" name="receipt_date" id="receipt_date"
                                    value="{{ old('receipt_date', date('Y-m-d')) }}" required
                                    class="mt-1 block w-full erp-input">
                                @error('receipt_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">PO Number</label>
                                <p class="mt-1 text-sm font-medium text-slate-800">{{ $purchaseOrder->po_number }}</p>
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
                        <h3 class="text-sm font-medium text-slate-500 mb-4">Items to Receive</h3>
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Product</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Ordered</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Previously Received</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Expected *</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Received *</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Condition</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach ($purchaseOrder->items as $i => $item)
                                    <tr>
                                        <input type="hidden" name="items[{{ $i }}][purchase_order_item_id]" value="{{ $item->id }}">
                                        <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item->product_id }}">
                                        <td class="px-4 py-2 text-sm text-slate-800">{{ $item->product?->name ?? '-' }}</td>
                                        <td class="px-4 py-2 text-sm text-slate-500">{{ $item->quantity }}</td>
                                        <td class="px-4 py-2 text-sm text-slate-500">{{ $item->received_quantity ?? 0 }}</td>
                                        <td class="px-4 py-2">
                                            <input type="number" step="0.01" name="items[{{ $i }}][expected_quantity]"
                                                value="{{ old("items.$i.expected_quantity", $item->quantity - ($item->received_quantity ?? 0)) }}"
                                                required class="erp-input" style="width:100px">
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="number" step="0.01" name="items[{{ $i }}][received_quantity]"
                                                value="{{ old("items.$i.received_quantity", $item->quantity - ($item->received_quantity ?? 0)) }}"
                                                required class="erp-input" style="width:100px">
                                        </td>
                                        <td class="px-4 py-2">
                                            <select name="items[{{ $i }}][condition]" class="erp-input">
                                                @foreach (['good', 'damaged', 'partial', 'return'] as $cond)
                                                    <option value="{{ $cond }}" {{ old("items.$i.condition") == $cond ? 'selected' : '' }}>{{ ucfirst($cond) }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-2">
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

                <div class="flex justify-end">
                    <a href="{{ route('purchasing.receipts.index') }}" class="mr-4 erp-btn-secondary">Cancel</a>
                    <button type="submit" class="erp-btn-primary">Create Goods Receipt</button>
                </div>
            </form>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 text-center text-sm text-slate-500">
                Select a purchase order to start receiving goods.
            </div>
        @endif
    </div>
</x-app-layout>
