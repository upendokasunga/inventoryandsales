<x-app-layout>
    <x-slot name="header">Edit Bank Account</x-slot>

    <x-breadcrumbs :items="[['label' => 'Bank Accounts', 'url' => route('bank-accounts.index')], ['label' => $bankAccount->name, 'url' => route('bank-accounts.show', $bankAccount)], ['label' => 'Edit']]" />

    <div class="max-w-2xl mx-auto">
        <form method="POST" action="{{ route('bank-accounts.update', $bankAccount) }}" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Account Name *</label>
                    <input type="text" name="name" value="{{ old('name', $bankAccount->name) }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                    @error('name') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Account Number *</label>
                    <input type="text" name="account_number" value="{{ old('account_number', $bankAccount->account_number) }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                    @error('account_number') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Bank Name *</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $bankAccount->bank_name) }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Branch</label>
                    <input type="text" name="branch" value="{{ old('branch', $bankAccount->branch) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Account Type *</label>
                    <select name="account_type" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                        @foreach(['checking', 'savings', 'fixed_deposit'] as $type)
                            <option value="{{ $type }}" @selected(old('account_type', $bankAccount->account_type) === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Chart of Account</label>
                    <select name="account_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                        <option value="">-- None --</option>
                        @foreach($coaAccounts as $acc)
                            <option value="{{ $acc->id }}" @selected(old('account_id', $bankAccount->account_id) == $acc->id)>{{ $acc->code }} - {{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $bankAccount->is_active)) class="rounded border-slate-300">
                    <span class="text-sm text-slate-700">Active</span>
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-600 transition">Update Account</button>
                <a href="{{ route('bank-accounts.show', $bankAccount) }}" class="px-4 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
