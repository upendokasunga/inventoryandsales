<x-app-layout>
    <x-slot name="header">{{ __('Proforma Invoices') }}</x-slot>
    <x-slot name="headerDescription">Track and manage all proforma invoices from creation through fulfillment.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('sales.orders.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Proforma Invoice
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-3 gap-4 mb-6">
            <x-stats-card title="Total Orders" :value="$stats['total']" color="primary" />
            <x-stats-card title="Pending Approval" :value="$stats['pending_approval']" color="warning" />
            <x-stats-card title="Fulfilled" :value="$stats['fulfilled']" color="success" />

        </div>

        <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
            <form method="GET" class="flex gap-2">
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search SO # or customer..." class="erp-input pl-10">
                </div>
                <select name="status" class="erp-input" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach (\App\Models\SalesOrder::STATUSES as $s)
                        <option value="{{ $s }}" {{ ($filters['status'] ?? '') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <select name="customer_id" class="erp-input" onchange="this.form.submit()">
                    <option value="">All Customers</option>
                    @foreach ($customers as $id => $name)
                        <option value="{{ $id }}" {{ ($filters['customer_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="erp-btn-primary">Search</button>
            </form>
        </div>

        <x-table-card :empty="count($orders) === 0" emptyMessage="No proforma invoices found. Create one to get started." colspan="6">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">SO #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($orders as $order)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                            <a href="{{ route('sales.orders.show', $order) }}" class="text-primary hover:text-primary/80 transition">{{ $order->so_number }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $order->customer?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 font-medium">{{ number_format($order->total, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $c = ['pending_approval' => 'erp-badge-pending', 'approved' => 'erp-badge-approved', 'reserved' => 'erp-badge-reserved', 'partially_fulfilled' => 'erp-badge-partial', 'fulfilled' => 'erp-badge-fulfilled', 'cancelled' => 'erp-badge-cancelled'];
                            @endphp
                            <span class="{{ $c[$order->status] ?? 'erp-badge-pending' }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $order->order_date?->format('M d, Y') ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links
                                :view="route('sales.orders.show', $order)"
                                :edit="$order->status === 'pending_approval' ? route('sales.orders.edit', $order) : null"
                            />
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
        <div class="mt-4">{{ $orders->links() }}</div>
    </div>
</x-app-layout>
