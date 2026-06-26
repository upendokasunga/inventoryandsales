<x-app-layout>
    <x-slot name="header">{{ __('Reservation Details') }}</x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('sales.reservations.index') }}" class="erp-btn-secondary">Back to List</a>
            @if ($stockReservation->status === 'active')
                <form action="{{ route('sales.reservations.release', $stockReservation) }}" method="POST" class="inline"
                    onsubmit="return confirm('Release this reservation? Stock will be freed up.');">
                    @csrf
                    <button type="submit" class="erp-btn-danger">Release Reservation</button>
                </form>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Reservation Info</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Sales Order</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $stockReservation->salesOrder?->so_number ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Customer</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $stockReservation->salesOrder?->customer?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Status</dt>
                            <dd><span class="px-2 py-1 text-xs font-medium rounded-full {{ $stockReservation->status === 'active' ? 'bg-blue-100 text-blue-700' : ($stockReservation->status === 'fulfilled' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600') }}">{{ ucfirst($stockReservation->status) }}</span></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Reserved At</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $stockReservation->reserved_at?->format('M d, Y H:i') ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Expires At</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $stockReservation->expires_at?->format('M d, Y H:i') ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Audit</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Created By</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $stockReservation->creator?->name ?? '-' }}</dd>
                        </div>
                        @if ($stockReservation->releaser)
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Released By</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $stockReservation->releaser->name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Released At</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $stockReservation->released_at?->format('M d, Y H:i') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
            @if ($stockReservation->notes)
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <p class="text-sm text-slate-700">{{ $stockReservation->notes }}</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <h3 class="text-sm font-medium text-slate-500 mb-4">Reserved Items</h3>
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Batch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Reserved Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Fulfilled Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($stockReservation->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $item->product?->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-500">{{ $item->inventoryBatch?->batch_number ?? 'Auto' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ number_format($item->quantity, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ ($item->quantity_fulfilled ?? 0) >= $item->quantity ? 'text-green-600 font-semibold' : 'text-slate-500' }}">
                                    {{ number_format($item->quantity_fulfilled ?? 0, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-slate-500">No reserved items.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
