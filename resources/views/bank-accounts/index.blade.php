<x-app-layout>
    <x-slot name="header">Bank Accounts</x-slot>
    <x-slot name="headerDescription">Manage your organization's bank accounts.</x-slot>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-stat-card label="Total Accounts" :value="$stats['total_accounts']" color="primary" />
        <x-stat-card label="Active Accounts" :value="$stats['active_accounts']" color="success" />
        <x-stat-card label="Total Balance" :value="number_format($stats['total_balance'], 0)" color="info" />
    </div>

    <div class="flex justify-end mb-4">
        <a href="{{ route('bank-accounts.create') }}" class="px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-600 transition">New Bank Account</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Account Name</th>
                    <th class="text-left px-4 py-3">Bank</th>
                    <th class="text-left px-4 py-3">Account #</th>
                    <th class="text-right px-4 py-3">Balance</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($accounts as $account)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium">{{ $account->name }}</td>
                        <td class="px-4 py-3">{{ $account->bank_name }}</td>
                        <td class="px-4 py-3">{{ $account->account_number }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($account->current_balance, 0) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $account->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $account->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('bank-accounts.show', $account) }}" class="text-primary hover:underline text-xs">View</a>
                            <a href="{{ route('bank-accounts.edit', $account) }}" class="text-slate-500 hover:underline text-xs ml-2">Edit</a>
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

    <div class="mt-4">{{ $accounts->links() }}</div>
</x-app-layout>
