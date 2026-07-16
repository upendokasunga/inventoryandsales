<x-app-layout>
    <x-slot name="header">{{ __('Account Statement') }}</x-slot>
    <x-slot name="headerDescription">Detailed ledger for a single account with running balance.</x-slot>

    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <form action="{{ route('financial-reports.account-statement') }}" method="GET" class="flex gap-3 items-end flex-wrap">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Account</label>
                    <select name="account_id" class="erp-input" required>
                        @foreach ($accounts as $a)
                            <option value="{{ $a->id }}" {{ $account->id == $a->id ? 'selected' : '' }}>{{ $a->code }} — {{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">From</label>
                    <input type="date" name="from" value="{{ $from }}" class="erp-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">To</label>
                    <input type="date" name="to" value="{{ $to }}" class="erp-input">
                </div>
                <button type="submit" class="erp-btn-primary">Generate</button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/60">
                <h3 class="text-lg font-semibold text-slate-800">{{ $account->code }} — {{ $account->name }}</h3>
                <p class="text-xs text-slate-400 mt-1">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200/60">
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Entry #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Description</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Debit</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Credit</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr class="bg-slate-50/80">
                            <td colspan="4" class="px-4 py-2 text-sm font-medium text-slate-600">Opening Balance</td>
                            <td></td>
                            <td class="px-4 py-2 text-sm font-mono text-right font-semibold {{ $openingBalance >= 0 ? 'text-slate-800' : 'text-red-600' }}">
                                TSh {{ number_format($openingBalance, 2) }}
                            </td>
                        </tr>
                        @forelse ($lines as $line)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-2 text-sm text-slate-600 whitespace-nowrap">{{ $line->entry_date }}</td>
                                <td class="px-4 py-2 text-sm font-mono text-primary whitespace-nowrap">{{ $line->entry_number }}</td>
                                <td class="px-4 py-2 text-sm text-slate-700 max-w-xs truncate">{{ $line->line_description ?: $line->entry_description }}</td>
                                <td class="px-4 py-2 text-sm font-mono text-right {{ $line->debit > 0 ? 'text-slate-800' : 'text-slate-400' }}">
                                    {{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}
                                </td>
                                <td class="px-4 py-2 text-sm font-mono text-right {{ $line->credit > 0 ? 'text-slate-800' : 'text-slate-400' }}">
                                    {{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}
                                </td>
                                <td class="px-4 py-2 text-sm font-mono text-right font-semibold {{ $line->balance >= 0 ? 'text-slate-800' : 'text-red-600' }}">
                                    TSh {{ number_format($line->balance, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-400">No transactions found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-300 bg-slate-50">
                            <td colspan="3" class="px-4 py-3 text-sm font-semibold text-slate-800">Closing Balance</td>
                            <td class="px-4 py-3"></td>
                            <td></td>
                            <td class="px-4 py-3 text-sm font-mono text-right font-bold {{ $closingBalance >= 0 ? 'text-slate-800' : 'text-red-600' }}">
                                TSh {{ number_format($closingBalance, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
