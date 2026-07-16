<x-app-layout>
    <x-slot name="header">{{ __('Chart of Accounts') }}</x-slot>
    <x-slot name="headerDescription">All accounts with current balances.</x-slot>

    <div class="max-w-7xl mx-auto">
        <x-table-card :empty="count($accounts) === 0" emptyMessage="No accounts found." colspan="7">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Account Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Account ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Currency</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Account Number</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Balance</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Overdraft Limit</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($accounts as $account)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                            <a href="{{ route('accounts.show', $account) }}" class="text-primary hover:text-primary/80 transition">{{ $account->name }}</a>
                            @if ($account->children->isNotEmpty())
                                <span class="text-xs text-slate-400 ml-1">({{ $account->children->count() }})</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-800">{{ $account->code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $account->currency_code ?: 'TSh' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @php
                                $typeBadge = match($account->type) {
                                    'asset' => 'bg-blue-100 text-blue-700',
                                    'liability' => 'bg-amber-100 text-amber-700',
                                    'equity' => 'bg-purple-100 text-purple-700',
                                    'income' => 'bg-green-100 text-green-700',
                                    'expense' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $typeBadge }}">{{ ucfirst($account->type) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $account->account_number ?: '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-right {{ ($account->current_balance ?? 0) < 0 ? 'text-red-600' : 'text-slate-800' }}">
                            TSh {{ number_format($account->current_balance ?? $account->opening_balance, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-right text-slate-600">
                            @if ($account->allow_overdraft && $account->overdraft_limit > 0)
                                TSh {{ number_format($account->overdraft_limit, 2) }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $accounts->links() }}</div>
    </div>
</x-app-layout>
