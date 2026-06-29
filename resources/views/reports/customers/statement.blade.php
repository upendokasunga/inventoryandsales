@php
    $header = __('Customer Statement');
@endphp

<x-app-layout>
    <div class="space-y-6">
        <div class="erp-card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-slate-800">{{ __('Customer Statement') }}</h2>
                <form method="GET" class="flex items-center gap-3">
                    <select name="customer_id" class="erp-input text-sm" required>
                        <option value="">{{ __('Select Customer') }}</option>
                        @foreach(\App\Models\Customer::orderBy('name')->get(['id', 'name']) as $customer)
                            <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="start_date" class="erp-input text-sm" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                    <input type="date" name="end_date" class="erp-input text-sm" value="{{ request('end_date', now()->format('Y-m-d')) }}">
                    <button type="submit" class="erp-btn-primary text-sm">{{ __('Generate') }}</button>
                </form>
            </div>

            @if($statement ?? false)
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-slate-50 rounded-lg p-4">
                            <p class="text-xs text-slate-500">{{ __('Total Invoiced') }}</p>
                            <p class="text-lg font-bold text-slate-800">{{ number_format($statement['total_invoiced'], 2) }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-4">
                            <p class="text-xs text-slate-500">{{ __('Total Paid') }}</p>
                            <p class="text-lg font-bold text-success-600">{{ number_format($statement['total_paid'], 2) }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-4">
                            <p class="text-xs text-slate-500">{{ __('Balance') }}</p>
                            <p class="text-lg font-bold text-danger-600">{{ number_format($statement['balance'], 2) }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-4">
                            <p class="text-xs text-slate-500">{{ __('Invoices') }}</p>
                            <p class="text-lg font-bold text-slate-800">{{ count($statement['invoices']) }}</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-slate-700 mb-2">{{ __('Invoices') }}</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-slate-50">
                                        <th class="text-left px-3 py-2 text-xs font-medium text-slate-500">{{ __('Invoice') }}</th>
                                        <th class="text-left px-3 py-2 text-xs font-medium text-slate-500">{{ __('Date') }}</th>
                                        <th class="text-right px-3 py-2 text-xs font-medium text-slate-500">{{ __('Amount') }}</th>
                                        <th class="text-right px-3 py-2 text-xs font-medium text-slate-500">{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($statement['invoices'] as $invoice)
                                        <tr>
                                            <td class="px-3 py-2">{{ $invoice['invoice_number'] }}</td>
                                            <td class="px-3 py-2 text-slate-500">{{ \Carbon\Carbon::parse($invoice['created_at'])->format('Y-m-d') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($invoice['total_amount'], 2) }}</td>
                                            <td class="px-3 py-2 text-right">
                                                <span class="erp-badge-{{ $invoice['payment_status'] === 'paid' ? 'active' : 'inactive' }}">
                                                    {{ $invoice['payment_status'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <p class="text-sm text-slate-500 text-center py-8">{{ __('Select a customer and date range to generate a statement.') }}</p>
            @endif
        </div>
    </div>
</x-app-layout>
