<x-app-layout>
    <x-slot name="header">{{ $bankAccount->name }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Bank Accounts', 'url' => route('bank-accounts.index')], ['label' => $bankAccount->name]]" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">{{ $bankAccount->bank_name }}</h2>
                        <p class="text-sm text-slate-500">{{ $bankAccount->account_number }} &middot; {{ ucfirst($bankAccount->account_type) }}</p>
                        @if($bankAccount->branch)
                            <p class="text-sm text-slate-500">{{ $bankAccount->branch }}</p>
                        @endif
                    </div>
                    <div>
                        <span class="px-3 py-1 text-sm rounded-full font-medium {{ $bankAccount->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $bankAccount->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="p-4 bg-slate-50 rounded-lg text-center">
                        <p class="text-xs text-slate-400 uppercase">Balance</p>
                        <p class="text-xl font-bold text-slate-800">{{ number_format($bankAccount->current_balance, 0) }}</p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-lg text-center">
                        <p class="text-xs text-slate-400 uppercase">Opening</p>
                        <p class="text-xl font-bold text-slate-800">{{ number_format($bankAccount->opening_balance, 0) }}</p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-lg text-center">
                        <p class="text-xs text-slate-400 uppercase">Transactions</p>
                        <p class="text-xl font-bold text-slate-800">{{ $bankAccount->transactions->count() }}</p>
                    </div>
                </div>

                @if($bankAccount->coaAccount)
                    <div class="text-sm text-slate-500">Linked COA: <span class="font-medium text-slate-700">{{ $bankAccount->coaAccount->code }} - {{ $bankAccount->coaAccount->name }}</span></div>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-700">Recent Transactions</h3>
                    <a href="{{ route('bank-transactions.create', $bankAccount) }}" class="px-3 py-1.5 bg-primary text-white text-xs rounded-lg hover:bg-primary-600 transition">Record Transaction</a>
                </div>

                @if($bankAccount->transactions->count() > 0)
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                            <tr>
                                <th class="text-left px-3 py-2">Date</th>
                                <th class="text-left px-3 py-2">Description</th>
                                <th class="text-left px-3 py-2">Type</th>
                                <th class="text-right px-3 py-2">Amount</th>
                                <th class="text-right px-3 py-2">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($bankAccount->transactions as $tx)
                                <tr>
                                    <td class="px-3 py-2">{{ $tx->transaction_date->format('d M Y') }}</td>
                                    <td class="px-3 py-2">{{ $tx->description }}</td>
                                    <td class="px-3 py-2 capitalize">{{ str_replace('_', ' ', $tx->type) }}</td>
                                    <td class="px-3 py-2 text-right font-medium {{ in_array($tx->type, ['deposit', 'transfer_in']) ? 'text-success' : 'text-danger' }}">
                                        {{ in_array($tx->type, ['deposit', 'transfer_in']) ? '+' : '-' }}{{ number_format($tx->amount, 0) }}
                                    </td>
                                    <td class="px-3 py-2 text-right">{{ number_format($tx->running_balance, 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3 text-right">
                        <a href="{{ route('bank-transactions.index', $bankAccount) }}" class="text-xs text-primary hover:underline">View All Transactions &rarr;</a>
                    </div>
                @else
                    <p class="text-sm text-slate-400 text-center py-4">No transactions yet.</p>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-3">
                <h3 class="text-sm font-semibold text-slate-700">Actions</h3>
                <a href="{{ route('bank-transactions.create', $bankAccount) }}" class="block w-full text-center px-3 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-600 transition">Record Deposit / Withdrawal</a>
                <a href="{{ route('bank-accounts.edit', $bankAccount) }}" class="block w-full text-center px-3 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition">Edit Account</a>
                <a href="{{ route('bank-reconciliations.create') }}?bank_account_id={{ $bankAccount->id }}" class="block w-full text-center px-3 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition">Reconcile</a>
                <form action="{{ route('bank-accounts.destroy', $bankAccount) }}" method="POST" onsubmit="return confirm('Delete this bank account?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-3 py-2 bg-danger text-white text-sm rounded-lg hover:bg-danger-600 transition">Delete</button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Details</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Created</span><span>{{ $bankAccount->created_at->format('d M Y') }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">By</span><span>{{ $bankAccount->creator?->name ?? 'System' }}</span></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
