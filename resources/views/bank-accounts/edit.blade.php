<x-app-layout>
    <x-slot name="header">{{ __('Edit Bank Account') }}</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('bank-accounts.show', $bankAccount) }}" class="erp-btn-secondary">Back to Account</a>
        </div>

        <form method="POST" action="{{ route('bank-accounts.update', $bankAccount) }}">
            @csrf
            @method('PATCH')

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60">
                    <h3 class="text-lg font-semibold text-slate-800">Account Information</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Account Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $bankAccount->name) }}" required class="mt-1 block w-full erp-input">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Account Number <span class="text-red-500">*</span></label>
                        <input type="text" name="account_number" value="{{ old('account_number', $bankAccount->account_number) }}" required class="mt-1 block w-full erp-input font-mono">
                        @error('account_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div x-data="{ search: '{{ $bankAccount->bank?->name ? $bankAccount->bank->name . ' (' . ($bankAccount->bank->branch ?? '') . ')' : '' }}', open: false, selected: '{{ old('bank_id', $bankAccount->bank_id) }}' }" @click.outside="open = false" class="relative">
                        <label class="block text-sm font-medium text-slate-700">Bank <span class="text-red-500">*</span></label>
                        <input type="hidden" name="bank_id" :value="selected">
                        <input type="text" x-model="search" @focus="open = true" @input="open = true" placeholder="Search bank..." class="mt-1 block w-full erp-input" autocomplete="off">
                        <div x-show="open && search.length >= 0" class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-60 overflow-auto">
                            @foreach($banks as $bank)
                                <div class="px-3 py-2 cursor-pointer hover:bg-slate-50 text-sm"
                                     x-show="search === '' || '{{ strtolower($bank->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($bank->branch ?? '') }}'.includes(search.toLowerCase())"
                                     @click="selected = '{{ $bank->id }}'; search = '{{ $bank->name }} ({{ $bank->branch ?? '' }})'; open = false"
                                     :class="{ 'bg-primary-50': selected === '{{ $bank->id }}' }">
                                    <span class="font-medium">{{ $bank->name }}</span>
                                    <span class="text-slate-400 text-xs ml-1">{{ $bank->branch ?? '' }}</span>
                                    <span class="text-slate-300 text-xs ml-1">{{ $bank->swift_code ?? '' }}</span>
                                </div>
                            @endforeach
                        </div>
                        @error('bank_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Branch</label>
                        <input type="text" name="branch" value="{{ old('branch', $bankAccount->branch) }}" class="mt-1 block w-full erp-input">
                        @error('branch') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Account Type <span class="text-red-500">*</span></label>
                        <select name="account_type_id" required class="mt-1 block w-full erp-input">
                            <option value="">Select Account Type</option>
                            @foreach($accountTypes as $type)
                                <option value="{{ $type->id }}" @selected(old('account_type_id', $bankAccount->account_type_id) == $type->id)>{{ $type->label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-400 mt-1">Account types from Chart of Accounts</p>
                        @error('account_type_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Opening Balance (TSh)</label>
                        <input type="number" step="1" min="0" name="opening_balance" value="{{ old('opening_balance', $bankAccount->opening_balance) }}" class="mt-1 block w-full erp-input" x-data="priceInput()" data-decimals="0">
                        @error('opening_balance') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $bankAccount->is_active)) class="rounded border-slate-300 text-primary focus:ring-primary-500">
                            <span class="text-sm text-slate-700">Active</span>
                        </label>
                    </div>
                    @if($bankAccount->coaAccount)
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="allow_overdraft" value="1" @checked(old('allow_overdraft', $bankAccount->coaAccount->allow_overdraft)) class="rounded border-slate-300 text-primary focus:ring-primary-500">
                            <span class="text-sm font-medium text-slate-700">Allow Overdraft</span>
                        </label>
                        <p class="text-xs text-slate-400 mt-1">Enable if this account can go negative</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Overdraft Limit (TSh)</label>
                        <input type="number" step="1" min="0" name="overdraft_limit" value="{{ old('overdraft_limit', $bankAccount->coaAccount->overdraft_limit) }}" class="mt-1 block w-full erp-input" x-data="priceInput()" data-decimals="0">
                        <p class="text-xs text-slate-400 mt-1">Maximum negative balance allowed</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('bank-accounts.show', $bankAccount) }}" class="mr-4 erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Update Account</button>
            </div>
        </form>
    </div>
</x-app-layout>
