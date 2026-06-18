<x-app-layout>
    <x-slot name="header">
        {{ __('Purchase Suggestion') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('purchasing.suggestions.index') }}" class="erp-btn-secondary">
                Back to List
            </a>
            <div class="flex gap-2">
                @if ($suggestion->status === 'pending')
                    <form action="{{ route('purchasing.suggestions.approve', $suggestion) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="erp-btn-primary">Approve</button>
                    </form>
                    <form action="{{ route('purchasing.suggestions.reject', $suggestion) }}" method="POST" class="inline" onsubmit="return confirm('Reject this suggestion?');">
                        @csrf
                        <button type="submit" class="erp-btn-danger">Reject</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden mb-6">
            <div class="p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-slate-500 mb-4">Suggestion Details</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Product</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $suggestion->product?->name ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Suggested Quantity</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $suggestion->suggested_quantity }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Reason</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $suggestion->reason ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Status</dt>
                                <dd>
                                    @php
                                        $colors = ['pending' => 'bg-amber-100 text-amber-700', 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700', 'converted' => 'bg-blue-100 text-blue-700'];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $colors[$suggestion->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($suggestion->status) }}</span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-slate-500 mb-4">Audit Info</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Created By</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $suggestion->creator?->name ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Created At</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $suggestion->created_at->format('M d, Y H:i') }}</dd>
                            </div>
                            @if ($suggestion->reviewer)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-slate-500">Reviewed By</dt>
                                    <dd class="text-sm font-medium text-slate-800">{{ $suggestion->reviewer->name }}</dd>
                                </div>
                            @endif
                            @if ($suggestion->reviewed_at)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-slate-500">Reviewed At</dt>
                                    <dd class="text-sm font-medium text-slate-800">{{ $suggestion->reviewed_at->format('M d, Y H:i') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                @if ($suggestion->notes)
                    <div class="mt-6 pt-6 border-t border-slate-100">
                        <h3 class="text-sm font-medium text-slate-500 mb-2">Notes</h3>
                        <p class="text-sm text-slate-700">{{ $suggestion->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
