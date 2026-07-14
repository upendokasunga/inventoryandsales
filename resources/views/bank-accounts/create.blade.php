<x-app-layout>
    <x-slot name="header">{{ __('Create Bank Account') }}</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('bank-accounts.index') }}" class="erp-btn-secondary">Back to List</a>
        </div>

        <form method="POST" action="{{ route('bank-accounts.store') }}">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60">
                    <h3 class="text-lg font-semibold text-slate-800">Account Information</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Account Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full erp-input" placeholder="Main Operating Account">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Account Number <span class="text-red-500">*</span></label>
                        <input type="text" name="account_number" value="{{ old('account_number') }}" required class="mt-1 block w-full erp-input font-mono" placeholder="0150123456789">
                        @error('account_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Bank Name <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}" required class="mt-1 block w-full erp-input" placeholder="CRDB Bank">
                        @error('bank_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Branch</label>
                        <input type="text" name="branch" value="{{ old('branch') }}" class="mt-1 block w-full erp-input" placeholder="Main Branch">
                        @error('branch') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Account Type <span class="text-red-500">*</span></label>
                        <select name="account_type" required class="mt-1 block w-full erp-input">
                            <option value="checking" @selected(old('account_type') === 'checking')>Checking</option>
                            <option value="savings" @selected(old('account_type') === 'savings')>Savings</option>
                            <option value="fixed_deposit" @selected(old('account_type') === 'fixed_deposit')>Fixed Deposit</option>
                        </select>
                        @error('account_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Opening Balance (TSh)</label>
                        <input type="number" step="0.01" min="0" name="opening_balance" value="{{ old('opening_balance', 0) }}" class="mt-1 block w-full erp-input" placeholder="0">
                        @error('opening_balance') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Chart of Account (COA Link)</label>
                        <select name="account_id" class="mt-1 block w-full erp-input">
                            <option value="">-- None --</option>
                            @foreach($coaAccounts as $acc)
                                <option value="{{ $acc->id }}" @selected(old('account_id') == $acc->id)>{{ $acc->code }} - {{ $acc->name }}</option>
                            @endforeach
                        </select>
                        @error('account_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-slate-300 text-primary focus:ring-primary-500">
                            <span class="text-sm text-slate-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('bank-accounts.index') }}" class="mr-4 erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Create Account</button>
            </div>
        </form>
    </div>
</x-app-layout>
