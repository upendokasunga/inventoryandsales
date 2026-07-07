<x-app-layout>
    <x-slot name="header">Bank Reconciliations</x-slot>
    <x-slot name="headerDescription">Reconcile bank statements against system transactions.</x-slot>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-stat-card label="Total Reconciliations" :value="$stats['total_reconciliations']" color="primary" />
        <x-stat-card label="In Progress" :value="$stats['pending_reconciliations']" color="warning" />
        <x-stat-card label="Completed" :value="$stats['completed_reconciliations']" color="success" />
    </div>

    <div class="flex justify-end mb-4">
        <a href="{{ route('bank-reconciliations.create') }}" class="px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-600 transition">New Reconciliation</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Recon #</th>
                    <th class="text-left px-4 py-3">Bank Account</th>
                    <th class="text-left px-4 py-3">Period</th>
                    <th class="text-right px-4 py-3">Opening</th>
                    <th class="text-right px-4 py-3">Closing</th>
                    <th class="text-right px-4 py-3">Difference</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($reconciliations as $rec)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium">{{ $rec->reconciliation_number }}</td>
                        <td class="px-4 py-3">{{ $rec->bankAccount->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">{{ $rec->start_date->format('d M') }} - {{ $rec->end_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($rec->opening_balance, 0) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($rec->closing_balance, 0) }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ $rec->difference != 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($rec->difference, 0) }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-xs rounded-full font-medium
                                @if($rec->status === 'completed') bg-green-100 text-green-700
                                @elseif($rec->status === 'draft') bg-yellow-100 text-yellow-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ ucfirst($rec->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('bank-reconciliations.show', $rec) }}" class="text-primary hover:underline text-xs">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-400">No reconciliations yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $reconciliations->links() }}</div>
</x-app-layout>
