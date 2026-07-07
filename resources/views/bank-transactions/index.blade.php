<x-app-layout>
    <x-slot name="header">Transactions - {{ $bankAccount->name }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Bank Accounts', 'url' => route('bank-accounts.index')], ['label' => $bankAccount->name, 'url' => route('bank-accounts.show', $bankAccount)], ['label' => 'Transactions']]" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Type</label>
                <select name="type" class="text-sm border border-slate-200 rounded-lg px-3 py-2">
                    <option value="">All</option>
                    <option value="deposits" @selected(request('type') === 'deposits')>Deposits</option>
                    <option value="withdrawals" @selected(request('type') === 'withdrawals')>Withdrawals</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="text-sm border border-slate-200 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="text-sm border border-slate-200 rounded-lg px-3 py-2">
            </div>
            <button type="submit" class="px-3 py-2 bg-slate-100 text-slate-700 text-sm rounded-lg hover:bg-slate-200 transition">Filter</button>
            <a href="{{ route('bank-transactions.create', $bankAccount) }}" class="px-3 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-600 transition ml-auto">New Transaction</a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-left px-4 py-3">Description</th>
                    <th class="text-left px-4 py-3">Ref #</th>
                    <th class="text-left px-4 py-3">Type</th>
                    <th class="text-right px-4 py-3">Amount</th>
                    <th class="text-right px-4 py-3">Balance</th>
                    <th class="text-left px-4 py-3">Reconciled</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transactions as $tx)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">{{ $tx->transaction_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('bank-transactions.show', $tx) }}" class="text-primary hover:underline">{{ $tx->description }}</a>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $tx->reference_number ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-xs rounded-full font-medium
                                {{ in_array($tx->type, ['deposit', 'transfer_in']) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ str_replace('_', ' ', ucfirst($tx->type)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-medium {{ in_array($tx->type, ['deposit', 'transfer_in']) ? 'text-success' : 'text-danger' }}">
                            {{ in_array($tx->type, ['deposit', 'transfer_in']) ? '+' : '-' }}{{ number_format($tx->amount, 0) }}
                        </td>
                        <td class="px-4 py-3 text-right">{{ number_format($tx->running_balance, 0) }}</td>
                        <td class="px-4 py-3">
                            @if($tx->reconciled)
                                <span class="text-xs text-green-600 font-medium">Yes</span>
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
    <div class="mt-4">{{ $transactions->appends(request()->query())->links() }}</div>
</x-app-layout>
