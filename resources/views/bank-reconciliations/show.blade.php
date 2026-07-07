<x-app-layout>
    <x-slot name="header">Reconciliation {{ $bankReconciliation->reconciliation_number }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Reconciliations', 'url' => route('bank-reconciliations.index')], ['label' => $bankReconciliation->reconciliation_number]]" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">{{ $bankReconciliation->bankAccount->name ?? 'N/A' }}</h2>
                        <p class="text-sm text-slate-500">
                            Period: {{ $bankReconciliation->start_date->format('d M Y') }} - {{ $bankReconciliation->end_date->format('d M Y') }}
                        </p>
                        @if($bankReconciliation->statement_reference)
                            <p class="text-sm text-slate-500">Statement: {{ $bankReconciliation->statement_reference }}</p>
                        @endif
                    </div>
                    <div>
                        <span class="px-3 py-1 text-sm rounded-full font-medium
                            @if($bankReconciliation->status === 'completed') bg-green-100 text-green-700
                            @elseif($bankReconciliation->status === 'draft') bg-yellow-100 text-yellow-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ ucfirst($bankReconciliation->status) }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-4 mb-6">
                    <div class="p-4 bg-slate-50 rounded-lg text-center">
                        <p class="text-xs text-slate-400 uppercase">Opening</p>
                        <p class="text-lg font-bold text-slate-800">{{ number_format($bankReconciliation->opening_balance, 0) }}</p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-lg text-center">
                        <p class="text-xs text-slate-400 uppercase">Closing (Statement)</p>
                        <p class="text-lg font-bold text-slate-800">{{ number_format($bankReconciliation->closing_balance, 0) }}</p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-lg text-center">
                        <p class="text-xs text-slate-400 uppercase">System Balance</p>
                        <p class="text-lg font-bold text-slate-800">{{ number_format($bankReconciliation->bankAccount->current_balance ?? 0, 0) }}</p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-lg text-center">
                        <p class="text-xs text-slate-400 uppercase">Difference</p>
                        <p class="text-lg font-bold {{ $bankReconciliation->difference == 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($bankReconciliation->difference, 0) }}
                        </p>
                    </div>
                </div>

                {{-- Transactions --}}
                @if($bankReconciliation->transactions->count() > 0)
                    <div class="mt-6">
                        <h3 class="text-sm font-semibold text-slate-700 mb-3">Transactions</h3>
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                                <tr>
                                    <th class="text-left px-3 py-2">Date</th>
                                    <th class="text-left px-3 py-2">Description</th>
                                    <th class="text-left px-3 py-2">Type</th>
                                    <th class="text-right px-3 py-2">Amount</th>
                                    <th class="text-left px-3 py-2">Status</th>
                                    @if($bankReconciliation->status === 'draft')
                                        <th class="text-left px-3 py-2">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($bankReconciliation->transactions as $tx)
                                    <tr>
                                        <td class="px-3 py-2">{{ $tx->transaction_date->format('d M Y') }}</td>
                                        <td class="px-3 py-2">{{ $tx->description }}</td>
                                        <td class="px-3 py-2 capitalize">{{ str_replace('_', ' ', $tx->type) }}</td>
                                        <td class="px-3 py-2 text-right font-medium">
                                            {{ in_array($tx->type, ['deposit', 'transfer_in']) ? '+' : '-' }}{{ number_format($tx->amount, 0) }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-0.5 text-xs rounded-full font-medium
                                                @if($tx->pivot?->status === 'matched') bg-green-100 text-green-700
                                                @else bg-slate-100 text-slate-500 @endif">
                                                {{ $tx->pivot?->status ?? 'unmatched' }}
                                            </span>
                                        </td>
                                        @if($bankReconciliation->status === 'draft')
                                            <td class="px-3 py-2">
                                                @if($tx->pivot?->status !== 'matched')
                                                    <form action="{{ route('bank-reconciliations.match', $bankReconciliation) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="transaction_id" value="{{ $tx->id }}">
                                                        <button type="submit" class="text-xs text-primary hover:underline">Match</button>
                                                    </form>
                                                @else
                                                    <span class="text-xs text-green-600">Matched</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-slate-400 text-center py-4">No transactions in this period.</p>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-3">
                <h3 class="text-sm font-semibold text-slate-700">Actions</h3>

                @if($bankReconciliation->status === 'draft')
                    <form action="{{ route('bank-reconciliations.complete', $bankReconciliation) }}" method="POST" onsubmit="return confirm('Complete this reconciliation?')">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 bg-success text-white text-sm rounded-lg hover:bg-success-600 transition">Complete Reconciliation</button>
                    </form>
                    <form action="{{ route('bank-reconciliations.cancel', $bankReconciliation) }}" method="POST" onsubmit="return confirm('Cancel this reconciliation?')">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 bg-danger text-white text-sm rounded-lg hover:bg-danger-600 transition">Cancel</button>
                    </form>
                @endif

                <a href="{{ route('bank-reconciliations.index') }}" class="block w-full text-center px-3 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition">Back to List</a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Details</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Created By</span><span>{{ $bankReconciliation->creator?->name ?? 'System' }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Created</span><span>{{ $bankReconciliation->created_at->format('d M Y') }}</span></div>
                    @if($bankReconciliation->completed_at)
                        <div class="flex justify-between"><span class="text-slate-500">Completed</span><span>{{ $bankReconciliation->completed_at->format('d M Y H:i') }}</span></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
