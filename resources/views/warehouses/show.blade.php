<x-app-layout>
    <x-slot name="header">{{ $warehouse->code }} - {{ $warehouse->name }}</x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="mb-4 flex gap-2">
            <a href="{{ route('warehouses.index') }}" class="inline-flex items-center px-4 py-2 erp-btn-secondary">Back to List</a>
            <a href="{{ route('warehouses.edit', $warehouse) }}" class="inline-flex items-center px-4 py-2 erp-btn-primary">Edit Warehouse</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="px-6 py-4 border-b border-blue-100">
                        <h3 class="text-lg font-semibold text-slate-800">Warehouse Information</h3>
                    </div>
                    <div class="p-6 grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Code</span>
                            <p class="mt-1 text-sm font-mono text-slate-800">{{ $warehouse->code }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Name</span>
                            <p class="mt-1 text-sm text-slate-800">{{ $warehouse->name }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Type</span>
                            <p class="mt-1 text-sm text-slate-800">{{ ucfirst(str_replace('_', ' ', $warehouse->type)) }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Branch</span>
                            <p class="mt-1 text-sm text-slate-800">{{ $warehouse->branch?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Location</span>
                            <p class="mt-1 text-sm text-slate-800">{{ $warehouse->location ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 uppercase">Status</span>
                            <p class="mt-1">
                                @if ($warehouse->is_active)
                                    <span class="erp-badge-active">Active</span>
                                @else
                                    <span class="erp-badge-inactive">Inactive</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    @if ($warehouse->description)
                        <div class="px-6 pb-4">
                            <span class="text-xs font-medium text-slate-500 uppercase">Description</span>
                            <p class="mt-1 text-sm text-slate-700">{{ $warehouse->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="px-6 py-4 border-b border-blue-100">
                        <h3 class="text-lg font-semibold text-slate-800">Quick Actions</h3>
                    </div>
                    <div class="p-6 space-y-2">
                        <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full erp-btn-secondary text-xs text-red-600 border-red-200 hover:bg-red-50">Delete Warehouse</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
