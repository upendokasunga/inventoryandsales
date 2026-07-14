<x-app-layout>
    <x-slot name="header">{{ __('Transaction Details') }}</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('bank-accounts.show', $bankTransaction->bankAccount) }}" class="erp-btn-secondary">Back to Account</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">{{ $bankTransaction->description }}</h2>
                    <p class="text-sm text-slate-500">{{ $bankTransaction->bankAccount->name }} &middot; {{ $bankTransaction->transaction_date->format('d M Y') }}</p>
                </div>
                <span class="erp-badge {{ in_array($bankTransaction->type, ['deposit', 'transfer_in']) ? 'erp-badge-active' : 'erp-badge-danger' }}">
                    {{ str_replace('_', ' ', ucfirst($bankTransaction->type)) }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <div class="p-4 bg-slate-50 rounded-lg text-center">
                    <p class="text-xs text-slate-400 uppercase">Amount</p>
                    <p class="text-xl font-bold {{ in_array($bankTransaction->type, ['deposit', 'transfer_in']) ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ in_array($bankTransaction->type, ['deposit', 'transfer_in']) ? '+' : '-' }}TSh {{ number_format($bankTransaction->amount, 0) }}
                    </p>
                </div>
                <div class="p-4 bg-slate-50 rounded-lg text-center">
                    <p class="text-xs text-slate-400 uppercase">Running Balance</p>
                    <p class="text-xl font-bold text-slate-800">TSh {{ number_format($bankTransaction->running_balance, 0) }}</p>
                </div>
            </div>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Reference #</span><span class="font-mono">{{ $bankTransaction->reference_number ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Reconciled</span>
                    <span class="{{ $bankTransaction->reconciled ? 'text-emerald-600' : 'text-slate-400' }}">{{ $bankTransaction->reconciled ? 'Yes' : 'No' }}</span>
                </div>
                <div class="flex justify-between"><span class="text-slate-500">Recorded By</span><span>{{ $bankTransaction->creator?->name ?? 'System' }}</span></div>
                @if($bankTransaction->notes)
                    <div class="mt-4 p-3 bg-slate-50 rounded-lg">
                        <p class="text-xs text-slate-400 uppercase mb-1">Notes</p>
                        <p class="text-slate-600">{{ $bankTransaction->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
