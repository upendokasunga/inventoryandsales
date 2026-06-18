<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Customer Statement</h3>
        <form method="GET" action="{{ route('customers.statement') }}" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">From Date</label>
                <input type="date" name="from" value="{{ request('from') }}" class="erp-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">To Date</label>
                <input type="date" name="to" value="{{ request('to') }}" class="erp-input">
            </div>
            <button type="submit" class="erp-btn-primary">Generate</button>
            <a href="{{ route('customers.profile', [$customer, 'tab' => 'statements']) }}" class="text-sm text-slate-500 hover:text-primary">Clear</a>
        </form>
    </div>

    @if (request('customer_id'))
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="text-md font-semibold text-slate-800">Statement</h4>
                    <p class="text-sm text-slate-500">
                        {{ $customer->name }} &middot;
                        {{ request('from') ? request('from') . ' to ' : 'Up to ' }}{{ request('to') ?? now()->format('Y-m-d') }}
                    </p>
                </div>
                <a href="{{ route('customers.statement-pdf', $customer) }}?from={{ request('from') }}&to={{ request('to') }}"
                   class="erp-btn-secondary" target="_blank">Download PDF</a>
            </div>

            @php
                $txns = $transactions ?? collect();
                $totalDebit = $txns->whereIn('type', ['order', 'allocation'])->sum('amount');
                $totalCredit = $txns->whereIn('type', ['payment', 'refund', 'reversal'])->sum('amount');
            @endphp

            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Description</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Debit</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Credit</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($txns as $tx)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ $tx->created_at->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $tx->description }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-right {{ in_array($tx->type, ['order', 'allocation']) ? 'text-danger-600 font-medium' : 'text-slate-500' }}">
                                {{ in_array($tx->type, ['order', 'allocation']) ? number_format($tx->amount, 0) : '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right {{ in_array($tx->type, ['payment', 'refund', 'reversal']) ? 'text-success-600 font-medium' : 'text-slate-500' }}">
                                {{ in_array($tx->type, ['payment', 'refund', 'reversal']) ? number_format(abs($tx->amount), 0) : '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right font-medium text-slate-800">{{ number_format($tx->balance_after, 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-center text-slate-500">No transactions in this period.</td>
                        </tr>
                    @endforelse
                    @if ($txns->isNotEmpty())
                        <tr class="bg-slate-50 font-semibold">
                            <td colspan="2" class="px-4 py-3 text-slate-700">Totals</td>
                            <td class="px-4 py-3 text-right text-danger-600">{{ number_format($totalDebit, 0) }}</td>
                            <td class="px-4 py-3 text-right text-success-600">{{ number_format($totalCredit, 0) }}</td>
                            <td class="px-4 py-3 text-right text-slate-800">{{ number_format($customer->outstanding_balance, 0) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 text-center text-slate-500">
            Select a date range and generate a statement to see results.
        </div>
    @endif
</div>
