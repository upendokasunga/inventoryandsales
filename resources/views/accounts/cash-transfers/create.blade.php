<x-app-layout>
    <x-slot name="header">New Cash Transfer</x-slot>

    <div class="max-w-xl mx-auto">
        <form action="{{ route('cash-transfers.store') }}" method="POST">
            @csrf
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">From Account <span class="text-red-500">*</span></label>
                        <select name="from_account_id" required class="mt-1 block w-full erp-input">
                            <option value="">Select</option>
                            @foreach($accounts as $a)
                                <option value="{{ $a->id }}" {{ old('from_account_id') == $a->id ? 'selected' : '' }}>{{ $a->code }} - {{ $a->name }}</option>
                            @endforeach
                        </select>
                        @error('from_account_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">To Account <span class="text-red-500">*</span></label>
                        <select name="to_account_id" required class="mt-1 block w-full erp-input">
                            <option value="">Select</option>
                            @foreach($accounts as $a)
                                <option value="{{ $a->id }}" {{ old('to_account_id') == $a->id ? 'selected' : '' }}>{{ $a->code }} - {{ $a->name }}</option>
                            @endforeach
                        </select>
                        @error('to_account_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Amount (TSh) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required class="mt-1 block w-full erp-input">
                        @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Exchange Rate</label>
                        <input type="number" step="0.0001" name="exchange_rate" value="{{ old('exchange_rate', '1.0000') }}" class="mt-1 block w-full erp-input">
                        <p class="mt-1 text-xs text-slate-400">Defaults to 1.0000 if same currency</p>
                        @error('exchange_rate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" rows="2" class="mt-1 block w-full erp-input">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-4">
                <a href="{{ route('cash-transfers.index') }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Create Transfer</button>
            </div>
        </form>
    </div>
</x-app-layout>
