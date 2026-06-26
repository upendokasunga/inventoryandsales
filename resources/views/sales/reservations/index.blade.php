<x-app-layout>
    <x-slot name="header">{{ __('Stock Reservations') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-4">
            <form method="GET" class="flex gap-2">
                <select name="status" class="erp-input" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="fulfilled" {{ request('status') == 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                    <option value="released" {{ request('status') == 'released' ? 'selected' : '' }}>Released</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">SO #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Reserved At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($reservations as $reservation)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                                    <a href="{{ route('sales.reservations.show', $reservation) }}" class="text-blue-600 hover:text-blue-500">{{ $reservation->salesOrder?->so_number ?? '-' }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $reservation->salesOrder?->customer?->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $reservation->items->count() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $c = ['active' => 'bg-blue-100 text-blue-700', 'fulfilled' => 'bg-green-100 text-green-700', 'released' => 'bg-slate-100 text-slate-600', 'cancelled' => 'bg-red-100 text-red-700'];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $c[$reservation->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($reservation->status) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $reservation->reserved_at?->format('M d, H:i') ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('sales.reservations.show', $reservation) }}" class="text-blue-600 hover:text-blue-500">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500">No reservations found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">{{ $reservations->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
