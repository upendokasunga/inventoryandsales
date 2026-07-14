<x-app-layout>
    <x-slot name="header">{{ __('Customer Import Preview') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('data-migration.customers.upload') }}" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div class="flex-1">
                <h2 class="text-lg font-semibold text-slate-800">Customer Import Preview</h2>
                <p class="text-sm text-slate-500">Review your data before importing</p>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Total Rows</p>
                <p class="text-2xl font-bold text-slate-800">{{ $totalCount }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Valid Rows</p>
                <p class="text-2xl font-bold text-emerald-600">{{ $totalCount - $errorCount }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Rows with Errors</p>
                <p class="text-2xl font-bold {{ $errorCount > 0 ? 'text-red-600' : 'text-slate-800' }}">{{ $errorCount }}</p>
            </div>
        </div>

        @if ($errorCount > 0)
            <div class="bg-red-50 rounded-xl border border-red-200 p-4 mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    <div>
                        <p class="text-sm font-medium text-red-800">{{ $errorCount }} row(s) have errors and will be skipped during import.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Import Settings --}}
        <form action="{{ route('data-migration.customers.import') }}" method="POST">
            @csrf
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60">
                    <h3 class="text-lg font-semibold text-slate-800">Import Settings</h3>
                </div>
                <div class="p-6">
                    <div class="max-w-md">
                        <label for="default_group_id" class="block text-sm font-medium text-slate-700">Default Customer Group</label>
                        <select name="default_group_id" id="default_group_id" class="mt-1 block w-full erp-input">
                            <option value="">No default group</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 mt-1">Applied to rows without a customer_group value</p>
                    </div>
                </div>
            </div>

            {{-- Data Preview Table --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="px-6 py-4 border-b border-slate-200/60 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800">Data Preview</h3>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Valid: {{ $totalCount - $errorCount }}</span>
                        @if ($errorCount > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">Errors: {{ $errorCount }}</span>
                        @endif
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase">Row</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase">Phone</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase">Email</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase">City</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase">Group</th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-slate-500 uppercase">Credit Limit</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase">Terms</th>
                                <th class="px-3 py-3 text-center text-xs font-medium text-slate-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($customers as $row)
                                <tr class="{{ !empty($row['errors']) ? 'bg-red-50/50' : '' }}">
                                    <td class="px-3 py-2.5 text-xs text-slate-500">{{ $row['row'] }}</td>
                                    <td class="px-3 py-2.5 text-sm font-medium text-slate-800">{{ $row['name'] }}</td>
                                    <td class="px-3 py-2.5 text-sm text-slate-600">{{ $row['phone'] ?: '—' }}</td>
                                    <td class="px-3 py-2.5 text-sm text-slate-600">{{ $row['email'] ?: '—' }}</td>
                                    <td class="px-3 py-2.5 text-sm text-slate-600">{{ $row['city'] ?: '—' }}</td>
                                    <td class="px-3 py-2.5 text-sm text-slate-600">{{ $row['customer_group'] ?: '—' }}</td>
                                    <td class="px-3 py-2.5 text-sm text-slate-800 text-right">{{ $row['credit_limit'] ? number_format($row['credit_limit'], 0) : '—' }}</td>
                                    <td class="px-3 py-2.5 text-sm text-slate-600">{{ $row['payment_terms'] }}</td>
                                    <td class="px-3 py-2.5 text-center">
                                        @if (!empty($row['errors']))
                                            <span class="inline-flex items-center gap-1 text-xs text-red-600" title="{{ implode('; ', $row['errors']) }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                                {{ count($row['errors']) }} error(s)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-xs text-emerald-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                OK
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Error Details --}}
            @if ($errorCount > 0)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                    <div class="px-6 py-4 border-b border-slate-200/60">
                        <h3 class="text-sm font-semibold text-red-800">Error Details</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        @foreach ($customers as $row)
                            @if (!empty($row['errors']))
                                <div class="flex items-start gap-2 text-sm">
                                    <span class="font-medium text-slate-700 shrink-0">Row {{ $row['row'] }}:</span>
                                    <span class="text-red-600">{{ implode('; ', $row['errors']) }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Actions --}}
            <div class="flex justify-end mb-8">
                <a href="{{ route('data-migration.customers.upload') }}" class="mr-3 erp-btn-secondary">Re-upload File</a>
                <button type="submit" class="erp-btn-primary" {{ $errorCount === $totalCount ? 'disabled' : '' }}>
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    Import {{ $totalCount - $errorCount }} Valid Customer(s)
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
