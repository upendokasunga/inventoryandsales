<x-app-layout>
    <x-slot name="header">Edit Invoice {{ $invoice->invoice_number }}</x-slot>

    <x-breadcrumbs :items="[['label' => 'Invoices', 'url' => route('invoices.index')], ['label' => $invoice->invoice_number, 'url' => route('invoices.show', $invoice)], ['label' => 'Edit']]" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form action="{{ route('invoices.update', $invoice) }}" method="POST">
            @csrf @method('PATCH')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Customer</label>
                    <x-create-inline selectId="customer_id" :createUrl="route('customers.store')" title="Create New Customer"
                        :fields="[['name'=>'name','label'=>'Customer Name','required'=>true],['name'=>'phone','label'=>'Phone'],['name'=>'email','label'=>'Email']]">
                        <select name="customer_id" id="customer_id" required class="erp-input w-full">
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" @selected($invoice->customer_id === $c->id)>{{ $c->name }}</option>
                            @endforeach
                            <option value="" disabled>---</option>
                            <option value="__create__">&plus; Not in the list? Create new</option>
                        </select>
                    </x-create-inline>
                    @error('customer_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Invoice Date</label>
                    <input type="date" name="invoice_date" value="{{ $invoice->invoice_date->format('Y-m-d') }}" required class="erp-input w-full">
                    @error('invoice_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Discount</label>
                    <input type="number" name="discount" step="0.01" value="{{ $invoice->discount }}" class="erp-input w-full">
                    @error('discount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="erp-input w-full">{{ $invoice->notes }}</textarea>
                    @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('invoices.show', $invoice) }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Update Invoice</button>
            </div>
        </form>
    </div>
</x-app-layout>
