<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Menu;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupController extends Controller
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function index(): View
    {
        $groups = Group::withCount('users')->latest()->paginate(20);
        return view('groups.index', compact('groups'));
    }

    public function create(): View
    {
        $menus = $this->permissionService->getAvailableMenus();
        return view('groups.create', compact('menus'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:groups',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'permissions' => 'nullable|array',
        ]);

        $group = Group::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (!empty($validated['permissions'])) {
            $this->permissionService->assignGroupPermissions($group, $validated['permissions']);
        }

        return redirect()->route('groups.index')
            ->with('success', 'Group created successfully.');
    }

    public function edit(Group $group): View
    {
        $menus = $this->permissionService->getAvailableMenus();

        $currentPermissions = $group->menus()
            ->get()
            ->keyBy('id')
            ->map(function ($menu) {
                return [
                    'can_view' => $menu->pivot->can_view,
                    'can_create' => $menu->pivot->can_create,
                    'can_edit' => $menu->pivot->can_edit,
                    'can_delete' => $menu->pivot->can_delete,
                    'can_approve' => $menu->pivot->can_approve,
                    'can_2fa' => $menu->pivot->can_2fa,
                    'can_print' => $menu->pivot->can_print,
                    'can_export' => $menu->pivot->can_export,
                    'can_import' => $menu->pivot->can_import,
                    'can_reverse' => $menu->pivot->can_reverse,
                    'can_cancel' => $menu->pivot->can_cancel,
                ];
            });

        $groupUsers = $group->users()->orderBy('name')->get();
        $availableUsers = User::whereDoesntHave('groups', fn($q) => $q->where('groups.id', $group->id))
            ->orderBy('name')
            ->get();

        return view('groups.edit', compact('group', 'menus', 'currentPermissions', 'groupUsers', 'availableUsers'));
    }

    public function update(Request $request, Group $group): RedirectResponse
    {
        if ($group->is_super_admin) {
            return back()->with('error', 'Super Admin group cannot be modified.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:groups,name,' . $group->id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'permissions' => 'nullable|array',
        ]);

        $group->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', $group->is_active),
        ]);

        if (isset($validated['permissions'])) {
            $this->permissionService->assignGroupPermissions($group, $validated['permissions']);
        }

        return redirect()->route('groups.index')
            ->with('success', 'Group updated successfully.');
    }

    public function destroy(Group $group): RedirectResponse
    {
        if ($group->is_super_admin) {
            return back()->with('error', 'Super Admin group cannot be deleted.');
        }

        if ($group->users()->exists()) {
            return back()->with('error', 'Cannot delete group with assigned users.');
        }

        $group->delete();

        return redirect()->route('groups.index')
            ->with('success', 'Group deleted successfully.');
    }

    public function assignUsers(Request $request, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $group->users()->syncWithoutDetaching($validated['user_ids']);

        return redirect()->route('groups.index')
            ->with('success', 'Users assigned to group.');
    }

    public function removeUser(Group $group, User $user): RedirectResponse
    {
        $group->users()->detach($user->id);

        return redirect()->route('groups.edit', $group)
            ->with('success', 'User removed from group.');
    }
}
