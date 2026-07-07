<x-app-layout>
    <x-slot name="header">
        {{ __('Create Group') }}
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <form action="{{ route('groups.store') }}" method="POST">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="p-6">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="mt-1 block w-full erp-input">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" id="description" rows="2"
                            class="mt-1 block w-full erp-input">{{ old('description') }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" checked
                                class="erp-input">
                            <span class="ml-2 text-sm text-slate-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Menu Permissions</h3>
                    <p class="text-sm text-slate-500 mb-4">Configure which menus this group can access and what actions they can perform.</p>

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
                                            <tr>
                                                <td class="px-3 py-2 text-slate-800">{{ $menu['name'] }}</td>
                                                        @foreach (['can_view', 'can_create', 'can_edit', 'can_delete', 'can_approve', 'can_2fa', 'can_print', 'can_export', 'can_import', 'can_reverse', 'can_cancel'] as $perm)
                                                    <td class="px-3 py-2 text-center">
                                                        <input type="checkbox" name="permissions[{{ $menu['id'] }}][{{ $perm }}]" value="1"
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

            <div class="flex justify-end">
                <a href="{{ route('groups.index') }}" class="mr-4 erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Create Group</button>
            </div>
        </form>
    </div>
</x-app-layout>
