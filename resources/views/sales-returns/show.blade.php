<x-app-layout>
    <x-slot name="header">Sales Return {{ $salesReturn->return_number }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Returns', 'url' => route('sales-returns.index')], ['label' => $salesReturn->return_number]]" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Return #{{ $salesReturn->return_number }}</h2>
                        <p class="text-sm text-slate-500">Date: {{ $salesReturn->created_at->format('d M Y') }}</p>
                        <p class="text-sm text-slate-500">Customer: {{ $salesReturn->customer->name }}</p>
                        @if($salesReturn->invoice)
                            <p class="text-sm text-slate-500">Invoice: {{ $salesReturn->invoice->invoice_number }}</p>
                        @endif
                    </div>
                    <span class="px-3 py-1 text-sm rounded-full font-medium
                        @if($salesReturn->status === 'draft') bg-slate-100 text-slate-600
                        @elseif($salesReturn->status === 'pending_approval') bg-orange-100 text-orange-700
                        @elseif($salesReturn->status === 'approved') bg-blue-100 text-blue-700
                        @elseif($salesReturn->status === 'rejected') bg-red-100 text-red-700
                        @else bg-green-100 text-green-700 @endif">
                        {{ ucfirst(str_replace('_', ' ', $salesReturn->status)) }}
                    </span>
                </div>

                <table class="w-full text-sm mb-4">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="text-left px-3 py-2">Product</th>
                            <th class="text-center px-3 py-2">Qty</th>
                            <th class="text-right px-3 py-2">Price</th>
                            <th class="text-right px-3 py-2">Total</th>
                            <th class="text-left px-3 py-2">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($salesReturn->items as $item)
                            <tr>
                                <td class="px-3 py-2.5">{{ $item->product->name ?? 'N/A' }}</td>
                                <td class="px-3 py-2.5 text-center">{{ $item->quantity }}</td>
                                <td class="px-3 py-2.5 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-3 py-2.5 text-right font-medium">{{ number_format($item->line_total, 2) }}</td>
                                <td class="px-3 py-2.5 capitalize text-slate-500">{{ str_replace('_', ' ', $item->reason) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="border-t border-slate-200 pt-4 text-right">
                    <p class="text-sm text-slate-500">Total Amount</p>
                    <p class="text-xl font-bold text-primary">{{ number_format($salesReturn->total_amount, 2) }}</p>
                </div>

                @if($salesReturn->notes)
                    <div class="mt-4 p-3 bg-slate-50 rounded-lg text-sm text-slate-600">
                        <p class="text-xs text-slate-400 uppercase">Notes</p>
                        {{ $salesReturn->notes }}
                    </div>
                @endif
            </div>

            {{-- Credit Note --}}
            @if($salesReturn->creditNote->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-sm font-semibold text-slate-700 mb-4">Credit Notes</h3>
                    @foreach($salesReturn->creditNote as $cn)
                        <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                            <div>
                                <a href="{{ route('credit-notes.show', $cn) }}" class="font-medium text-primary hover:underline">{{ $cn->credit_note_number }}</a>
                                <p class="text-xs text-slate-500">{{ number_format($cn->amount, 2) }} — {{ ucfirst($cn->status) }}</p>
                            </div>
                            <span class="text-xs text-slate-400">{{ $cn->issued_date->format('d M Y') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-3">
                <h3 class="text-sm font-semibold text-slate-700">Actions</h3>

                @if($salesReturn->status === 'draft')
                    <form action="{{ route('sales-returns.approve', $salesReturn) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600 transition">Approve & Generate Credit Note</button>
                    </form>
                    <form action="{{ route('sales-returns.reject', $salesReturn) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 bg-danger text-white text-sm rounded-lg hover:bg-danger-600 transition">Reject</button>
                    </form>
                @endif

                @if($salesReturn->status === 'approved')
                    <form action="{{ route('sales-returns.complete', $salesReturn) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 bg-success text-white text-sm rounded-lg hover:bg-success-600 transition">Mark Completed</button>
                    </form>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Details</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Created By</span><span>{{ $salesReturn->creator?->name ?? 'System' }}</span></div>
                    @if($salesReturn->approver)
                        <div class="flex justify-between"><span class="text-slate-500">Approved By</span><span>{{ $salesReturn->approver->name }}</span></div>
                    @endif
                    <div class="flex justify-between"><span class="text-slate-500">Reason</span><span class="capitalize">{{ $salesReturn->reason ?? 'N/A' }}</span></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
