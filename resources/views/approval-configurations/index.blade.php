<x-app-layout>
    <x-slot name="header">{{ __('Approval Configurations') }}</x-slot>
    <x-slot name="headerDescription">Configure approval workflows for different modules.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('approval-configurations.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Configuration
        </a>
    </x-slot>
    <div class="max-w-7xl mx-auto">
        <x-table-card :empty="count($configs) === 0" emptyMessage="No approval configurations found." colspan="5">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Module Key</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Levels</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($configs as $config)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-800">{{ $config->module_key }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ Str::limit($config->description ?? $config->module_name, 50) ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse ($config->levels as $level)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                                        L{{ $level->level }}: {{ $level->group?->name ?? 'N/A' }}
                                    </span>
                                @empty
                                    <span class="text-xs text-slate-400">No levels</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="{{ $config->is_active ? 'erp-badge-active' : 'erp-badge-inactive' }}">{{ $config->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links :view="route('approval-configurations.show', $config)" :edit="route('approval-configurations.edit', $config)" :delete="route('approval-configurations.destroy', $config)" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $configs->links() }}</div>
    </div>
</x-app-layout>
