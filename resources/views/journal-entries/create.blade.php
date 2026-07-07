<x-app-layout>
    <x-slot name="header">{{ __('Create Journal Entry') }}</x-slot>
    <div class="max-w-7xl mx-auto">
        <form action="{{ route('journal-entries.store') }}" method="POST" x-data="journalEntryForm()">
            @csrf
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60"><h3 class="text-lg font-semibold text-slate-800">Entry Details</h3></div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Entry Date</label>
                        <input type="date" name="entry_date" value="{{ old('entry_date', date('Y-m-d')) }}" required class="mt-1 block w-full erp-input">
                        @error('entry_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" rows="2" class="mt-1 block w-full erp-input" required>{{ old('description') }}</textarea>
                        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800">Journal Lines</h3>
                    <button type="button" @click="addLine()" class="erp-btn-primary text-xs">Add Line</button>
                </div>
                <div class="p-6">
                    <div class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-3">Debit total: <span x-text="debitTotal().toFixed(2)" class="font-semibold text-slate-800"></span> | Credit total: <span x-text="creditTotal().toFixed(2)" class="font-semibold text-slate-800"></span> <span x-show="Math.abs(debitTotal() - creditTotal()) > 0.01" class="text-red-600 font-semibold">(Not balanced)</span></div>
                    <template x-for="(line, index) in lines" :key="index">
                        <div class="line-row grid grid-cols-12 gap-3 mb-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                            <div class="col-span-4">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Account</label>
                                <select :name="'lines[' + index + '][account_id]'" required class="block w-full erp-input text-sm">
                                    <option value="">Select Account</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Debit</label>
                                <input type="number" step="0.01" min="0" :name="'lines[' + index + '][debit]'" x-model="line.debit" class="block w-full erp-input text-sm">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Credit</label>
                                <input type="number" step="0.01" min="0" :name="'lines[' + index + '][credit]'" x-model="line.credit" class="block w-full erp-input text-sm">
                            </div>
                            <div class="col-span-3">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Description</label>
                                <input type="text" :name="'lines[' + index + '][description]'" class="block w-full erp-input text-sm" placeholder="Line description">
                            </div>
                            <div class="col-span-1 pt-5">
                                <button type="button" @click="removeLine(index)" class="text-red-500 hover:text-red-700" title="Remove"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div>
                        </div>
                    </template>
                    <p x-show="lines.length === 0" class="text-sm text-slate-400 text-center py-4">No lines added. Click "Add Line" to begin.</p>
                    @error('lines') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex justify-end mb-8">
                <a href="{{ route('journal-entries.index') }}" class="mr-4 inline-flex items-center px-4 py-2 erp-btn-secondary">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 erp-btn-primary">Create Journal Entry</button>
            </div>
        </form>
    </div>
    @push('scripts')
    <script>
        function journalEntryForm() {
            return {
                lines: [{
                    debit: 0,
                    credit: 0
                }, {
                    debit: 0,
                    credit: 0
                }],
                addLine() {
                    this.lines.push({ debit: 0, credit: 0 });
                },
                removeLine(index) {
                    if (this.lines.length > 2) {
                        this.lines.splice(index, 1);
                    }
                },
                debitTotal() {
                    return this.lines.reduce((sum, l) => sum + parseFloat(l.debit || 0), 0);
                },
                creditTotal() {
                    return this.lines.reduce((sum, l) => sum + parseFloat(l.credit || 0), 0);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
