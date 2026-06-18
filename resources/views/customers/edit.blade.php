<x-app-layout>
    <x-slot name="header">{{ __('Edit Customer') }}: {{ $customer->name }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Customers', 'url' => route('customers.index')], ['label' => $customer->name, 'url' => route('customers.show', $customer)], ['label' => 'Edit']]" />

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('customers.update', $customer) }}" method="POST">
            @csrf @method('PATCH')

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-lg font-semibold text-slate-800">Business Information</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700">Business Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $customer->name) }}" required
                                class="mt-1 block w-full erp-input">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="customer_group_id" class="block text-sm font-medium text-slate-700">Customer Group</label>
                            <select name="customer_group_id" id="customer_group_id" class="mt-1 block w-full erp-input">
                                <option value="">Select Group</option>
                                @foreach ($customerGroups as $id => $name)
                                    <option value="{{ $id }}" {{ old('customer_group_id', $customer->customer_group_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $customer->email) }}"
                                class="mt-1 block w-full erp-input">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-700">Phone</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $customer->phone) }}"
                                class="mt-1 block w-full erp-input">
                        </div>
                    </div>
                    <div>
                        <label for="address" class="block text-sm font-medium text-slate-700">Address</label>
                        <textarea name="address" id="address" rows="2" class="mt-1 block w-full erp-input">{{ old('address', $customer->address) }}</textarea>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label for="city" class="block text-sm font-medium text-slate-700">City</label>
                            <input type="text" name="city" id="city" value="{{ old('city', $customer->city) }}"
                                class="mt-1 block w-full erp-input">
                        </div>
                        <div>
                            <label for="region" class="block text-sm font-medium text-slate-700">Region</label>
                            <input type="text" name="region" id="region" value="{{ old('region', $customer->region) }}"
                                class="mt-1 block w-full erp-input">
                        </div>
                        <div>
                            <label for="country" class="block text-sm font-medium text-slate-700">Country</label>
                            <input type="text" name="country" id="country" value="{{ old('country', $customer->country) }}"
                                class="mt-1 block w-full erp-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="tax_id" class="block text-sm font-medium text-slate-700">Tax ID</label>
                            <input type="text" name="tax_id" id="tax_id" value="{{ old('tax_id', $customer->tax_id) }}"
                                class="mt-1 block w-full erp-input">
                        </div>
                        <div>
                            <label for="registration_number" class="block text-sm font-medium text-slate-700">Registration No.</label>
                            <input type="text" name="registration_number" id="registration_number" value="{{ old('registration_number', $customer->registration_number) }}"
                                class="mt-1 block w-full erp-input">
                        </div>
                    </div>
                    <div>
                        <label for="website" class="block text-sm font-medium text-slate-700">Website</label>
                        <input type="url" name="website" id="website" value="{{ old('website', $customer->website) }}"
                            class="mt-1 block w-full erp-input">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-lg font-semibold text-slate-800">Contact Person</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="contact_person" class="block text-sm font-medium text-slate-700">Contact Name</label>
                            <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person', $customer->contact_person) }}"
                                class="mt-1 block w-full erp-input">
                        </div>
                        <div>
                            <label for="contact_phone" class="block text-sm font-medium text-slate-700">Contact Phone</label>
                            <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $customer->contact_phone) }}"
                                class="mt-1 block w-full erp-input">
                        </div>
                    </div>
                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-slate-700">Contact Email</label>
                        <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $customer->contact_email) }}"
                            class="mt-1 block w-full erp-input">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-lg font-semibold text-slate-800">Credit Settings</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="credit_limit" class="block text-sm font-medium text-slate-700">Credit Limit (TZS)</label>
                            <input type="number" step="0.01" min="0" name="credit_limit" id="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}"
                                class="mt-1 block w-full erp-input">
                        </div>
                        <div>
                            <label for="payment_terms" class="block text-sm font-medium text-slate-700">Payment Terms</label>
                            <select name="payment_terms" id="payment_terms" class="mt-1 block w-full erp-input">
                                <option value="">Select...</option>
                                @foreach (\App\Models\Customer::PAYMENT_TERMS as $term)
                                    <option value="{{ $term }}" {{ old('payment_terms', $customer->payment_terms) === $term ? 'selected' : '' }}>{{ $term }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }} class="erp-input">
                            <span class="ml-2 text-sm text-slate-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('customers.show', $customer) }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Update Customer</button>
            </div>
        </form>
    </div>
</x-app-layout>
