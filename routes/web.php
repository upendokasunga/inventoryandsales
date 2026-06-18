<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PriceListController;
use App\Http\Controllers\PricingSimulatorController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseSuggestionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\SupplierAnalyticsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get("/", function () {
    return view("welcome");
});

Route::middleware(["auth", "verified"])->group(function () {
    Route::get("/dashboard", [DashboardController::class, "index"])->name("dashboard");

    // --- Users (no show/delete) ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("users", [UserController::class, "index"])->name("users.index");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("users/create", [UserController::class, "create"])->name("users.create");
        Route::post("users", [UserController::class, "store"])->name("users.store");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("users/{user}/edit", [UserController::class, "edit"])->name("users.edit");
        Route::patch("users/{user}", [UserController::class, "update"])->name("users.update");
    });

    // --- Groups ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("groups", [GroupController::class, "index"])->name("groups.index");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("groups/create", [GroupController::class, "create"])->name("groups.create");
        Route::post("groups", [GroupController::class, "store"])->name("groups.store");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("groups/{group}/edit", [GroupController::class, "edit"])->name("groups.edit");
        Route::patch("groups/{group}", [GroupController::class, "update"])->name("groups.update");
        Route::post("groups/{group}/assign-users", [GroupController::class, "assignUsers"])->name("groups.assign-users");
        Route::delete("groups/{group}/users/{user}", [GroupController::class, "removeUser"])->name("groups.remove-user");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("groups/{group}", [GroupController::class, "destroy"])->name("groups.destroy");
    });

    // --- Menus ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("menus", [MenuController::class, "index"])->name("menus.index");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("menus/create", [MenuController::class, "create"])->name("menus.create");
        Route::post("menus", [MenuController::class, "store"])->name("menus.store");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("menus/{menu}/edit", [MenuController::class, "edit"])->name("menus.edit");
        Route::patch("menus/{menu}", [MenuController::class, "update"])->name("menus.update");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("menus/{menu}", [MenuController::class, "destroy"])->name("menus.destroy");
    });

    // --- Settings ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("settings", [SettingController::class, "index"])->name("settings.index");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::patch("settings", [SettingController::class, "update"])->name("settings.update");
    });

    // --- Audit Logs ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("audit-logs", [AuditLogController::class, "index"])->name("audit-logs.index");
    });

    // --- Categories ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("categories", [CategoryController::class, "index"])->name("categories.index");
        Route::get("categories-tree", [CategoryController::class, "tree"])->name("categories.tree");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("categories/create", [CategoryController::class, "create"])->name("categories.create");
        Route::post("categories", [CategoryController::class, "store"])->name("categories.store");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("categories/{category}/edit", [CategoryController::class, "edit"])->name("categories.edit");
        Route::patch("categories/{category}", [CategoryController::class, "update"])->name("categories.update");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("categories/{category}", [CategoryController::class, "destroy"])->name("categories.destroy");
    });

    // --- Units ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("units", [UnitController::class, "index"])->name("units.index");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("units/create", [UnitController::class, "create"])->name("units.create");
        Route::post("units", [UnitController::class, "store"])->name("units.store");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("units/{unit}/edit", [UnitController::class, "edit"])->name("units.edit");
        Route::patch("units/{unit}", [UnitController::class, "update"])->name("units.update");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("units/{unit}", [UnitController::class, "destroy"])->name("units.destroy");
    });

    // --- Products (fixed paths BEFORE wildcard {product}) ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("products", [ProductController::class, "index"])->name("products.index");
        Route::get("products-export", [ProductController::class, "exportCsv"])->name("products.export-csv");
        Route::post("products/barcodes", [ProductController::class, "barcodes"])->name("products.barcodes");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("products/create", [ProductController::class, "create"])->name("products.create");
        Route::post("products", [ProductController::class, "store"])->name("products.store");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("products/{product}/edit", [ProductController::class, "edit"])->name("products.edit");
        Route::patch("products/{product}", [ProductController::class, "update"])->name("products.update");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("products/{product}", [ProductController::class, "destroy"])->name("products.destroy");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("products/{product}/print-barcode", [ProductController::class, "printBarcode"])->name("products.print-barcode");
        Route::get("products/{product}", [ProductController::class, "show"])->name("products.show");
    });

    // --- Customer Groups ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("customer-groups", [CustomerGroupController::class, "index"])->name("customer-groups.index");
        Route::get("customer-groups/{customerGroup}", [CustomerGroupController::class, "show"])->name("customer-groups.show");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("customer-groups/create", [CustomerGroupController::class, "create"])->name("customer-groups.create");
        Route::post("customer-groups", [CustomerGroupController::class, "store"])->name("customer-groups.store");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("customer-groups/{customerGroup}/edit", [CustomerGroupController::class, "edit"])->name("customer-groups.edit");
        Route::patch("customer-groups/{customerGroup}", [CustomerGroupController::class, "update"])->name("customer-groups.update");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("customer-groups/{customerGroup}", [CustomerGroupController::class, "destroy"])->name("customer-groups.destroy");
    });

    // --- Suppliers (fixed paths BEFORE wildcard {supplier}) ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("suppliers", [SupplierController::class, "index"])->name("suppliers.index");
        Route::get("suppliers-export", [SupplierController::class, "exportCsv"])->name("suppliers.export-csv");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("suppliers/create", [SupplierController::class, "create"])->name("suppliers.create");
        Route::post("suppliers", [SupplierController::class, "store"])->name("suppliers.store");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("suppliers/{supplier}/edit", [SupplierController::class, "edit"])->name("suppliers.edit");
        Route::patch("suppliers/{supplier}", [SupplierController::class, "update"])->name("suppliers.update");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("suppliers/{supplier}", [SupplierController::class, "destroy"])->name("suppliers.destroy");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("suppliers/{supplier}", [SupplierController::class, "show"])->name("suppliers.show");
    });

    // --- Purchasing (Phase 6) ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("purchasing/suggestions", [PurchaseSuggestionController::class, "index"])->name("purchasing.suggestions.index");
        Route::get("purchasing/suggestions/{suggestion}", [PurchaseSuggestionController::class, "show"])->name("purchasing.suggestions.show");
        Route::get("purchasing/orders", [PurchaseOrderController::class, "index"])->name("purchasing.orders.index");
        Route::get("purchasing/orders/{purchaseOrder}", [PurchaseOrderController::class, "show"])->name("purchasing.orders.show");
        Route::get("purchasing/receipts", [GoodsReceiptController::class, "index"])->name("purchasing.receipts.index");
        Route::get("purchasing/receipts/{goodsReceipt}", [GoodsReceiptController::class, "show"])->name("purchasing.receipts.show");
        Route::get("purchasing/analytics", [SupplierAnalyticsController::class, "index"])->name("purchasing.analytics");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("purchasing/suggestions/create", [PurchaseSuggestionController::class, "create"])->name("purchasing.suggestions.create");
        Route::post("purchasing/suggestions", [PurchaseSuggestionController::class, "store"])->name("purchasing.suggestions.store");
        Route::post("purchasing/suggestions/generate", [PurchaseSuggestionController::class, "generate"])->name("purchasing.suggestions.generate");
        Route::get("purchasing/orders/create", [PurchaseOrderController::class, "create"])->name("purchasing.orders.create");
        Route::post("purchasing/orders", [PurchaseOrderController::class, "store"])->name("purchasing.orders.store");
        Route::get("purchasing/receipts/create", [GoodsReceiptController::class, "create"])->name("purchasing.receipts.create");
        Route::post("purchasing/receipts", [GoodsReceiptController::class, "store"])->name("purchasing.receipts.store");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("purchasing/orders/{purchaseOrder}/edit", [PurchaseOrderController::class, "edit"])->name("purchasing.orders.edit");
        Route::patch("purchasing/orders/{purchaseOrder}", [PurchaseOrderController::class, "update"])->name("purchasing.orders.update");
        Route::patch("purchasing/receipts/{goodsReceipt}/complete", [GoodsReceiptController::class, "complete"])->name("purchasing.receipts.complete");
    });
    Route::middleware("menu.access:can_approve")->group(function () {
        Route::post("purchasing/suggestions/{suggestion}/approve", [PurchaseSuggestionController::class, "approve"])->name("purchasing.suggestions.approve");
        Route::post("purchasing/suggestions/{suggestion}/reject", [PurchaseSuggestionController::class, "reject"])->name("purchasing.suggestions.reject");
        Route::post("purchasing/orders/{purchaseOrder}/submit-for-approval", [PurchaseOrderController::class, "submitForApproval"])->name("purchasing.orders.submit-approval");
        Route::post("purchasing/orders/{purchaseOrder}/approve", [PurchaseOrderController::class, "approve"])->name("purchasing.orders.approve");
        Route::post("purchasing/orders/{purchaseOrder}/reject", [PurchaseOrderController::class, "reject"])->name("purchasing.orders.reject");
        Route::post("purchasing/orders/{purchaseOrder}/send", [PurchaseOrderController::class, "send"])->name("purchasing.orders.send");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::post("purchasing/orders/{purchaseOrder}/cancel", [PurchaseOrderController::class, "cancel"])->name("purchasing.orders.cancel");
        Route::delete("purchasing/orders/{purchaseOrder}", [PurchaseOrderController::class, "destroy"])->name("purchasing.orders.destroy");
    });

    // --- Customers (fixed paths BEFORE wildcard {customer}) ---
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("customers/create", [CustomerController::class, "create"])->name("customers.create");
        Route::post("customers", [CustomerController::class, "store"])->name("customers.store");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("customers/dashboard", [CustomerDashboardController::class, "index"])->name("customers.dashboard");
        Route::get("customers", [CustomerController::class, "index"])->name("customers.index");
        Route::get("customers/export", [CustomerController::class, "exportCsv"])->name("customers.export-csv");
        Route::get("customers/statement", [StatementController::class, "index"])->name("customers.statement");
        Route::get("customers/statement/{customer}/pdf", [StatementController::class, "pdf"])->name("customers.statement-pdf");
        Route::get("customers/{customer}/profile/{tab?}", [CustomerController::class, "profile"])->name("customers.profile")->where("tab", "overview|credit|statements|purchases|payments|audit-logs");
        Route::get("customers/{customer}", [CustomerController::class, "show"])->name("customers.show");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("customers/{customer}/edit", [CustomerController::class, "edit"])->name("customers.edit");
        Route::patch("customers/{customer}", [CustomerController::class, "update"])->name("customers.update");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("customers/{customer}", [CustomerController::class, "destroy"])->name("customers.destroy");
    });
    Route::middleware("menu.access:can_approve")->group(function () {
        // Reserved for credit limit approval workflow (Phase 6)
    });

    // --- Price Lists ---
    // IMPORTANT: create/store defined BEFORE {priceList} wildcard to avoid 404
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("price-lists/create", [PriceListController::class, "create"])->name("price-lists.create");
        Route::post("price-lists", [PriceListController::class, "store"])->name("price-lists.store");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("price-lists", [PriceListController::class, "dashboard"])->name("price-lists.dashboard");
        Route::get("price-lists/list", [PriceListController::class, "index"])->name("price-lists.index");
        Route::get("price-lists/export", [PriceListController::class, "exportCsv"])->name("price-lists.export-csv");
        Route::get("price-lists/simulator", [PricingSimulatorController::class, "index"])->name("price-lists.simulator");
        Route::post("price-lists/simulate", [PricingSimulatorController::class, "simulate"])->name("price-lists.simulate")->middleware("throttle:30,1");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("price-lists/{priceList}/edit", [PriceListController::class, "edit"])->name("price-lists.edit");
        Route::patch("price-lists/{priceList}", [PriceListController::class, "update"])->name("price-lists.update");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("price-lists/{priceList}", [PriceListController::class, "destroy"])->name("price-lists.destroy");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("price-lists/{priceList}", [PriceListController::class, "show"])->name("price-lists.show");
    });
});

Route::middleware("auth")->group(function () {
    Route::get("/profile", [ProfileController::class, "edit"])->name("profile.edit");
    Route::patch("/profile", [ProfileController::class, "update"])->name("profile.update");
    Route::delete("/profile", [ProfileController::class, "destroy"])->name("profile.destroy");
});

require __DIR__ . "/auth.php";