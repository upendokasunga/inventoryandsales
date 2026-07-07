<x-app-layout>
    <x-slot name="header">{{ __('Products') }}</x-slot>
    <x-slot name="headerDescription">Manage your product catalog — add, edit, and organize products.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('products.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Product
        </a>
        <a href="{{ route('products.export') }}" class="erp-btn-secondary">Export CSV</a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
            <form action="{{ route('products.index') }}" method="GET" class="flex gap-2 flex-nowrap items-center">
                <select name="category_id" class="erp-input">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <select name="product_type" class="erp-input">
                    <option value="">All Types</option>
                    <option value="goods" {{ request('product_type') == 'goods' ? 'selected' : '' }}>Goods</option>
                    <option value="service" {{ request('product_type') == 'service' ? 'selected' : '' }}>Service</option>
                    <option value="fixed_asset" {{ request('product_type') == 'fixed_asset' ? 'selected' : '' }}>Fixed Asset</option>
                </select>
                <select name="status" class="erp-input">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search name, SKU, barcode..." class="erp-input pl-10 w-48">
                </div>
                <button type="submit" class="erp-btn-primary">Search</button>
            </form>
        </div>

        <x-table-card :empty="count($products) === 0" emptyMessage="No products found. Create your first product to get started." colspan="10">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Image</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Product ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Income Account</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Units</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($products as $product)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($product->image)
                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="h-10 w-10 rounded-lg object-cover ring-1 ring-slate-200">
                            @else
                                <div class="h-10 w-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 ring-1 ring-slate-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-500">{{ $product->product_id ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-600">{{ $product->product_code ?? $product->sku }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                            <a href="{{ route('products.show', $product) }}" class="text-primary hover:text-primary/80 transition">{{ $product->name }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $typeBadge = match($product->product_type) {
                                    'goods' => 'erp-badge-success',
                                    'service' => 'erp-badge-info',
                                    'fixed_asset' => 'erp-badge-warning',
                                    default => 'erp-badge-draft',
                                };
                            @endphp
                            <span class="{{ $typeBadge }}">{{ $product->product_type ? ucfirst(str_replace('_', ' ', $product->product_type)) : 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            {{ $product->incomeAccount?->code ? $product->incomeAccount->code . ' - ' . $product->incomeAccount->name : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $product->category?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($product->is_active)
                                <span class="erp-badge-active">Active</span>
                            @else
                                <span class="erp-badge-inactive">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            {{ $product->productUnits->count() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links
                                :view="route('products.show', $product)"
                                :edit="route('products.edit', $product)"
                                :delete="route('products.destroy', $product)"
                                extra='<a href="'.route('products.print-barcode', $product).'" target="_blank" class="text-sky-600 hover:text-sky-500 transition">Print</a>'
                            />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table-card>

        <div class="mt-4">{{ $products->links() }}</div>
    </div>
</x-app-layout>
