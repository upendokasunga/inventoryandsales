<x-app-layout>
    <x-slot name="header">{{ __('Cost Centres') }}</x-slot>
    <x-slot name="headerDescription">Manage cost centres for tracking expenses across departments and projects.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('cost-centers.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Cost Centre
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        @if ($search)
            <div class="mb-4 flex items-center gap-2 text-sm text-slate-600">
                <span>Showing results for "<strong>{{ $search }}</strong>"</span>
                <a href="{{ route('cost-centers.index') }}" class="text-primary hover:underline">Clear</a>
            </div>
        @endif

        <x-table-card :empty="count($costCenters) === 0" emptyMessage="No cost centres found. Create one to get started." colspan="5">
            <x-slot name="searchAction">
                <form action="{{ route('cost-centers.index') }}" method="GET" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search cost centres..."
                        class="erp-input text-sm py-1.5">
                    <button type="submit" class="erp-btn-secondary text-sm py-1.5">Search</button>
                </form>
            </x-slot>
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($costCenters as $center)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-500">{{ $center->code ?: '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $center->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500 max-w-xs truncate">{{ $center->description ?: '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($center->is_active)
                                <span class="erp-badge-active">Active</span>
                            @else
                                <span class="erp-badge-inactive">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links
                                :edit="route('cost-centers.edit', $center)"
                                :delete="route('cost-centers.destroy', $center)"
                            />
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $costCenters->links() }}</div>
    </div>
</x-app-layout>
