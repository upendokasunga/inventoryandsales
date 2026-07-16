<x-app-layout>
    <x-slot name="header">{{ __('General Ledger') }}</x-slot>
    <x-slot name="headerDescription">All journal entries for a date range, optionally filtered by account.</x-slot>

    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <form action="{{ route('financial-reports.general-ledger') }}" method="GET" class="flex gap-3 items-end flex-wrap">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">From</label>
                    <input type="date" name="from" value="{{ $from }}" class="erp-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">To</label>
                    <input type="date" name="to" value="{{ $to }}" class="erp-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Account</label>
                    <select name="account_id" class="erp-input">
                        <option value="">All Accounts</option>
                        @foreach ($accounts as $a)
                            <option value="{{ $a->id }}" {{ $accountId == $a->id ? 'selected' : '' }}>{{ $a->code }} — {{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="erp-btn-primary">Generate</button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/60">
                <h3 class="text-lg font-semibold text-slate-800">General Ledger</h3>
                <p class="text-xs text-slate-400 mt-1">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}{{ $accountId ? ' — Filtered' : '' }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200/60">
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Entry #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Account</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Debit</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Credit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($entries as $entry)
                            @foreach ($entry->lines as $line)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-2 text-sm text-slate-600 whitespace-nowrap">{{ $entry->entry_date }}</td>
                                    <td class="px-4 py-2 text-sm font-mono text-primary whitespace-nowrap">{{ $entry->entry_number }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-700 max-w-xs truncate">{{ $line->description ?: $entry->description }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ $line->account->code }} — {{ $line->account->name }}</td>
                                    <td class="px-4 py-2 text-sm font-mono text-right {{ $line->debit > 0 ? 'text-slate-800' : 'text-slate-400' }}">
                                        {{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}
                                    </td>
                                    <td class="px-4 py-2 text-sm font-mono text-right {{ $line->credit > 0 ? 'text-slate-800' : 'text-slate-400' }}">
                                        {{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-slate-50/80 border-t border-slate-200">
                                <td colspan="4" class="px-4 py-1"></td>
                                <td class="px-4 py-1 text-xs font-mono text-right font-semibold text-slate-600">{{ number_format($entry->total_debit, 2) }}</td>
                                <td class="px-4 py-1 text-xs font-mono text-right font-semibold text-slate-600">{{ number_format($entry->total_credit, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-400">No journal entries found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
