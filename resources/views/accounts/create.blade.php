<x-app-layout>
    <x-slot name="header">{{ __('Create Account') }}</x-slot>

    <div class="max-w-3xl mx-auto">
        <form action="{{ route('accounts.store') }}" method="POST">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60">
                    <h3 class="text-lg font-semibold text-slate-800">Account Details</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="code" class="block text-sm font-medium text-slate-700">Code</label>
                        <input type="text" name="code" id="code" value="{{ old('code') }}" required class="mt-1 block w-full erp-input">
                        @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full erp-input">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="type" class="block text-sm font-medium text-slate-700">Type</label>
                        <select name="type" id="type" required class="mt-1 block w-full erp-input">
                            <option value="">Select Type</option>
                            <option value="asset" {{ old('type') == 'asset' ? 'selected' : '' }}>Asset</option>
                            <option value="liability" {{ old('type') == 'liability' ? 'selected' : '' }}>Liability</option>
                            <option value="equity" {{ old('type') == 'equity' ? 'selected' : '' }}>Equity</option>
                            <option value="income" {{ old('type') == 'income' ? 'selected' : '' }}>Income</option>
                            <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                        </select>
                        @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="category" class="block text-sm font-medium text-slate-700">Category</label>
                        <select name="category" id="category" required class="mt-1 block w-full erp-input">
                            <option value="">Select Category</option>
                            <option value="current" {{ old('category') == 'current' ? 'selected' : '' }}>Current</option>
                            <option value="non_current" {{ old('category') == 'non_current' ? 'selected' : '' }}>Non-Current</option>
                            <option value="fixed" {{ old('category') == 'fixed' ? 'selected' : '' }}>Fixed</option>
                            <option value="intangible" {{ old('category') == 'intangible' ? 'selected' : '' }}>Intangible</option>
                            <option value="owner" {{ old('category') == 'owner' ? 'selected' : '' }}>Owner</option>
                            <option value="retained" {{ old('category') == 'retained' ? 'selected' : '' }}>Retained</option>
                            <option value="operating" {{ old('category') == 'operating' ? 'selected' : '' }}>Operating</option>
                            <option value="non_operating" {{ old('category') == 'non_operating' ? 'selected' : '' }}>Non-Operating</option>
                            <option value="administrative" {{ old('category') == 'administrative' ? 'selected' : '' }}>Administrative</option>
                            <option value="selling" {{ old('category') == 'selling' ? 'selected' : '' }}>Selling</option>
                        </select>
                        @error('category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-slate-700">Parent Account</label>
                        <select name="parent_id" id="parent_id" class="mt-1 block w-full erp-input">
                            <option value="">None (Top Level)</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->code }} - {{ $parent->name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="opening_balance" class="block text-sm font-medium text-slate-700">Opening Balance (TSh)</label>
                        <input type="number" step="1" min="0" name="opening_balance" id="opening_balance" value="{{ old('opening_balance', 0) }}" class="mt-1 block w-full erp-input" x-data="priceInput()" data-decimals="0">
                        @error('opening_balance') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" id="description" rows="3" class="mt-1 block w-full erp-input">{{ old('description') }}</textarea>
                        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="erp-input">
                            <span class="ml-2 text-sm text-slate-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60">
                    <h3 class="text-lg font-semibold text-slate-800">IFRS Classification</h3>
                    <p class="text-xs text-slate-400 mt-1">Optional IFRS reporting fields</p>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="ifrs_category" class="block text-sm font-medium text-slate-700">IFRS Category</label>
                        <input type="text" name="ifrs_category" id="ifrs_category" value="{{ old('ifrs_category') }}" class="mt-1 block w-full erp-input" placeholder="e.g. PPE, cash, bank, trade_receivables">
                        @error('ifrs_category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="presentation_order" class="block text-sm font-medium text-slate-700">Presentation Order</label>
                        <input type="number" name="presentation_order" id="presentation_order" value="{{ old('presentation_order') }}" class="mt-1 block w-full erp-input">
                        @error('presentation_order') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60">
                    <h3 class="text-lg font-semibold text-slate-800">Bank Details</h3>
                    <p class="text-xs text-slate-400 mt-1">For cash/bank accounts only</p>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="bank_name" class="block text-sm font-medium text-slate-700">Bank Name</label>
                        <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name') }}" class="mt-1 block w-full erp-input" placeholder="e.g. CRDB, NMB">
                        @error('bank_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="bank_branch" class="block text-sm font-medium text-slate-700">Branch</label>
                        <input type="text" name="bank_branch" id="bank_branch" value="{{ old('bank_branch') }}" class="mt-1 block w-full erp-input">
                        @error('bank_branch') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="bank_swift_code" class="block text-sm font-medium text-slate-700">SWIFT Code</label>
                        <input type="text" name="bank_swift_code" id="bank_swift_code" value="{{ old('bank_swift_code') }}" class="mt-1 block w-full erp-input">
                        @error('bank_swift_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-3">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="allow_overdraft" value="1" {{ old('allow_overdraft') ? 'checked' : '' }} class="rounded border-slate-300 text-primary focus:ring-primary-500">
                            <span class="ml-2 text-sm font-medium text-slate-700">Allow Overdraft</span>
                        </label>
                        <p class="text-xs text-slate-400 mt-1">Enable if this account can go negative</p>
                    </div>
                    <div class="md:col-span-3" x-show="$el.parentElement.querySelector('[name=allow_overdraft]').checked">
                        <label for="overdraft_limit" class="block text-sm font-medium text-slate-700">Overdraft Limit (TSh)</label>
                        <input type="number" step="1" min="0" name="overdraft_limit" id="overdraft_limit" value="{{ old('overdraft_limit', 0) }}" class="mt-1 block w-full erp-input" x-data="priceInput()" data-decimals="0">
                        <p class="text-xs text-slate-400 mt-1">Maximum negative balance allowed</p>
                        @error('overdraft_limit') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end mb-8">
                <a href="{{ route('accounts.index') }}" class="mr-4 inline-flex items-center px-4 py-2 erp-btn-secondary">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 erp-btn-primary">Create Account</button>
            </div>
        </form>
    </div>
</x-app-layout>
