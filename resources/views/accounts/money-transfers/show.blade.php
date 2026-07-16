<x-app-layout>
    <x-slot name="header">Money Transfer: {{ $transfer->reference }}</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="mb-4"><a href="{{ route('money-transfers.index') }}" class="erp-btn-secondary">Back to List</a></div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-slate-500">Reference:</span> <span class="font-mono font-semibold">{{ $transfer->reference }}</span></div>
                <div><span class="text-slate-500">Amount:</span> <span class="font-semibold">TSh {{ number_format($transfer->amount, 2) }}</span></div>
                <div><span class="text-slate-500">From:</span> <span class="font-medium">{{ $transfer->fromAccount->name ?? '-' }}</span></div>
                <div><span class="text-slate-500">To:</span> <span class="font-medium">{{ $transfer->toAccount->name ?? '-' }}</span></div>
                <div><span class="text-slate-500">Status:</span>
                    @php $badge = match($transfer->status) {'pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700', default=>'bg-slate-100 text-slate-600'}; @endphp
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $badge }}">{{ ucfirst($transfer->status) }}</span>
                </div>
                <div><span class="text-slate-500">Created by:</span> {{ $transfer->creator?->name ?? '-' }}</div>
                @if($transfer->approver)
                    <div><span class="text-slate-500">Approved by:</span> {{ $transfer->approver->name }}</div>
                @endif
                @if($transfer->journalEntry)
                    <div class="md:col-span-2"><span class="text-slate-500">Journal Entry:</span> <span class="font-mono">{{ $transfer->journalEntry->entry_number }}</span></div>
                @endif
            </div>
        </div>

        @if($transfer->status === 'pending')
            <div class="flex gap-3">
                <form action="{{ route('money-transfers.approve', $transfer) }}" method="POST" onsubmit="return confirm('Approve this transfer?');">
                    @csrf
                    <button type="submit" class="erp-btn-primary">Approve</button>
                </form>
                <form action="{{ route('money-transfers.reject', $transfer) }}" method="POST" onsubmit="return confirm('Reject?');">
                    @csrf
                    <button type="submit" class="erp-btn-danger">Reject</button>
                </form>
            </div>
        @endif
    </div>
</x-app-layout>
