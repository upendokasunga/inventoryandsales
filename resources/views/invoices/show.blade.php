<x-app-layout>
    <x-slot name="header">Invoice {{ $invoice->invoice_number }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Sales', 'url' => route('invoices.index')], ['label' => $invoice->invoice_number]]" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Invoice Details --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">{{ config('app.name') }}</h2>
                        <p class="text-sm text-slate-500">Invoice #: <span class="font-medium text-slate-700">{{ $invoice->invoice_number }}</span></p>
                        <p class="text-sm text-slate-500">Date: {{ $invoice->invoice_date->format('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 text-sm rounded-full font-medium
                            @if($invoice->status === 'draft') bg-slate-100 text-slate-600
                            @elseif($invoice->status === 'proforma') bg-purple-100 text-purple-700
                            @elseif($invoice->status === 'approved') bg-blue-100 text-blue-700
                            @elseif($invoice->status === 'posted') bg-teal-100 text-teal-700
                            @elseif($invoice->status === 'completed') bg-green-100 text-green-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ ucfirst($invoice->status) }}
                        </span>
                        <span class="ml-2 px-3 py-1 text-sm rounded-full font-medium
                            @if($invoice->payment_status === 'paid') bg-green-100 text-green-700
                            @elseif($invoice->payment_status === 'partial') bg-purple-100 text-purple-700
                            @elseif($invoice->payment_status === 'overdue') bg-red-800 text-white
                            @else bg-slate-100 text-slate-600 @endif">
                            {{ ucfirst($invoice->payment_status) }}
                        </span>
                    </div>
                </div>

                {{-- Customer --}}
                <div class="mb-6 p-4 bg-slate-50 rounded-lg">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Customer</p>
                    <p class="font-medium text-slate-700">{{ $invoice->customer->name ?? 'Walk-in Customer' }}</p>
                    @if($invoice->customer)
                        <p class="text-sm text-slate-500">{{ $invoice->customer->phone }}</p>
                        <p class="text-sm text-slate-500">{{ $invoice->customer->email }}</p>
                    @endif
                </div>

                {{-- Items --}}
                <table class="w-full text-sm mb-6">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="text-left px-3 py-2">Product</th>
                            <th class="text-center px-3 py-2">Qty</th>
                            <th class="text-right px-3 py-2">Price</th>
                            <th class="text-right px-3 py-2">Discount</th>
                            <th class="text-right px-3 py-2">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($invoice->items as $item)
                            <tr>
                                <td class="px-3 py-2.5">
                                    {{ $item->product->name ?? 'N/A' }}
                                    @if($item->store)
                                        <br><span class="text-xs text-slate-400">Store: {{ $item->store->name }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-center">{{ $item->quantity }} {{ $item->unit?->name ?? '' }}</td>
                                <td class="px-3 py-2.5 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-3 py-2.5 text-right">{{ number_format($item->discount, 2) }}</td>
                                <td class="px-3 py-2.5 text-right font-medium">{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Totals --}}
                <div class="border-t border-slate-200 pt-4 space-y-1 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span>{{ number_format($invoice->subtotal, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Discount</span><span class="text-danger">-{{ number_format($invoice->discount, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Tax</span><span>{{ number_format($invoice->tax, 2) }}</span></div>
                    <div class="flex justify-between text-base font-bold text-slate-800 pt-2 border-t border-slate-200"><span>Total</span><span class="text-primary">{{ number_format($invoice->total, 2) }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Paid</span><span class="text-success">{{ number_format($invoice->amount_paid, 2) }}</span></div>
                    <div class="flex justify-between text-sm font-medium"><span class="text-slate-500">Balance Due</span><span class="{{ $invoice->balance_due > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($invoice->balance_due, 2) }}</span></div>
                </div>

                @if($invoice->notes)
                    <div class="mt-4 p-3 bg-slate-50 rounded-lg text-sm text-slate-600">
                        <p class="text-xs text-slate-400 uppercase">Notes</p>
                        {{ $invoice->notes }}
                    </div>
                @endif
            </div>

            {{-- Payments --}}
            @if($invoice->payments->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-sm font-semibold text-slate-700 mb-4">Payment History</h3>
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                            <tr>
                                <th class="text-left px-3 py-2">Date</th>
                                <th class="text-left px-3 py-2">Method</th>
                                <th class="text-right px-3 py-2">Amount</th>
                                <th class="text-left px-3 py-2">Reference</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($invoice->payments as $payment)
                                <tr>
                                    <td class="px-3 py-2">{{ $payment->payment_date->format('d M Y') }}</td>
                                    <td class="px-3 py-2 capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                    <td class="px-3 py-2 text-right font-medium text-success">{{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-3 py-2 text-slate-500">{{ $payment->reference_number ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-3">
                <h3 class="text-sm font-semibold text-slate-700">Actions</h3>

                @if($invoice->status === 'draft')
                    <form action="{{ route('invoices.proforma', $invoice) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 bg-purple-500 text-white text-sm rounded-lg hover:bg-purple-600 transition">Convert to Proforma</button>
                    </form>
                    <form action="{{ route('invoices.approve', $invoice) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600 transition">Approve Invoice</button>
                    </form>
                @endif

                @if($invoice->status === 'proforma')
                    <form action="{{ route('invoices.approve', $invoice) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600 transition">Submit for Approval</button>
                    </form>
                    <form action="{{ route('invoices.revert-draft', $invoice) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 bg-slate-500 text-white text-sm rounded-lg hover:bg-slate-600 transition">Revert to Draft</button>
                    </form>
                @endif

                @if(in_array($invoice->payment_status, ['pending', 'partial']))
                    <a href="{{ route('payments.create', $invoice) }}" class="block w-full text-center px-3 py-2 bg-success text-white text-sm rounded-lg hover:bg-success-600 transition">Record Payment</a>
                @endif

                @if(in_array($invoice->status, ['approved', 'posted']))
                    <a href="{{ route('invoices.return-create', $invoice) }}" class="block w-full text-center px-3 py-2 bg-warning text-white text-sm rounded-lg hover:bg-warning-600 transition">Create Return</a>
                    <a href="{{ route('invoices.discount-create', $invoice) }}" class="block w-full text-center px-3 py-2 bg-info text-white text-sm rounded-lg hover:bg-info-600 transition">Apply Discount</a>
                @endif

                <a href="{{ route('invoices.credit-notes', $invoice) }}" class="block w-full text-center px-3 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition">Credit Notes</a>

                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="block w-full text-center px-3 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition">Print Invoice</a>
                <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="block w-full text-center px-3 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition">Print PDF</a>
                <a href="{{ route('invoices.receipt', $invoice) }}" target="_blank" class="block w-full text-center px-3 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition">Print Receipt</a>

                @can('edit')
                    <a href="{{ route('invoices.edit', $invoice) }}" class="block w-full text-center px-3 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition">Edit</a>
                @endcan

                @can('delete')
                    <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('Delete this invoice?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full px-3 py-2 bg-danger text-white text-sm rounded-lg hover:bg-danger-600 transition">Delete</button>
                    </form>
                @endcan
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Created By</span><span>{{ $invoice->creator?->name ?? 'System' }}</span></div>
                    @if($invoice->approver)
                        <div class="flex justify-between"><span class="text-slate-500">Approved By</span><span>{{ $invoice->approver->name }}</span></div>
                    @endif
                    <div class="flex justify-between"><span class="text-slate-500">Payment Type</span><span class="capitalize">{{ str_replace('_', ' ', $invoice->payment_type) }}</span></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
