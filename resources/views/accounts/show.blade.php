<x-app-layout>
    <x-slot name="header">{{ $account->code }} - {{ $account->name }}</x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="mb-4 flex gap-2 flex-wrap">
            <a href="{{ route('accounts.index') }}" class="inline-flex items-center px-4 py-2 erp-btn-secondary">Back to List</a>
            <a href="{{ route('accounts.edit', $account) }}" class="inline-flex items-center px-4 py-2 erp-btn-primary">Edit Account</a>
            @if($account->isCashOrBank())
                <a href="{{ route('accounts.statement', $account) }}" class="inline-flex items-center px-4 py-2 erp-btn-secondary">View Statement</a>
            @endif
            @if(in_array($account->type, ['asset', 'liability', 'equity']))
                <a href="{{ route('accounts.balances') }}" class="inline-flex items-center px-4 py-2 erp-btn-secondary">All Balances</a>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="px-6 py-4 border-b border-blue-100">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <h3 class="text-lg font-semibold text-slate-800">Account Information</h3>
                            <div class="flex gap-2">
                                @if($account->isCashOrBank())
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">Cash/Bank</span>
                                @endif
                                @if($account->ifrs_category)
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-700">IFRS: {{ strtoupper($account->ifrs_category) }}</span>
                                @endif
                                @if($account->allow_overdraft && $account->overdraft_limit > 0)
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Overdraft: TSh {{ number_format($account->overdraft_limit, 0) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Code</span>
                            <p class="mt-1 text-sm font-mono text-slate-800">{{ $account->code }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Name</span>
                            <p class="mt-1 text-sm text-slate-800">{{ $account->name }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Type</span>
                            <p class="mt-1 text-sm text-slate-800">{{ ucfirst($account->type) }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Category</span>
                            <p class="mt-1 text-sm text-slate-800">{{ str_replace('_', ' ', ucfirst($account->category)) }}</p>
                        </div>
                        @if($account->ifrs_category)
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">IFRS Classification</span>
                            <p class="mt-1 text-sm text-slate-800">{{ strtoupper($account->ifrs_category) }}</p>
                        </div>
                        @endif
                        @if ($account->parent)
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Parent</span>
                            <p class="mt-1 text-sm"><a href="{{ route('accounts.show', $account->parent) }}" class="text-primary hover:underline">{{ $account->parent->code }} - {{ $account->parent->name }}</a></p>
                        </div>
                        @endif
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Current Balance</span>
                            <p class="mt-1 text-sm font-mono {{ ($account->current_balance ?? 0) < 0 ? 'text-red-600' : 'text-slate-800' }}">TSh {{ number_format($account->current_balance ?? $account->opening_balance, 2) }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Opening Balance</span>
                            <p class="mt-1 text-sm font-mono text-slate-800">TSh {{ number_format($account->opening_balance, 2) }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Status</span>
                            <p class="mt-1">
                                @if ($account->is_active)
                                    <span class="erp-badge-active">Active</span>
                                @else
                                    <span class="erp-badge-inactive">Inactive</span>
                                @endif
                            </p>
                        </div>
                        @if($account->isCashOrBank() && $account->user)
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Assigned To</span>
                            <p class="mt-1 text-sm text-slate-800">{{ $account->user->name }}</p>
                        </div>
                        @endif
                    </div>
                    @if ($account->description)
                        <div class="px-6 pb-4">
                            <span class="text-xs font-medium text-slate-500 uppercase">Description</span>
                            <p class="mt-1 text-sm text-slate-700">{{ $account->description }}</p>
                        </div>
                    @endif
                </div>

                @if ($account->children->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                        <div class="px-6 py-4 border-b border-blue-100">
                            <h3 class="text-lg font-semibold text-slate-800">Child Accounts</h3>
                        </div>
                        <div class="p-6">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Code</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Type</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Balance</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach ($account->children as $child)
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-mono">{{ $child->code }}</td>
                                            <td class="px-4 py-3 text-sm"><a href="{{ route('accounts.show', $child) }}" class="text-primary hover:underline">{{ $child->name }}</a></td>
                                            <td class="px-4 py-3 text-sm">{{ ucfirst($child->type) }}</td>
                                            <td class="px-4 py-3 text-sm font-mono text-right">TSh {{ number_format($child->current_balance ?? $child->opening_balance, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @php
                    $recentLines = $account->journalLines()->with('journalEntry')->latest()->take(10)->get();
                @endphp
                @if($recentLines->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                        <div class="px-6 py-4 border-b border-blue-100">
                            <h3 class="text-lg font-semibold text-slate-800">Recent Journal Entries</h3>
                        </div>
                        <div class="p-6">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Entry #</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Description</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Debit</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Credit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach ($recentLines as $line)
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-mono">
                                                <a href="{{ route('journal-entries.show', $line->journalEntry) }}" class="text-primary hover:underline">{{ $line->journalEntry->entry_number }}</a>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-slate-500">{{ $line->journalEntry->entry_date->format('d M Y') }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-600 max-w-xs truncate">{{ $line->description ?? $line->journalEntry->description }}</td>
                                            <td class="px-4 py-3 text-sm font-mono text-right">{{ $line->debit > 0 ? 'TSh ' . number_format($line->debit, 2) : '-' }}</td>
                                            <td class="px-4 py-3 text-sm font-mono text-right">{{ $line->credit > 0 ? 'TSh ' . number_format($line->credit, 2) : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="px-6 py-4 border-b border-blue-100">
                        <h3 class="text-lg font-semibold text-slate-800">Quick Actions</h3>
                    </div>
                    <div class="p-6 space-y-2">
                        @if($account->isCashOrBank())
                            <a href="{{ route('accounts.statement', $account) }}" class="erp-btn-secondary w-full text-center text-sm">View Statement</a>
                            <a href="{{ route('accounts.balances') }}" class="erp-btn-secondary w-full text-center text-sm">Cash &amp; Bank Balances</a>
                        @endif
                        <form action="{{ route('accounts.destroy', $account) }}" method="POST" onsubmit="return confirm('Are you sure? This will permanently delete this account.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full erp-btn-secondary text-xs text-red-600 border-red-200 hover:bg-red-50">Delete Account</button>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="px-6 py-4 border-b border-blue-100">
                        <h3 class="text-lg font-semibold text-slate-800">Account Details</h3>
                    </div>
                    <div class="p-6 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Account Type</span>
                            <span class="text-slate-800">{{ ucfirst($account->type) }} / {{ str_replace('_', ' ', ucfirst($account->category)) }}</span>
                        </div>
                        @if($account->ifrs_category)
                            <div class="flex justify-between">
                                <span class="text-slate-500">IFRS</span>
                                <span class="text-slate-800">{{ strtoupper($account->ifrs_category) }}</span>
                            </div>
                        @endif
                        @if($account->allow_overdraft && $account->overdraft_limit > 0)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Overdraft Limit</span>
                                <span class="text-amber-600">TSh {{ number_format($account->overdraft_limit, 2) }}</span>
                            </div>
                        @endif
                        @if($account->bank_name)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Bank</span>
                                <span class="text-slate-800">{{ $account->bank_name }}</span>
                            </div>
                        @endif
                        @if($account->bank_branch)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Branch</span>
                                <span class="text-slate-800">{{ $account->bank_branch }}</span>
                            </div>
                        @endif
                        @if($account->bank_swift_code)
                            <div class="flex justify-between">
                                <span class="text-slate-500">SWIFT Code</span>
                                <span class="font-mono text-slate-800">{{ $account->bank_swift_code }}</span>
                            </div>
                        @endif
                        @if($account->user)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Assigned To</span>
                                <span class="text-slate-800">{{ $account->user->name }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-slate-500">Created</span>
                            <span class="text-slate-800">{{ $account->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
