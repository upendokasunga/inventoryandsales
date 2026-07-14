<x-app-layout>
    <x-slot name="header">{{ __('Record Transaction') }} — {{ $bankAccount->name }}</x-slot>

    <div class="max-w-lg mx-auto">
        <div class="mb-4">
            <a href="{{ route('bank-accounts.show', $bankAccount) }}" class="erp-btn-secondary">Back to Account</a>
        </div>

        <form method="POST" action="{{ route('bank-transactions.store', $bankAccount) }}">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60">
                    <h3 class="text-lg font-semibold text-slate-800">Transaction Details</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Type <span class="text-red-500">*</span></label>
                        <select name="type" required class="mt-1 block w-full erp-input">
                            <option value="deposit" @selected(old('type') === 'deposit')>Deposit</option>
                            <option value="withdrawal" @selected(old('type') === 'withdrawal')>Withdrawal</option>
                        </select>
                        @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Amount (TSh) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required class="mt-1 block w-full erp-input" placeholder="0">
                        @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="transaction_date" value="{{ old('transaction_date', now()->format('Y-m-d')) }}" required class="mt-1 block w-full erp-input">
                        @error('transaction_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Description <span class="text-red-500">*</span></label>
                        <input type="text" name="description" value="{{ old('description') }}" required class="mt-1 block w-full erp-input" placeholder="e.g., Customer payment deposit">
                        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Reference Number</label>
                        <input type="text" name="reference_number" value="{{ old('reference_number') }}" class="mt-1 block w-full erp-input" placeholder="Cheque / Ref #">
                        @error('reference_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Notes</label>
                        <textarea name="notes" rows="2" class="mt-1 block w-full erp-input" placeholder="Optional notes...">{{ old('notes') }}</textarea>
                        @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('bank-accounts.show', $bankAccount) }}" class="mr-4 erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Record Transaction</button>
            </div>
        </form>
    </div>
</x-app-layout>
