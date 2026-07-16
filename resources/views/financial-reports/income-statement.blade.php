<x-app-layout>
    <x-slot name="header">{{ __('Income Statement') }}</x-slot>
    <x-slot name="headerDescription">Revenue, expenses, and net income for the period (IFRS format).</x-slot>

    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <form action="{{ route('financial-reports.income-statement') }}" method="GET" class="flex gap-3 items-end flex-wrap">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">From</label>
                    <input type="date" name="from" value="{{ $from }}" class="erp-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">To</label>
                    <input type="date" name="to" value="{{ $to }}" class="erp-input">
                </div>
                <button type="submit" class="erp-btn-primary">Generate</button>
                <a href="{{ route('financial-reports.income-statement', ['from' => now()->startOfYear()->toDateString(), 'to' => now()->toDateString()]) }}" class="erp-btn-ghost text-sm">Year to Date</a>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/60">
                <h3 class="text-lg font-semibold text-slate-800">Income Statement</h3>
                <p class="text-xs text-slate-400 mt-1">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
            </div>
            <div class="p-6 space-y-8">
                {{-- Revenue --}}
                <div>
                    <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Revenue</h4>
                    <table class="w-full">
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($revenue as $row)
                                <tr>
                                    <td class="py-2 text-sm text-slate-700 pl-4">{{ $row['account']->code }} — {{ $row['account']->name }}</td>
                                    <td class="py-2 text-sm font-mono text-right {{ $row['amount'] >= 0 ? 'text-slate-800' : 'text-red-600' }}">
                                        TSh {{ number_format(abs($row['amount']), 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="py-2 text-sm text-slate-400 pl-4">No revenue accounts</td><td></td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-300 font-semibold">
                                <td class="py-2 text-sm text-slate-800">Total Revenue</td>
                                <td class="py-2 text-sm font-mono text-right text-slate-800">TSh {{ number_format($totalRevenue, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Cost of Goods Sold --}}
                <div>
                    <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Cost of Goods Sold</h4>
                    <table class="w-full">
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($cogs as $row)
                                <tr>
                                    <td class="py-2 text-sm text-slate-700 pl-4">{{ $row['account']->code }} — {{ $row['account']->name }}</td>
                                    <td class="py-2 text-sm font-mono text-right text-slate-800">TSh {{ number_format(abs($row['amount']), 2) }}</td>
                                </tr>
                            @empty
                                <tr><td class="py-2 text-sm text-slate-400 pl-4">No COGS accounts</td><td></td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-300 font-semibold">
                                <td class="py-2 text-sm text-slate-800">Total COGS</td>
                                <td class="py-2 text-sm font-mono text-right text-slate-800">TSh {{ number_format($totalCOGS, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Gross Profit --}}
                <div class="bg-slate-50 rounded-lg px-4 py-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-slate-800">Gross Profit</span>
                        <span class="text-sm font-mono font-semibold {{ $grossProfit >= 0 ? 'text-green-700' : 'text-red-600' }}">
                            TSh {{ number_format($grossProfit, 2) }}
                        </span>
                    </div>
                    @if ($totalRevenue > 0)
                        <div class="text-xs text-slate-500 mt-1">Gross Margin: {{ number_format(($grossProfit / $totalRevenue) * 100, 1) }}%</div>
                    @endif
                </div>

                {{-- Operating Expenses --}}
                <div>
                    <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Operating Expenses</h4>

                    @if ($adminExpenses->isNotEmpty())
                        <div class="mb-4">
                            <h5 class="text-xs font-medium text-slate-400 uppercase mb-2 pl-4">Administrative Expenses</h5>
                            <table class="w-full">
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($adminExpenses as $row)
                                        <tr>
                                            <td class="py-2 text-sm text-slate-700 pl-8">{{ $row['account']->code }} — {{ $row['account']->name }}</td>
                                            <td class="py-2 text-sm font-mono text-right text-slate-800">TSh {{ number_format(abs($row['amount']), 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="border-t border-slate-200 font-medium">
                                        <td class="py-2 text-sm text-slate-600 pl-8">Subtotal Admin</td>
                                        <td class="py-2 text-sm font-mono text-right text-slate-600">TSh {{ number_format($totalAdmin, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif

                    @if ($sellingExpenses->isNotEmpty())
                        <div class="mb-4">
                            <h5 class="text-xs font-medium text-slate-400 uppercase mb-2 pl-4">Selling & Distribution Expenses</h5>
                            <table class="w-full">
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($sellingExpenses as $row)
                                        <tr>
                                            <td class="py-2 text-sm text-slate-700 pl-8">{{ $row['account']->code }} — {{ $row['account']->name }}</td>
                                            <td class="py-2 text-sm font-mono text-right text-slate-800">TSh {{ number_format(abs($row['amount']), 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="border-t border-slate-200 font-medium">
                                        <td class="py-2 text-sm text-slate-600 pl-8">Subtotal Selling</td>
                                        <td class="py-2 text-sm font-mono text-right text-slate-600">TSh {{ number_format($totalSelling, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif

                    <table class="w-full">
                        <tfoot>
                            <tr class="border-t border-slate-300 font-semibold">
                                <td class="py-2 text-sm text-slate-800">Total Operating Expenses</td>
                                <td class="py-2 text-sm font-mono text-right text-slate-800">TSh {{ number_format($totalOperatingExpenses, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Operating Income --}}
                <div class="bg-slate-50 rounded-lg px-4 py-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-slate-800">Operating Income</span>
                        <span class="text-sm font-mono font-semibold {{ $operatingIncome >= 0 ? 'text-green-700' : 'text-red-600' }}">
                            TSh {{ number_format($operatingIncome, 2) }}
                        </span>
                    </div>
                </div>

                {{-- Other Income --}}
                @if ($otherIncome->isNotEmpty())
                    <div>
                        <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Other Income</h4>
                        <table class="w-full">
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($otherIncome as $row)
                                    <tr>
                                        <td class="py-2 text-sm text-slate-700 pl-4">{{ $row['account']->code }} — {{ $row['account']->name }}</td>
                                        <td class="py-2 text-sm font-mono text-right text-slate-800">TSh {{ number_format(abs($row['amount']), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-slate-300 font-semibold">
                                    <td class="py-2 text-sm text-slate-800">Total Other Income</td>
                                    <td class="py-2 text-sm font-mono text-right text-slate-800">TSh {{ number_format($totalOtherIncome, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif

                {{-- Net Income --}}
                <div class="bg-primary-50 rounded-lg px-4 py-4 border border-primary-200">
                    <div class="flex justify-between items-center">
                        <span class="text-base font-bold text-slate-900">Net Income</span>
                        <span class="text-base font-mono font-bold {{ $netIncome >= 0 ? 'text-green-700' : 'text-red-600' }}">
                            TSh {{ number_format($netIncome, 2) }}
                        </span>
                    </div>
                    @if ($totalRevenue > 0)
                        <div class="text-xs text-slate-500 mt-1">Net Profit Margin: {{ number_format(($netIncome / $totalRevenue) * 100, 1) }}%</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
