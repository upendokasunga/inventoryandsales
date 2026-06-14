<x-app-layout>
    <x-slot name="header">
        {{ __('Edit Unit') }}: {{ $unit->name }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <form action="{{ route('units.update', $unit) }}" method="POST">
            @csrf @method('PATCH')

            <div class="bg-white rounded-xl shadow-lg shadow-blue-500/5 border border-blue-100 mb-6">
                <div class="p-6">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $unit->name) }}" required
                            class="mt-1 block w-full rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="short_code" class="block text-sm font-medium text-slate-700">Short Code</label>
                        <input type="text" name="short_code" id="short_code" value="{{ old('short_code', $unit->short_code) }}" required
                            class="mt-1 block w-40 rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('short_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_base" value="1" {{ old('is_base', $unit->is_base) ? 'checked' : '' }}
                                class="rounded border-blue-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="ml-2 text-sm text-slate-700">Base Unit</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('units.index') }}" class="mr-4 inline-flex items-center px-4 py-2 bg-white border border-blue-200 rounded-lg font-semibold text-xs text-slate-700 hover:bg-blue-50 transition">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-sky-500 hover:from-blue-500 hover:to-sky-400 border border-transparent rounded-lg font-semibold text-xs text-white shadow-lg shadow-blue-500/20 transition">Update Unit</button>
            </div>
        </form>
    </div>
</x-app-layout>
