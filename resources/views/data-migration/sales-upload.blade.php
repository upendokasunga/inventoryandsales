<x-app-layout>
    <x-slot name="header">{{ __('Import Sales') }}</x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
            <div class="px-6 py-4 border-b border-slate-200/60">
                <h3 class="text-lg font-semibold text-slate-800">Upload Sales Data</h3>
            </div>
            <div class="p-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                        <div class="text-sm text-blue-800">
                            <p class="font-medium mb-1">How it works:</p>
                            <ul class="list-disc list-inside space-y-1 text-blue-700">
                                <li>Download the <a href="{{ route('data-migration.sample', 'sales') }}" class="underline font-medium">sample template</a></li>
                                <li>Fill in your sales data (customer, product/SKU, quantity, unit price, date, payment type)</li>
                                <li>Upload the file and preview before importing</li>
                                <li>Select a store/warehouse and default payment type</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <form action="{{ route('data-migration.sales.preview') }}" method="POST" enctype="multipart/form-data" id="upload-form">
                    @csrf
                    <div class="mb-6">
                        <label for="file" class="block text-sm font-medium text-slate-700 mb-2">Select Excel File</label>
                        <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv" required
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('file') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end">
                        <a href="{{ route('data-migration.index') }}" class="mr-3 inline-flex items-center px-4 py-2 erp-btn-secondary">Cancel</a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 erp-btn-primary">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                            Upload & Preview
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('file').addEventListener('change', function() {
            const name = this.files[0]?.name || 'No file selected';
            document.getElementById('file-name').textContent = name;
        });
    </script>
    @endpush
</x-app-layout>
