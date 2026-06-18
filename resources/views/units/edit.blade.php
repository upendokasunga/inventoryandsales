<x-app-layout>
    <x-slot name="header">
        {{ __('Edit Unit') }}: {{ $unit->name }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <form action="{{ route('units.update', $unit) }}" method="POST">
            @csrf @method('PATCH')

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="p-6">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $unit->name) }}" required
                            class="mt-1 block w-full erp-input">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="short_code" class="block text-sm font-medium text-slate-700">Short Code</label>
                        <input type="text" name="short_code" id="short_code" value="{{ old('short_code', $unit->short_code) }}" required
                            class="mt-1 block w-40 erp-input">
                        @error('short_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_base" value="1" {{ old('is_base', $unit->is_base) ? 'checked' : '' }}
                                class="erp-input">
                            <span class="ml-2 text-sm text-slate-700">Base Unit</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('units.index') }}" class="mr-4 erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Update Unit</button>
            </div>
        </form>
    </div>
</x-app-layout>
