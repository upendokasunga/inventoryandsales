<x-app-layout>
    <x-slot name="header">
        {{ __('Create Category') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf

            <div class="erp-card mb-6">
                <div class="p-6">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="mt-1 block w-full erp-input">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="parent_id" class="block text-sm font-medium text-slate-700">Parent Category</label>
                        <select name="parent_id" id="parent_id"
                            class="mt-1 block w-full erp-input">
                            <option value="">None (Top Level)</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="mt-1 block w-full erp-input">{{ old('description') }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label for="sort_order" class="block text-sm font-medium text-slate-700">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}"
                            class="mt-1 block w-32 erp-input">
                    </div>
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" checked
                                class="erp-input">
                            <span class="ml-2 text-sm text-slate-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('categories.index') }}" class="mr-4 erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Create Category</button>
            </div>
        </form>
    </div>
</x-app-layout>
