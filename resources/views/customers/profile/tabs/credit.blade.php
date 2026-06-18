<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="erp-card text-center">
            <p class="text-sm font-medium text-slate-500">Credit Limit</p>
            <p class="mt-2 text-2xl font-bold text-slate-800">{{ number_format($creditInfo['limit'], 0) }}</p>
        </div>
        <div class="erp-card text-center">
            <p class="text-sm font-medium text-slate-500">Outstanding</p>
            <p class="mt-2 text-2xl font-bold {{ $creditInfo['outstanding'] > 0 ? 'text-warning-600' : 'text-slate-800' }}">{{ number_format($creditInfo['outstanding'], 0) }}</p>
        </div>
        <div class="erp-card text-center">
            <p class="text-sm font-medium text-slate-500">Available</p>
            <p class="mt-2 text-2xl font-bold {{ $creditInfo['available'] > 0 ? 'text-success-600' : 'text-danger-600' }}">{{ number_format($creditInfo['available'], 0) }}</p>
        </div>
    </div>

    @if ($creditInfo['limit'] > 0)
        @php $utilPct = min(100, ($creditInfo['outstanding'] / $creditInfo['limit']) * 100); @endphp
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
            <div class="flex justify-between text-sm text-slate-500 mb-1">
                <span>Credit Utilization</span>
                <span class="font-semibold {{ $utilPct > 90 ? 'text-danger-600' : ($utilPct > 70 ? 'text-warning-600' : 'text-success-600') }}">{{ number_format($utilPct, 1) }}%</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-3">
                <div class="h-3 rounded-full {{ $utilPct > 90 ? 'bg-danger-500' : ($utilPct > 70 ? 'bg-warning-500' : 'bg-success-500') }}" style="width: {{ $utilPct }}%"></div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-800">Credit Transaction History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Processed By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($transactions as $tx)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    {{ in_array($tx->type, ['payment', 'refund']) ? 'bg-success-50 text-success-600' : 'bg-warning-50 text-warning-600' }}">
                                    {{ ucfirst($tx->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700 max-w-xs truncate">{{ $tx->description }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ $tx->amount > 0 ? 'text-danger-600' : 'text-success-600' }}">
                                {{ $tx->amount > 0 ? '+' : '' }}{{ number_format($tx->amount, 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-slate-700">{{ number_format($tx->balance_after, 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $tx->user?->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500">No credit transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
