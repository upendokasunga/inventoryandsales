<x-app-layout>
    <x-slot name="header">{{ __('Transactions') }} — {{ $bankAccount->name }}</x-slot>

    <div class="mb-4">
        <a href="{{ route('bank-accounts.show', $bankAccount) }}" class="erp-btn-secondary">Back to Account</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Type</label>
                <select name="type" class="erp-input">
                    <option value="">All</option>
                    <option value="deposits" @selected(request('type') === 'deposits')>Deposits</option>
                    <option value="withdrawals" @selected(request('type') === 'withdrawals')>Withdrawals</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="erp-input">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="erp-input">
            </div>
            <button type="submit" class="erp-btn-secondary erp-btn-sm">Filter</button>
            <a href="{{ route('bank-transactions.create', $bankAccount) }}" class="erp-btn-primary erp-btn-sm ml-auto">New Transaction</a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Ref #</th>
                        <th>Type</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Balance</th>
                        <th>Reconciled</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $tx)
                        <tr>
                            <td>{{ $tx->transaction_date->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('bank-transactions.show', $tx) }}" class="text-primary hover:underline">{{ $tx->description }}</a>
                            </td>
                            <td class="font-mono text-xs text-slate-500">{{ $tx->reference_number ?? '-' }}</td>
                            <td>
                                <span class="erp-badge {{ in_array($tx->type, ['deposit', 'transfer_in']) ? 'erp-badge-active' : 'erp-badge-danger' }}">
                                    {{ str_replace('_', ' ', ucfirst($tx->type)) }}
                                </span>
                            </td>
                            <td class="text-right font-mono {{ in_array($tx->type, ['deposit', 'transfer_in']) ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ in_array($tx->type, ['deposit', 'transfer_in']) ? '+' : '-' }}TSh {{ number_format($tx->amount, 0) }}
                            </td>
                            <td class="text-right font-mono">TSh {{ number_format($tx->running_balance, 0) }}</td>
                            <td>
                                @if($tx->reconciled)
                                    <span class="erp-badge erp-badge-active">Yes</span>
                                @else
                                    <span class="text-xs text-slate-400">No</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $transactions->appends(request()->query())->links() }}</div>
</x-app-layout>
