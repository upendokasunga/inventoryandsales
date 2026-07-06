<x-app-layout>
    <x-slot name="header">{{ __('Audit Logs') }}</x-slot>
    <x-slot name="headerDescription">Track all system activity — creations, updates, deletions, and more.</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="erp-card mb-6">
            <form method="GET" class="flex gap-2">
                <div class="relative flex-1">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" placeholder="Search by model or event..." value="{{ request('search') }}" class="erp-input pl-10">
                </div>
                <select name="event" class="erp-input w-40">
                    <option value="">All Events</option>
                    <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                    <option value="restored" {{ request('event') == 'restored' ? 'selected' : '' }}>Restored</option>
                </select>
                <button type="submit" class="erp-btn-primary">Filter</button>
            </form>
        </div>

        <x-table-card :empty="count($logs) === 0" emptyMessage="No audit logs found." colspan="6">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Event</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Model</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($logs as $log)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $log->user?->name ?? 'System' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="erp-badge
                                {{ $log->event === 'created' ? 'erp-badge-active' : '' }}
                                {{ $log->event === 'updated' ? 'erp-badge-info' : '' }}
                                {{ $log->event === 'deleted' ? 'erp-badge-inactive' : '' }}
                                {{ $log->event === 'restored' ? 'erp-badge-warning' : '' }}">
                                {{ $log->event }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 font-mono">{{ class_basename($log->auditable_type) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $log->auditable_id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $logs->links() }}</div>
    </div>
</x-app-layout>
