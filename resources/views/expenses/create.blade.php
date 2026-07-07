<x-app-layout>
    <x-slot name="header">{{ __('Create Expense') }}</x-slot>
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('expenses.store') }}" method="POST">
            @csrf
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60"><h3 class="text-lg font-semibold text-slate-800">Expense Details</h3></div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Category</label>
                        <select name="expense_category_id" class="mt-1 block w-full erp-input">
                            <option value="">Select Category</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('expense_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('expense_category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Amount</label>
                        <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" required class="mt-1 block w-full erp-input">
                        @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Expense Date</label>
                        <input type="date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required class="mt-1 block w-full erp-input">
                        @error('expense_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Payment Method</label>
                        <select name="payment_method" class="mt-1 block w-full erp-input">
                            <option value="">Select</option>
                            @foreach (['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'mobile_money' => 'Mobile Money', 'cheque' => 'Cheque'] as $val => $label)
                                <option value="{{ $val }}" {{ old('payment_method') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('payment_method') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Paid To</label>
                        <select name="paid_to" class="mt-1 block w-full erp-input">
                            <option value="">Select</option>
                            @foreach (\App\Models\User::orderBy('name')->get() as $u)
                                <option value="{{ $u->id }}" {{ old('paid_to') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                        @error('paid_to') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Account</label>
                        <select name="account_id" class="mt-1 block w-full erp-input">
                            <option value="">Select Account</option>
                            @foreach ($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->code }} - {{ $acc->name }}</option>
                            @endforeach
                        </select>
                        @error('account_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" rows="3" class="mt-1 block w-full erp-input">{{ old('description') }}</textarea>
                        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <div class="flex justify-end mb-8">
                <a href="{{ route('expenses.index') }}" class="mr-4 inline-flex items-center px-4 py-2 erp-btn-secondary">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 erp-btn-primary">Create Expense</button>
            </div>
        </form>
    </div>
</x-app-layout>
