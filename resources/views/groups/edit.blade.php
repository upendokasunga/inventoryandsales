<x-app-layout>
    <x-slot name="header">
        {{ __('Edit Group') }}: {{ $group->name }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 px-4 py-2 text-success-700 bg-success-50 border border-success-100 rounded-lg">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 px-4 py-2 text-danger-700 bg-danger-50 border border-danger-100 rounded-lg">{{ session('error') }}</div>
        @endif

        @if ($group->is_super_admin)
            <div class="mb-4 px-4 py-2 text-amber-700 bg-amber-50 border border-amber-200 rounded-lg">
                Super Admin group has full access to all menus and cannot be modified.
            </div>
        @endif

        <form action="{{ route('groups.update', $group) }}" method="POST">
            @csrf @method('PATCH')

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="p-6">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $group->name) }}" {{ $group->is_super_admin ? 'disabled' : '' }} required
                            class="mt-1 block w-full erp-input">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" id="description" rows="2" {{ $group->is_super_admin ? 'disabled' : '' }}
                            class="mt-1 block w-full erp-input">{{ old('description', $group->description) }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ $group->is_super_admin ? 'disabled' : '' }} {{ old('is_active', $group->is_active) ? 'checked' : '' }}
                                class="erp-input">
                            <span class="ml-2 text-sm text-slate-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            @if (!$group->is_super_admin)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-slate-800 mb-4">Menu Permissions</h3>

                        @foreach ($menus as $module => $moduleMenus)
                            <div class="mb-6">
                                <h4 class="text-md font-semibold text-slate-700 mb-2">{{ $module }}</h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                                        <thead>
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-slate-500">Menu</th>
                                                <th class="px-3 py-2 text-center font-medium text-slate-500">View</th>
                                                <th class="px-3 py-2 text-center font-medium text-slate-500">Create</th>
                                                <th class="px-3 py-2 text-center font-medium text-slate-500">Edit</th>
                                                <th class="px-3 py-2 text-center font-medium text-slate-500">Delete</th>
                                                <th class="px-3 py-2 text-center font-medium text-slate-500">Approve</th>
                                                <th class="px-3 py-2 text-center font-medium text-slate-500">2FA</th>
                                                <th class="px-3 py-2 text-center font-medium text-slate-500">Print</th>
                                                <th class="px-3 py-2 text-center font-medium text-slate-500">Export</th>
                                                <th class="px-3 py-2 text-center font-medium text-slate-500">Import</th>
                                                <th class="px-3 py-2 text-center font-medium text-slate-500">Reverse</th>
                                                <th class="px-3 py-2 text-center font-medium text-slate-500">Cancel</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-50">
                                            @foreach ($moduleMenus as $menu)
                                                @php $perms = $currentPermissions[$menu['id']] ?? []; @endphp
                                                <tr>
                                                    <td class="px-3 py-2 text-slate-800">{{ $menu['name'] }}</td>
                                                    @foreach (['can_view', 'can_create', 'can_edit', 'can_delete', 'can_approve', 'can_2fa', 'can_print', 'can_export', 'can_import', 'can_reverse', 'can_cancel'] as $perm)
                                                        <td class="px-3 py-2 text-center">
                                                            <input type="checkbox" name="permissions[{{ $menu['id'] }}][{{ $perm }}]" value="1"
                                                                {{ ($perms[$perm] ?? false) ? 'checked' : '' }}
                                                                class="erp-input">
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Assigned Users</h3>

                    @if ($groupUsers->isNotEmpty())
                        <div class="mb-4">
                            <table class="min-w-full divide-y divide-slate-100 text-sm">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-slate-500">Name</th>
                                        <th class="px-4 py-2 text-left font-medium text-slate-500">Email</th>
                                        <th class="px-4 py-2 text-right font-medium text-slate-500">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach ($groupUsers as $u)
                                        <tr>
                                            <td class="px-4 py-2 text-slate-800">{{ $u->name }}</td>
                                            <td class="px-4 py-2 text-slate-500">{{ $u->email }}</td>
                                            <td class="px-4 py-2 text-right">
                                                <form action="{{ route('groups.remove-user', [$group, $u]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this user from the group?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-xs text-red-600 hover:text-red-500">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-slate-500 mb-4">No users assigned to this group.</p>
                    @endif

                    @if ($availableUsers->isNotEmpty())
                        <form action="{{ route('groups.assign-users', $group) }}" method="POST" class="flex items-end gap-2">
                            @csrf
                            <div class="flex-1">
                                <label for="user_ids" class="block text-sm font-medium text-slate-700 mb-1">Add User</label>
                                <select name="user_ids[]" id="user_ids" required
                                    class="block w-full erp-input">
                                    <option value="">Select a user...</option>
                                    @foreach ($availableUsers as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="erp-btn-primary">Assign</button>
                        </form>
                    @else
                        <p class="text-sm text-slate-500">All users are already assigned to this group.</p>
                    @endif
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('groups.index') }}" class="mr-4 erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Update Group</button>
            </div>
        </form>
    </div>
</x-app-layout>
