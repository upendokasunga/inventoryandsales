<x-app-layout>
    <x-slot name="header">{{ __('Chart of Accounts') }}</x-slot>
    <x-slot name="headerDescription">Manage your chart of accounts — define asset, liability, equity, income, and expense accounts.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('accounts.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Account
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
            <form action="{{ route('accounts.index') }}" method="GET" class="flex gap-2 flex-wrap">
                <select name="type" class="erp-input" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="asset" {{ request('type') == 'asset' ? 'selected' : '' }}>Asset</option>
                    <option value="liability" {{ request('type') == 'liability' ? 'selected' : '' }}>Liability</option>
                    <option value="equity" {{ request('type') == 'equity' ? 'selected' : '' }}>Equity</option>
                    <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income</option>
                    <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                </select>
                <button type="submit" class="erp-btn-primary">Filter</button>
            </form>
        </div>

        <x-table-card :empty="count($accounts) === 0" emptyMessage="No accounts found. Create your first account to get started." colspan="7">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($accounts as $account)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-800">{{ $account->code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                            <a href="{{ route('accounts.show', $account) }}" class="text-primary hover:text-primary/80 transition">{{ $account->name }}</a>
                            @if ($account->children->isNotEmpty())
                                <span class="text-xs text-slate-400 ml-1">({{ $account->children->count() }} children)</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><span class="px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-700">{{ ucfirst($account->type) }}</span></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ str_replace('_', ' ', ucfirst($account->category)) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-800">{{ number_format($account->current_balance ?? $account->opening_balance, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($account->is_active)
                                <span class="erp-badge-active">Active</span>
                            @else
                                <span class="erp-badge-inactive">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links
                                :view="route('accounts.show', $account)"
                                :edit="route('accounts.edit', $account)"
                                :delete="route('accounts.destroy', $account)"
                            />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $accounts->links() }}</div>
    </div>
</x-app-layout>
