<x-app-layout>
    <x-slot name="header">{{ $customerGroup->name }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Customer Groups', 'url' => route('customer-groups.index')], ['label' => $customerGroup->name]]" />

    <div class="max-w-4xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                    <h2 class="text-lg font-semibold text-slate-800 mb-2">{{ $customerGroup->name }}</h2>
                    <p class="text-sm text-slate-500 mb-4">{{ $customerGroup->description ?? 'No description.' }}</p>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><span class="text-slate-500">Default Credit Limit:</span> <span class="font-medium">{{ number_format($customerGroup->default_credit_limit ?? 0, 2) }}</span></div>
                        <div><span class="text-slate-500">Default Payment Terms:</span> <span class="font-medium">{{ $customerGroup->default_payment_terms ?? 'N/A' }}</span></div>
                        <div>
                            <span class="text-slate-500">Status:</span>
                            @if ($customerGroup->is_active)
                                <span class="erp-badge-active">Active</span>
                            @else
                                <span class="erp-badge-inactive">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                    <h3 class="text-md font-semibold text-slate-800 mb-4">Assigned Price Lists ({{ $customerGroup->priceLists->count() }})</h3>
                    @forelse ($customerGroup->priceLists as $list)
                        <div class="flex items-center justify-between py-3 border-b border-slate-50 last:border-0">
                            <div>
                                <a href="{{ route('price-lists.show', $list) }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">{{ $list->name }}</a>
                                <p class="text-xs text-slate-500">{{ $list->currency }} &middot; Items: {{ $list->items_count ?? $list->items->count() }}</p>
                            </div>
                            <div class="text-right">
                                @if ($list->isExpired())
                                    <span class="erp-badge-inactive text-xs">Expired</span>
                                @elseif ($list->is_active)
                                    <span class="erp-badge-active text-xs">Active</span>
                                @else
                                    <span class="erp-badge-inactive text-xs">Inactive</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No price lists assigned to this group.</p>
                    @endforelse
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                    <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-3">Quick Actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('customer-groups.edit', $customerGroup) }}" class="block w-full text-center px-4 py-2 erp-btn-primary">Edit Group</a>
                        <form action="{{ route('customer-groups.destroy', $customerGroup) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="block w-full text-center px-4 py-2 erp-btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>