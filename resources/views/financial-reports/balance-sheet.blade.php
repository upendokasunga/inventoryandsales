<x-app-layout>
    <x-slot name="header">{{ __('Balance Sheet') }}</x-slot>
    <x-slot name="headerDescription">Assets, liabilities, and equity as of a specific date (IFRS format).</x-slot>

    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <form action="{{ route('financial-reports.balance-sheet') }}" method="GET" class="flex gap-3 items-end flex-wrap">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">As of Date</label>
                    <input type="date" name="as_of" value="{{ $asOf }}" class="erp-input">
                </div>
                <button type="submit" class="erp-btn-primary">Generate</button>
                <a href="{{ route('financial-reports.balance-sheet', ['as_of' => now()->toDateString()]) }}" class="erp-btn-ghost text-sm">Today</a>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/60">
                <h3 class="text-lg font-semibold text-slate-800">Balance Sheet</h3>
                <p class="text-xs text-slate-400 mt-1">As of {{ \Carbon\Carbon::parse($asOf)->format('d M Y') }}</p>
            </div>
            <div class="p-6 space-y-8">
                {{-- ASSETS --}}
                <div>
                    <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Assets</h4>

                    {{-- Current Assets --}}
                    <div class="mb-4">
                        <h5 class="text-xs font-medium text-slate-400 uppercase mb-2 pl-4">Current Assets</h5>
                        <table class="w-full">
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($currentAssets as $row)
                                    <tr>
                                        <td class="py-2 text-sm text-slate-700 pl-4">{{ $row['account']->code }} — {{ $row['account']->name }}</td>
                                        <td class="py-2 text-sm font-mono text-right {{ $row['balance'] >= 0 ? 'text-slate-800' : 'text-red-600' }}">
                                            TSh {{ number_format(abs($row['balance']), 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="py-2 text-sm text-slate-400 pl-4">No current assets</td><td></td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-slate-200 font-medium">
                                    <td class="py-2 text-sm text-slate-600 pl-4">Total Current Assets</td>
                                    <td class="py-2 text-sm font-mono text-right text-slate-600">TSh {{ number_format($totalCurrentAssets, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Non-Current Assets --}}
                    <div class="mb-4">
                        <h5 class="text-xs font-medium text-slate-400 uppercase mb-2 pl-4">Non-Current Assets</h5>
                        <table class="w-full">
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($nonCurrentAssets as $row)
                                    <tr>
                                        <td class="py-2 text-sm text-slate-700 pl-4">{{ $row['account']->code }} — {{ $row['account']->name }}</td>
                                        <td class="py-2 text-sm font-mono text-right {{ $row['balance'] >= 0 ? 'text-slate-800' : 'text-red-600' }}">
                                            TSh {{ number_format(abs($row['balance']), 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="py-2 text-sm text-slate-400 pl-4">No non-current assets</td><td></td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-slate-200 font-medium">
                                    <td class="py-2 text-sm text-slate-600 pl-4">Total Non-Current Assets</td>
                                    <td class="py-2 text-sm font-mono text-right text-slate-600">TSh {{ number_format($totalNonCurrentAssets, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Total Assets --}}
                    <div class="bg-slate-50 rounded-lg px-4 py-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-bold text-slate-800">Total Assets</span>
                            <span class="text-sm font-mono font-bold text-slate-800">TSh {{ number_format($totalAssets, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- LIABILITIES --}}
                <div>
                    <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Liabilities</h4>

                    {{-- Current Liabilities --}}
                    <div class="mb-4">
                        <h5 class="text-xs font-medium text-slate-400 uppercase mb-2 pl-4">Current Liabilities</h5>
                        <table class="w-full">
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($currentLiabilities as $row)
                                    <tr>
                                        <td class="py-2 text-sm text-slate-700 pl-4">{{ $row['account']->code }} — {{ $row['account']->name }}</td>
                                        <td class="py-2 text-sm font-mono text-right text-slate-800">TSh {{ number_format(abs($row['balance']), 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="py-2 text-sm text-slate-400 pl-4">No current liabilities</td><td></td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-slate-200 font-medium">
                                    <td class="py-2 text-sm text-slate-600 pl-4">Total Current Liabilities</td>
                                    <td class="py-2 text-sm font-mono text-right text-slate-600">TSh {{ number_format($totalCurrentLiabilities, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Non-Current Liabilities --}}
                    <div class="mb-4">
                        <h5 class="text-xs font-medium text-slate-400 uppercase mb-2 pl-4">Non-Current Liabilities</h5>
                        <table class="w-full">
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($nonCurrentLiabilities as $row)
                                    <tr>
                                        <td class="py-2 text-sm text-slate-700 pl-4">{{ $row['account']->code }} — {{ $row['account']->name }}</td>
                                        <td class="py-2 text-sm font-mono text-right text-slate-800">TSh {{ number_format(abs($row['balance']), 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="py-2 text-sm text-slate-400 pl-4">No non-current liabilities</td><td></td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-slate-200 font-medium">
                                    <td class="py-2 text-sm text-slate-600 pl-4">Total Non-Current Liabilities</td>
                                    <td class="py-2 text-sm font-mono text-right text-slate-600">TSh {{ number_format($totalNonCurrentLiabilities, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Total Liabilities --}}
                    <div class="bg-slate-50 rounded-lg px-4 py-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-bold text-slate-800">Total Liabilities</span>
                            <span class="text-sm font-mono font-bold text-slate-800">TSh {{ number_format($totalLiabilities, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- EQUITY --}}
                <div>
                    <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Equity</h4>
                    <table class="w-full">
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($equity as $row)
                                <tr>
                                    <td class="py-2 text-sm text-slate-700 pl-4">{{ $row['account']->code }} — {{ $row['account']->name }}</td>
                                    <td class="py-2 text-sm font-mono text-right {{ $row['balance'] >= 0 ? 'text-slate-800' : 'text-red-600' }}">
                                        TSh {{ number_format(abs($row['balance']), 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="py-2 text-sm text-slate-400 pl-4">No equity accounts</td><td></td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-300 font-semibold">
                                <td class="py-2 text-sm text-slate-800">Total Equity</td>
                                <td class="py-2 text-sm font-mono text-right text-slate-800">TSh {{ number_format($totalEquity, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Total Liabilities + Equity --}}
                <div class="bg-primary-50 rounded-lg px-4 py-4 border border-primary-200">
                    <div class="flex justify-between items-center">
                        <span class="text-base font-bold text-slate-900">Total Liabilities & Equity</span>
                        <span class="text-base font-mono font-bold text-slate-900">TSh {{ number_format($totalLiabilitiesAndEquity, 2) }}</span>
                    </div>
                </div>

                {{-- Balance Check --}}
                @if (abs($totalAssets - $totalLiabilitiesAndEquity) > 0.01)
                    <div class="bg-red-50 rounded-lg px-4 py-3 border border-red-200">
                        <p class="text-sm text-red-600 text-center">
                            Difference: TSh {{ number_format(abs($totalAssets - $totalLiabilitiesAndEquity), 2) }} — Balance sheet is out of balance!
                        </p>
                    </div>
                @else
                    <div class="bg-green-50 rounded-lg px-4 py-3 border border-green-200">
                        <p class="text-sm text-green-600 text-center">Balance sheet is balanced</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
