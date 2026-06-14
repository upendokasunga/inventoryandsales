<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\CustomerGroup;
use App\Models\Group;
use App\Models\Menu;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Unit;
use App\Observers\AuditObserver;
use App\Services\AuditService;
use App\Services\CategoryService;
use App\Services\CustomerGroupService;
use App\Services\PermissionService;
use App\Services\SettingsService;
use App\Services\SupplierService;
use App\Services\UnitService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PermissionService::class);
        $this->app->singleton(AuditService::class);
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(CategoryService::class);
        $this->app->singleton(UnitService::class);
        $this->app->singleton(CustomerGroupService::class);
        $this->app->singleton(SupplierService::class);
    }

    public function boot(): void
    {
        Category::observe(AuditObserver::class);
        CustomerGroup::observe(AuditObserver::class);
        Group::observe(AuditObserver::class);
        Menu::observe(AuditObserver::class);
        Setting::observe(AuditObserver::class);
        Supplier::observe(AuditObserver::class);
        Unit::observe(AuditObserver::class);
    }
}
