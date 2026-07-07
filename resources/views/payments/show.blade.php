<x-app-layout>
    <x-slot name="header">
        {{ __('Payment Receipt') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('payments.index') }}" class="erp-btn-secondary">Back to List</a>
            <div class="flex gap-2">
                <a href="{{ route('payments.print', $payment) }}" class="erp-btn-primary" target="_blank">Print PDF</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
            <div class="p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-slate-500 mb-4">Payment Information</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Payment Method</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Reference #</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ $payment->reference_number ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Amount</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ number_format($payment->amount, 0) }} TZS</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Date</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ $payment->payment_date->format('d M Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Received By</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ $payment->receiver->name ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-slate-500 mb-4">Customer</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Name</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ $payment->customer->name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Phone</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ $payment->customer->phone }}</dd>
                            </div>
                        </dl>
                        <h3 class="text-sm font-medium text-slate-500 mb-4 mt-6">Invoice</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Invoice #</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ $payment->invoice->invoice_number }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Total</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ number_format($payment->invoice->total, 0) }} TZS</dd>
                            </div>
                        </dl>
                    </div>
                </div>
                @if($payment->notes)
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                        <h4 class="text-sm font-medium text-slate-500 mb-2">Notes</h4>
                        <p class="text-sm text-slate-700">{{ $payment->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
