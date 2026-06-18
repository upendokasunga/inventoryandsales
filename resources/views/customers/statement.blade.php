<x-app-layout>
    <x-slot name="header">{{ __('Generate Statement') }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Customers', 'url' => route('customers.index')], ['label' => 'Statement']]" />

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Select Customer & Date Range</h3>
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Customer *</label>
                    <select name="customer_id" required class="erp-input w-64">
                        <option value="">Select Customer...</option>
                        @foreach ($customers as $id => $name)
                            <option value="{{ $id }}" {{ request('customer_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="erp-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="erp-input">
                </div>
                <button type="submit" class="erp-btn-primary">Generate</button>
            </form>
        </div>

        @if ($statement)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">
                            Statement &mdash; {{ $selectedCustomer->name }}
                        </h3>
                        <p class="text-sm text-slate-500">
                            {{ $statement['from'] ?? 'All time' }} to {{ $statement['to'] }}
                            &middot; Generated {{ $statement['generated_at']->format('Y-m-d H:i') }}
                        </p>
                    </div>
                    <a href="{{ route('customers.statement-pdf', $selectedCustomer) }}?from={{ request('from') }}&to={{ request('to') }}"
                       class="erp-btn-secondary" target="_blank">Download PDF</a>
                </div>

                <div class="grid grid-cols-3 gap-4 px-6 py-4 bg-slate-50 border-b border-slate-100">
                    <div>
                        <span class="text-xs text-slate-500">Opening Balance</span>
                        <p class="text-lg font-bold text-slate-800">{{ number_format($statement['opening_balance'], 0) }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500">Total Debit</span>
                        <p class="text-lg font-bold text-danger-600">{{ number_format($statement['total_debit'], 0) }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500">Total Credit</span>
                        <p class="text-lg font-bold text-success-600">{{ number_format($statement['total_credit'], 0) }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Debit</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Credit</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse ($statement['transactions'] as $tx)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-500">{{ $tx->created_at->format('Y-m-d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                            {{ in_array($tx->type, ['payment', 'refund']) ? 'bg-success-50 text-success-600' : 'bg-warning-50 text-warning-600' }}">
                                            {{ ucfirst($tx->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-700 max-w-xs truncate">{{ $tx->description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right {{ in_array($tx->type, ['order', 'allocation']) ? 'text-danger-600 font-medium' : 'text-slate-500' }}">
                                        {{ in_array($tx->type, ['order', 'allocation']) ? number_format($tx->amount, 0) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right {{ in_array($tx->type, ['payment', 'refund', 'reversal']) ? 'text-success-600 font-medium' : 'text-slate-500' }}">
                                        {{ in_array($tx->type, ['payment', 'refund', 'reversal']) ? number_format(abs($tx->amount), 0) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-slate-800">{{ number_format($tx->balance_after, 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-slate-500">No transactions found.</td>
                                </tr>
                            @endforelse
                            @if ($statement['transactions']->isNotEmpty())
                                <tr class="bg-slate-50 font-semibold">
                                    <td colspan="3" class="px-6 py-4 text-slate-700">Totals</td>
                                    <td class="px-6 py-4 text-right text-danger-600">{{ number_format($statement['total_debit'], 0) }}</td>
                                    <td class="px-6 py-4 text-right text-success-600">{{ number_format($statement['total_credit'], 0) }}</td>
                                    <td class="px-6 py-4 text-right text-slate-800">{{ number_format($statement['closing_balance'], 0) }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
