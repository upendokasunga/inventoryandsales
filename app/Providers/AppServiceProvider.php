<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use App\Models\CustomerGroup;
use App\Models\GoodsReceipt;
use App\Models\Group;
use App\Models\Menu;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseSuggestion;
use App\Models\SalesOrder;
use App\Models\Setting;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use App\Models\Unit;
use App\Observers\AuditObserver;
use App\Observers\CustomerObserver;
use App\Observers\GoodsReceiptObserver;
use App\Observers\PriceListItemObserver;
use App\Observers\PriceListObserver;
use App\Observers\ProductObserver;
use App\Observers\PurchaseOrderObserver;
use App\Observers\SalesOrderObserver;
use App\Services\AuditService;
use App\Services\BatchService;
use App\Services\CategoryService;
use App\Services\CreditService;
use App\Services\CustomerAnalyticsService;
use App\Services\CustomerGroupService;
use App\Services\CustomerService;
use App\Services\ExpiryMonitoringService;
use App\Services\FifoService;
use App\Services\FulfillmentService;
use App\Services\GoodsReceiptService;
use App\Services\InventoryAnalyticsService;
use App\Services\InventoryService;
use App\Services\InventoryValuationService;
use App\Services\PermissionService;
use App\Services\PriceListService;
use App\Services\PriceRuleService;
use App\Services\PricingService;
use App\Services\PricingSimulationService;
use App\Services\PurchaseApprovalService;
use App\Services\PurchaseOrderService;
use App\Services\PurchaseSuggestionService;
use App\Services\ReservationService;
use App\Services\SalesOrderApprovalService;
use App\Services\SalesOrderService;
use App\Services\SettingsService;
use App\Services\StatementService;
use App\Services\SupplierAnalyticsService;
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
        $this->app->singleton(PriceListService::class);
        $this->app->singleton(PriceRuleService::class);
        $this->app->singleton(PricingService::class);
        $this->app->singleton(PricingSimulationService::class);
        $this->app->singleton(CustomerService::class);
        $this->app->singleton(CreditService::class);
        $this->app->singleton(StatementService::class);
        $this->app->singleton(CustomerAnalyticsService::class);
        $this->app->singleton(PurchaseSuggestionService::class);
        $this->app->singleton(PurchaseOrderService::class);
        $this->app->singleton(PurchaseApprovalService::class);
        $this->app->singleton(GoodsReceiptService::class);
        $this->app->singleton(SupplierAnalyticsService::class);
        $this->app->singleton(InventoryService::class);
        $this->app->singleton(FifoService::class);
        $this->app->singleton(BatchService::class);
        $this->app->singleton(ExpiryMonitoringService::class);
        $this->app->singleton(InventoryValuationService::class);
        $this->app->singleton(InventoryAnalyticsService::class);
        $this->app->singleton(SalesOrderService::class);
        $this->app->singleton(SalesOrderApprovalService::class);
        $this->app->singleton(ReservationService::class);
        $this->app->singleton(FulfillmentService::class);
    }

    public function boot(): void
    {
        Category::observe(AuditObserver::class);
        Customer::observe(CustomerObserver::class);
        Customer::observe(AuditObserver::class);
        CustomerCreditTransaction::observe(AuditObserver::class);
        CustomerGroup::observe(AuditObserver::class);
        Group::observe(AuditObserver::class);
        Menu::observe(AuditObserver::class);
        PriceList::observe(PriceListObserver::class);
        PriceList::observe(AuditObserver::class);
        PriceListItem::observe(PriceListItemObserver::class);
        PriceListItem::observe(AuditObserver::class);
        Product::observe(ProductObserver::class);
        Product::observe(AuditObserver::class);
        Setting::observe(AuditObserver::class);
        PurchaseOrder::observe(PurchaseOrderObserver::class);
        PurchaseOrder::observe(AuditObserver::class);
        PurchaseSuggestion::observe(AuditObserver::class);
        GoodsReceipt::observe(GoodsReceiptObserver::class);
        GoodsReceipt::observe(AuditObserver::class);
        SalesOrder::observe(SalesOrderObserver::class);
        SalesOrder::observe(AuditObserver::class);
        StockAdjustment::observe(AuditObserver::class);
        Supplier::observe(AuditObserver::class);
        Unit::observe(AuditObserver::class);
    }
}