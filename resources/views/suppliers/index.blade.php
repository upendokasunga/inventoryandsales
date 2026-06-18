<x-app-layout>
    <x-slot name="header">
        {{ __('Suppliers') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="mb-4 flex items-center justify-between">
            <div>
                <a href="{{ route('suppliers.create') }}" class="inline-flex items-center px-4 py-2 erp-btn-primary">
                    Create Supplier
                </a>
            </div>
            <form action="{{ route('suppliers.index') }}" method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search suppliers..."
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">City</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($suppliers as $supplier)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="text-blue-600 hover:text-blue-500">{{ $supplier->name }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $supplier->contact_person ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $supplier->email ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $supplier->city ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="text-blue-600 hover:text-blue-500 mr-3">View</a>
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="text-blue-600 hover:text-blue-500 mr-3">Edit</a>
                                    <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-500">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">No suppliers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">{{ $suppliers->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
