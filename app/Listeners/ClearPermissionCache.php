<?php

namespace App\Listeners;

use App\Events\GroupPermissionsUpdated;
use App\Services\PermissionService;

class ClearPermissionCache
{
    public function handle(GroupPermissionsUpdated $event): void
    {
        $permissionService = app(PermissionService::class);
        $permissionService->clearGroupMenuCaches($event->group);
    }
}
