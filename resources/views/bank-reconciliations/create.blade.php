<x-app-layout>
    <x-slot name="header">New Bank Reconciliation</x-slot>

    <x-breadcrumbs :items="[['label' => 'Reconciliations', 'url' => route('bank-reconciliations.index')], ['label' => 'New']]" />

    <div class="max-w-2xl mx-auto">
        <form method="POST" action="{{ route('bank-reconciliations.store') }}" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Bank Account *</label>
                <x-create-inline selectId="bank_account_id" :createUrl="route('bank-accounts.store')" title="Create New Bank Account"
                    :fields="[['name'=>'name','label'=>'Account Name','required'=>true],['name'=>'account_number','label'=>'Account Number'],['name'=>'bank_name','label'=>'Bank Name','required'=>true],['name'=>'branch','label'=>'Branch']]">
                    <select name="bank_account_id" id="bank_account_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                        <option value="">Select Account</option>
                        @foreach($bankAccounts as $acc)
                            <option value="{{ $acc->id }}" @selected(old('bank_account_id') == $acc->id)>{{ $acc->name }} ({{ $acc->bank_name }} - {{ $acc->account_number }})</option>
                        @endforeach
                        <option value="" disabled>---</option>
                        <option value="__create__">&plus; Not in the list? Create new</option>
                    </select>
                </x-create-inline>
                @error('bank_account_id') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Start Date *</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                    @error('start_date') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">End Date *</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                    @error('end_date') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Closing Balance per Statement *</label>
                <input type="number" step="0.01" name="closing_balance" value="{{ old('closing_balance') }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="0.00">
                @error('closing_balance') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Statement Reference</label>
                <input type="text" name="statement_reference" value="{{ old('statement_reference') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="Statement # or month">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="Optional notes...">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-600 transition">Start Reconciliation</button>
                <a href="{{ route('bank-reconciliations.index') }}" class="px-4 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
