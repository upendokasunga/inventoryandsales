<x-app-layout>
    <x-slot name="header">Scheduled Reports</x-slot>
    <x-breadcrumbs :items="[['label' => 'Reports'], ['label' => 'Scheduled Reports']]" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Create Scheduled Report</h3>
        <form method="POST" action="{{ route('reports.scheduled.store') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="block text-xs text-slate-500 mb-1">Name</label>
                <input type="text" name="name" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Type</label>
                <select name="type" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="sales">Sales</option>
                    <option value="inventory">Inventory</option>
                    <option value="customer">Customer</option>
                    <option value="supplier">Supplier</option>
                    <option value="tax">Tax</option>
                    <option value="payment">Payment</option>
                    <option value="kpi">KPI</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Frequency</label>
                <select name="frequency" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="annual">Annual</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Format</label>
                <div class="flex gap-3 pt-2">
                    <label class="flex items-center gap-1 text-sm"><input type="checkbox" name="format[]" value="pdf" checked> PDF</label>
                    <label class="flex items-center gap-1 text-sm"><input type="checkbox" name="format[]" value="excel"> Excel</label>
                    <label class="flex items-center gap-1 text-sm"><input type="checkbox" name="format[]" value="csv"> CSV</label>
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs text-slate-500 mb-1">Recipients (comma-separated emails)</label>
                <input type="text" name="recipients" required placeholder="admin@example.com, manager@example.com" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-6 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-600">Schedule</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Scheduled Reports</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200">
                        <th class="py-2 font-medium">Name</th>
                        <th class="py-2 font-medium">Type</th>
                        <th class="py-2 font-medium">Frequency</th>
                        <th class="py-2 font-medium">Last Run</th>
                        <th class="py-2 font-medium">Next Run</th>
                        <th class="py-2 font-medium text-center">Active</th>
                        <th class="py-2 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reports as $report)
                    <tr class="hover:bg-slate-50">
                        <td class="py-2.5 text-slate-700 font-medium">{{ $report->name }}</td>
                        <td class="py-2.5 text-slate-600 capitalize">{{ $report->type }}</td>
                        <td class="py-2.5 text-slate-600 capitalize">{{ $report->frequency }}</td>
                        <td class="py-2.5 text-slate-600">{{ $report->last_run_at ? $report->last_run_at->format('Y-m-d H:i') : '-' }}</td>
                        <td class="py-2.5 text-slate-600">{{ $report->next_run_at ? $report->next_run_at->format('Y-m-d H:i') : '-' }}</td>
                        <td class="py-2.5 text-center">
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $report->is_active ? 'bg-success-50 text-success' : 'bg-slate-100 text-slate-500' }}">
                                {{ $report->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-2.5 text-right">
                            <div class="flex justify-end gap-1">
                                <form action="{{ route('reports.scheduled.trigger', $report->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 text-xs bg-primary text-white rounded hover:bg-primary-600">Run Now</button>
                                </form>
                                <form action="{{ route('reports.scheduled.destroy', $report->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this scheduled report?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2 py-1 text-xs bg-danger text-white rounded hover:bg-danger-600">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-8 text-center text-slate-400">No scheduled reports yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
