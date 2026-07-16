<x-app-layout>
    <x-slot name="header">Account Balances</x-slot>

    <x-breadcrumbs :items="[['label' => 'Chart of Accounts', 'url' => route('accounts.index')], ['label' => 'Balances']]" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">Code</th>
                        <th class="text-left">Account Name</th>
                        <th class="text-left">Type</th>
                        <th class="text-right">Current Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td class="font-mono text-sm">{{ $account->code }}</td>
                            <td class="text-sm font-medium text-slate-800">{{ $account->name }}</td>
                            <td class="text-sm text-slate-500">{{ ucfirst($account->type) }}</td>
                            <td class="text-sm text-right font-mono {{ $account->current_balance >= 0 ? 'text-slate-800' : 'text-red-600' }}">
                                TSh {{ number_format($account->current_balance, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-slate-400 py-8">No accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
