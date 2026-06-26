<x-app-layout>
    <x-slot name="header">{{ __('Sales Orders') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-4 gap-4 mb-6">
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

        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('sales.orders.create') }}" class="erp-btn-primary">Create Sales Order</a>
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search SO # or customer..." class="erp-input">
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

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
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
                        @forelse ($orders as $order)
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
                                    <a href="{{ route('sales.orders.show', $order) }}" class="text-blue-600 hover:text-blue-500 mr-2">View</a>
                                    @if ($order->status === 'draft')
                                        <a href="{{ route('sales.orders.edit', $order) }}" class="text-blue-600 hover:text-blue-500">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500">No sales orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">{{ $orders->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
