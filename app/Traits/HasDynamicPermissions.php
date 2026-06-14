<?php

namespace App\Traits;

use App\Models\Menu;
use Illuminate\Support\Facades\Cache;

trait HasDynamicPermissions
{
    public function hasMenuAccess(string $routeName, string $permission = 'can_view'): bool
    {
        $menus = $this->getCachedMenus();

        foreach ($menus as $menu) {
            if ($menu['route'] === $routeName) {
                return $menu[$permission] ?? false;
            }
        }

        return false;
    }

    public function hasAnyMenuAccess(string $routeName): bool
    {
        $menus = $this->getCachedMenus();

        foreach ($menus as $menu) {
            if ($menu['route'] === $routeName) {
                return $menu['can_view'] || $menu['can_create']
                    || $menu['can_edit'] || $menu['can_delete'];
            }
        }

        return false;
    }

    public function getCachedMenus()
    {
        $cacheKey = 'user.menus.' . $this->id;
        $ttl = now()->addHour();

        $cached = Cache::remember($cacheKey, $ttl, function () {
            return $this->loadMenuPermissions()->toArray();
        });

        return collect($cached);
    }

    public function loadMenuPermissions()
    {
        $groups = $this->groups()->with('menus')->get();

        $menus = collect();

        foreach ($groups as $group) {
            foreach ($group->menus as $menu) {
                if ($menus->has($menu->id)) {
                    $existing = $menus->get($menu->id);
                    $merged = [];
                    foreach (['can_view', 'can_create', 'can_edit', 'can_delete', 'can_approve', 'can_2fa'] as $perm) {
                        $merged[$perm] = $existing[$perm] || $menu->pivot[$perm];
                    }
                    $menus->put($menu->id, array_merge($menu->toArray(), $merged));
                } else {
                    $menus->put($menu->id, array_merge($menu->toArray(), [
                        'can_view' => $menu->pivot->can_view,
                        'can_create' => $menu->pivot->can_create,
                        'can_edit' => $menu->pivot->can_edit,
                        'can_delete' => $menu->pivot->can_delete,
                        'can_approve' => $menu->pivot->can_approve,
                        'can_2fa' => $menu->pivot->can_2fa,
                    ]));
                }
            }
        }

        if ($this->isSuperAdmin()) {
            $allMenus = Menu::where('is_active', true)->get();
            foreach ($allMenus as $menu) {
                $menus->put($menu->id, array_merge($menu->toArray(), [
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'can_approve' => true,
                    'can_2fa' => true,
                ]));
            }
        }

        return $menus->sortBy('sort_order')->values();
    }

    public function isSuperAdmin(): bool
    {
        return $this->groups()
            ->where('is_super_admin', true)
            ->where('is_active', true)
            ->exists();
    }

    public function clearMenuCache(): void
    {
        Cache::forget('user.menus.' . $this->id);
    }
}
