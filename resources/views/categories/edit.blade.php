<x-app-layout>
    <x-slot name="header">
        {{ __('Edit Category') }}: {{ $category->name }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <form action="{{ route('categories.update', $category) }}" method="POST">
            @csrf @method('PATCH')

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="p-6">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required
                            class="mt-1 block w-full erp-input">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="parent_id" class="block text-sm font-medium text-slate-700">Parent Category</label>
                        <select name="parent_id" id="parent_id"
                            class="mt-1 block w-full erp-input">
                            <option value="">None (Top Level)</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="mt-1 block w-full erp-input">{{ old('description', $category->description) }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label for="sort_order" class="block text-sm font-medium text-slate-700">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $category->sort_order) }}"
                            class="mt-1 block w-32 erp-input">
                    </div>
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                                class="erp-input">
                            <span class="ml-2 text-sm text-slate-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('categories.index') }}" class="mr-4 erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Update Category</button>
            </div>
        </form>
    </div>
</x-app-layout>
