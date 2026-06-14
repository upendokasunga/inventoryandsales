<x-app-layout>
    <x-slot name="header">
        {{ __('Audit Logs') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg shadow-blue-500/5 border border-blue-100 mb-6">
            <div class="p-4">
                <form method="GET" class="flex gap-2">
                    <input type="text" name="search" placeholder="Search by model or event..." value="{{ request('search') }}"
                        class="rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 flex-1">
                    <select name="event" class="rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Events</option>
                        <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Created</option>
                        <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Updated</option>
                        <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                        <option value="restored" {{ request('event') == 'restored' ? 'selected' : '' }}>Restored</option>
                    </select>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-sky-500 hover:from-blue-500 hover:to-sky-400 border border-transparent rounded-lg font-semibold text-xs text-white shadow-lg shadow-blue-500/20 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">Filter</button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg shadow-blue-500/5 border border-blue-100 overflow-hidden">
            <div class="p-6">
                <table class="min-w-full divide-y divide-blue-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Model</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blue-50">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $log->user?->name ?? 'System' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $log->event === 'created' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                        {{ $log->event === 'updated' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $log->event === 'deleted' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $log->event === 'restored' ? 'bg-amber-100 text-amber-700' : '' }}">
                                        {{ $log->event }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 font-mono">{{ class_basename($log->auditable_type) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $log->auditable_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $log->ip_address }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500">No audit logs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">{{ $logs->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
