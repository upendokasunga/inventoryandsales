<x-app-layout>
    <x-slot name="header">
        {{ __('Users') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="mb-4">
            <a href="{{ route('users.create') }}" class="erp-btn-primary">Create User</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Groups</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    @foreach ($user->groups as $g)
                                        <span class="inline-block px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700 mr-1">{{ $g->name }}</span>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($user->is_active)
                                        <span class="erp-badge-active">Active</span>
                                    @else
                                        <span class="erp-badge-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('users.edit', $user) }}" class="text-blue-600 hover:text-blue-500">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">{{ $users->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
