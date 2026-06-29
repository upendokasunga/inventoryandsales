<x-app-layout>
    <x-slot name="header">Edit Invoice {{ $invoice->invoice_number }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Invoices', 'url' => route('invoices.index')], ['label' => $invoice->invoice_number, 'url' => route('invoices.show', $invoice)], ['label' => 'Edit']]" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form action="{{ route('invoices.update', $invoice) }}" method="POST">
            @csrf @method('PATCH')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Customer</label>
                    <select name="customer_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm">
                        @foreach(\App\Models\Customer::all() as $c)
                            <option value="{{ $c->id }}" @selected($invoice->customer_id === $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Invoice Date</label>
                    <input type="date" name="invoice_date" value="{{ $invoice->invoice_date->format('Y-m-d') }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Discount</label>
                    <input type="number" name="discount" step="0.01" value="{{ $invoice->discount }}" class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm">{{ $invoice->notes }}</textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('invoices.show', $invoice) }}" class="px-4 py-2.5 border border-slate-200 text-slate-600 rounded-lg text-sm hover:bg-slate-50">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-primary text-white font-medium rounded-lg hover:bg-primary-600 transition">Update Invoice</button>
            </div>
        </form>
    </div>
</x-app-layout>
