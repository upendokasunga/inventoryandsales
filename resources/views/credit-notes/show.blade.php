<x-app-layout>
    <x-slot name="header">Credit Note {{ $creditNote->credit_note_number }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Returns', 'url' => route('credit-notes.index')], ['label' => $creditNote->credit_note_number]]" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">{{ $creditNote->credit_note_number }}</h2>
                        <p class="text-sm text-slate-500">Issued: {{ $creditNote->issued_date->format('d M Y') }}</p>
                        <p class="text-sm text-slate-500">Customer: {{ $creditNote->customer->name }}</p>
                    </div>
                    <span class="px-3 py-1 text-sm rounded-full font-medium
                        @if($creditNote->status === 'issued') bg-blue-100 text-blue-700
                        @elseif($creditNote->status === 'applied') bg-green-100 text-green-700
                        @else bg-red-100 text-red-700 @endif">
                        {{ ucfirst($creditNote->status) }}
                    </span>
                </div>

                <div class="bg-slate-50 rounded-lg p-6 text-center mb-6">
                    <p class="text-sm text-slate-500 mb-1">Credit Amount</p>
                    <p class="text-3xl font-bold text-success">{{ number_format($creditNote->amount, 2) }}</p>
                    @if($creditNote->refund_method)
                        <p class="text-sm text-slate-500 mt-2">Refund Method: <span class="font-medium capitalize">{{ str_replace('_', ' ', $creditNote->refund_method) }}</span></p>
                    @endif
                </div>

                @if($creditNote->salesReturn)
                    <div class="p-3 bg-slate-50 rounded-lg mb-4">
                        <p class="text-xs text-slate-400">Related Sales Return</p>
                        <a href="{{ route('sales-returns.show', $creditNote->salesReturn) }}" class="text-primary hover:underline font-medium">
                            {{ $creditNote->salesReturn->return_number }}
                        </a>
                    </div>
                @endif

                @if($creditNote->notes)
                    <div class="p-3 bg-slate-50 rounded-lg text-sm text-slate-600">
                        <p class="text-xs text-slate-400 uppercase">Notes</p>
                        {{ $creditNote->notes }}
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-3">
                <h3 class="text-sm font-semibold text-slate-700">Actions</h3>

                @if($creditNote->status === 'issued')
                    <form action="{{ route('refunds.process') }}" method="POST">
                        @csrf
                        <input type="hidden" name="credit_note_id" value="{{ $creditNote->id }}">
                        <div class="mb-3">
                            <label class="text-xs text-slate-500 block mb-1">Refund Method</label>
                            <select name="refund_method" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                                <option value="cash">Cash Refund</option>
                                <option value="store_credit">Store Credit</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full px-3 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-600 transition">Process Refund</button>
                    </form>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Details</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Issued By</span><span>{{ $creditNote->creator?->name ?? 'System' }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Status</span><span class="capitalize">{{ $creditNote->status }}</span></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
