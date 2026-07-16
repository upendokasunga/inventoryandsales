<x-app-layout>
    <x-slot name="header">
        {{ __('Goods Receipt') }} #{{ $goodsReceipt->receipt_number ?? $goodsReceipt->id }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('purchasing.receipts.index') }}" class="erp-btn-secondary">Back to List</a>
            <div class="flex gap-2">
                <a href="{{ route('purchasing.receipts.print', $goodsReceipt) }}" class="erp-btn-secondary" target="_blank">Print PDF</a>
                <form action="{{ route('purchasing.receipts.complete', $goodsReceipt) }}" method="POST" class="inline"
                    onsubmit="return confirm('Complete this receipt? This will update order quantities and post to inventory.');">
                    @csrf @method('PATCH')
                    <button type="submit" class="erp-btn-primary">Complete Receipt</button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-slate-500 mb-4">Receipt Information</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Receipt #</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ $goodsReceipt->receipt_number ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">PO Number</dt>
                                <dd class="text-sm font-semibold text-slate-800">
                                    @if($goodsReceipt->purchaseOrder)
                                        <a href="{{ route('purchasing.orders.show', $goodsReceipt->purchaseOrder) }}" class="text-primary hover:underline">{{ $goodsReceipt->purchaseOrder->po_number }}</a>
                                    @else
                                        -
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Supplier</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $goodsReceipt->purchaseOrder?->supplier?->name ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Store</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $goodsReceipt->warehouse?->name ?? 'Main Store' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Receipt Date</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $goodsReceipt->receipt_date?->format('M d, Y') ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Status</dt>
                                <dd>
                                    @php
                                        $c = ['completed' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-700'];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $c[$goodsReceipt->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($goodsReceipt->status) }}</span>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Created By</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $goodsReceipt->creator?->name ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-slate-500 mb-4">Accounting Impact</h3>
                        <dl class="space-y-3">
                            @if($goodsReceipt->status === 'completed')
                                <div class="p-3 bg-green-50 rounded-lg border border-green-100">
                                    <p class="text-sm font-medium text-green-700">Inventory Updated</p>
                                    <p class="text-xs text-green-600 mt-1">Stock has been received and inventory balances updated. Journal entry posted for goods received.</p>
                                </div>
                                @if($goodsReceipt->purchaseOrder)
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-slate-500">PO Total</dt>
                                        <dd class="text-sm font-medium text-slate-800">TSh {{ number_format($goodsReceipt->purchaseOrder->total, 2) }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-slate-500">PO Paid</dt>
                                        <dd class="text-sm font-medium {{ $goodsReceipt->purchaseOrder->amount_paid > 0 ? 'text-green-600' : 'text-slate-500' }}">TSh {{ number_format($goodsReceipt->purchaseOrder->amount_paid, 2) }}</dd>
                                    </div>
                                @endif
                            @endif
                        </dl>
                    </div>
                </div>

                @if ($goodsReceipt->notes)
                    <div class="mt-6 pt-6 border-t border-slate-100">
                        <h3 class="text-sm font-medium text-slate-500 mb-2">Notes</h3>
                        <p class="text-sm text-slate-700">{{ $goodsReceipt->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <h3 class="text-sm font-medium text-slate-500 mb-4">Received Items</h3>
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Expected</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Received</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Unit Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Condition</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($goodsReceipt->items as $receiptItem)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $receiptItem->product?->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $receiptItem->expected_quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $receiptItem->received_quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">TSh {{ number_format($receiptItem->purchaseOrderItem?->unit_price ?? $receiptItem->product?->standard_cost ?? 0, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $col = ['good' => 'text-green-600', 'damaged' => 'text-red-600', 'partial' => 'text-amber-600', 'return' => 'text-purple-600'];
                                    @endphp
                                    <span class="text-sm font-medium {{ $col[$receiptItem->condition] ?? 'text-slate-600' }}">{{ ucfirst($receiptItem->condition) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $receiptItem->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500">No items.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
