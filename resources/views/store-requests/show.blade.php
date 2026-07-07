<x-app-layout>
    <x-slot name="header">{{ __('Store Request') }}: {{ $storeRequest->request_number }}</x-slot>
    <div class="max-w-7xl mx-auto">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('store-requests.index') }}" class="erp-btn-secondary">Back to List</a>
            <div class="flex gap-2">
                <a href="{{ route('store-requests.print', $storeRequest) }}" class="erp-btn-secondary" target="_blank">Print PDF</a>
                @if (in_array($storeRequest->status, ['pending', 'draft']))
                    <a href="{{ route('store-requests.edit', $storeRequest) }}" class="erp-btn-primary">Edit</a>
                @endif
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Request Information</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Request #</dt>
                            <dd class="text-sm font-semibold text-slate-800">{{ $storeRequest->request_number }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Source Warehouse</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $storeRequest->sourceWarehouse?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Destination Warehouse</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $storeRequest->destinationWarehouse?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Status</dt>
                            <dd>@php $c = ['pending' => 'erp-badge-pending', 'approved' => 'erp-badge-approved', 'issued' => 'erp-badge-info', 'received' => 'erp-badge-fulfilled', 'rejected' => 'erp-badge-cancelled']; @endphp <span class="{{ $c[$storeRequest->status] ?? 'erp-badge-draft' }}">{{ ucfirst($storeRequest->status) }}</span></dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Audit</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Created By</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $storeRequest->creator?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Created At</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $storeRequest->created_at->format('d M Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Reason</h3>
                    <p class="text-sm text-slate-700">{{ $storeRequest->reason ?? '-' }}</p>
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
                                <p class="text-xs text-slate-500">{{ $storeRequest->creator?->name ?? 'System' }} — {{ $storeRequest->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </li>
                    @if ($storeRequest->approved_at)
                        <li class="relative pb-4">
                            <div class="absolute left-2 top-2 -bottom-4 w-0.5 bg-slate-200"></div>
                            <div class="flex items-start gap-3">
                                <span class="relative z-10 mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-blue-500 ring-2 ring-white"><svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4.5 12.75l6 6 9-13.5"/></svg></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-800">Approved</p>
                                    <p class="text-xs text-slate-500">{{ $storeRequest->approver?->name ?? 'System' }} — {{ $storeRequest->approved_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        </li>
                    @endif
                    @if ($storeRequest->issued_at)
                        <li class="relative pb-4">
                            <div class="absolute left-2 top-2 -bottom-4 w-0.5 bg-slate-200"></div>
                            <div class="flex items-start gap-3">
                                <span class="relative z-10 mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 ring-2 ring-white"><svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4.5 12.75l6 6 9-13.5"/></svg></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-800">Issued</p>
                                    <p class="text-xs text-slate-500">{{ $storeRequest->issuer?->name ?? 'System' }} — {{ $storeRequest->issued_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        </li>
                    @endif
                    @if ($storeRequest->received_at)
                        <li class="relative pb-4">
                            <div class="flex items-start gap-3">
                                <span class="relative z-10 mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 ring-2 ring-white"><svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4.5 12.75l6 6 9-13.5"/></svg></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-800">Received</p>
                                    <p class="text-xs text-slate-500">{{ $storeRequest->receiver?->name ?? 'System' }} — {{ $storeRequest->received_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        </li>
                    @endif
                    @if ($storeRequest->status === 'rejected' && !$storeRequest->received_at)
                        <li class="relative pb-4">
                            <div class="flex items-start gap-3">
                                <span class="relative z-10 mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 ring-2 ring-white"><svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-800">Rejected</p>
                                    <p class="text-xs text-slate-500">{{ $storeRequest->approver?->name ?? 'System' }}</p>
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
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Requested</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Issued</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Received</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($storeRequest->items as $item)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $item->product?->name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 text-right">{{ number_format($item->quantity_requested, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 text-right">{{ number_format($item->quantity_issued, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 text-right">{{ number_format($item->quantity_received, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-slate-400">No items found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
