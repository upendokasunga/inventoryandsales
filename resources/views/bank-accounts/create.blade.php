<x-app-layout>
    <x-slot name="header">Create Bank Account</x-slot>

    <x-breadcrumbs :items="[['label' => 'Bank Accounts', 'url' => route('bank-accounts.index')], ['label' => 'New Account']]" />

    <div class="max-w-2xl mx-auto">
        <form method="POST" action="{{ route('bank-accounts.store') }}" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Account Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="Main Operating Account">
                    @error('name') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Account Number *</label>
                    <input type="text" name="account_number" value="{{ old('account_number') }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="0150123456789">
                    @error('account_number') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Bank Name *</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="CRDB Bank">
                    @error('bank_name') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Branch</label>
                    <input type="text" name="branch" value="{{ old('branch') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="Main Branch">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Account Type *</label>
                    <select name="account_type" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                        <option value="checking" @selected(old('account_type') === 'checking')>Checking</option>
                        <option value="savings" @selected(old('account_type') === 'savings')>Savings</option>
                        <option value="fixed_deposit" @selected(old('account_type') === 'fixed_deposit')>Fixed Deposit</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Opening Balance</label>
                    <input type="number" step="0.01" min="0" name="opening_balance" value="{{ old('opening_balance', 0) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Chart of Account (COA Link)</label>
                <select name="account_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                    <option value="">-- None --</option>
                    @foreach($coaAccounts as $acc)
                        <option value="{{ $acc->id }}" @selected(old('account_id') == $acc->id)>{{ $acc->code }} - {{ $acc->name }}</option>
                    @endforeach
                </select>
                @error('account_id') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-600 transition">Create Account</button>
                <a href="{{ route('bank-accounts.index') }}" class="px-4 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
