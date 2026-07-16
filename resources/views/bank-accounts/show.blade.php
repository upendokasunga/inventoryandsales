<x-app-layout>
    <x-slot name="header">{{ $bankAccount->name }}</x-slot>

    <div class="mb-4">
        <a href="{{ route('bank-accounts.index') }}" class="erp-btn-secondary">Back to List</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">{{ $bankAccount->bank?->name ?? $bankAccount->bank_name }}</h2>
                        <p class="text-sm text-slate-500">{{ $bankAccount->account_number }} &middot; {{ $bankAccount->accountType?->label ?? ucfirst(str_replace('_', ' ', $bankAccount->account_type)) }}</p>
                        @if($bankAccount->branch || $bankAccount->bank?->branch)
                            <p class="text-sm text-slate-500">{{ $bankAccount->branch ?? $bankAccount->bank->branch }}</p>
                        @endif
                        @if($bankAccount->bank?->swift_code)
                            <p class="text-xs text-slate-400">SWIFT: {{ $bankAccount->bank->swift_code }}</p>
                        @endif
                    </div>
                    <span class="erp-badge {{ $bankAccount->is_active ? 'erp-badge-active' : 'erp-badge-inactive' }}">
                        {{ $bankAccount->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="p-4 bg-slate-50 rounded-lg text-center">
                        <p class="text-xs text-slate-400 uppercase">Balance</p>
                        <p class="text-xl font-bold text-slate-800">TSh {{ number_format($bankAccount->current_balance, 0) }}</p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-lg text-center">
                        <p class="text-xs text-slate-400 uppercase">Opening</p>
                        <p class="text-xl font-bold text-slate-800">TSh {{ number_format($bankAccount->opening_balance, 0) }}</p>
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

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-700">Recent Transactions</h3>
                    <a href="{{ route('bank-transactions.create', $bankAccount) }}" class="erp-btn-primary erp-btn-sm">Record Transaction</a>
                </div>

                @if($bankAccount->transactions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="erp-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($bankAccount->transactions as $tx)
                                    <tr>
                                        <td>{{ $tx->transaction_date->format('d M Y') }}</td>
                                        <td>{{ $tx->description }}</td>
                                        <td>
                                            <span class="erp-badge {{ in_array($tx->type, ['deposit', 'transfer_in']) ? 'erp-badge-active' : 'erp-badge-danger' }}">
                                                {{ str_replace('_', ' ', ucfirst($tx->type)) }}
                                            </span>
                                        </td>
                                        <td class="text-right font-mono {{ in_array($tx->type, ['deposit', 'transfer_in']) ? 'text-emerald-600' : 'text-red-600' }}">
                                            {{ in_array($tx->type, ['deposit', 'transfer_in']) ? '+' : '-' }}TSh {{ number_format($tx->amount, 0) }}
                                        </td>
                                        <td class="text-right font-mono">TSh {{ number_format($tx->running_balance, 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 text-right">
                        <a href="{{ route('bank-transactions.index', $bankAccount) }}" class="erp-link text-xs">View All Transactions &rarr;</a>
                    </div>
                @else
                    <p class="text-sm text-slate-400 text-center py-4">No transactions yet.</p>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 space-y-3">
                <h3 class="text-sm font-semibold text-slate-700">Actions</h3>
                <a href="{{ route('bank-transactions.create', $bankAccount) }}" class="block w-full text-center erp-btn-primary">Record Deposit / Withdrawal</a>
                <a href="{{ route('bank-accounts.edit', $bankAccount) }}" class="block w-full text-center erp-btn-secondary">Edit Account</a>
                <a href="{{ route('bank-reconciliations.create') }}?bank_account_id={{ $bankAccount->id }}" class="block w-full text-center erp-btn-secondary">Reconcile</a>
                <form action="{{ route('bank-accounts.destroy', $bankAccount) }}" method="POST" onsubmit="return confirm('Delete this bank account?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full erp-btn-danger">Delete</button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Details</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Created</span><span>{{ $bankAccount->created_at->format('d M Y') }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">By</span><span>{{ $bankAccount->creator?->name ?? 'System' }}</span></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
