<x-app-layout>
    <x-slot name="header">{{ __('Supplier Payment') }} #{{ $supplierPayment->id }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('supplier-payments.index') }}" class="erp-btn-secondary">Back to List</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
            <div class="p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-slate-500 mb-4">Payment Details</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Payment #</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ $supplierPayment->id }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Supplier</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $supplierPayment->supplier?->name ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Amount</dt>
                                <dd class="text-sm font-bold text-slate-800">{{ number_format($supplierPayment->amount, 2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Purchase Order</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $supplierPayment->purchaseOrder?->po_number ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Status</dt>
                                <dd>
                                    @php
                                        $c = ['pending' => 'bg-amber-100 text-amber-700', 'approved' => 'bg-blue-100 text-blue-700', 'paid' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700'];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $c[$supplierPayment->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($supplierPayment->status) }}</span>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Payment Date</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $supplierPayment->payment_date?->format('M d, Y') ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Method</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $supplierPayment->payment_method ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Created By</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $supplierPayment->creator?->name ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        @if ($supplierPayment->notes)
                            <div class="mb-4">
                                <h3 class="text-sm font-medium text-slate-500 mb-2">Notes</h3>
                                <p class="text-sm text-slate-700">{{ $supplierPayment->notes }}</p>
                            </div>
                        @endif

                        @if ($supplierPayment->purchaseOrder)
                            @php
                                $po = $supplierPayment->purchaseOrder;
                                $poTotal = (float) ($po->total_amount ?: $po->total);
                                $paid = (float) $po->amount_paid;
                                $remaining = $poTotal - $paid;
                                $pct = $poTotal > 0 ? round(($paid / $poTotal) * 100) : 0;
                            @endphp
                            <div class="p-4 bg-slate-50 rounded-lg border border-slate-200 mb-4">
                                <h3 class="text-sm font-medium text-slate-700 mb-3">PO Payment Status</h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">PO Total</span>
                                        <span class="font-medium text-slate-800">{{ number_format($poTotal, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">Amount Paid</span>
                                        <span class="font-medium text-emerald-600">{{ number_format($paid, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">Balance Due</span>
                                        <span class="font-medium {{ $remaining > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ number_format($remaining, 2) }}</span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-2 mt-2">
                                        <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <p class="text-xs text-slate-500 text-right">{{ $pct }}% paid</p>
                                </div>
                            </div>
                        @endif

                        @if ($supplierPayment->status === 'pending')
                            <div class="p-4 bg-amber-50 rounded-lg border border-amber-200">
                                <p class="text-sm font-medium text-amber-800 mb-3">Pending Approval</p>
                                <div class="flex gap-2">
                                    <form action="{{ route('supplier-payments.approve', $supplierPayment) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="erp-btn-primary">Approve Payment</button>
                                    </form>
                                    <form action="{{ route('supplier-payments.reject', $supplierPayment) }}" method="POST" onsubmit="return confirm('Reject this payment?');">
                                        @csrf
                                        <button type="submit" class="erp-btn-danger">Reject Payment</button>
                                    </form>
                                </div>
                            </div>
                        @endif

                        @if ($supplierPayment->status === 'approved')
                            <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <p class="text-sm font-medium text-blue-800 mb-3">Approved — Ready to Process</p>
                                <form action="{{ route('supplier-payments.process-payment', $supplierPayment) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-slate-700">Payment Account</label>
                                        <select name="payment_account_id" required class="mt-1 block w-full erp-input">
                                            <option value="">Select Account</option>
                                            @foreach (\App\Models\Account::where('type', 'asset')->where('is_active', true)->get() as $acct)
                                                <option value="{{ $acct->id }}">{{ $acct->name }} ({{ $acct->code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-slate-700">Payment Date</label>
                                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="mt-1 block w-full erp-input">
                                    </div>
                                    <button type="submit" class="erp-btn-primary">Process Payment</button>
                                </form>
                            </div>
                        @endif

                        @if ($supplierPayment->status === 'paid')
                            <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                                <p class="text-sm font-medium text-green-800">Payment completed.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment History for this PO --}}
        @if (count($paymentHistory) > 1)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60">
                    <h3 class="text-lg font-semibold text-slate-800">Payment History for {{ $supplierPayment->purchaseOrder?->po_number }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Created By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($paymentHistory as $hist)
                                <tr class="{{ $hist->id === $supplierPayment->id ? 'bg-blue-50/50' : '' }}">
                                    <td class="px-6 py-3 text-sm font-medium text-primary">
                                        <a href="{{ route('supplier-payments.show', $hist) }}">{{ $hist->id }}</a>
                                    </td>
                                    <td class="px-6 py-3 text-sm font-mono text-slate-800">{{ number_format($hist->amount, 2) }}</td>
                                    <td class="px-6 py-3">
                                        @php
                                            $hc = ['pending' => 'erp-badge-draft', 'approved' => 'erp-badge-info', 'paid' => 'erp-badge-fulfilled', 'rejected' => 'erp-badge-cancelled'];
                                        @endphp
                                        <span class="{{ $hc[$hist->status] ?? 'erp-badge-draft' }}">{{ ucfirst($hist->status) }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-slate-500">{{ $hist->payment_date?->format('M d, Y') ?? '-' }}</td>
                                    <td class="px-6 py-3 text-sm text-slate-500">{{ $hist->creator?->name ?? '-' }}</td>
                                    <td class="px-6 py-3 text-sm">
                                        @if ($hist->id !== $supplierPayment->id)
                                            <a href="{{ route('supplier-payments.show', $hist) }}" class="text-primary hover:underline">View</a>
                                        @else
                                            <span class="text-xs text-slate-400">Current</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
