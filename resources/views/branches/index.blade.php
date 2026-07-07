<x-app-layout>
    <x-slot name="header">{{ __('Branches') }}</x-slot>
    <x-slot name="headerDescription">Manage your business branches — locations, contact information, and more.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('branches.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Branch
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <x-table-card :empty="count($branches) === 0" emptyMessage="No branches found. Create your first branch to get started." colspan="6">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($branches as $branch)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-800">{{ $branch->code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                            <a href="{{ route('branches.show', $branch) }}" class="text-primary hover:text-primary/80 transition">{{ $branch->name }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $branch->location ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $branch->phone ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($branch->is_active)
                                <span class="erp-badge-active">Active</span>
                            @else
                                <span class="erp-badge-inactive">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links
                                :view="route('branches.show', $branch)"
                                :edit="route('branches.edit', $branch)"
                                :delete="route('branches.destroy', $branch)"
                            />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $branches->links() }}</div>
    </div>
</x-app-layout>
