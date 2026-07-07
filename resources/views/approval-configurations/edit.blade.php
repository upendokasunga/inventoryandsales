<x-app-layout>
    <x-slot name="header">{{ __('Edit Approval Configuration') }}: {{ $approvalConfiguration->module_key }}</x-slot>
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('approval-configurations.update', $approvalConfiguration) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60"><h3 class="text-lg font-semibold text-slate-800">Configuration Details</h3></div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Module Key</label>
                        <select name="module_key" required class="mt-1 block w-full erp-input">
                            <option value="">Select Module</option>
                            @foreach ($moduleKeys as $key)
                                <option value="{{ $key }}" {{ old('module_key', $approvalConfiguration->module_key) == $key ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $key)) }}</option>
                            @endforeach
                        </select>
                        @error('module_key') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Module Name</label>
                        <input type="text" name="module_name" value="{{ old('module_name', $approvalConfiguration->module_name) }}" class="mt-1 block w-full erp-input" placeholder="Display name (optional)">
                        @error('module_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" rows="3" class="mt-1 block w-full erp-input">{{ old('description', $approvalConfiguration->description) }}</textarea>
                        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $approvalConfiguration->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-primary focus:ring-primary">
                            <span class="text-sm font-medium text-slate-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800">Approval Levels</h3>
                    <button type="button" id="add-level" class="erp-btn-primary text-xs">Add Level</button>
                </div>
                <div class="p-6">
                    <div id="levels-container">
                        @forelse ($approvalConfiguration->levels as $index => $level)
                            <div class="level-row grid grid-cols-12 gap-3 mb-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                                <div class="col-span-3">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Level</label>
                                    <select name="levels[{{ $index }}][level]" required class="block w-full erp-input text-sm">
                                        <option value="">Select</option>
                                        @for ($i = 0; $i <= 3; $i++)
                                            <option value="{{ $i }}" {{ old('levels.'.$index.'.level', $level->level) == $i ? 'selected' : '' }}>Level {{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-span-5">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Group</label>
                                    <select name="levels[{{ $index }}][group_id]" required class="block w-full erp-input text-sm">
                                        <option value="">Select Group</option>
                                        @foreach ($groups as $g)
                                            <option value="{{ $g->id }}" {{ old('levels.'.$index.'.group_id', $level->group_id) == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Sort Order</label>
                                    <input type="number" min="0" name="levels[{{ $index }}][sort_order]" value="{{ old('levels.'.$index.'.sort_order', $level->sort_order) }}" required class="block w-full erp-input text-sm">
                                </div>
                                <div class="col-span-1 pt-5">
                                    <button type="button" class="remove-level text-red-500 hover:text-red-700" title="Remove"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>
                            </div>
                        @empty
                            <div class="level-row grid grid-cols-12 gap-3 mb-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                                <div class="col-span-3">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Level</label>
                                    <select name="levels[0][level]" required class="block w-full erp-input text-sm">
                                        <option value="">Select</option>
                                        @for ($i = 0; $i <= 3; $i++)
                                            <option value="{{ $i }}">Level {{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-span-5">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Group</label>
                                    <select name="levels[0][group_id]" required class="block w-full erp-input text-sm">
                                        <option value="">Select Group</option>
                                        @foreach ($groups as $g)
                                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Sort Order</label>
                                    <input type="number" min="0" name="levels[0][sort_order]" value="0" required class="block w-full erp-input text-sm">
                                </div>
                                <div class="col-span-1 pt-5">
                                    <button type="button" class="remove-level text-red-500 hover:text-red-700" title="Remove"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    @error('levels') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex justify-end mb-8">
                <a href="{{ route('approval-configurations.index') }}" class="mr-4 inline-flex items-center px-4 py-2 erp-btn-secondary">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 erp-btn-primary">Update Configuration</button>
            </div>
        </form>
    </div>
    @push('scripts')
    <script>
        let levelIndex = {{ count($approvalConfiguration->levels) }};
        document.getElementById('add-level').addEventListener('click', function() {
            const template = document.querySelector('.level-row').cloneNode(true);
            template.querySelectorAll('[name]').forEach(input => {
                const name = input.getAttribute('name');
                if (name) input.setAttribute('name', name.replace(/\[\d+\]/, '[' + levelIndex + ']'));
                if (input.type !== 'checkbox') input.value = '';
            });
            template.querySelector('.remove-level').addEventListener('click', function() { template.remove(); });
            document.getElementById('levels-container').appendChild(template);
            levelIndex++;
        });
        document.querySelectorAll('.remove-level').forEach(btn => btn.addEventListener('click', function() { this.closest('.level-row').remove(); }));
    </script>
    @endpush
</x-app-layout>
