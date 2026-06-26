<x-app-layout>
    <x-slot name="header">{{ __('Sales Dashboard') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Total Orders</p>
                <p class="text-2xl font-bold text-slate-800">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Pending Approval</p>
                <p class="text-2xl font-bold text-amber-600">{{ $stats['pending_approval'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Fulfilled</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['fulfilled'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Draft</p>
                <p class="text-2xl font-bold text-slate-600">{{ $stats['draft'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                <h3 class="text-sm font-medium text-slate-500 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('sales.orders.create') }}" class="px-4 py-3 text-sm bg-slate-50 rounded-lg border border-slate-200 hover:border-primary hover:text-primary transition text-slate-700">New Sales Order</a>
                    <a href="{{ route('sales.orders.index') }}" class="px-4 py-3 text-sm bg-slate-50 rounded-lg border border-slate-200 hover:border-primary hover:text-primary transition text-slate-700">View Orders</a>
                    <a href="{{ route('sales.reservations.index') }}" class="px-4 py-3 text-sm bg-slate-50 rounded-lg border border-slate-200 hover:border-primary hover:text-primary transition text-slate-700">Reservations</a>
                    <a href="{{ route('customers.dashboard') }}" class="px-4 py-3 text-sm bg-slate-50 rounded-lg border border-slate-200 hover:border-primary hover:text-primary transition text-slate-700">Customer Dashboard</a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                <h3 class="text-sm font-medium text-slate-500 mb-4">Order Status</h3>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Approved</span>
                        <span class="font-semibold text-blue-600">{{ $stats['approved'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Reserved</span>
                        <span class="font-semibold text-purple-600">{{ $stats['reserved'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Partially Fulfilled</span>
                        <span class="font-semibold text-amber-600">{{ $stats['partially_fulfilled'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Cancelled</span>
                        <span class="font-semibold text-red-600">{{ $stats['cancelled'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <h3 class="text-sm font-medium text-slate-500 mb-4">Recent Orders</h3>
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">SO #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                                    <a href="{{ route('sales.orders.show', $order) }}" class="text-blue-600 hover:text-blue-500">{{ $order->so_number }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $order->customer?->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ number_format($order->total, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $c = ['draft' => 'bg-slate-100 text-slate-600', 'pending_approval' => 'bg-amber-100 text-amber-700', 'approved' => 'bg-blue-100 text-blue-700', 'reserved' => 'bg-purple-100 text-purple-700', 'partially_fulfilled' => 'bg-amber-100 text-amber-700', 'fulfilled' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-700'];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $c[$order->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $order->order_date?->format('M d, Y') ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('sales.orders.show', $order) }}" class="text-blue-600 hover:text-blue-500">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500">No orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
