<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-800">Activity Log</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Changes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($auditLogs as $log)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    {{ match($log->event) {
                                        'created' => 'bg-success-50 text-success-600',
                                        'updated' => 'bg-primary-50 text-primary',
                                        'deleted' => 'bg-danger-50 text-danger-600',
                                        default => 'bg-slate-50 text-slate-600',
                                    } }}">
                                    {{ ucfirst($log->event) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700 max-w-xs truncate">
                                @if ($log->event === 'updated' && $log->new_values)
                                    @php $changed = array_keys($log->new_values); @endphp
                                    {{ implode(', ', $changed) }}
                                @elseif ($log->event === 'created')
                                    <span class="text-success-600">Record created</span>
                                @elseif ($log->event === 'deleted')
                                    <span class="text-danger-600">Record deleted</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 font-mono">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">No audit logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($auditLogs instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>
</div>
