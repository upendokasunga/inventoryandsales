<?php

namespace App\Providers;

use App\Events\GroupPermissionsUpdated;
use App\Events\UserLoggedIn;
use App\Events\UserLoginFailed;
use App\Listeners\ClearPermissionCache;
use App\Listeners\LogFailedLogin;
use App\Listeners\LogSuccessfulLogin;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserLoggedIn::class => [
            LogSuccessfulLogin::class,
        ],
        UserLoginFailed::class => [
            LogFailedLogin::class,
        ],
        GroupPermissionsUpdated::class => [
            ClearPermissionCache::class,
        ],
    ];
}
