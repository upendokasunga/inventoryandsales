<x-app-layout>
    <x-slot name="header">Advance {{ $customerAdvance->advance_number }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Customer Advances', 'url' => route('customer-advances.index')], ['label' => $customerAdvance->advance_number]]" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">{{ $customerAdvance->customer->name ?? 'N/A' }}</h2>
                        <p class="text-sm text-slate-500">Advance #: <span class="font-medium">{{ $customerAdvance->advance_number }}</span></p>
                        <p class="text-sm text-slate-500">Date: {{ $customerAdvance->advance_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <span class="px-3 py-1 text-sm rounded-full font-medium
                            @if($customerAdvance->status === 'completed') bg-blue-100 text-blue-700
                            @elseif($customerAdvance->status === 'partially_applied') bg-purple-100 text-purple-700
                            @elseif($customerAdvance->status === 'applied') bg-green-100 text-green-700
                            @elseif($customerAdvance->status === 'cancelled') bg-red-100 text-red-700
                            @else bg-slate-100 text-slate-600 @endif">
                            {{ str_replace('_', ' ', ucfirst($customerAdvance->status)) }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="p-4 bg-slate-50 rounded-lg text-center">
                        <p class="text-xs text-slate-400 uppercase">Amount</p>
                        <p class="text-xl font-bold text-slate-800">{{ number_format($customerAdvance->amount, 0) }}</p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-lg text-center">
                        <p class="text-xs text-slate-400 uppercase">Applied</p>
                        <p class="text-xl font-bold text-success">{{ number_format($customerAdvance->amount - $customerAdvance->balance, 0) }}</p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-lg text-center">
                        <p class="text-xs text-slate-400 uppercase">Balance</p>
                        <p class="text-xl font-bold text-primary">{{ number_format($customerAdvance->balance, 0) }}</p>
                    </div>
                </div>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Payment Method</span><span class="capitalize">{{ str_replace('_', ' ', $customerAdvance->payment_method) }}</span></div>
                    @if($customerAdvance->reference_number)
                        <div class="flex justify-between"><span class="text-slate-500">Reference</span><span>{{ $customerAdvance->reference_number }}</span></div>
                    @endif
                    @if($customerAdvance->notes)
                        <div class="mt-4 p-3 bg-slate-50 rounded-lg">
                            <p class="text-xs text-slate-400 uppercase mb-1">Notes</p>
                            <p class="text-slate-600">{{ $customerAdvance->notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- Applications --}}
                @if($customerAdvance->applications->count() > 0)
                    <div class="mt-6">
                        <h3 class="text-sm font-semibold text-slate-700 mb-3">Applied to Invoices</h3>
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                                <tr>
                                    <th class="text-left px-3 py-2">Invoice</th>
                                    <th class="text-right px-3 py-2">Amount</th>
                                    <th class="text-left px-3 py-2">Applied By</th>
                                    <th class="text-left px-3 py-2">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($customerAdvance->applications as $app)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <a href="{{ route('invoices.show', $app->invoice) }}" class="text-primary hover:underline">{{ $app->invoice->invoice_number }}</a>
                                        </td>
                                        <td class="px-3 py-2 text-right font-medium">{{ number_format($app->amount, 0) }}</td>
                                        <td class="px-3 py-2">{{ $app->appliedBy?->name ?? 'N/A' }}</td>
                                        <td class="px-3 py-2">{{ $app->applied_at?->format('d M Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-3">
                <h3 class="text-sm font-semibold text-slate-700">Actions</h3>

                @if(in_array($customerAdvance->status, ['completed', 'partially_applied']))
                    <a href="{{ route('invoices.index') }}?customer_id={{ $customerAdvance->customer_id }}" class="block w-full text-center px-3 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-600 transition">Apply to Invoice</a>
                @endif

                @if(!in_array($customerAdvance->status, ['applied', 'cancelled']))
                    <form action="{{ route('customer-advances.cancel', $customerAdvance) }}" method="POST" onsubmit="return confirm('Cancel this advance?')">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 bg-danger text-white text-sm rounded-lg hover:bg-danger-600 transition">Cancel Advance</button>
                    </form>
                @endif

                <a href="{{ route('customer-advances.index') }}" class="block w-full text-center px-3 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition">Back to List</a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Recorded By</span><span>{{ $customerAdvance->creator?->name ?? 'System' }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Created</span><span>{{ $customerAdvance->created_at->format('d M Y') }}</span></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
