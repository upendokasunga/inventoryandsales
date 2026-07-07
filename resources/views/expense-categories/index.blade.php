<x-app-layout>
    <x-slot name="header">{{ __('Expense Categories') }}</x-slot>
    <x-slot name="headerDescription">Manage expense categories for classifying expenses.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('expense-categories.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Category
        </a>
    </x-slot>
    <div class="max-w-4xl mx-auto">
        <x-table-card :empty="count($categories) === 0" emptyMessage="No expense categories found." colspan="5">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Expenses</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($categories as $cat)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $cat->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ Str::limit($cat->description, 40) ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="{{ $cat->is_active ? 'erp-badge-active' : 'erp-badge-inactive' }}">{{ $cat->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 text-center">{{ $cat->expenses_count }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links :edit="route('expense-categories.edit', $cat)" :delete="route('expense-categories.destroy', $cat)" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $categories->links() }}</div>
    </div>
</x-app-layout>
