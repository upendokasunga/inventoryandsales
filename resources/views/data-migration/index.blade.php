<x-app-layout>
    <x-slot name="header">{{ __('Data Migration') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <p class="text-sm text-slate-600 mb-6">Bulk upload your data using Excel files. Download sample templates, fill them in, then upload for preview and import.</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Products --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l3-3m-3 3l-3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">Products</h3>
                        <p class="text-xs text-slate-500">Bulk import products with prices</p>
                    </div>
                </div>
                <ul class="text-sm text-slate-600 space-y-1.5 mb-5">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Selling price update on existing products
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Auto-generate SKU and product codes
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Match by SKU or barcode
                    </li>
                </ul>
                <div class="flex items-center gap-2">
                    <a href="{{ route('data-migration.products.upload') }}" class="flex-1 text-center erp-btn-primary">Upload Products</a>
                    <a href="{{ route('data-migration.sample', 'products') }}" class="erp-btn-secondary text-xs px-3 py-2" title="Download sample template">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    </a>
                </div>
            </div>

            {{-- Customers --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">Customers</h3>
                        <p class="text-xs text-slate-500">Bulk import customer records</p>
                    </div>
                </div>
                <ul class="text-sm text-slate-600 space-y-1.5 mb-5">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Auto-dedup by phone or email
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Assign to customer groups
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Set credit limits and payment terms
                    </li>
                </ul>
                <div class="flex items-center gap-2">
                    <a href="{{ route('data-migration.customers.upload') }}" class="flex-1 text-center erp-btn-primary">Upload Customers</a>
                    <a href="{{ route('data-migration.sample', 'customers') }}" class="erp-btn-secondary text-xs px-3 py-2" title="Download sample template">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    </a>
                </div>
            </div>

            {{-- Sales --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">Sales</h3>
                        <p class="text-xs text-slate-500">Bulk import past sales records</p>
                    </div>
                </div>
                <ul class="text-sm text-slate-600 space-y-1.5 mb-5">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Creates invoices with line items
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Cash and credit payment types
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Validate customer and product existence
                    </li>
                </ul>
                <div class="flex items-center gap-2">
                    <a href="{{ route('data-migration.sales.upload') }}" class="flex-1 text-center erp-btn-primary">Upload Sales</a>
                    <a href="{{ route('data-migration.sample', 'sales') }}" class="erp-btn-secondary text-xs px-3 py-2" title="Download sample template">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- Format Guide --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mt-8 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">File Format Guide</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm text-slate-600">
                <div>
                    <h4 class="font-medium text-slate-700 mb-2">Supported Formats</h4>
                    <ul class="space-y-1">
                        <li>.xlsx (Excel 2007+)</li>
                        <li>.xls (Excel 97-2003)</li>
                        <li>.csv (Comma-separated)</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium text-slate-700 mb-2">Column Headers</h4>
                    <p>Use the exact header names from the sample files. Headers are case-insensitive and spaces are converted to underscores.</p>
                </div>
                <div>
                    <h4 class="font-medium text-slate-700 mb-2">Import Behavior</h4>
                    <ul class="space-y-1">
                        <li>Existing items are updated (matched by SKU/phone)</li>
                        <li>New items are created automatically</li>
                        <li>Errors are shown before import</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
