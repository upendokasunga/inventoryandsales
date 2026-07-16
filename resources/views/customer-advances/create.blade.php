<x-app-layout>
    <x-slot name="header">Record Customer Advance</x-slot>

    <x-breadcrumbs :items="[['label' => 'Customer Advances', 'url' => route('customer-advances.index')], ['label' => 'New Advance']]" />

    <div class="max-w-2xl mx-auto">
        <form method="POST" action="{{ route('customer-advances.store') }}" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Customer *</label>
                <x-create-inline selectId="customer_id" :createUrl="route('customers.store')" title="Create New Customer"
                    :fields="[['name'=>'name','label'=>'Customer Name','required'=>true],['name'=>'phone','label'=>'Phone'],['name'=>'email','label'=>'Email']]">
                    <select name="customer_id" id="customer_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm @error('customer_id') border-danger @enderror">
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }}</option>
                        @endforeach
                        <option value="" disabled>---</option>
                        <option value="__create__">&plus; Not in the list? Create new</option>
                    </select>
                </x-create-inline>
                @error('customer_id') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Amount *</label>
                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm @error('amount') border-danger @enderror" placeholder="0.00">
                @error('amount') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Reference Number</label>
                <input type="text" name="reference_number" value="{{ old('reference_number') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="Cheque/Transaction ref">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Advance Date *</label>
                <input type="date" name="advance_date" value="{{ old('advance_date', now()->format('Y-m-d')) }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm @error('advance_date') border-danger @enderror">
                @error('advance_date') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="Optional notes...">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-600 transition">Record Advance</button>
                <a href="{{ route('customer-advances.index') }}" class="px-4 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
