<x-app-layout>
    <x-slot name="header">Bank Institutions</x-slot>

    <div class="mb-4 flex justify-between items-center">
        <div></div>
        <a href="{{ route('banks.create') }}" class="erp-btn-primary">Add Bank</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">Name</th>
                        <th class="text-left">SWIFT Code</th>
                        <th class="text-left">Country</th>
                        <th class="text-left">Currency</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banks as $bank)
                        <tr>
                            <td class="text-sm font-medium text-slate-800">{{ $bank->name }}</td>
                            <td class="text-sm font-mono">{{ $bank->swift_code ?? '-' }}</td>
                            <td class="text-sm text-slate-500">{{ $bank->country ?? '-' }}</td>
                            <td class="text-sm font-mono">{{ $bank->currency_code ?? '-' }}</td>
                            <td class="text-center">
                                @if($bank->is_active)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-600">Inactive</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('banks.edit', $bank) }}" class="erp-btn-ghost text-xs">Edit</a>
                                    <form action="{{ route('banks.destroy', $bank) }}" method="POST" onsubmit="return confirm('Delete this bank?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 text-xs hover:text-red-700">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-slate-400 py-8">No banks.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $banks->links() }}</div>
</x-app-layout>
