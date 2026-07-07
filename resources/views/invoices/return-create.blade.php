<x-app-layout>
    <x-slot name="header">Create Return - Invoice #{{ $invoice->invoice_number }}</x-slot>

    <x-breadcrumbs :items="[
        ['label' => 'Sales', 'url' => route('invoices.index')],
        ['label' => 'Invoice #' . $invoice->invoice_number, 'url' => route('invoices.show', $invoice)],
        ['label' => 'Create Return'],
    ]" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form action="{{ route('invoices.return-store', $invoice) }}" method="POST">
            @csrf

            <div class="overflow-x-auto mb-6">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="text-left px-3 py-2.5 font-medium">Product</th>
                            <th class="text-center px-3 py-2.5 font-medium">Quantity Purchased</th>
                            <th class="text-center px-3 py-2.5 font-medium">Unit Price</th>
                            <th class="text-center px-3 py-2.5 font-medium">Return Qty</th>
                            <th class="text-left px-3 py-2.5 font-medium">Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr class="border-b border-slate-100">
                            <td class="px-3 py-2.5">
                                {{ $item->product->name }}
                                <input type="hidden" name="items[{{ $loop->index }}][product_id]" value="{{ $item->product_id }}">
                                <input type="hidden" name="items[{{ $loop->index }}][product_unit_id]" value="{{ $item->product_unit_id }}">
                                <input type="hidden" name="items[{{ $loop->index }}][unit_price]" value="{{ $item->unit_price }}">
                            </td>
                            <td class="text-center px-3 py-2.5">{{ $item->quantity }}</td>
                            <td class="text-center px-3 py-2.5">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-center px-3 py-2.5">
                                <input type="number" name="items[{{ $loop->index }}][quantity]" min="0.001" max="{{ $item->quantity }}" step="0.001" value="0" class="w-24 border border-slate-300 rounded px-2 py-1 text-sm text-center">
                            </td>
                            <td class="px-3 py-2.5">
                                <select name="items[{{ $loop->index }}][reason]" class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                                    <option value="damaged">Damaged</option>
                                    <option value="wrong_item">Wrong Item</option>
                                    <option value="expired">Expired</option>
                                    <option value="customer_dissatisfaction">Customer Dissatisfaction</option>
                                    <option value="pricing_error">Pricing Error</option>
                                    <option value="duplicate_order">Duplicate Order</option>
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Reason (Overall)</label>
                    <input type="text" name="reason" class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm"></textarea>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-warning text-white text-sm rounded-lg hover:bg-warning-600 transition">Create Return</button>
                <a href="{{ route('invoices.show', $invoice) }}" class="px-4 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
