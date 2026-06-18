<x-app-layout>
    <x-slot name="header">
        {{ __('Purchase Orders') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Total Orders</p>
                <p class="text-2xl font-bold text-slate-800">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Draft</p>
                <p class="text-2xl font-bold text-slate-600">{{ $stats['draft'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Pending Approval</p>
                <p class="text-2xl font-bold text-amber-600">{{ $stats['pending_approval'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Completed</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</p>
            </div>
        </div>

        <div class="mb-4 flex items-center justify-between">
            <div>
                <a href="{{ route('purchasing.orders.create') }}" class="inline-flex items-center px-4 py-2 erp-btn-primary">
                    Create Purchase Order
                </a>
            </div>
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search PO # or supplier..."
                    class="erp-input">
                <select name="status" class="erp-input" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach (['draft', 'pending_approval', 'approved', 'sent', 'partially_received', 'completed', 'cancelled'] as $s)
                        <option value="{{ $s }}" {{ ($filters['status'] ?? '') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <select name="supplier_id" class="erp-input" onchange="this.form.submit()">
                    <option value="">All Suppliers</option>
                    @foreach ($suppliers as $id => $name)
                        <option value="{{ $id }}" {{ ($filters['supplier_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">PO #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Supplier</th>
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
                                    <a href="{{ route('purchasing.orders.show', $order) }}" class="text-blue-600 hover:text-blue-500">{{ $order->po_number }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $order->supplier?->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ number_format($order->total, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $c = ['draft' => 'bg-slate-100 text-slate-600', 'pending_approval' => 'bg-amber-100 text-amber-700', 'approved' => 'bg-green-100 text-green-700', 'sent' => 'bg-blue-100 text-blue-700', 'partially_received' => 'bg-purple-100 text-purple-700', 'completed' => 'bg-emerald-100 text-emerald-700', 'cancelled' => 'bg-red-100 text-red-700'];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $c[$order->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $order->order_date?->format('M d, Y') ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('purchasing.orders.show', $order) }}" class="text-blue-600 hover:text-blue-500 mr-2">View</a>
                                    @if ($order->status === 'draft')
                                        <a href="{{ route('purchasing.orders.edit', $order) }}" class="text-blue-600 hover:text-blue-500 mr-2">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500">No purchase orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">{{ $orders->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
