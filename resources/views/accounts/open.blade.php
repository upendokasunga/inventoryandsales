<x-app-layout>
    <x-slot name="header">Open Account</x-slot>

    <x-breadcrumbs :items="[['label' => 'Chart of Accounts', 'url' => route('accounts.index')], ['label' => 'Open Account']]" />

    <div class="max-w-2xl mx-auto">
        <form action="{{ route('accounts.open-store') }}" method="POST" x-data="{ type: '' }">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60">
                    <h3 class="text-lg font-semibold text-slate-800">Account Type & Details</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Account Type <span class="text-red-500">*</span></label>
                        <select name="account_type_key" x-model="type" required class="mt-1 block w-full erp-input">
                            <option value="">Select Type</option>
                            @foreach($accountTypes as $at)
                                <option value="{{ $at->key }}" {{ old('account_type_key') == $at->key ? 'selected' : '' }}>{{ $at->label }} ({{ ucfirst(str_replace('_', ' ', $at->asset_class ?? '')) }})</option>
                            @endforeach
                        </select>
                        @error('account_type_key') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Account Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full erp-input" placeholder="e.g. CRDB Main Account">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div x-show="type === 'asset_bank' || type === 'asset_cash'">
                        <label class="block text-sm font-medium text-slate-700">Account Number</label>
                        <input type="text" name="account_number" value="{{ old('account_number') }}" class="mt-1 block w-full erp-input font-mono" placeholder="Auto-generated if empty">
                        @error('account_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Opening Balance (TSh)</label>
                        <input type="number" step="1" min="0" name="opening_balance" value="{{ old('opening_balance', 0) }}" class="mt-1 block w-full erp-input" x-data="priceInput()" data-decimals="0">
                    </div>
                    <div x-show="type === 'asset_bank'" class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Bank Name <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="mt-1 block w-full erp-input" placeholder="CRDB Bank">
                        @error('bank_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div x-show="type === 'asset_bank'">
                        <label class="block text-sm font-medium text-slate-700">Branch <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_branch" value="{{ old('bank_branch') }}" class="mt-1 block w-full erp-input" placeholder="Main Branch">
                        @error('bank_branch') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div x-show="type === 'asset_bank'">
                        <label class="block text-sm font-medium text-slate-700">SWIFT Code</label>
                        <input type="text" name="bank_swift_code" value="{{ old('bank_swift_code') }}" class="mt-1 block w-full erp-input" placeholder="CORUTZTZ">
                        @error('bank_swift_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" rows="2" class="mt-1 block w-full erp-input">{{ old('description') }}</textarea>
                    </div>
                    <div x-show="type === 'asset_bank' || type === 'asset_cash'" class="md:col-span-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="allow_overdraft" value="1" {{ old('allow_overdraft') ? 'checked' : '' }} class="rounded border-slate-300 text-primary focus:ring-primary-500">
                            <span class="ml-2 text-sm font-medium text-slate-700">Allow Overdraft</span>
                        </label>
                        <p class="text-xs text-slate-400 mt-1">Enable if this account can go negative</p>
                    </div>
                    <div x-show="type === 'asset_bank' || type === 'asset_cash'">
                        <label class="block text-sm font-medium text-slate-700">Overdraft Limit (TSh)</label>
                        <input type="number" step="1" min="0" name="overdraft_limit" value="{{ old('overdraft_limit', 0) }}" class="mt-1 block w-full erp-input" x-data="priceInput()" data-decimals="0">
                        <p class="text-xs text-slate-400 mt-1">Maximum negative balance allowed</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('accounts.index') }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Open Account</button>
            </div>
        </form>
    </div>
</x-app-layout>
