<x-app-layout>
    <x-slot name="header">
        {{ __('Create Menu') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <form action="{{ route('menus.store') }}" method="POST">
            @csrf
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="mt-1 block w-full erp-input">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="module" class="block text-sm font-medium text-slate-700">Module</label>
                        <select name="module" id="module" required
                            class="mt-1 block w-full erp-input">
                            @foreach ($modules as $module)
                                <option value="{{ $module }}" {{ old('module') == $module ? 'selected' : '' }}>{{ $module }}</option>
                            @endforeach
                            <option value="New">-- New Module --</option>
                        </select>
                        @error('module') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="route" class="block text-sm font-medium text-slate-700">Route Name</label>
                        <input type="text" name="route" id="route" value="{{ old('route') }}"
                            class="mt-1 block w-full erp-input">
                    </div>
                    <div>
                        <label for="icon" class="block text-sm font-medium text-slate-700">Icon</label>
                        <input type="text" name="icon" id="icon" value="{{ old('icon', 'circle') }}"
                            class="mt-1 block w-full erp-input">
                    </div>
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-slate-700">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}"
                            class="mt-1 block w-full erp-input">
                    </div>
                    <div class="flex items-center">
                        <label class="inline-flex items-center mt-6">
                            <input type="checkbox" name="is_active" value="1" checked
                                class="erp-input">
                            <span class="ml-2 text-sm text-slate-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('menus.index') }}" class="mr-4 erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Create Menu</button>
            </div>
        </form>
    </div>
</x-app-layout>
