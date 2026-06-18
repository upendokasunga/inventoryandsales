<x-app-layout>
    <x-slot name="header">
        {{ __('Categories') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 px-4 py-2 text-danger-700 bg-danger-50 border border-danger-100 rounded-lg">{{ session('error') }}</div>
        @endif

        <div class="mb-4 flex items-center justify-between">
            <div class="flex gap-2">
                <a href="{{ route('categories.create') }}" class="erp-btn-primary">
                    Create Category
                </a>
                <a href="{{ route('categories.tree') }}" class="erp-btn-secondary">
                    Tree View
                </a>
            </div>
            <form action="{{ route('categories.index') }}" method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search categories..."
                    class="erp-input">
                <button type="submit" class="erp-btn-primary">Search</button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Parent</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($categories as $category)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $category->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $category->parent?->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ Str::limit($category->description, 50) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($category->is_active)
                                        <span class="erp-badge-active">Active</span>
                                    @else
                                        <span class="erp-badge-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('categories.edit', $category) }}" class="text-blue-600 hover:text-blue-500 mr-3">Edit</a>
                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-500">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">{{ $categories->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
