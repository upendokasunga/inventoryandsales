<x-app-layout>
    <x-slot name="header">Record Transaction - {{ $bankAccount->name }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Bank Accounts', 'url' => route('bank-accounts.index')], ['label' => $bankAccount->name, 'url' => route('bank-accounts.show', $bankAccount)], ['label' => 'New Transaction']]" />

    <div class="max-w-lg mx-auto">
        <form method="POST" action="{{ route('bank-transactions.store', $bankAccount) }}" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Type *</label>
                <select name="type" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" id="tx-type">
                    <option value="deposit" @selected(old('type') === 'deposit')>Deposit</option>
                    <option value="withdrawal" @selected(old('type') === 'withdrawal')>Withdrawal</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Amount *</label>
                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="0.00">
                @error('amount') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Date *</label>
                <input type="date" name="transaction_date" value="{{ old('transaction_date', now()->format('Y-m-d')) }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Description *</label>
                <input type="text" name="description" value="{{ old('description') }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="e.g., Customer payment deposit">
                @error('description') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Reference Number</label>
                <input type="text" name="reference_number" value="{{ old('reference_number') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="Cheque/Ref #">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="Optional notes...">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-600 transition">Record Transaction</button>
                <a href="{{ route('bank-accounts.show', $bankAccount) }}" class="px-4 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
