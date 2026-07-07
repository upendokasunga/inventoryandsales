<x-app-layout>
    <x-slot name="header">Apply Discount - Invoice #{{ $invoice->invoice_number }}</x-slot>

    <x-breadcrumbs :items="[
        ['label' => 'Sales', 'url' => route('invoices.index')],
        ['label' => 'Invoice #' . $invoice->invoice_number, 'url' => route('invoices.show', $invoice)],
        ['label' => 'Apply Discount'],
    ]" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="mb-6 p-4 bg-slate-50 rounded-lg">
            <h3 class="text-sm font-semibold text-slate-700 mb-2">Invoice Summary</h3>
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div><span class="text-slate-500">Current Total:</span> <span class="font-medium">{{ number_format($invoice->total, 2) }}</span></div>
                <div><span class="text-slate-500">Balance Due:</span> <span class="font-medium">{{ number_format($invoice->balance_due, 2) }}</span></div>
                <div><span class="text-slate-500">Existing Discount:</span> <span class="font-medium text-danger">{{ number_format($invoice->discount, 2) }}</span></div>
            </div>
        </div>

        <form action="{{ route('invoices.discount-store', $invoice) }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Discount Amount</label>
                    <input type="number" name="discount_amount" min="0" max="{{ $invoice->total }}" step="0.01" required
                        class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm">
                    <p class="text-xs text-slate-400 mt-1">Max discount: {{ number_format($invoice->total, 2) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Reason</label>
                    <textarea name="reason" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm" placeholder="Reason for discount..."></textarea>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-success text-white text-sm rounded-lg hover:bg-success-600 transition">Apply Discount</button>
                <a href="{{ route('invoices.show', $invoice) }}" class="px-4 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
