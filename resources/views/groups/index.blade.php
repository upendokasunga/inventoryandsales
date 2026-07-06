<x-app-layout>
    <x-slot name="header">{{ __('Groups') }}</x-slot>
    <x-slot name="headerDescription">Manage user groups and role-based access permissions.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('groups.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Group
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <x-table-card :empty="count($groups) === 0" emptyMessage="No groups found. Create one to get started." colspan="5">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Users</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($groups as $group)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                            {{ $group->name }}
                            @if ($group->is_super_admin)
                                <span class="erp-badge-purple ml-1">Super Admin</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $group->description }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $group->users_count }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($group->is_active)
                                <span class="erp-badge-active">Active</span>
                            @else
                                <span class="erp-badge-inactive">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links
                                :edit="route('groups.edit', $group)"
                                :delete="$group->is_super_admin ? null : route('groups.destroy', $group)"
                            />
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $groups->links() }}</div>
    </div>
</x-app-layout>
