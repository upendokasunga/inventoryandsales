<x-app-layout>
    <x-slot name="header">{{ __('Expenses') }}</x-slot>
    <x-slot name="headerDescription">Manage all expenses — track, approve, and record payments.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('expenses.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Expense
        </a>
    </x-slot>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 border-b border-slate-200">
            <nav class="flex space-x-4 -mb-px">
                @foreach (['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'paid' => 'Paid', 'rejected' => 'Rejected', 'reversed' => 'Reversed'] as $key => $label)
                    <a href="{{ route('expenses.index', ['tab' => $key]) }}" class="pb-3 px-1 text-sm font-medium border-b-2 whitespace-nowrap {{ $tab === $key ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </div>
        <x-table-card :empty="count($expenses) === 0" emptyMessage="No expenses found." colspan="8">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Expense #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Account</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Created By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($expenses as $expense)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-800">{{ $expense->expense_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $expense->category?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 text-right font-medium">{{ number_format($expense->amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $expense->expense_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php $c = ['pending' => 'erp-badge-pending', 'approved' => 'erp-badge-approved', 'paid' => 'erp-badge-fulfilled', 'rejected' => 'erp-badge-cancelled', 'reversed' => 'erp-badge-draft']; @endphp
                            <span class="{{ $c[$expense->status] ?? 'erp-badge-draft' }}">{{ ucfirst($expense->status) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $expense->account?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $expense->creator?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links :view="route('expenses.show', $expense)" :edit="route('expenses.edit', $expense)" :delete="route('expenses.destroy', $expense)" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $expenses->appends(['tab' => $tab])->links() }}</div>
    </div>
</x-app-layout>
