<x-app-layout>
    <x-slot name="header">{{ __('Trial Balance') }}</x-slot>
    <x-slot name="headerDescription">All accounts with debit and credit balances as of a specific date.</x-slot>

    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <form action="{{ route('financial-reports.trial-balance') }}" method="GET" class="flex gap-3 items-end flex-wrap">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">As of Date</label>
                    <input type="date" name="as_of" value="{{ $asOf }}" class="erp-input">
                </div>
                <button type="submit" class="erp-btn-primary">Generate</button>
                <a href="{{ route('financial-reports.trial-balance', ['as_of' => now()->toDateString()]) }}" class="erp-btn-ghost text-sm">Today</a>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/60">
                <h3 class="text-lg font-semibold text-slate-800">Trial Balance</h3>
                <p class="text-xs text-slate-400 mt-1">As of {{ \Carbon\Carbon::parse($asOf)->format('d M Y') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200/60">
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Account Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Debit (TSh)</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Credit (TSh)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($accounts as $row)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-3 text-sm font-mono text-slate-800">{{ $row['account']->code }}</td>
                                <td class="px-6 py-3 text-sm font-medium text-slate-800">{{ $row['account']->name }}</td>
                                <td class="px-6 py-3 text-sm text-slate-600">{{ ucfirst($row['account']->type) }}</td>
                                <td class="px-6 py-3 text-sm font-mono text-right {{ $row['debit'] > 0 ? 'text-slate-800' : 'text-slate-400' }}">
                                    {{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '-' }}
                                </td>
                                <td class="px-6 py-3 text-sm font-mono text-right {{ $row['credit'] > 0 ? 'text-slate-800' : 'text-slate-400' }}">
                                    {{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-400">No accounts with balances found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-300 bg-slate-50 font-semibold">
                            <td colspan="3" class="px-6 py-3 text-sm text-slate-800">Total</td>
                            <td class="px-6 py-3 text-sm font-mono text-right text-slate-800">{{ number_format($totalDebit, 2) }}</td>
                            <td class="px-6 py-3 text-sm font-mono text-right text-slate-800">{{ number_format($totalCredit, 2) }}</td>
                        </tr>
                        @if (abs($totalDebit - $totalCredit) > 0.01)
                            <tr class="bg-red-50">
                                <td colspan="5" class="px-6 py-2 text-sm text-red-600 text-center">
                                    Difference: TSh {{ number_format(abs($totalDebit - $totalCredit), 2) }} — Books are out of balance!
                                </td>
                            </tr>
                        @else
                            <tr class="bg-green-50">
                                <td colspan="5" class="px-6 py-2 text-sm text-green-600 text-center">
                                    Books are balanced
                                </td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
