<x-app-layout>
    <x-slot name="header">Statement: {{ $account->name }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Chart of Accounts', 'url' => route('accounts.index')], ['label' => $account->name, 'url' => route('accounts.show', $account)], ['label' => 'Statement']]" />

    <div class="mb-4">
        <form method="GET" class="flex gap-3 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1">From</label>
                <input type="date" name="from" value="{{ $from }}" class="erp-input">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">To</label>
                <input type="date" name="to" value="{{ $to }}" class="erp-input">
            </div>
            <button type="submit" class="erp-btn-primary">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">Date</th>
                        <th class="text-left">Reference</th>
                        <th class="text-left">Description</th>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lines as $line)
                        <tr>
                            <td class="text-sm">{{ $line->entry_date }}</td>
                            <td class="text-sm font-mono">{{ $line->entry_number }}</td>
                            <td class="text-sm text-slate-600">{{ $line->description ?? '-' }}</td>
                            <td class="text-sm text-right font-mono">{{ $line->debit > 0 ? number_format($line->debit, 2) : '' }}</td>
                            <td class="text-sm text-right font-mono">{{ $line->credit > 0 ? number_format($line->credit, 2) : '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-slate-400 py-8">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-semibold">
                        <td colspan="3" class="text-right text-sm">Totals:</td>
                        <td class="text-right text-sm font-mono">{{ number_format($lines->sum('debit'), 2) }}</td>
                        <td class="text-right text-sm font-mono">{{ number_format($lines->sum('credit'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
