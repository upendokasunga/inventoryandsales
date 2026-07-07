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
                        <label for="opening_balance" class="block text-sm font-medium text-slate-700">Opening Balance</label>
                        <input type="number" step="0.01" min="0" name="opening_balance" id="opening_balance" value="{{ old('opening_balance', 0) }}" class="mt-1 block w-full erp-input">
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

            <div class="flex justify-end mb-8">
                <a href="{{ route('accounts.index') }}" class="mr-4 inline-flex items-center px-4 py-2 erp-btn-secondary">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 erp-btn-primary">Create Account</button>
            </div>
        </form>
    </div>
</x-app-layout>
