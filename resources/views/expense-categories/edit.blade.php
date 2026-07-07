<x-app-layout>
    <x-slot name="header">{{ __('Edit Expense Category') }}: {{ $expenseCategory->name }}</x-slot>
    <div class="max-w-2xl mx-auto">
        <form action="{{ route('expense-categories.update', $expenseCategory) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60"><h3 class="text-lg font-semibold text-slate-800">Category Details</h3></div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Name</label>
                        <input type="text" name="name" value="{{ old('name', $expenseCategory->name) }}" required class="mt-1 block w-full erp-input">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" rows="3" class="mt-1 block w-full erp-input">{{ old('description', $expenseCategory->description) }}</textarea>
                        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $expenseCategory->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-primary focus:ring-primary">
                            <span class="text-sm font-medium text-slate-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="flex justify-end mb-8">
                <a href="{{ route('expense-categories.index') }}" class="mr-4 inline-flex items-center px-4 py-2 erp-btn-secondary">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 erp-btn-primary">Update Category</button>
            </div>
        </form>
    </div>
</x-app-layout>
