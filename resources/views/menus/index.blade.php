<x-app-layout>
    <x-slot name="header">{{ __('Menus') }}</x-slot>
    <x-slot name="headerDescription">Manage navigation menu items and their display order.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('menus.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Menu
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <x-table-card :empty="count($menus) === 0" emptyMessage="No menus found. Create one to get started." colspan="6">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Module</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Route</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($menus as $menu)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $menu->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $menu->module }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 font-mono">{{ $menu->route }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $menu->sort_order }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($menu->is_active)
                                <span class="erp-badge-active">Active</span>
                            @else
                                <span class="erp-badge-inactive">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links
                                :edit="route('menus.edit', $menu)"
                                :delete="route('menus.destroy', $menu)"
                            />
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $menus->links() }}</div>
    </div>
</x-app-layout>
