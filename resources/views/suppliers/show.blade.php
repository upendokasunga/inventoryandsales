<x-app-layout>
    <x-slot name="header">
        {{ __('Supplier') }}: {{ $supplier->name }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-4 flex items-center justify-between">
            <div class="flex gap-2">
                <a href="{{ route('suppliers.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-blue-200 rounded-lg font-semibold text-xs text-slate-700 hover:bg-blue-50 transition">
                    Back to List
                </a>
                <a href="{{ route('suppliers.edit', $supplier) }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-sky-500 hover:from-blue-500 hover:to-sky-400 border border-transparent rounded-lg font-semibold text-xs text-white shadow-lg shadow-blue-500/20 transition">
                    Edit Supplier
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg shadow-blue-500/5 border border-blue-100 overflow-hidden mb-6">
            <div class="border-b border-blue-100">
                <nav class="flex px-6" aria-label="Tabs">
                    <button class="px-4 py-3 text-sm font-medium text-blue-600 border-b-2 border-blue-600">Overview</button>
                    <button class="px-4 py-3 text-sm font-medium text-slate-500 hover:text-slate-700" disabled>Products</button>
                    <button class="px-4 py-3 text-sm font-medium text-slate-500 hover:text-slate-700" disabled>Purchases</button>
                    <button class="px-4 py-3 text-sm font-medium text-slate-500 hover:text-slate-700" disabled>Performance</button>
                    <button class="px-4 py-3 text-sm font-medium text-slate-500 hover:text-slate-700" disabled>Audit Logs</button>
                </nav>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-slate-500 mb-4">General Information</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Name</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $supplier->name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Contact Person</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $supplier->contact_person ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Email</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $supplier->email ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Phone 1</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $supplier->phone1 ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Phone 2</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $supplier->phone2 ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Status</dt>
                                <dd>
                                    @if ($supplier->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-700">Active</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-700">Inactive</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-slate-500 mb-4">Location & Business</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Address</dt>
                                <dd class="text-sm font-medium text-slate-800 text-right">{{ $supplier->address ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">City</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $supplier->city ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Tax ID</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $supplier->tax_id ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Payment Terms</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $supplier->payment_terms ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                @if ($supplier->notes)
                    <div class="mt-6 pt-6 border-t border-blue-100">
                        <h3 class="text-sm font-medium text-slate-500 mb-2">Notes</h3>
                        <p class="text-sm text-slate-700">{{ $supplier->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
