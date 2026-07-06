<x-app-layout>
    <x-slot name="header">{{ __('Sales Dashboard') }}</x-slot>
    <x-slot name="headerDescription">Overview of sales performance, order statuses, and recent activity.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('sales.orders.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Sales Order
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-stats-card title="Total Orders" :value="$stats['total']" color="primary" />
            <x-stats-card title="Pending Approval" :value="$stats['pending_approval']" color="warning" />
            <x-stats-card title="Fulfilled" :value="$stats['fulfilled']" color="success" />
            <x-stats-card title="Draft" :value="$stats['draft']" color="slate" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="erp-card">
                <h3 class="text-sm font-semibold text-slate-800 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('sales.orders.create') }}" class="px-4 py-3 text-sm bg-slate-50 rounded-xl border border-slate-200 hover:border-primary hover:text-primary hover:bg-primary-50/30 transition-all text-slate-700 font-medium">New Sales Order</a>
                    <a href="{{ route('sales.orders.index') }}" class="px-4 py-3 text-sm bg-slate-50 rounded-xl border border-slate-200 hover:border-primary hover:text-primary hover:bg-primary-50/30 transition-all text-slate-700 font-medium">View Orders</a>
                    <a href="{{ route('sales.reservations.index') }}" class="px-4 py-3 text-sm bg-slate-50 rounded-xl border border-slate-200 hover:border-primary hover:text-primary hover:bg-primary-50/30 transition-all text-slate-700 font-medium">Reservations</a>
                    <a href="{{ route('customers.dashboard') }}" class="px-4 py-3 text-sm bg-slate-50 rounded-xl border border-slate-200 hover:border-primary hover:text-primary hover:bg-primary-50/30 transition-all text-slate-700 font-medium">Customer Dashboard</a>
                </div>
            </div>

            <div class="erp-card">
                <h3 class="text-sm font-semibold text-slate-800 mb-4">Order Status</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Approved</span>
                        <span class="text-sm font-semibold text-blue-600">{{ $stats['approved'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Reserved</span>
                        <span class="text-sm font-semibold text-purple-600">{{ $stats['reserved'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Partially Fulfilled</span>
                        <span class="text-sm font-semibold text-amber-600">{{ $stats['partially_fulfilled'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Cancelled</span>
                        <span class="text-sm font-semibold text-red-600">{{ $stats['cancelled'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <x-table-card title="Recent Orders" :empty="count($recentOrders) === 0" emptyMessage="No orders yet." colspan="6">
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
                @forelse ($recentOrders as $order)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                            <a href="{{ route('sales.orders.show', $order) }}" class="text-primary hover:text-primary/80 transition">{{ $order->so_number }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $order->customer?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 font-medium">{{ number_format($order->total, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $c = ['draft' => 'erp-badge-draft', 'pending_approval' => 'erp-badge-pending', 'approved' => 'erp-badge-approved', 'reserved' => 'erp-badge-reserved', 'partially_fulfilled' => 'erp-badge-partial', 'fulfilled' => 'erp-badge-fulfilled', 'cancelled' => 'erp-badge-cancelled'];
                            @endphp
                            <span class="{{ $c[$order->status] ?? 'erp-badge-draft' }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $order->order_date?->format('M d, Y') ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links :view="route('sales.orders.show', $order)" />
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </x-table-card>
    </div>
</x-app-layout>
