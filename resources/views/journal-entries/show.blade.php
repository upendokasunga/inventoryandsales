<x-app-layout>
    <x-slot name="header">{{ __('Journal Entry') }}: {{ $journalEntry->entry_number }}</x-slot>
    <div class="max-w-7xl mx-auto">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('journal-entries.index') }}" class="erp-btn-secondary">Back to List</a>
            <div class="flex gap-2">
                <a href="{{ route('journal-entries.print', $journalEntry) }}" class="erp-btn-secondary" target="_blank">Print PDF</a>
                @if ($journalEntry->status === 'draft')
                    <form action="{{ route('journal-entries.approve', $journalEntry) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="erp-btn-primary">Post Entry</button>
                    </form>
                @endif
                @if (in_array($journalEntry->status, ['posted', 'approved']))
                    <form action="{{ route('journal-entries.reverse', $journalEntry) }}" method="POST" class="inline" onsubmit="return confirm('Reverse this journal entry?')">
                        @csrf
                        <button type="submit" class="erp-btn-danger">Reverse</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Entry Information</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Entry #</dt>
                            <dd class="text-sm font-semibold text-slate-800">{{ $journalEntry->entry_number }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Date</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $journalEntry->entry_date->format('d M Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Status</dt>
                            <dd>@php $c = ['draft' => 'erp-badge-draft', 'posted' => 'erp-badge-approved', 'approved' => 'erp-badge-approved', 'reversed' => 'erp-badge-cancelled']; @endphp <span class="{{ $c[$journalEntry->status] ?? 'erp-badge-draft' }}">{{ ucfirst($journalEntry->status) }}</span></dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Totals</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Total Debit</dt>
                            <dd class="text-sm font-semibold text-slate-800">{{ number_format($journalEntry->total_debit, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Total Credit</dt>
                            <dd class="text-sm font-semibold text-slate-800">{{ number_format($journalEntry->total_credit, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Difference</dt>
                            <dd class="text-sm font-semibold {{ abs($journalEntry->total_debit - $journalEntry->total_credit) < 0.01 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($journalEntry->total_debit - $journalEntry->total_credit, 2) }}
                            </dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Audit</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Created By</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $journalEntry->creator?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Created At</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $journalEntry->created_at->format('d M Y H:i') }}</dd>
                        </div>
                        @if ($journalEntry->approved_by)
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Approved By</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $journalEntry->approver?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Approved At</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $journalEntry->approved_at?->format('d M Y H:i') }}</dd>
                        </div>
                        @endif
                        @if ($journalEntry->reversed_by)
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Reversed By</dt>
                            <dd class="text-sm font-semibold text-red-600">{{ $journalEntry->reverser?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Reversed At</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $journalEntry->reversed_at?->format('d M Y H:i') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
                <div class="md:col-span-3">
                    <h3 class="text-sm font-medium text-slate-500 mb-2">Description</h3>
                    <p class="text-sm text-slate-700">{{ $journalEntry->description }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-200/60">
                <h3 class="text-sm font-medium text-slate-500">Journal Lines</h3>
            </div>
            <table class="erp-table">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Account</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Debit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Credit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($journalEntry->lines as $line)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $line->account?->code ?? '-' }} - {{ $line->account?->name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $line->description ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 text-right font-medium">{{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 text-right font-medium">{{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-slate-400">No lines found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-semibold">
                        <td colspan="2" class="px-6 py-3 text-sm text-slate-700 text-right">Totals</td>
                        <td class="px-6 py-3 text-sm text-slate-800 text-right">{{ number_format($journalEntry->total_debit, 2) }}</td>
                        <td class="px-6 py-3 text-sm text-slate-800 text-right">{{ number_format($journalEntry->total_credit, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
