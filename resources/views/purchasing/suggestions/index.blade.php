<x-app-layout>
    <x-slot name="header">
        {{ __('Purchase Suggestions') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Total</p>
                <p class="text-2xl font-bold text-slate-800">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Pending</p>
                <p class="text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Approved</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Converted</p>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['converted'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                <p class="text-sm text-slate-500">Rejected</p>
                <p class="text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</p>
            </div>
        </div>

        <div class="mb-4 flex items-center justify-between">
            <div class="flex gap-2">
                <a href="{{ route('purchasing.suggestions.create') }}" class="inline-flex items-center px-4 py-2 erp-btn-primary">
                    Create Suggestion
                </a>
                <form action="{{ route('purchasing.suggestions.generate') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="erp-btn-secondary" onclick="return confirm('Generate auto-suggestions based on reorder levels?');">
                        Auto-Generate
                    </button>
                </form>
            </div>
            <form method="GET" class="flex gap-2">
                <select name="status" class="erp-input" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach (['pending', 'approved', 'rejected', 'converted'] as $s)
                        <option value="{{ $s }}" {{ ($filters['status'] ?? '') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <select name="product_id" class="erp-input" onchange="this.form.submit()">
                    <option value="">All Products</option>
                    @foreach ($products as $id => $name)
                        <option value="{{ $id }}" {{ ($filters['product_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="p-6">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($suggestions as $suggestion)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                                    <a href="{{ route('purchasing.suggestions.show', $suggestion) }}" class="text-blue-600 hover:text-blue-500">
                                        {{ $suggestion->product?->name ?? '-' }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $suggestion->suggested_quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $colors = ['pending' => 'bg-amber-100 text-amber-700', 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700', 'converted' => 'bg-blue-100 text-blue-700'];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $colors[$suggestion->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($suggestion->status) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $suggestion->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('purchasing.suggestions.show', $suggestion) }}" class="text-blue-600 hover:text-blue-500 mr-2">View</a>
                                    @if ($suggestion->status === 'pending')
                                        <form action="{{ route('purchasing.suggestions.approve', $suggestion) }}" method="POST" class="inline mr-2">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-500">Approve</button>
                                        </form>
                                        <form action="{{ route('purchasing.suggestions.reject', $suggestion) }}" method="POST" class="inline mr-2" onsubmit="return confirm('Reject this suggestion?');">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-500">Reject</button>
                                        </form>
                                    @endif
                                    @if (in_array($suggestion->status, ['approved', 'pending']))
                                        <form action="{{ route('purchasing.suggestions.convert', $suggestion) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-blue-600 hover:text-blue-500">Convert to PO</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">No purchase suggestions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">{{ $suggestions->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
