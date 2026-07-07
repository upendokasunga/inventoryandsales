<?php

namespace App\Services;

use App\Events\GroupPermissionsUpdated;
use App\Models\Group;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PermissionService
{
    public function getUserMenus(User $user)
    {
        $cacheKey = 'user.menus.' . $user->id;

        return Cache::remember($cacheKey, 3600, function () use ($user) {
            return $user->loadMenuPermissions();
        });
    }

    public function clearUserMenuCache(User $user): void
    {
        Cache::forget('user.menus.' . $user->id);
    }

    public function clearAllGroupMenuCaches(): void
    {
        $userIds = DB::table('group_user')->distinct()->pluck('user_id');

        foreach ($userIds as $userId) {
            Cache::forget('user.menus.' . $userId);
        }
    }

    public function clearGroupMenuCaches(Group $group): void
    {
        $userIds = $group->users()->pluck('user_id');

        foreach ($userIds as $userId) {
            Cache::forget('user.menus.' . $userId);
        }
    }

    public function assignGroupPermissions(Group $group, array $menuPermissions): void
    {
        $data = [];
        foreach ($menuPermissions as $menuId => $perms) {
            $data[$menuId] = [
                'can_view' => $perms['can_view'] ?? false,
                'can_create' => $perms['can_create'] ?? false,
                'can_edit' => $perms['can_edit'] ?? false,
                'can_delete' => $perms['can_delete'] ?? false,
                'can_approve' => $perms['can_approve'] ?? false,
                'can_2fa' => $perms['can_2fa'] ?? false,
                'can_print' => $perms['can_print'] ?? false,
                'can_export' => $perms['can_export'] ?? false,
                'can_import' => $perms['can_import'] ?? false,
                'can_reverse' => $perms['can_reverse'] ?? false,
                'can_cancel' => $perms['can_cancel'] ?? false,
            ];
        }

        $group->menus()->sync($data);
        event(new GroupPermissionsUpdated($group));
    }

    public function getAvailableMenus(): array
    {
        return Menu::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('module')
            ->toArray();
    }

    public function userCanAccess(User $user, string $routeName): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $menus = $this->getUserMenus($user);

        foreach ($menus as $menu) {
            if ($menu['route'] === $routeName && ($menu['can_view'] || $menu['can_create'])) {
                return true;
            }
        }

        return false;
    }
}
