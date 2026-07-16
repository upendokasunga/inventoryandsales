<x-app-layout>
    <x-slot name="header">{{ __('Expense') }}: {{ $expense->expense_number }}</x-slot>
    <div class="max-w-4xl mx-auto">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('expenses.index') }}" class="erp-btn-secondary">Back to List</a>
            <div class="flex gap-2">
                @if (in_array($expense->status, ['pending', 'draft']))
                    <a href="{{ route('expenses.edit', $expense) }}" class="erp-btn-primary">Edit</a>
                @endif
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Expense Information</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Expense #</dt>
                            <dd class="text-sm font-semibold text-slate-800">{{ $expense->expense_number }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Category</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $expense->category?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Amount</dt>
                            <dd class="text-sm font-semibold text-slate-800">{{ number_format($expense->amount, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Date</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $expense->expense_date->format('d M Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Status</dt>
                            <dd>@php $c = ['pending' => 'erp-badge-pending', 'approved' => 'erp-badge-approved', 'paid' => 'erp-badge-fulfilled', 'rejected' => 'erp-badge-cancelled', 'reversed' => 'erp-badge-draft']; @endphp <span class="{{ $c[$expense->status] ?? 'erp-badge-draft' }}">{{ ucfirst($expense->status) }}</span></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Payment Account</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $expense->account?->name ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Audit</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Created By</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $expense->creator?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-slate-500">Created At</dt>
                            <dd class="text-sm font-medium text-slate-800">{{ $expense->created_at->format('d M Y H:i') }}</dd>
                        </div>
                        @if ($expense->payee)
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Paid To</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $expense->payee->name }}</dd>
                            </div>
                        @endif
                        @if ($expense->account)
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Account</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $expense->account->code }} - {{ $expense->account->name }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500 mb-4">Description</h3>
                    <p class="text-sm text-slate-700">{{ $expense->description ?? '-' }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-6 mb-6">
            <h3 class="text-sm font-medium text-slate-500 mb-4">Approval Timeline</h3>
            <div class="flow-root">
                <ul class="-mb-4">
                    <li class="relative pb-4">
                        <div class="absolute left-2 top-2 -bottom-4 w-0.5 bg-slate-200"></div>
                        <div class="flex items-start gap-3">
                            <span class="relative z-10 mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 ring-2 ring-white"><svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4.5 12.75l6 6 9-13.5"/></svg></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-slate-800">Created</p>
                                <p class="text-xs text-slate-500">{{ $expense->creator?->name ?? 'System' }} — {{ $expense->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </li>
                    @if ($expense->approved_at)
                        <li class="relative pb-4">
                            <div class="absolute left-2 top-2 -bottom-4 w-0.5 bg-slate-200"></div>
                            <div class="flex items-start gap-3">
                                <span class="relative z-10 mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-blue-500 ring-2 ring-white"><svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4.5 12.75l6 6 9-13.5"/></svg></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-800">Approved</p>
                                    <p class="text-xs text-slate-500">{{ $expense->approver?->name ?? 'System' }} — {{ $expense->approved_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        </li>
                    @endif
                    @if ($expense->status === 'rejected' && !$expense->paid_at)
                        <li class="relative pb-4">
                            <div class="flex items-start gap-3">
                                <span class="relative z-10 mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 ring-2 ring-white"><svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-800">Rejected</p>
                                    <p class="text-xs text-slate-500">{{ $expense->approver?->name ?? 'System' }}</p>
                                </div>
                            </div>
                        </li>
                    @endif
                    @if ($expense->paid_at)
                        <li class="relative pb-4">
                            <div class="flex items-start gap-3">
                                <span class="relative z-10 mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 ring-2 ring-white"><svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4.5 12.75l6 6 9-13.5"/></svg></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-800">Paid</p>
                                    <p class="text-xs text-slate-500">{{ $expense->payer?->name ?? 'System' }} — {{ $expense->paid_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
