<x-app-layout>
    <x-slot name="header">{{ __('Warehouses') }}</x-slot>
    <x-slot name="headerDescription">Manage your warehouse locations — organize inventory storage across branches.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('warehouses.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Warehouse
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
            <form action="{{ route('warehouses.index') }}" method="GET" class="flex gap-2 flex-wrap">
                <select name="type" class="erp-input" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="goods" {{ request('type') == 'goods' ? 'selected' : '' }}>Goods</option>
                </select>
                <button type="submit" class="erp-btn-primary">Filter</button>
            </form>
        </div>

        <x-table-card :empty="count($warehouses) === 0" emptyMessage="No warehouses found. Create your first warehouse to get started." colspan="7">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Branch</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($warehouses as $warehouse)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-800">{{ $warehouse->code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                            <a href="{{ route('warehouses.show', $warehouse) }}" class="text-primary hover:text-primary/80 transition">{{ $warehouse->name }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><span class="px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-700">{{ ucfirst(str_replace('_', ' ', $warehouse->type)) }}</span></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $warehouse->branch?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $warehouse->location ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($warehouse->is_active)
                                <span class="erp-badge-active">Active</span>
                            @else
                                <span class="erp-badge-inactive">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links
                                :view="route('warehouses.show', $warehouse)"
                                :edit="route('warehouses.edit', $warehouse)"
                                :delete="route('warehouses.destroy', $warehouse)"
                            />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $warehouses->links() }}</div>
    </div>
</x-app-layout>
