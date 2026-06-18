<x-app-layout>
    <x-slot name="header">
        {{ __('Customer Groups') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 px-4 py-2 text-danger-700 bg-danger-50 border border-danger-100 rounded-lg">{{ session('error') }}</div>
        @endif

        <div class="mb-4">
            <a href="{{ route('customer-groups.create') }}" class="erp-btn-primary">
                Create Customer Group
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Credit Limit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Payment Terms</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($customerGroups as $group)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $group->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ number_format($group->default_credit_limit, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $group->default_payment_terms ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($group->is_active)
                                        <span class="erp-badge-active">Active</span>
                                    @else
                                        <span class="erp-badge-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('customer-groups.edit', $group) }}" class="text-blue-600 hover:text-blue-500 mr-3">Edit</a>
                                    <form action="{{ route('customer-groups.destroy', $group) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-500">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">No customer groups found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">{{ $customerGroups->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
