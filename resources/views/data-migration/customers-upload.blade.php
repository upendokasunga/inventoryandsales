<x-app-layout>
    <x-slot name="header">{{ __('Import Customers') }}</x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('data-migration.index') }}" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Import Customers</h2>
                <p class="text-sm text-slate-500">Upload an Excel file with your customer data</p>
            </div>
        </div>

        {{-- Template Download --}}
        <div class="bg-blue-50 rounded-xl border border-blue-200 p-4 mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                <div>
                    <p class="text-sm font-medium text-blue-800">Need a template?</p>
                    <p class="text-xs text-blue-600">Download a sample Excel file with the correct column headers</p>
                </div>
            </div>
            <a href="{{ route('data-migration.sample', 'customers') }}" class="erp-btn-secondary text-sm">Download Sample</a>
        </div>

        {{-- Expected Columns --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
            <div class="px-6 py-4 border-b border-slate-200/60">
                <h3 class="text-sm font-semibold text-slate-800">Expected Columns</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                    <div class="bg-slate-50 rounded-lg p-2"><span class="font-medium text-slate-700">name</span> <span class="text-red-500">*</span><br><span class="text-slate-500">Customer name</span></div>
                    <div class="bg-slate-50 rounded-lg p-2"><span class="font-medium text-slate-700">phone</span> <span class="text-slate-400">*</span><br><span class="text-slate-500">Phone number</span></div>
                    <div class="bg-slate-50 rounded-lg p-2"><span class="font-medium text-slate-700">email</span><br><span class="text-slate-500">Email address</span></div>
                    <div class="bg-slate-50 rounded-lg p-2"><span class="font-medium text-slate-700">address</span><br><span class="text-slate-500">Street address</span></div>
                    <div class="bg-slate-50 rounded-lg p-2"><span class="font-medium text-slate-700">city</span><br><span class="text-slate-500">City name</span></div>
                    <div class="bg-slate-50 rounded-lg p-2"><span class="font-medium text-slate-700">region</span><br><span class="text-slate-500">Region/area</span></div>
                    <div class="bg-slate-50 rounded-lg p-2"><span class="font-medium text-slate-700">customer_group</span><br><span class="text-slate-500">Group name</span></div>
                    <div class="bg-slate-50 rounded-lg p-2"><span class="font-medium text-slate-700">credit_limit</span><br><span class="text-slate-500">Credit limit (TZS)</span></div>
                    <div class="bg-slate-50 rounded-lg p-2"><span class="font-medium text-slate-700">payment_terms</span><br><span class="text-slate-500">Cash, Net 30, etc.</span></div>
                </div>
            </div>
        </div>

        {{-- Upload Form --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60">
            <div class="px-6 py-4 border-b border-slate-200/60">
                <h3 class="text-lg font-semibold text-slate-800">Upload File</h3>
            </div>
            <form action="{{ route('data-migration.customers.preview') }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                <div class="mb-4">
                    <label for="file" class="block text-sm font-medium text-slate-700 mb-2">Select Excel or CSV File</label>
                    <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv" required
                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    @error('file') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end">
                    <a href="{{ route('data-migration.index') }}" class="mr-3 erp-btn-secondary">Cancel</a>
                    <button type="submit" class="erp-btn-primary">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        Upload & Preview
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
