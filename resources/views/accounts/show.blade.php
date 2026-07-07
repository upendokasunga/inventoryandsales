<x-app-layout>
    <x-slot name="header">{{ $account->code }} - {{ $account->name }}</x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="mb-4 flex gap-2">
            <a href="{{ route('accounts.index') }}" class="inline-flex items-center px-4 py-2 erp-btn-secondary">Back to List</a>
            <a href="{{ route('accounts.edit', $account) }}" class="inline-flex items-center px-4 py-2 erp-btn-primary">Edit Account</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="px-6 py-4 border-b border-blue-100">
                        <h3 class="text-lg font-semibold text-slate-800">Account Information</h3>
                    </div>
                    <div class="p-6 grid grid-cols-2 gap-4">
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
                        @if ($account->parent)
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Parent</span>
                            <p class="mt-1 text-sm"><a href="{{ route('accounts.show', $account->parent) }}" class="text-primary hover:underline">{{ $account->parent->code }} - {{ $account->parent->name }}</a></p>
                        </div>
                        @endif
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Balance</span>
                            <p class="mt-1 text-sm font-mono text-slate-800">{{ number_format($account->current_balance ?? $account->opening_balance, 2) }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Opening Balance</span>
                            <p class="mt-1 text-sm font-mono text-slate-800">{{ number_format($account->opening_balance, 2) }}</p>
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
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Balance</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach ($account->children as $child)
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-mono">{{ $child->code }}</td>
                                            <td class="px-4 py-3 text-sm"><a href="{{ route('accounts.show', $child) }}" class="text-primary hover:underline">{{ $child->name }}</a></td>
                                            <td class="px-4 py-3 text-sm">{{ ucfirst($child->type) }}</td>
                                            <td class="px-4 py-3 text-sm font-mono">{{ number_format($child->current_balance ?? $child->opening_balance, 2) }}</td>
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
                        <form action="{{ route('accounts.destroy', $account) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full erp-btn-secondary text-xs text-red-600 border-red-200 hover:bg-red-50">Delete Account</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
