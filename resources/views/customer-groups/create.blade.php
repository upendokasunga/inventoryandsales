<x-app-layout>
    <x-slot name="header">
        {{ __('Create Customer Group') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <form action="{{ route('customer-groups.store') }}" method="POST">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="p-6">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="mt-1 block w-full erp-input">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" id="description" rows="2"
                            class="mt-1 block w-full erp-input">{{ old('description') }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="default_credit_limit" class="block text-sm font-medium text-slate-700">Default Credit Limit</label>
                            <input type="number" step="0.01" min="0" name="default_credit_limit" id="default_credit_limit" value="{{ old('default_credit_limit', 0) }}"
                                class="mt-1 block w-full erp-input">
                        </div>
                        <div>
                            <label for="default_payment_terms" class="block text-sm font-medium text-slate-700">Default Payment Terms</label>
                            <select name="default_payment_terms" id="default_payment_terms"
                                class="mt-1 block w-full erp-input">
                                <option value="">Select...</option>
                                <option value="Net 30" {{ old('default_payment_terms') == 'Net 30' ? 'selected' : '' }}>Net 30</option>
                                <option value="Net 60" {{ old('default_payment_terms') == 'Net 60' ? 'selected' : '' }}>Net 60</option>
                                <option value="Net 90" {{ old('default_payment_terms') == 'Net 90' ? 'selected' : '' }}>Net 90</option>
                                <option value="COD" {{ old('default_payment_terms') == 'COD' ? 'selected' : '' }}>Cash on Delivery</option>
                            </select>
                        </div>
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
                <a href="{{ route('customer-groups.index') }}" class="mr-4 erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Create Customer Group</button>
            </div>
        </form>
    </div>
</x-app-layout>
