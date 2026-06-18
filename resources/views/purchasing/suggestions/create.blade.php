<x-app-layout>
    <x-slot name="header">
        {{ __('Create Purchase Suggestion') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <form action="{{ route('purchasing.suggestions.store') }}" method="POST">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-slate-700">Product *</label>
                            <select name="product_id" id="product_id" required
                                class="mt-1 block w-full erp-input">
                                <option value="">Select Product</option>
                                @foreach ($products as $id => $name)
                                    <option value="{{ $id }}" {{ old('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('product_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="suggested_quantity" class="block text-sm font-medium text-slate-700">Suggested Quantity *</label>
                            <input type="number" step="0.01" name="suggested_quantity" id="suggested_quantity"
                                value="{{ old('suggested_quantity') }}" required
                                class="mt-1 block w-full erp-input">
                            @error('suggested_quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium text-slate-700">Reason</label>
                        <textarea name="reason" id="reason" rows="2"
                            class="mt-1 block w-full erp-input">{{ old('reason') }}</textarea>
                        @error('reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="notes" class="block text-sm font-medium text-slate-700">Notes</label>
                        <textarea name="notes" id="notes" rows="2"
                            class="mt-1 block w-full erp-input">{{ old('notes') }}</textarea>
                        @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('purchasing.suggestions.index') }}" class="mr-4 erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Create Suggestion</button>
            </div>
        </form>
    </div>
</x-app-layout>
