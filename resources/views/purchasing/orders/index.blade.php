<x-app-layout>
    <x-slot name="header">{{ __('Purchase Orders') }}</x-slot>
    <x-slot name="headerDescription">Manage purchase orders from creation through receipt and fulfillment.</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('purchasing.orders.create') }}" class="erp-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Purchase Order
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-6 border-b border-slate-200/60">
            <nav class="flex gap-6 -mb-px overflow-x-auto">
                @php $tabs = ['all' => 'All', 'pending_approval' => 'Pending', 'approved' => 'Approved', 'cancelled' => 'Cancelled']; @endphp
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('purchasing.orders.index', ['tab' => $key] + request()->except(['tab', 'status'])) }}"
                       class="whitespace-nowrap pb-3 px-1 text-sm font-medium border-b-2 transition
                       {{ ($tab ?? 'all') === $key ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                        {{ $label }}
                        <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-500">{{ $stats[$key === 'all' ? 'total' : $key] ?? 0 }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
            <form method="GET" class="flex gap-2 flex-wrap">
                <input type="hidden" name="tab" value="{{ $tab ?? 'all' }}">
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search PO # or supplier..." class="erp-input pl-10">
                </div>
                <select name="supplier_id" class="erp-input" onchange="this.form.submit()">
                    <option value="">All Suppliers</option>
                    @foreach ($suppliers as $id => $name)
                        <option value="{{ $id }}" {{ ($filters['supplier_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="erp-btn-primary">Search</button>
            </form>
        </div>

        <x-table-card :empty="count($orders) === 0" emptyMessage="No purchase orders found." colspan="6">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">PO #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Supplier</th>
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
                            <a href="{{ route('purchasing.orders.show', $order) }}" class="text-primary hover:text-primary/80 transition">{{ $order->po_number }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $order->supplier?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 font-medium">{{ number_format($order->total, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $c = ['pending_approval' => 'erp-badge-pending', 'approved' => 'erp-badge-approved', 'partially_received' => 'erp-badge-partial', 'completed' => 'erp-badge-fulfilled', 'cancelled' => 'erp-badge-cancelled'];
                            @endphp
                            <span class="{{ $c[$order->status] ?? 'erp-badge-pending' }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $order->order_date?->format('M d, Y') ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-action-links
                                :view="route('purchasing.orders.show', $order)"
                                :edit="$order->status === 'pending_approval' ? route('purchasing.orders.edit', $order) : null"
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
