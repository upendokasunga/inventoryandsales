<x-app-layout>
    <x-slot name="header">{{ __('Approval Configuration') }}: {{ $approvalConfiguration->module_key }}</x-slot>
    <div class="max-w-4xl mx-auto">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('approval-configurations.index') }}" class="erp-btn-secondary">Back to List</a>
            <div class="flex gap-2">
                <a href="{{ route('approval-configurations.edit', $approvalConfiguration) }}" class="erp-btn-primary">Edit</a>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Configuration Information</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Module Key</dt>
                            <dd class="text-sm font-mono font-semibold text-slate-800">{{ $approvalConfiguration->module_key }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Module Name</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $approvalConfiguration->module_name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Status</dt>
                            <dd><span class="{{ $approvalConfiguration->is_active ? 'erp-badge-active' : 'erp-badge-inactive' }}">{{ $approvalConfiguration->is_active ? 'Active' : 'Inactive' }}</span></dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Description</h3>
                    <p class="text-sm text-slate-700">{{ $approvalConfiguration->description ?? $approvalConfiguration->module_name ?? '-' }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-200/60">
                <h3 class="text-sm font-medium text-slate-500">Approval Levels</h3>
            </div>
            <table class="erp-table">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Group</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Sort Order</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($approvalConfiguration->levels as $level)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">Level {{ $level->level }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $level->group?->name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $level->sort_order }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-8 text-center text-sm text-slate-400">No approval levels configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
