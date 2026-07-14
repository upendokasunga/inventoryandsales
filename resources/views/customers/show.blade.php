<x-app-layout>
    <x-slot name="header">{{ $customer->name }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Customers', 'url' => route('customers.index')], ['label' => $customer->name]]" />

    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl bg-primary-50 flex items-center justify-center text-primary font-bold text-xl">
                        {{ substr($customer->name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800">{{ $customer->name }}</h2>
                        <p class="text-sm text-slate-500">{{ $customer->code }} &middot; {{ $customer->group?->name ?? 'No Group' }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('customers.edit', $customer) }}" class="erp-btn-primary">Edit Customer</a>
                    <a href="{{ route('customers.statement') }}?customer_id={{ $customer->id }}" class="erp-btn-secondary">Statement</a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
            <div class="erp-card">
                <p class="text-sm font-medium text-slate-500">Credit Limit</p>
                <p class="mt-1 text-xl font-bold text-slate-800">{{ number_format($creditInfo['limit'], 0) }}</p>
            </div>
            <div class="erp-card">
                <p class="text-sm font-medium text-slate-500">Available</p>
                <p class="mt-1 text-xl font-bold {{ $creditInfo['available'] > 0 ? 'text-success-600' : 'text-danger-600' }}">{{ number_format($creditInfo['available'], 0) }}</p>
            </div>
            <div class="erp-card">
                <p class="text-sm font-medium text-slate-500">Outstanding</p>
                <p class="mt-1 text-xl font-bold {{ $customer->outstanding_balance > 0 ? 'text-warning-600' : 'text-slate-800' }}">{{ number_format($creditInfo['outstanding'], 0) }}</p>
            </div>
            <div class="erp-card">
                <p class="text-sm font-medium text-slate-500">Status</p>
                <p class="mt-1">
                    @php
                        $badge = match($customer->credit_status) {
                            'good' => 'erp-badge-active',
                            'overdue' => 'erp-badge-inactive',
                            'suspended' => 'erp-badge-inactive',
                            default => 'erp-badge-inactive',
                        };
                    @endphp
                    <span class="{{ $badge }} text-sm">{{ ucfirst($customer->credit_status) }}</span>
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                    <h3 class="text-md font-semibold text-slate-800 mb-4">Contact Information</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><span class="text-slate-500">Email:</span> <span class="font-medium">{{ $customer->email ?? 'N/A' }}</span></div>
                        <div><span class="text-slate-500">Phone:</span> <span class="font-medium">{{ $customer->phone ?? 'N/A' }}</span></div>
                        <div><span class="text-slate-500">Address:</span> <span class="font-medium">{{ $customer->address ?? 'N/A' }}</span></div>
                        <div><span class="text-slate-500">City/Region:</span> <span class="font-medium">{{ $customer->city }}, {{ $customer->region }}</span></div>
                        <div><span class="text-slate-500">Tax ID:</span> <span class="font-medium">{{ $customer->tax_id ?? 'N/A' }}</span></div>
                        <div><span class="text-slate-500">Payment Terms:</span> <span class="font-medium">{{ $customer->payment_terms ?? 'N/A' }}</span></div>
                    </div>
                    @if ($customer->contact_person)
                        <hr class="my-4 border-slate-100">
                        <h4 class="text-sm font-semibold text-slate-700 mb-2">Contact Person</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div><span class="text-slate-500">Name:</span> <span class="font-medium">{{ $customer->contact_person }}</span></div>
                            <div><span class="text-slate-500">Phone:</span> <span class="font-medium">{{ $customer->contact_phone }}</span></div>
                            <div><span class="text-slate-500">Email:</span> <span class="font-medium">{{ $customer->contact_email }}</span></div>
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-md font-semibold text-slate-800">Recent Credit Transactions</h3>
                        <a href="{{ route('customers.profile', ['customer' => $customer, 'tab' => 'credit']) }}" class="text-sm text-blue-600 hover:text-blue-500">View All</a>
                    </div>
                    @php $recent = $customer->creditTransactions()->latest()->take(5)->get(); @endphp
                    @forelse ($recent as $tx)
                        <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                            <div>
                                <p class="text-sm font-medium text-slate-800">{{ $tx->description }}</p>
                                <p class="text-xs text-slate-500">{{ $tx->type }} &middot; {{ $tx->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="text-sm font-medium {{ $tx->amount > 0 ? 'text-danger-600' : 'text-success-600' }}">
                                {{ $tx->amount > 0 ? '+' : '' }}{{ number_format($tx->amount, 0) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No transactions yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                    <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-3">Quick Actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('customers.edit', $customer) }}" class="block w-full text-center px-4 py-2 erp-btn-primary">Edit Customer</a>
                        <a href="{{ route('customers.profile', ['customer' => $customer, 'tab' => 'credit']) }}" class="block w-full text-center px-4 py-2 erp-btn-secondary">Credit Details</a>
                        <a href="{{ route('customers.statement') }}?customer_id={{ $customer->id }}" class="block w-full text-center px-4 py-2 erp-btn-secondary">View Statement</a>
                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Delete this customer?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="block w-full text-center px-4 py-2 erp-btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
