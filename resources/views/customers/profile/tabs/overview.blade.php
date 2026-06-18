<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
            <h3 class="text-md font-semibold text-slate-800 mb-4">Business Information</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-slate-500">Company Name:</span> <span class="font-medium">{{ $customer->name }}</span></div>
                <div><span class="text-slate-500">Customer Code:</span> <span class="font-mono font-medium">{{ $customer->code }}</span></div>
                <div><span class="text-slate-500">Customer Group:</span> <span class="font-medium">{{ $customer->group?->name ?? 'None' }}</span></div>
                <div><span class="text-slate-500">Tax ID:</span> <span class="font-medium">{{ $customer->tax_id ?? 'N/A' }}</span></div>
                <div><span class="text-slate-500">Registration No:</span> <span class="font-medium">{{ $customer->registration_number ?? 'N/A' }}</span></div>
                <div><span class="text-slate-500">Website:</span> <span class="font-medium">{{ $customer->website ?? 'N/A' }}</span></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
            <h3 class="text-md font-semibold text-slate-800 mb-4">Contact Information</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-slate-500">Email:</span> <span class="font-medium">{{ $customer->email ?? 'N/A' }}</span></div>
                <div><span class="text-slate-500">Phone:</span> <span class="font-medium">{{ $customer->phone ?? 'N/A' }}</span></div>
                <div><span class="text-slate-500">Address:</span> <span class="font-medium">{{ $customer->address ?? 'N/A' }}</span></div>
                <div><span class="text-slate-500">City:</span> <span class="font-medium">{{ $customer->city ?? 'N/A' }}</span></div>
                <div><span class="text-slate-500">Region:</span> <span class="font-medium">{{ $customer->region ?? 'N/A' }}</span></div>
                <div><span class="text-slate-500">Country:</span> <span class="font-medium">{{ $customer->country }}</span></div>
            </div>
            @if ($customer->contact_person)
                <hr class="my-4 border-slate-100">
                <h4 class="text-sm font-semibold text-slate-700 mb-2">Contact Person</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-slate-500">Name:</span> <span class="font-medium">{{ $customer->contact_person }}</span></div>
                    <div><span class="text-slate-500">Phone:</span> <span class="font-medium">{{ $customer->contact_phone ?? 'N/A' }}</span></div>
                    <div><span class="text-slate-500">Email:</span> <span class="font-medium">{{ $customer->contact_email ?? 'N/A' }}</span></div>
                </div>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
            <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-3">Credit Summary</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Credit Limit</span><span class="font-medium">{{ number_format($creditInfo['limit'], 0) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Outstanding</span><span class="font-medium {{ $customer->outstanding_balance > 0 ? 'text-warning-600' : '' }}">{{ number_format($creditInfo['outstanding'], 0) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Available</span><span class="font-medium {{ $creditInfo['available'] > 0 ? 'text-success-600' : 'text-danger-600' }}">{{ number_format($creditInfo['available'], 0) }}</span></div>
                @if ($creditInfo['limit'] > 0)
                    @php $utilPct = min(100, ($creditInfo['outstanding'] / $creditInfo['limit']) * 100); @endphp
                    <div>
                        <div class="flex justify-between text-xs text-slate-500 mb-1">
                            <span>Utilization</span>
                            <span>{{ number_format($utilPct, 1) }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $utilPct > 90 ? 'bg-danger-500' : ($utilPct > 70 ? 'bg-warning-500' : 'bg-success-500') }}" style="width: {{ $utilPct }}%"></div>
                        </div>
                    </div>
                @endif
                <div class="flex justify-between"><span class="text-slate-500">Status</span>
                    @php
                        $badge = match($customer->credit_status) {
                            'good' => 'erp-badge-active',
                            'overdue' => 'erp-badge-inactive',
                            'suspended' => 'erp-badge-inactive',
                            default => 'erp-badge-inactive',
                        };
                    @endphp
                    <span class="{{ $badge }}">{{ ucfirst($customer->credit_status) }}</span>
                </div>
                <div class="flex justify-between"><span class="text-slate-500">Payment Terms</span><span class="font-medium">{{ $customer->payment_terms ?? 'N/A' }}</span></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
            <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-3">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('customers.edit', $customer) }}" class="block w-full text-center px-4 py-2 erp-btn-primary">Edit Customer</a>
                <a href="{{ route('customers.statement') }}?customer_id={{ $customer->id }}" class="block w-full text-center px-4 py-2 erp-btn-secondary">Generate Statement</a>
            </div>
        </div>
    </div>
</div>
