<x-app-layout>
    <x-slot name="header">{{ __('Customers') }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Customers']]" />

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" placeholder="Search customers..." value="{{ request('search') }}"
                        class="erp-input pl-10 w-64">
                </div>
                <select name="credit_status" class="erp-input w-40">
                    <option value="">All Statuses</option>
                    <option value="good" {{ request('credit_status') === 'good' ? 'selected' : '' }}>Good</option>
                    <option value="overdue" {{ request('credit_status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="suspended" {{ request('credit_status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
                <select name="customer_group_id" class="erp-input w-48">
                    <option value="">All Groups</option>
                    @foreach ($customerGroups as $id => $name)
                        <option value="{{ $id }}" {{ request('customer_group_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="erp-btn-secondary">Filter</button>
                <a href="{{ route('customers.index') }}" class="text-sm text-slate-500 hover:text-primary">Clear</a>
            </form>
            <div class="flex gap-2">
                <a href="{{ route('customers.dashboard') }}" class="erp-btn-secondary">Dashboard</a>
                <a href="{{ route('customers.export-csv') }}" class="erp-btn-secondary">Export CSV</a>
                <a href="{{ route('customers.create') }}" class="erp-btn-primary">Add Customer</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Group</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Credit Limit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Outstanding</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($customers as $customer)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-500">{{ $customer->code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('customers.show', $customer) }}" class="text-blue-600 hover:text-blue-500">{{ $customer->name }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $customer->group?->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    <div>{{ $customer->phone }}</div>
                                    <div class="text-xs">{{ $customer->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">{{ number_format($customer->credit_limit, 0) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $customer->outstanding_balance > 0 ? 'text-warning-600 font-medium' : 'text-slate-500' }}">{{ number_format($customer->outstanding_balance, 0) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $badge = match($customer->credit_status) {
                                            'good' => 'erp-badge-active',
                                            'overdue' => 'erp-badge-inactive',
                                            'suspended' => 'erp-badge-inactive',
                                            default => 'erp-badge-inactive',
                                        };
                                    @endphp
                                    <span class="{{ $badge }}">{{ ucfirst($customer->credit_status) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="{{ route('customers.show', $customer) }}" class="text-blue-600 hover:text-blue-500">View</a>
                                    <a href="{{ route('customers.edit', $customer) }}" class="text-blue-600 hover:text-blue-500">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-slate-500">No customers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $customers->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
