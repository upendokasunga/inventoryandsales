<x-app-layout>
    <x-slot name="header">Edit Bank: {{ $bank->name }}</x-slot>

    <div class="max-w-xl mx-auto">
        <form action="{{ route('banks.update', $bank) }}" method="POST">
            @csrf @method('PATCH')
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Bank Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $bank->name) }}" required class="mt-1 block w-full erp-input">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Branch <span class="text-red-500">*</span></label>
                        <input type="text" name="branch" value="{{ old('branch', $bank->branch) }}" required class="mt-1 block w-full erp-input">
                        @error('branch') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">SWIFT Code</label>
                        <input type="text" name="swift_code" value="{{ old('swift_code', $bank->swift_code) }}" class="mt-1 block w-full erp-input font-mono">
                        @error('swift_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Country</label>
                        <input type="text" name="country" value="{{ old('country', $bank->country) }}" class="mt-1 block w-full erp-input">
                        @error('country') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Currency</label>
                        <input type="text" name="currency_code" value="{{ old('currency_code', $bank->currency_code) }}" class="mt-1 block w-full erp-input font-mono" maxlength="3">
                        @error('currency_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-4">
                <a href="{{ route('banks.index') }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Update Bank</button>
            </div>
        </form>
    </div>
</x-app-layout>
