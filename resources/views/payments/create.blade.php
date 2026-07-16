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
                           x-data="priceInput()" class="erp-input w-full" placeholder="TSh 0.00">
                    @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Payment Account</label>
                    <select name="payment_account_id" required class="erp-input w-full">
                        <option value="">Select Account</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                        @endforeach
                    </select>
                    @error('payment_account_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Reference Number <span class="text-slate-400">(optional)</span></label>
                    <input type="text" name="reference_number" class="erp-input w-full" placeholder="Cheque no / TXN ID">
                    @error('reference_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Payment Date</label>
                    <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}" required class="erp-input w-full">
                    @error('payment_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="erp-input w-full"></textarea>
                    @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="erp-btn-primary w-full">Record Payment</button>
            </form>
        </div>
    </div>
</x-app-layout>
