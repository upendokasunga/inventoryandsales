<x-app-layout>
    <x-slot name="header">{{ __('Edit Cost Centre') }}: {{ $costCenter->name }}</x-slot>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('cost-centers.update', $costCenter) }}" method="POST">
            @csrf @method('PATCH')

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60">
                    <h3 class="text-lg font-semibold text-slate-800">Cost Centre Details</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $costCenter->name) }}" required
                            class="mt-1 block w-full erp-input">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="code" class="block text-sm font-medium text-slate-700">Code</label>
                        <input type="text" name="code" id="code" value="{{ old('code', $costCenter->code) }}"
                            class="mt-1 block w-full erp-input">
                        @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="mt-1 block w-full erp-input">{{ old('description', $costCenter->description) }}</textarea>
                        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $costCenter->is_active) ? 'checked' : '' }}
                                class="rounded border-slate-300 text-primary focus:ring-primary">
                            <span class="ml-2 text-sm text-slate-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('cost-centers.index') }}" class="mr-4 erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Update Cost Centre</button>
            </div>
        </form>
    </div>
</x-app-layout>
