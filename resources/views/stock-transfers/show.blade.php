<x-app-layout>
    <x-slot name="header">{{ __('Stock Transfer') }}: {{ $stockTransfer->transfer_number }}</x-slot>
    <div class="max-w-7xl mx-auto">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('stock-transfers.index') }}" class="erp-btn-secondary">Back to List</a>
            <div class="flex gap-2">
                <a href="{{ route('stock-transfers.print', $stockTransfer) }}" class="erp-btn-secondary" target="_blank">Print PDF</a>
                @if (in_array($stockTransfer->status, ['pending', 'draft']))
                    <a href="{{ route('stock-transfers.edit', $stockTransfer) }}" class="erp-btn-primary">Edit</a>
                @endif
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Transfer Information</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Transfer #</dt>
                            <dd class="text-sm font-semibold text-slate-800">{{ $stockTransfer->transfer_number }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Source Warehouse</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $stockTransfer->sourceWarehouse?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Destination Warehouse</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $stockTransfer->destinationWarehouse?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Status</dt>
                            <dd>@php $c = ['pending' => 'erp-badge-pending', 'approved' => 'erp-badge-approved', 'issued' => 'erp-badge-info', 'received' => 'erp-badge-fulfilled', 'rejected' => 'erp-badge-cancelled']; @endphp <span class="{{ $c[$stockTransfer->status] ?? 'erp-badge-draft' }}">{{ ucfirst($stockTransfer->status) }}</span></dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Audit</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Created By</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $stockTransfer->creator?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Created At</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $stockTransfer->created_at->format('d M Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Reason</h3>
                    <p class="text-sm text-slate-700">{{ $stockTransfer->reason ?? '-' }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
            <h3 class="text-sm font-medium text-slate-500 mb-4">Timeline</h3>
            <div class="flow-root">
                <ul class="-mb-4">
                    <li class="relative pb-4">
                        <div class="absolute left-2 top-2 -bottom-4 w-0.5 bg-slate-200"></div>
                        <div class="flex items-start gap-3">
                            <span class="relative z-10 mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 ring-2 ring-white"><svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4.5 12.75l6 6 9-13.5"/></svg></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-slate-800">Created</p>
                                <p class="text-xs text-slate-500">{{ $stockTransfer->creator?->name ?? 'System' }} — {{ $stockTransfer->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </li>
                    @if ($stockTransfer->approved_at)
                        <li class="relative pb-4">
                            <div class="absolute left-2 top-2 -bottom-4 w-0.5 bg-slate-200"></div>
                            <div class="flex items-start gap-3">
                                <span class="relative z-10 mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-blue-500 ring-2 ring-white"><svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4.5 12.75l6 6 9-13.5"/></svg></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-800">Approved</p>
                                    <p class="text-xs text-slate-500">{{ $stockTransfer->approver?->name ?? 'System' }} — {{ $stockTransfer->approved_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        </li>
                    @endif
                    @if ($stockTransfer->issued_at)
                        <li class="relative pb-4">
                            <div class="absolute left-2 top-2 -bottom-4 w-0.5 bg-slate-200"></div>
                            <div class="flex items-start gap-3">
                                <span class="relative z-10 mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 ring-2 ring-white"><svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4.5 12.75l6 6 9-13.5"/></svg></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-800">Issued</p>
                                    <p class="text-xs text-slate-500">{{ $stockTransfer->issuer?->name ?? 'System' }} — {{ $stockTransfer->issued_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        </li>
                    @endif
                    @if ($stockTransfer->received_at)
                        <li class="relative pb-4">
                            <div class="flex items-start gap-3">
                                <span class="relative z-10 mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 ring-2 ring-white"><svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4.5 12.75l6 6 9-13.5"/></svg></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-800">Received</p>
                                    <p class="text-xs text-slate-500">{{ $stockTransfer->receiver?->name ?? 'System' }} — {{ $stockTransfer->received_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        </li>
                    @endif
                    @if ($stockTransfer->status === 'rejected' && !$stockTransfer->received_at)
                        <li class="relative pb-4">
                            <div class="flex items-start gap-3">
                                <span class="relative z-10 mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 ring-2 ring-white"><svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-800">Rejected</p>
                                    <p class="text-xs text-slate-500">{{ $stockTransfer->approver?->name ?? 'System' }}</p>
                                </div>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-200/60">
                <h3 class="text-sm font-medium text-slate-500">Items</h3>
            </div>
            <table class="erp-table">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Transferred</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Received</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Unit Cost</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($stockTransfer->items as $item)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $item->product?->name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 text-right">{{ number_format($item->quantity_transferred, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 text-right">{{ number_format($item->quantity_received, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 text-right">{{ number_format($item->unit_cost, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 font-medium text-right">{{ number_format($item->quantity_transferred * $item->unit_cost, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-slate-400">No items found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
