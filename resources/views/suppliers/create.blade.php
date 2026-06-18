<x-app-layout>
    <x-slot name="header">
        {{ __('Create Supplier') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <form action="{{ route('suppliers.store') }}" method="POST">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700">Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="mt-1 block w-full erp-input">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="contact_person" class="block text-sm font-medium text-slate-700">Contact Person</label>
                            <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person') }}"
                                class="mt-1 block w-full erp-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                class="mt-1 block w-full erp-input">
                            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="phone1" class="block text-sm font-medium text-slate-700">Phone 1</label>
                            <input type="text" name="phone1" id="phone1" value="{{ old('phone1') }}"
                                class="mt-1 block w-full erp-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="phone2" class="block text-sm font-medium text-slate-700">Phone 2</label>
                            <input type="text" name="phone2" id="phone2" value="{{ old('phone2') }}"
                                class="mt-1 block w-full erp-input">
                        </div>
                        <div>
                            <label for="city" class="block text-sm font-medium text-slate-700">City</label>
                            <input type="text" name="city" id="city" value="{{ old('city') }}"
                                class="mt-1 block w-full erp-input">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="address" class="block text-sm font-medium text-slate-700">Address</label>
                        <textarea name="address" id="address" rows="2"
                            class="mt-1 block w-full erp-input">{{ old('address') }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="tax_id" class="block text-sm font-medium text-slate-700">Tax ID</label>
                            <input type="text" name="tax_id" id="tax_id" value="{{ old('tax_id') }}"
                                class="mt-1 block w-full erp-input">
                        </div>
                        <div>
                            <label for="payment_terms" class="block text-sm font-medium text-slate-700">Payment Terms</label>
                            <select name="payment_terms" id="payment_terms"
                                class="mt-1 block w-full erp-input">
                                <option value="">Select...</option>
                                <option value="Net 30" {{ old('payment_terms') == 'Net 30' ? 'selected' : '' }}>Net 30</option>
                                <option value="Net 60" {{ old('payment_terms') == 'Net 60' ? 'selected' : '' }}>Net 60</option>
                                <option value="Net 90" {{ old('payment_terms') == 'Net 90' ? 'selected' : '' }}>Net 90</option>
                                <option value="COD" {{ old('payment_terms') == 'COD' ? 'selected' : '' }}>Cash on Delivery</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="notes" class="block text-sm font-medium text-slate-700">Notes</label>
                        <textarea name="notes" id="notes" rows="3"
                            class="mt-1 block w-full erp-input">{{ old('notes') }}</textarea>
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
                <a href="{{ route('suppliers.index') }}" class="mr-4 erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Create Supplier</button>
            </div>
        </form>
    </div>
</x-app-layout>
