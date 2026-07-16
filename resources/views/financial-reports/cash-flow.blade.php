<x-app-layout>
    <x-slot name="header">{{ __('Cash Flow Statement') }}</x-slot>
    <x-slot name="headerDescription">Cash movements from operating, investing, and financing activities.</x-slot>

    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <form action="{{ route('financial-reports.cash-flow') }}" method="GET" class="flex gap-3 items-end flex-wrap">
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
                <h3 class="text-lg font-semibold text-slate-800">Cash Flow Statement</h3>
                <p class="text-xs text-slate-400 mt-1">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
            </div>
            <div class="p-6 space-y-8">
                {{-- Operating Activities --}}
                <div>
                    <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Operating Activities</h4>
                    <table class="w-full">
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($operatingCash as $row)
                                <tr>
                                    <td class="py-2 text-sm text-slate-700 pl-4">{{ $row['account']->code }} — {{ $row['account']->name }}</td>
                                    <td class="py-2 text-sm font-mono text-right {{ $row['amount'] >= 0 ? 'text-green-700' : 'text-red-600' }}">
                                        TSh {{ number_format($row['amount'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="py-2 text-sm text-slate-400 pl-4">No operating accounts</td><td></td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-300 font-semibold">
                                <td class="py-2 text-sm text-slate-800">Net Cash from Operating</td>
                                <td class="py-2 text-sm font-mono text-right {{ $operatingTotal >= 0 ? 'text-green-700' : 'text-red-600' }}">
                                    TSh {{ number_format($operatingTotal, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Investing Activities --}}
                <div>
                    <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Investing Activities</h4>
                    <table class="w-full">
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($investingCash as $row)
                                <tr>
                                    <td class="py-2 text-sm text-slate-700 pl-4">{{ $row['account']->code }} — {{ $row['account']->name }}</td>
                                    <td class="py-2 text-sm font-mono text-right {{ $row['amount'] >= 0 ? 'text-green-700' : 'text-red-600' }}">
                                        TSh {{ number_format($row['amount'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="py-2 text-sm text-slate-400 pl-4">No investing accounts</td><td></td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-300 font-semibold">
                                <td class="py-2 text-sm text-slate-800">Net Cash from Investing</td>
                                <td class="py-2 text-sm font-mono text-right {{ $investingTotal >= 0 ? 'text-green-700' : 'text-red-600' }}">
                                    TSh {{ number_format($investingTotal, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Financing Activities --}}
                <div>
                    <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Financing Activities</h4>
                    <table class="w-full">
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($financingCash as $row)
                                <tr>
                                    <td class="py-2 text-sm text-slate-700 pl-4">{{ $row['account']->code }} — {{ $row['account']->name }}</td>
                                    <td class="py-2 text-sm font-mono text-right {{ $row['amount'] >= 0 ? 'text-green-700' : 'text-red-600' }}">
                                        TSh {{ number_format($row['amount'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="py-2 text-sm text-slate-400 pl-4">No financing accounts</td><td></td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-300 font-semibold">
                                <td class="py-2 text-sm text-slate-800">Net Cash from Financing</td>
                                <td class="py-2 text-sm font-mono text-right {{ $financingTotal >= 0 ? 'text-green-700' : 'text-red-600' }}">
                                    TSh {{ number_format($financingTotal, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Net Cash Flow --}}
                <div class="bg-primary-50 rounded-lg px-4 py-4 border border-primary-200">
                    <div class="flex justify-between items-center">
                        <span class="text-base font-bold text-slate-900">Net Change in Cash</span>
                        <span class="text-base font-mono font-bold {{ $netCashFlow >= 0 ? 'text-green-700' : 'text-red-600' }}">
                            TSh {{ number_format($netCashFlow, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
