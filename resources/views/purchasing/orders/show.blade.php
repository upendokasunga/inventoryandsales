<x-app-layout>
    <x-slot name="header">
        {{ __('Purchase Order') }}: {{ $purchaseOrder->po_number }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('error'))
            <div class="mb-4 px-4 py-2 text-red-700 bg-red-50 border border-red-100 rounded-lg">{{ session('error') }}</div>
        @endif

        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('purchasing.orders.index') }}" class="erp-btn-secondary">Back to List</a>
            <div class="flex gap-2">
                @if ($purchaseOrder->status === 'draft')
                    <a href="{{ route('purchasing.orders.edit', $purchaseOrder) }}" class="erp-btn-primary">Edit</a>
                    <form action="{{ route('purchasing.orders.submit-approval', $purchaseOrder) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="erp-btn-secondary">Submit for Approval</button>
                    </form>
                @endif
                @if ($purchaseOrder->status === 'pending_approval')
                    <form action="{{ route('purchasing.orders.approve', $purchaseOrder) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="erp-btn-primary">Approve</button>
                    </form>
                    <form action="{{ route('purchasing.orders.reject', $purchaseOrder) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="erp-btn-danger">Reject</button>
                    </form>
                @endif
                @if ($purchaseOrder->status === 'approved')
                    <form action="{{ route('purchasing.orders.send', $purchaseOrder) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="erp-btn-primary">Send to Supplier</button>
                    </form>
                @endif
                @if (in_array($purchaseOrder->status, ['draft', 'approved', 'sent']))
                    <form action="{{ route('purchasing.orders.cancel', $purchaseOrder) }}" method="POST" class="inline"
                        onsubmit="return confirm('Cancel this order?');">
                        @csrf
                        <button type="submit" class="erp-btn-danger">Cancel Order</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
            <div class="p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-slate-500 mb-4">Order Information</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">PO Number</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ $purchaseOrder->po_number }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Supplier</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $purchaseOrder->supplier?->name ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Order Date</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $purchaseOrder->order_date?->format('M d, Y') ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Expected Date</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $purchaseOrder->expected_date?->format('M d, Y') ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Status</dt>
                                <dd>
                                    @php
                                        $c = ['draft' => 'bg-slate-100 text-slate-600', 'pending_approval' => 'bg-amber-100 text-amber-700', 'approved' => 'bg-green-100 text-green-700', 'sent' => 'bg-blue-100 text-blue-700', 'partially_received' => 'bg-purple-100 text-purple-700', 'completed' => 'bg-emerald-100 text-emerald-700', 'cancelled' => 'bg-red-100 text-red-700'];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $c[$purchaseOrder->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst(str_replace('_', ' ', $purchaseOrder->status)) }}</span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-slate-500 mb-4">Financial Summary</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Subtotal</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ number_format($purchaseOrder->subtotal, 2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Tax</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ number_format($purchaseOrder->tax, 2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Total</dt>
                                <dd class="text-sm font-bold text-slate-800">{{ number_format($purchaseOrder->total, 2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Created By</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $purchaseOrder->creator?->name ?? '-' }}</dd>
                            </div>
                            @if ($purchaseOrder->approver)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-slate-500">Approved By</dt>
                                    <dd class="text-sm font-medium text-slate-800">{{ $purchaseOrder->approver->name }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                @if ($purchaseOrder->notes)
                    <div class="mt-6 pt-6 border-t border-slate-100">
                        <h3 class="text-sm font-medium text-slate-500 mb-2">Notes</h3>
                        <p class="text-sm text-slate-700">{{ $purchaseOrder->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <h3 class="text-sm font-medium text-slate-500 mb-4">Order Items</h3>
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Unit Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Subtotal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Received</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($purchaseOrder->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $item->product?->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ number_format($item->subtotal, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $item->received_quantity ?? 0 }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">No items.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
