<x-app-layout>
    <x-slot name="header">Record Payment — {{ $invoice->invoice_number }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Invoices', 'url' => route('invoices.index')], ['label' => $invoice->invoice_number, 'url' => route('invoices.show', $invoice)], ['label' => 'Payment']]" />

    <div class="max-w-lg mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
            <div class="flex justify-between text-sm mb-4 p-3 bg-slate-50 rounded-lg">
                <div>
                    <p class="text-slate-500">Invoice Total</p>
                    <p class="text-lg font-bold text-slate-800">{{ number_format($invoice->total, 2) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-slate-500">Balance Due</p>
                    <p class="text-lg font-bold text-danger">{{ number_format($invoice->balance_due, 2) }}</p>
                </div>
            </div>

            <form action="{{ route('payments.store', $invoice) }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Amount</label>
                    <input type="number" name="amount" step="0.01" min="0.01" max="{{ $invoice->balance_due }}" required
                           class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm" placeholder="0.00">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Payment Method</label>
                    <select name="payment_method" required class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="cheque">Cheque</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Reference Number <span class="text-slate-400">(optional)</span></label>
                    <input type="text" name="reference_number" class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm" placeholder="Cheque no / TXN ID">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Payment Date</label>
                    <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm"></textarea>
                </div>

                <button type="submit" class="w-full px-4 py-2.5 bg-primary text-white font-medium rounded-lg hover:bg-primary-600 transition">Record Payment</button>
            </form>
        </div>
    </div>
</x-app-layout>
