<x-app-layout>
    <x-slot name="header">
        {{ __('Supplier') }}: {{ $supplier->name }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-4 flex items-center justify-between">
            <div class="flex gap-2">
                <a href="{{ route('suppliers.index') }}" class="erp-btn-secondary">
                    Back to List
                </a>
                <a href="{{ route('suppliers.edit', $supplier) }}" class="erp-btn-primary">
                    Edit Supplier
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
            <div class="border-b border-slate-100">
                <nav class="flex px-6" aria-label="Tabs" role="tablist">
                    <button class="px-4 py-3 text-sm font-medium text-primary border-b-2 border-primary" role="tab" aria-selected="true">Overview</button>
                    <button class="px-4 py-3 text-sm font-medium text-slate-500 hover:text-slate-700" disabled role="tab" aria-selected="false" aria-disabled="true">Products</button>
                    <button class="px-4 py-3 text-sm font-medium text-slate-500 hover:text-slate-700" disabled role="tab" aria-selected="false" aria-disabled="true">Purchases</button>
                    <button class="px-4 py-3 text-sm font-medium text-slate-500 hover:text-slate-700" disabled role="tab" aria-selected="false" aria-disabled="true">Performance</button>
                    <button class="px-4 py-3 text-sm font-medium text-slate-500 hover:text-slate-700" disabled role="tab" aria-selected="false" aria-disabled="true">Audit Logs</button>
                </nav>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                        <span class="erp-badge-active">Active</span>
                                    @else
                                        <span class="erp-badge-inactive">Inactive</span>
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
                    <div class="mt-6 pt-6 border-t border-slate-100">
                        <h3 class="text-sm font-medium text-slate-500 mb-2">Notes</h3>
                        <p class="text-sm text-slate-700">{{ $supplier->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
