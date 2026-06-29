@php
    $header = __('Inventory Movement Report');
@endphp

<x-app-layout>
    <div class="space-y-6">
        <div class="erp-card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-slate-800">{{ __('Stock Movement') }}</h2>
                <form method="GET" class="flex items-center gap-3">
                    <input type="date" name="start_date" class="erp-input text-sm" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                    <input type="date" name="end_date" class="erp-input text-sm" value="{{ request('end_date', now()->format('Y-m-d')) }}">
                    <button type="submit" class="erp-btn-primary text-sm">{{ __('Filter') }}</button>
                </form>
            </div>

            @if($report ?? false)
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-slate-50 rounded-lg p-4">
                            <p class="text-xs text-slate-500">{{ __('Total Movements') }}</p>
                            <p class="text-lg font-bold text-slate-800">{{ count($report['transactions']) }}</p>
                        </div>
                        @foreach($report['summary'] as $summary)
                            <div class="bg-slate-50 rounded-lg p-4">
                                <p class="text-xs text-slate-500">{{ ucfirst($summary['type']) }}</p>
                                <p class="text-lg font-bold text-slate-800">{{ number_format($summary['total_quantity'], 2) }}</p>
                                <p class="text-xs text-slate-400">{{ $summary['count'] }} {{ __('transactions') }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-slate-700 mb-2">{{ __('Transactions') }}</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-slate-50">
                                        <th class="text-left px-3 py-2 text-xs font-medium text-slate-500">{{ __('Date') }}</th>
                                        <th class="text-left px-3 py-2 text-xs font-medium text-slate-500">{{ __('Product') }}</th>
                                        <th class="text-left px-3 py-2 text-xs font-medium text-slate-500">{{ __('Type') }}</th>
                                        <th class="text-right px-3 py-2 text-xs font-medium text-slate-500">{{ __('Quantity') }}</th>
                                        <th class="text-right px-3 py-2 text-xs font-medium text-slate-500">{{ __('Reference') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($report['transactions'] as $txn)
                                        <tr>
                                            <td class="px-3 py-2">{{ \Carbon\Carbon::parse($txn['created_at'])->format('Y-m-d H:i') }}</td>
                                            <td class="px-3 py-2">{{ $txn['product']['name'] ?? 'N/A' }}</td>
                                            <td class="px-3 py-2">
                                                <span class="erp-badge-{{ in_array($txn['type'], ['sales_order', 'purchase_return']) ? 'inactive' : 'active' }}">
                                                    {{ $txn['type'] }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-right">{{ number_format(abs($txn['quantity']), 2) }}</td>
                                            <td class="px-3 py-2 text-right text-slate-500">{{ $txn['reference_type'] ?? '-' }} #{{ $txn['reference_id'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <p class="text-sm text-slate-500 text-center py-8">{{ __('Select a date range to view inventory movements.') }}</p>
            @endif
        </div>
    </div>
</x-app-layout>
