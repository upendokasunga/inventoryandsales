<x-app-layout>
    <x-slot name="header">{{ __('Sales Order') }}: {{ $salesOrder->so_number }}</x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('error'))
            <div class="mb-4 px-4 py-2 text-red-700 bg-red-50 border border-red-100 rounded-lg">{{ session('error') }}</div>
        @endif

        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('sales.orders.index') }}" class="erp-btn-secondary">Back to List</a>
            <div class="flex gap-2">
                <a href="{{ route('sales.orders.print', $salesOrder) }}" class="erp-btn-secondary" target="_blank">Print PDF</a>
                @if ($salesOrder->status === 'draft')
                    <a href="{{ route('sales.orders.edit', $salesOrder) }}" class="erp-btn-primary">Edit</a>
                    <form action="{{ route('sales.orders.submit-approval', $salesOrder) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="erp-btn-secondary">Submit for Approval</button>
                    </form>
                @endif
                @if ($salesOrder->status === 'pending_approval')
                    <form action="{{ route('sales.orders.approve', $salesOrder) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="erp-btn-primary">Approve</button>
                    </form>
                    <form action="{{ route('sales.orders.reject', $salesOrder) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="erp-btn-danger">Reject</button>
                    </form>
                @endif
                @if ($salesOrder->status === 'approved')
                    <form action="{{ route('sales.orders.reserve', $salesOrder) }}" method="POST" class="inline"
                        onsubmit="return confirm('Reserve stock for this order?');">
                        @csrf
                        <button type="submit" class="erp-btn-primary">Reserve Stock</button>
                    </form>
                @endif
                @if (in_array($salesOrder->status, ['reserved', 'partially_fulfilled']))
                    <form action="{{ route('sales.orders.fulfill', $salesOrder) }}" method="POST" class="inline"
                        onsubmit="return confirm('Fulfill this order? This will deduct stock and update credit.');">
                        @csrf
                        <button type="submit" class="erp-btn-primary bg-green-600 hover:bg-green-700">Fulfill</button>
                    </form>
                @endif
                @if (in_array($salesOrder->status, ['draft', 'pending_approval', 'approved']))
                    <form action="{{ route('sales.orders.cancel', $salesOrder) }}" method="POST" class="inline"
                        onsubmit="return confirm('Cancel this order?');">
                        @csrf
                        <button type="submit" class="erp-btn-danger">Cancel</button>
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
                                <dt class="text-sm text-slate-500">SO Number</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ $salesOrder->so_number }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Customer</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $salesOrder->customer?->name ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Order Date</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $salesOrder->order_date?->format('M d, Y') ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Delivery Date</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $salesOrder->delivery_date?->format('M d, Y') ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Status</dt>
                                <dd>
                                    @php
                                        $c = ['draft' => 'bg-slate-100 text-slate-600', 'pending_approval' => 'bg-amber-100 text-amber-700', 'approved' => 'bg-blue-100 text-blue-700', 'reserved' => 'bg-purple-100 text-purple-700', 'partially_fulfilled' => 'bg-amber-100 text-amber-700', 'fulfilled' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-700'];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $c[$salesOrder->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst(str_replace('_', ' ', $salesOrder->status)) }}</span>
                                </dd>
                            </div>
                            @if ($salesOrder->payment_terms)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-slate-500">Payment Terms</dt>
                                    <dd class="text-sm font-medium text-slate-800">{{ $salesOrder->payment_terms }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-slate-500 mb-4">Financial Summary</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Subtotal</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ number_format($salesOrder->subtotal, 2) }}</dd>
                            </div>
                            @if ($salesOrder->discount > 0)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-slate-500">Discount ({{ $salesOrder->discount_type === 'percentage' ? '%' : 'Fixed' }})</dt>
                                    <dd class="text-sm font-medium text-red-600">-{{ number_format($salesOrder->discount, 2) }}</dd>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Tax</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ number_format($salesOrder->tax, 2) }}</dd>
                            </div>
                            <div class="flex justify-between border-t border-slate-100 pt-2">
                                <dt class="text-sm font-semibold text-slate-700">Total</dt>
                                <dd class="text-sm font-bold text-slate-800">{{ number_format($salesOrder->total, 2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Fulfillment Progress</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $fulfillmentStatus['progress'] }}%</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                @if ($salesOrder->notes)
                    <div class="mt-6 pt-6 border-t border-slate-100">
                        <h3 class="text-sm font-medium text-slate-500 mb-2">Notes</h3>
                        <p class="text-sm text-slate-700">{{ $salesOrder->notes }}</p>
                    </div>
                @endif

                <div class="mt-6 pt-6 border-t border-slate-100">
                    <h3 class="text-sm font-medium text-slate-500 mb-2">Audit Trail</h3>
                    <div class="grid grid-cols-3 gap-4 text-xs text-slate-500">
                        <div><strong>Created:</strong> {{ $salesOrder->creator?->name ?? '-' }} @ {{ $salesOrder->created_at->format('M d, H:i') }}</div>
                        @if ($salesOrder->approver)
                            <div><strong>Approved:</strong> {{ $salesOrder->approver->name }} @ {{ $salesOrder->approved_at?->format('M d, H:i') }}</div>
                        @endif
                        @if ($salesOrder->reservist)
                            <div><strong>Reserved:</strong> {{ $salesOrder->reservist->name }} @ {{ $salesOrder->reserved_at?->format('M d, H:i') }}</div>
                        @endif
                        @if ($salesOrder->fulfiller)
                            <div><strong>Fulfilled:</strong> {{ $salesOrder->fulfiller->name }} @ {{ $salesOrder->fulfilled_at?->format('M d, H:i') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
            <div class="p-6">
                <h3 class="text-sm font-medium text-slate-500 mb-4">Order Items</h3>
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Unit Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Subtotal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Fulfilled</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($salesOrder->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $item->product?->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ number_format($item->quantity, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ number_format($item->subtotal, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $item->fulfilled_quantity >= $item->quantity ? 'text-green-600 font-semibold' : 'text-slate-500' }}">
                                    {{ number_format($item->fulfilled_quantity, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ number_format($item->total, 2) }}</td>
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

        @if ($salesOrder->reservations->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Reservations</h3>
                    @foreach ($salesOrder->reservations as $reservation)
                        <div class="border border-slate-200 rounded-lg p-3 mb-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">Status: <strong>{{ ucfirst($reservation->status) }}</strong></span>
                                <span class="text-slate-500">{{ $reservation->reserved_at?->format('M d, H:i') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
