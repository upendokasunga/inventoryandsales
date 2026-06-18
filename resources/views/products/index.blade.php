<x-app-layout>
    <x-slot name="header">
        {{ __('Products') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 px-4 py-2 text-danger-700 bg-danger-50 border border-danger-100 rounded-lg">{{ session('error') }}</div>
        @endif

        <div class="mb-4 flex items-center justify-between flex-wrap gap-2">
            <div class="flex gap-2">
                <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 erp-btn-primary">
                    Create Product
                </a>
                <a href="{{ route('products.export-csv') }}" class="inline-flex items-center px-4 py-2 erp-btn-secondary">
                    Export CSV
                </a>
            </div>
            <form action="{{ route('products.index') }}" method="GET" class="flex gap-2 flex-wrap">
                <select name="category_id" class="erp-input">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="erp-input">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search name, SKU, barcode..." class="erp-input w-64">
                <button type="submit" class="inline-flex items-center px-4 py-2 erp-btn-primary">
                    Search
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Barcode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Units</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($products as $product)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($product->image)
                                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="h-10 w-10 rounded object-cover">
                                    @else
                                        <div class="h-10 w-10 rounded bg-slate-100 flex items-center justify-center text-slate-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-800">{{ $product->sku }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-500">
                                    @if ($product->barcode_image)
                                        <img src="{{ Storage::url($product->barcode_image) }}" alt="{{ $product->barcode }}" class="h-8">
                                    @else
                                        {{ $product->barcode ?? '-' }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                                    <a href="{{ route('products.show', $product) }}" class="text-blue-600 hover:text-blue-500">{{ $product->name }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $product->category?->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($product->is_active)
                                        <span class="erp-badge-active">Active</span>
                                    @else
                                        <span class="erp-badge-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $product->productUnits->count() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('products.edit', $product) }}" class="text-blue-600 hover:text-blue-500 mr-2">Edit</a>
                                    <a href="{{ route('products.print-barcode', $product) }}" class="text-sky-600 hover:text-sky-500 mr-2" target="_blank">Print</a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-500">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-slate-500">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">{{ $products->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
