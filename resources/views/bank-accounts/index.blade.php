<x-app-layout>
    <x-slot name="header">{{ __('Bank Accounts') }}</x-slot>
    <x-slot name="headerDescription">Manage your organization's bank accounts.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('bank-accounts.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Bank Account
        </a>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="erp-stat">
            <p class="text-sm text-slate-500">Total Accounts</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['total_accounts'] }}</p>
        </div>
        <div class="erp-stat">
            <p class="text-sm text-slate-500">Active Accounts</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['active_accounts'] }}</p>
        </div>
        <div class="erp-stat">
            <p class="text-sm text-slate-500">Total Balance</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">TSh {{ number_format($stats['total_balance'], 0) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>Account Name</th>
                        <th>Bank</th>
                        <th>Account #</th>
                        <th class="text-right">Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($accounts as $account)
                        <tr>
                            <td class="font-medium text-slate-800">{{ $account->name }}</td>
                            <td>{{ $account->bank_name }}</td>
                            <td class="font-mono text-xs">{{ $account->account_number }}</td>
                            <td class="text-right font-mono">TSh {{ number_format($account->current_balance, 0) }}</td>
                            <td>
                                <span class="erp-badge {{ $account->is_active ? 'erp-badge-active' : 'erp-badge-inactive' }}">
                                    {{ $account->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('bank-accounts.show', $account) }}" class="text-primary hover:underline text-xs">View</a>
                                    <a href="{{ route('bank-accounts.edit', $account) }}" class="text-slate-500 hover:underline text-xs">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">No bank accounts yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $accounts->links() }}</div>
</x-app-layout>
