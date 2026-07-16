<x-app-layout>
    <x-slot name="header">Money Transfers</x-slot>

    <div class="mb-4 flex justify-between items-center">
        <div></div>
        <a href="{{ route('money-transfers.create') }}" class="erp-btn-primary">New Transfer</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">Reference</th>
                        <th class="text-left">From</th>
                        <th class="text-left">To</th>
                        <th class="text-right">Amount</th>
                        <th class="text-center">Status</th>
                        <th class="text-left">Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $t)
                        <tr class="cursor-pointer" onclick="location.href='{{ route('money-transfers.show', $t) }}'">
                            <td class="text-sm font-mono">{{ $t->reference }}</td>
                            <td class="text-sm">{{ $t->fromAccount->name ?? '-' }}</td>
                            <td class="text-sm">{{ $t->toAccount->name ?? '-' }}</td>
                            <td class="text-sm text-right font-mono">TSh {{ number_format($t->amount, 2) }}</td>
                            <td class="text-center">
                                @php $badge = match($t->status) {'pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700', default=>'bg-slate-100 text-slate-600'}; @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $badge }}">{{ ucfirst($t->status) }}</span>
                            </td>
                            <td class="text-sm text-slate-500">{{ $t->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-slate-400 py-8">No transfers.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $transfers->links() }}</div>
</x-app-layout>
