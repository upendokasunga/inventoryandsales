<x-app-layout>
    <x-slot name="header">{{ __('Price Lists') }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Pricing Dashboard', 'url' => route('price-lists.dashboard')], ['label' => 'Price Lists']]" />

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 px-4 py-2 text-danger-700 bg-danger-50 border border-danger-100 rounded-lg">{{ session('error') }}</div>
        @endif

        <div class="mb-4 flex items-center justify-between flex-wrap gap-2">
            <div class="flex gap-2">
                <a href="{{ route('price-lists.create') }}" class="inline-flex items-center px-4 py-2 erp-btn-primary">Create Price List</a>
                <a href="{{ route('price-lists.export-csv') }}" class="inline-flex items-center px-4 py-2 erp-btn-secondary">Export CSV</a>
            </div>
            <form action="{{ route('price-lists.index') }}" method="GET" class="flex gap-2 flex-wrap">
                <select name="customer_group_id" class="erp-input">
                    <option value="">All Groups</option>
                    @foreach ($customerGroups as $group)
                        <option value="{{ $group->id }}" {{ request('customer_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="erp-input">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search name..." class="erp-input w-64">
                <button type="submit" class="inline-flex items-center px-4 py-2 erp-btn-primary">Search</button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Customer Group</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Currency</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Valid Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($priceLists as $priceList)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('price-lists.show', $priceList) }}" class="text-blue-600 hover:text-blue-500">{{ $priceList->name }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $priceList->customerGroup?->name ?? 'General' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">TZS</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($priceList->isExpired())
                                        <span class="erp-badge-inactive">Expired</span>
                                    @elseif ($priceList->is_active)
                                        <span class="erp-badge-active">Active</span>
                                    @else
                                        <span class="erp-badge-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $priceList->items_count ?? $priceList->items->count() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    {{ $priceList->valid_from?->format('Y-m-d') ?? '∞' }} - {{ $priceList->valid_until?->format('Y-m-d') ?? '∞' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('price-lists.edit', $priceList) }}" class="text-blue-600 hover:text-blue-500 mr-2">Edit</a>
                                    <form action="{{ route('price-lists.destroy', $priceList) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-500">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-slate-500">No price lists found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">{{ $priceLists->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
