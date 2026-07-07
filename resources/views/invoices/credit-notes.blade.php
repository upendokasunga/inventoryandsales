<x-app-layout>
    <x-slot name="header">Credit Notes - Invoice #{{ $invoice->invoice_number }}</x-slot>

    <x-breadcrumbs :items="[
        ['label' => 'Sales', 'url' => route('invoices.index')],
        ['label' => 'Invoice #' . $invoice->invoice_number, 'url' => route('invoices.show', $invoice)],
        ['label' => 'Credit Notes'],
    ]" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        @if($creditNotes->isEmpty())
            <p class="text-sm text-slate-500 text-center py-8">No credit notes for this invoice.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="text-left px-3 py-2.5 font-medium">Credit Note #</th>
                            <th class="text-left px-3 py-2.5 font-medium">Amount</th>
                            <th class="text-left px-3 py-2.5 font-medium">Status</th>
                            <th class="text-left px-3 py-2.5 font-medium">Issue Date</th>
                            <th class="text-left px-3 py-2.5 font-medium">Notes</th>
                            <th class="text-left px-3 py-2.5 font-medium">Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($creditNotes as $cn)
                        <tr class="border-b border-slate-100">
                            <td class="px-3 py-2.5 font-medium">{{ $cn->credit_note_number }}</td>
                            <td class="px-3 py-2.5 text-danger font-medium">{{ number_format($cn->amount, 2) }}</td>
                            <td class="px-3 py-2.5">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    @if($cn->status === 'issued') bg-blue-100 text-blue-700
                                    @elseif($cn->status === 'applied') bg-green-100 text-green-700
                                    @else bg-red-100 text-red-700 @endif">
                                    {{ ucfirst($cn->status) }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5">{{ $cn->issued_date?->format('d/m/Y') }}</td>
                            <td class="px-3 py-2.5 text-slate-500">{{ $cn->notes }}</td>
                            <td class="px-3 py-2.5">{{ $cn->creator?->name ?? 'System' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('invoices.show', $invoice) }}" class="px-4 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition">Back to Invoice</a>
        </div>
    </div>
</x-app-layout>
