<x-app-layout>
    <x-slot name="header">{{ __('Journal Entries') }}</x-slot>
    <x-slot name="headerDescription">Manage general journal entries — track debits, credits, and financial postings.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('journal-entries.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Journal Entry
        </a>
    </x-slot>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 border-b border-slate-200">
            <nav class="flex space-x-4 -mb-px overflow-x-auto">
                @foreach (['all' => 'All', 'draft' => 'Draft', 'pending_approval' => 'Pending Approval', 'posted' => 'Posted', 'approved' => 'Approved', 'reversed' => 'Reversed', 'adjustment' => 'Adjustments'] as $key => $label)
                    <a href="{{ route('journal-entries.index', ['tab' => $key]) }}" class="pb-3 px-1 text-sm font-medium border-b-2 whitespace-nowrap {{ $tab === $key ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </div>
        <x-table-card :empty="count($entries) === 0" emptyMessage="No journal entries found." colspan="9">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Entry #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Debit</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Credit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Created By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($entries as $entry)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-800">{{ $entry->entry_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $entry->entry_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600 max-w-xs truncate">{{ $entry->description }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 text-right font-medium">TSh {{ number_format($entry->total_debit, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 text-right font-medium">TSh {{ number_format($entry->total_credit, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($entry->is_adjustment)
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Adjustment</span>
                            @else
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-slate-100 text-slate-600">Standard</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php $c = ['draft' => 'erp-badge-draft', 'posted' => 'erp-badge-approved', 'approved' => 'erp-badge-approved', 'reversed' => 'erp-badge-cancelled']; @endphp
                            <span class="{{ $c[$entry->status] ?? 'erp-badge-draft' }}">{{ ucfirst($entry->status) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $entry->creator?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links :view="route('journal-entries.show', $entry)" :delete="$entry->status === 'draft' ? route('journal-entries.destroy', $entry) : null" />
                            @if ($entry->status === 'draft')
                                <form action="{{ route('journal-entries.approve', $entry) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-primary hover:text-primary-dark font-medium">Post</button>
                                </form>
                            @endif
                            @if (in_array($entry->status, ['posted', 'approved']))
                                <form action="{{ route('journal-entries.reverse', $entry) }}" method="POST" class="inline" onsubmit="return confirm('Reverse this entry?')">
                                    @csrf
                                    <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">Reverse</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $entries->appends(['tab' => $tab])->links() }}</div>
    </div>
</x-app-layout>
