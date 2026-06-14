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
                        <label for="abbreviation" class="block text-sm font-medium text-slate-700">Abbreviation</label>
                        <input type="text" name="abbreviation" id="abbreviation" value="{{ old('abbreviation', $unit->abbreviation) }}" required
                            class="mt-1 block w-40 rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('abbreviation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
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
