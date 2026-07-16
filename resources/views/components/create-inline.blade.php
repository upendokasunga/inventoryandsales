@props([
    'selectId',
    'createUrl',
    'title' => 'Create New',
    'fields' => [],
])

<div x-data="createInline({ selectId: '{{ $selectId }}', createUrl: '{{ $createUrl }}', fields: @js($fields) })" x-cloak>
    {{ $slot }}

    {{-- Backdrop --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-slate-500/75" @click="open = false" style="display: none;"></div>

    {{-- Modal --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none" style="display: none;" @keydown.escape.window="open = false">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-6 pointer-events-auto" @click.stop>
            <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ $title }}</h3>

            @foreach($fields as $field)
            <div class="mb-3">
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ $field['label'] }}</label>
                @if(($field['type'] ?? 'text') === 'select')
                    <select x-model="form.{{ $field['name'] }}" class="erp-input w-full">
                        <option value="">Select {{ $field['label'] }}</option>
                        @foreach($field['options'] ?? [] as $optVal => $optLabel)
                            <option value="{{ $optVal }}">{{ $optLabel }}</option>
                        @endforeach
                    </select>
                @elseif(($field['type'] ?? 'text') === 'textarea')
                    <textarea x-model="form.{{ $field['name'] }}" class="erp-input w-full" rows="2"></textarea>
                @else
                    <input type="{{ $field['type'] ?? 'text' }}" x-model="form.{{ $field['name'] }}" class="erp-input w-full"
                           @if($field['name'] === 'name') x-ref="firstField" @endif
                           @keydown.enter.prevent="submit()">
                @endif
                <template x-if="errors.{{ $field['name'] }}">
                    <p class="mt-1 text-sm text-red-600" x-text="errors.{{ $field['name'] }}[0]"></p>
                </template>
            </div>
            @endforeach

            <div class="flex justify-end gap-3 mt-5 pt-4 border-t border-slate-100">
                <button type="button" @click="open = false" class="erp-btn-ghost">Cancel</button>
                <button type="button" @click="submit()" :disabled="loading" class="erp-btn-primary">
                    <span x-show="!loading">Create</span>
                    <span x-show="loading">Saving...</span>
                </button>
            </div>
        </div>
    </div>
</div>
