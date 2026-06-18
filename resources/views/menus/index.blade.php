<x-app-layout>
    <x-slot name="header">
        {{ __('Menus') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="mb-4">
            <a href="{{ route('menus.create') }}" class="inline-flex items-center px-4 py-2 erp-btn-primary">Create Menu</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Module</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Route</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($menus as $menu)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $menu->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $menu->module }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 font-mono">{{ $menu->route }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $menu->sort_order }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($menu->is_active)
                                        <span class="erp-badge-active">Active</span>
                                    @else
                                        <span class="erp-badge-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('menus.edit', $menu) }}" class="text-blue-600 hover:text-blue-500 mr-3">Edit</a>
                                    <form action="{{ route('menus.destroy', $menu) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-500">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500">No menus found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">{{ $menus->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
