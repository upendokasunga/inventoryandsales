<x-app-layout>
    <x-slot name="header">{{ __('Document Numbering') }}</x-slot>
    <x-slot name="headerDescription">Configure auto-numbering formats for documents.</x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif

        <form action="{{ route('settings.document-numbering.update') }}" method="POST">
            @csrf @method('PATCH')

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Document</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Prefix</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Separator</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Padding</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase">Active</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse ($configs as $config)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ ucwords(str_replace('_', ' ', $config->document_type)) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="hidden" name="configs[{{ $loop->index }}][document_type]" value="{{ $config->document_type }}">
                                        <input type="text" name="configs[{{ $loop->index }}][prefix]" value="{{ $config->prefix }}"
                                            class="erp-input w-24 uppercase" maxlength="20" required>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="text" name="configs[{{ $loop->index }}][separator]" value="{{ $config->separator }}"
                                            class="erp-input w-16 text-center" maxlength="5" required>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="number" name="configs[{{ $loop->index }}][padding]" value="{{ $config->padding }}"
                                            class="erp-input w-20 text-center" min="2" max="10" required>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <label class="inline-flex items-center">
                                            <input type="hidden" name="configs[{{ $loop->index }}][is_active]" value="0">
                                            <input type="checkbox" name="configs[{{ $loop->index }}][is_active]" value="1"
                                                {{ $config->is_active ? 'checked' : '' }} class="erp-input">
                                        </label>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">No numbering configs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($configs->isNotEmpty())
                <div class="mt-6 flex justify-end gap-3">
                    <p class="text-sm text-slate-500 self-center">Example: <span class="font-mono font-medium" id="numbering-example">PO-2026-000001</span></p>
                    <button type="submit" class="erp-btn-primary">Save Settings</button>
                </div>
            @endif
        </form>
    </div>

    @push('scripts')
    <script>
        document.querySelectorAll('input[name$="[prefix]"], input[name$="[separator]"], input[name$="[padding]"]').forEach(el => {
            el.addEventListener('input', updateExample);
        });

        function updateExample() {
            const rows = document.querySelectorAll('tbody tr');
            const example = document.getElementById('numbering-example');
            if (rows.length > 0) {
                const prefix = rows[0].querySelector('input[name$="[prefix]"]')?.value || 'PO';
                const sep = rows[0].querySelector('input[name$="[separator]"]')?.value || '-';
                const pad = rows[0].querySelector('input[name$="[padding]"]')?.value || 6;
                const year = new Date().getFullYear();
                const num = ''.padStart(parseInt(pad) || 6, '0').replace(/^/, '1');
                const padded = num.slice(-(parseInt(pad) || 6));
                example.textContent = prefix + sep + year + sep + padded;
            }
        }
    </script>
    @endpush
</x-app-layout>
