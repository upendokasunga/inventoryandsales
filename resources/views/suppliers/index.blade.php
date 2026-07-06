<x-app-layout>
    <x-slot name="header">{{ __('Suppliers') }}</x-slot>
    <x-slot name="headerDescription">Manage your supplier base — track contacts, purchases, and performance.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('suppliers.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Supplier
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
            <form action="{{ route('suppliers.index') }}" method="GET" class="flex gap-2">
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search suppliers..." class="erp-input pl-10">
                </div>
                <button type="submit" class="erp-btn-primary">Search</button>
            </form>
        </div>

        <x-table-card :empty="count($suppliers) === 0" emptyMessage="No suppliers found. Add your first supplier to get started." colspan="5">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">City</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($suppliers as $supplier)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                            <a href="{{ route('suppliers.show', $supplier) }}" class="text-primary hover:text-primary/80 transition">{{ $supplier->name }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $supplier->contact_person ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $supplier->email ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $supplier->city ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links
                                :view="route('suppliers.show', $supplier)"
                                :edit="route('suppliers.edit', $supplier)"
                                :delete="route('suppliers.destroy', $supplier)"
                            />
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $suppliers->links() }}</div>
    </div>
</x-app-layout>
