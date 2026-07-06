<x-app-layout>
    <x-slot name="header">{{ __('Units') }}</x-slot>
    <x-slot name="headerDescription">Manage units of measurement used across products and inventory.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('units.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Unit
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <x-table-card :empty="count($units) === 0" emptyMessage="No units found. Create one to get started." colspan="4">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Short Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Base Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($units as $unit)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $unit->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $unit->short_code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($unit->is_base)
                                <span class="erp-badge-active">Yes</span>
                            @else
                                <span class="erp-badge-inactive">No</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links
                                :edit="route('units.edit', $unit)"
                                :delete="route('units.destroy', $unit)"
                            />
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $units->links() }}</div>
    </div>
</x-app-layout>
