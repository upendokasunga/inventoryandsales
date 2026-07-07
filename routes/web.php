<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BankReconciliationController;
use App\Http\Controllers\BankTransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PriceListController;
use App\Http\Controllers\PricingSimulatorController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\PurchaseSuggestionController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ApprovalConfigurationController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SalesDashboardController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\StoreRequestController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DashboardCardConfigController;
use App\Http\Controllers\DocumentNumberingController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\StockAdjustmentController;
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

    // --- Dashboard Cards Config ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("settings/dashboard-cards", [DashboardCardConfigController::class, "index"])->name("settings.dashboard-cards.index");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::post("settings/dashboard-cards/toggle", [DashboardCardConfigController::class, "toggle"])->name("settings.dashboard-cards.toggle");
        Route::post("settings/dashboard-cards/reorder", [DashboardCardConfigController::class, "reorder"])->name("settings.dashboard-cards.reorder");
        Route::post("settings/dashboard-cards/reset", [DashboardCardConfigController::class, "reset"])->name("settings.dashboard-cards.reset");
    });

    // --- Document Numbering Config ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("settings/document-numbering", [DocumentNumberingController::class, "index"])->name("settings.document-numbering.index");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::patch("settings/document-numbering", [DocumentNumberingController::class, "update"])->name("settings.document-numbering.update");
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
    Route::middleware("menu.access:can_print")->group(function () {
        Route::get("products/{product}/print-barcode", [ProductController::class, "printBarcode"])->name("products.print-barcode");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("products/{product}", [ProductController::class, "show"])->name("products.show");
    });

    // --- Customer Groups (fixed paths BEFORE wildcards) ---
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("customer-groups/create", [CustomerGroupController::class, "create"])->name("customer-groups.create");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("customer-groups", [CustomerGroupController::class, "index"])->name("customer-groups.index");
        Route::get("customer-groups/{customerGroup}", [CustomerGroupController::class, "show"])->name("customer-groups.show");
    });
    Route::middleware("menu.access:can_create")->group(function () {
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

    // --- Purchasing (Phase 6; fixed paths BEFORE wildcards) ---
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("purchasing/suggestions/create", [PurchaseSuggestionController::class, "create"])->name("purchasing.suggestions.create");
        Route::get("purchasing/orders/create", [PurchaseOrderController::class, "create"])->name("purchasing.orders.create");
        Route::get("purchasing/receipts/create", [GoodsReceiptController::class, "create"])->name("purchasing.receipts.create");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("purchasing/suggestions", [PurchaseSuggestionController::class, "index"])->name("purchasing.suggestions.index");
        Route::get("purchasing/suggestions/{suggestion}", [PurchaseSuggestionController::class, "show"])->name("purchasing.suggestions.show");
        Route::get("purchasing/orders", [PurchaseOrderController::class, "index"])->name("purchasing.orders.index");
        Route::get("purchasing/orders/{purchaseOrder}", [PurchaseOrderController::class, "show"])->name("purchasing.orders.show");
        Route::get("purchasing/receipts", [GoodsReceiptController::class, "index"])->name("purchasing.receipts.index");
        Route::get("purchasing/receipts/{goodsReceipt}", [GoodsReceiptController::class, "show"])->name("purchasing.receipts.show");
        Route::get("purchasing/analytics", [SupplierAnalyticsController::class, "index"])->name("purchasing.analytics");
        Route::post("purchasing/analytics/recalculate", [SupplierAnalyticsController::class, "recalculate"])->name("purchasing.analytics.recalculate");
    });
    Route::middleware("menu.access:can_print")->group(function () {
        Route::get("purchasing/orders/{purchaseOrder}/print", [PurchaseOrderController::class, "print"])->name("purchasing.orders.print");
        Route::get("purchasing/receipts/{goodsReceipt}/print", [GoodsReceiptController::class, "print"])->name("purchasing.receipts.print");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::post("purchasing/suggestions", [PurchaseSuggestionController::class, "store"])->name("purchasing.suggestions.store");
        Route::post("purchasing/suggestions/generate", [PurchaseSuggestionController::class, "generate"])->name("purchasing.suggestions.generate");
        Route::post("purchasing/orders", [PurchaseOrderController::class, "store"])->name("purchasing.orders.store");
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
        Route::post("purchasing/suggestions/{suggestion}/convert", [PurchaseSuggestionController::class, "convert"])->name("purchasing.suggestions.convert");
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
        Route::get("customers/dashboard", fn() => redirect()->route('dashboard'))->name("customers.dashboard");
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
        Route::get("price-lists", fn() => redirect()->route('dashboard'))->name("price-lists.dashboard");
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

    // --- Inventory (Phase 7) ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("inventory", [InventoryController::class, "index"])->name("inventory.index");
        Route::get("inventory/transactions", [InventoryController::class, "transactions"])->name("inventory.transactions");
        Route::get("inventory/valuation", [InventoryController::class, "valuation"])->name("inventory.valuation");
        Route::get("inventory/analytics", [InventoryController::class, "analytics"])->name("inventory.analytics");
        Route::get("inventory/batches", [BatchController::class, "index"])->name("inventory.batches");
    });

    // --- Stock Adjustments (Phase 7) ---
    // IMPORTANT: create/store defined BEFORE {stockAdjustment} wildcard to avoid 404
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("stock-adjustments/create", [StockAdjustmentController::class, "create"])->name("stock-adjustments.create");
        Route::post("stock-adjustments", [StockAdjustmentController::class, "store"])->name("stock-adjustments.store");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("stock-adjustments", [StockAdjustmentController::class, "index"])->name("stock-adjustments.index");
        Route::get("stock-adjustments/{stockAdjustment}", [StockAdjustmentController::class, "show"])->name("stock-adjustments.show");
    });
    Route::middleware("menu.access:can_print")->group(function () {
        Route::get("stock-adjustments/{stockAdjustment}/print", [StockAdjustmentController::class, "print"])->name("stock-adjustments.print");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("stock-adjustments/{stockAdjustment}/edit", [StockAdjustmentController::class, "edit"])->name("stock-adjustments.edit");
        Route::patch("stock-adjustments/{stockAdjustment}", [StockAdjustmentController::class, "update"])->name("stock-adjustments.update");
        Route::post("stock-adjustments/{stockAdjustment}/submit-approval", [StockAdjustmentController::class, "submitForApproval"])->name("stock-adjustments.submit-approval");
    });
    Route::middleware("menu.access:can_approve")->group(function () {
        Route::post("stock-adjustments/{stockAdjustment}/approve", [StockAdjustmentController::class, "approve"])->name("stock-adjustments.approve");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::patch("stock-adjustments/{stockAdjustment}/complete", [StockAdjustmentController::class, "complete"])->name("stock-adjustments.complete");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("stock-adjustments/{stockAdjustment}", [StockAdjustmentController::class, "destroy"])->name("stock-adjustments.destroy");
    });

    // --- Sales (Phase 8) ---
    // IMPORTANT: create/store defined BEFORE {salesOrder} wildcard to avoid 404
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("sales/orders/create", [SalesOrderController::class, "create"])->name("sales.orders.create");
        Route::post("sales/orders", [SalesOrderController::class, "store"])->name("sales.orders.store");
        Route::post("sales/reservations", [ReservationController::class, "store"])->name("sales.reservations.store");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("sales/dashboard", fn() => redirect()->route('dashboard'))->name("sales.dashboard");
        Route::get("sales/orders", [SalesOrderController::class, "index"])->name("sales.orders.index");
        Route::get("sales/orders/{salesOrder}", [SalesOrderController::class, "show"])->name("sales.orders.show");
        Route::get("sales/reservations", [ReservationController::class, "index"])->name("sales.reservations.index");
        Route::get("sales/reservations/{stockReservation}", [ReservationController::class, "show"])->name("sales.reservations.show");
    });
    Route::middleware("menu.access:can_print")->group(function () {
        Route::get("sales/orders/{salesOrder}/print", [SalesOrderController::class, "print"])->name("sales.orders.print");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("sales/orders/{salesOrder}/edit", [SalesOrderController::class, "edit"])->name("sales.orders.edit");
        Route::patch("sales/orders/{salesOrder}", [SalesOrderController::class, "update"])->name("sales.orders.update");
    });
    Route::middleware("menu.access:can_approve")->group(function () {
        Route::post("sales/orders/{salesOrder}/submit-for-approval", [SalesOrderController::class, "submitForApproval"])->name("sales.orders.submit-approval");
        Route::post("sales/orders/{salesOrder}/approve", [SalesOrderController::class, "approve"])->name("sales.orders.approve");
        Route::post("sales/orders/{salesOrder}/reject", [SalesOrderController::class, "reject"])->name("sales.orders.reject");
        Route::post("sales/orders/{salesOrder}/reserve", [SalesOrderController::class, "reserve"])->name("sales.orders.reserve");
        Route::post("sales/orders/{salesOrder}/pick", [SalesOrderController::class, "startPicking"])->name("sales.orders.pick");
        Route::post("sales/orders/{salesOrder}/pack", [SalesOrderController::class, "markPacked"])->name("sales.orders.pack");
        Route::post("sales/orders/{salesOrder}/fulfill", [SalesOrderController::class, "fulfill"])->name("sales.orders.fulfill");
        Route::post("sales/orders/{salesOrder}/cancel", [SalesOrderController::class, "cancel"])->name("sales.orders.cancel");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("sales/orders/{salesOrder}", [SalesOrderController::class, "destroy"])->name("sales.orders.destroy");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::post("sales/reservations/{stockReservation}/release", [ReservationController::class, "release"])->name("sales.reservations.release");
    });

    // --- POS (Phase 9) ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("pos", [PosController::class, "index"])->name("pos.index");
        Route::get("pos/dashboard", fn() => redirect()->route('dashboard'))->name("pos.dashboard");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("pos/barcode", [PosController::class, "lookupBarcode"])->name("pos.barcode");
        Route::get("pos/sku", [PosController::class, "lookupSku"])->name("pos.sku");
        Route::get("pos/customer", [PosController::class, "getCustomer"])->name("pos.customer");
        Route::get("pos/price", [PosController::class, "getPrice"])->name("pos.price");
        Route::post("pos/validate-credit", [PosController::class, "validateCredit"])->name("pos.validate-credit");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::post("pos/checkout", [PosController::class, "checkout"])->name("pos.checkout")->middleware("throttle:10,1");
    });

    // --- Invoices (Phase 9) ---
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("invoices/create", [InvoiceController::class, "create"])->name("invoices.create");
        Route::post("invoices", [InvoiceController::class, "store"])->name("invoices.store")->middleware("throttle:30,1");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("invoices", [InvoiceController::class, "index"])->name("invoices.index");
        Route::get("invoices/{invoice}", [InvoiceController::class, "show"])->name("invoices.show");
        Route::get("invoices/{invoice}/receipt", [InvoiceController::class, "receipt"])->name("invoices.receipt");
    });
    Route::middleware("menu.access:can_print")->group(function () {
        Route::get("invoices/{invoice}/print", [InvoiceController::class, "print"])->name("invoices.print");
        Route::get("invoices/{invoice}/pdf", [InvoiceController::class, "pdf"])->name("invoices.pdf");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("invoices/{invoice}/edit", [InvoiceController::class, "edit"])->name("invoices.edit");
        Route::patch("invoices/{invoice}", [InvoiceController::class, "update"])->name("invoices.update");
        Route::post("invoices/{invoice}/proforma", [InvoiceController::class, "convertToProforma"])->name("invoices.proforma");
        Route::post("invoices/{invoice}/revert-draft", [InvoiceController::class, "revertProformaToDraft"])->name("invoices.revert-draft");
    });
    Route::middleware("menu.access:can_approve")->group(function () {
        Route::post("invoices/{invoice}/approve", [InvoiceController::class, "approve"])->name("invoices.approve");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("invoices/{invoice}", [InvoiceController::class, "destroy"])->name("invoices.destroy");
    });

    // --- Customer Advances (Phase 4.3) ---
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("customer-advances/create", [\App\Http\Controllers\CustomerAdvanceController::class, "create"])->name("customer-advances.create");
        Route::post("customer-advances", [\App\Http\Controllers\CustomerAdvanceController::class, "store"])->name("customer-advances.store");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("customer-advances", [\App\Http\Controllers\CustomerAdvanceController::class, "index"])->name("customer-advances.index");
        Route::get("customer-advances/{customerAdvance}", [\App\Http\Controllers\CustomerAdvanceController::class, "show"])->name("customer-advances.show");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::post("customer-advances/{customerAdvance}/apply", [\App\Http\Controllers\CustomerAdvanceController::class, "applyToInvoice"])->name("customer-advances.apply");
    });
    Route::middleware("menu.access:can_approve")->group(function () {
        Route::post("customer-advances/{customerAdvance}/cancel", [\App\Http\Controllers\CustomerAdvanceController::class, "cancel"])->name("customer-advances.cancel");
    });

    // --- Payments (Phase 9) ---
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("payments/create/{invoice}", [PaymentController::class, "create"])->name("payments.create");
        Route::post("payments/{invoice}", [PaymentController::class, "store"])->name("payments.store")->middleware("throttle:10,1");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("payments", [PaymentController::class, "index"])->name("payments.index");
        Route::get("payments/{payment}", [PaymentController::class, "show"])->name("payments.show");
    });
    Route::middleware("menu.access:can_print")->group(function () {
        Route::get("payments/{payment}/print", [PaymentController::class, "print"])->name("payments.print");
    });

    // --- Sales Returns (Phase 10) ---
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("sales-returns/create", [SalesReturnController::class, "create"])->name("sales-returns.create");
        Route::post("sales-returns", [SalesReturnController::class, "store"])->name("sales-returns.store");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("sales-returns", [SalesReturnController::class, "index"])->name("sales-returns.index");
        Route::get("sales-returns/{salesReturn}", [SalesReturnController::class, "show"])->name("sales-returns.show");
    });
    Route::middleware("menu.access:can_approve")->group(function () {
        Route::post("sales-returns/{salesReturn}/approve", [SalesReturnController::class, "approve"])->name("sales-returns.approve");
        Route::post("sales-returns/{salesReturn}/reject", [SalesReturnController::class, "reject"])->name("sales-returns.reject");
        Route::post("sales-returns/{salesReturn}/complete", [SalesReturnController::class, "complete"])->name("sales-returns.complete");
    });

    // --- Purchase Returns (Phase 10) ---
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("purchase-returns/create", [PurchaseReturnController::class, "create"])->name("purchase-returns.create");
        Route::post("purchase-returns", [PurchaseReturnController::class, "store"])->name("purchase-returns.store");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("purchase-returns", [PurchaseReturnController::class, "index"])->name("purchase-returns.index");
        Route::get("purchase-returns/{purchaseReturn}", [PurchaseReturnController::class, "show"])->name("purchase-returns.show");
    });
    Route::middleware("menu.access:can_approve")->group(function () {
        Route::post("purchase-returns/{purchaseReturn}/approve", [PurchaseReturnController::class, "approve"])->name("purchase-returns.approve");
        Route::post("purchase-returns/{purchaseReturn}/reject", [PurchaseReturnController::class, "reject"])->name("purchase-returns.reject");
        Route::post("purchase-returns/{purchaseReturn}/complete", [PurchaseReturnController::class, "complete"])->name("purchase-returns.complete");
    });

    // --- Credit Notes (Phase 10) ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("credit-notes", [CreditNoteController::class, "index"])->name("credit-notes.index");
        Route::get("credit-notes/{creditNote}", [CreditNoteController::class, "show"])->name("credit-notes.show");
    });

    // --- Refunds (Phase 10) ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("refunds", [RefundController::class, "index"])->name("refunds.index");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::post("refunds/process", [RefundController::class, "process"])->name("refunds.process")->middleware("throttle:10,1");
    });

    // ==================== PHASE 1 NEW RESOURCES ====================

    // --- Branches ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("branches", [BranchController::class, "index"])->name("branches.index");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("branches/create", [BranchController::class, "create"])->name("branches.create");
        Route::post("branches", [BranchController::class, "store"])->name("branches.store");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("branches/{branch}", [BranchController::class, "show"])->name("branches.show");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("branches/{branch}/edit", [BranchController::class, "edit"])->name("branches.edit");
        Route::patch("branches/{branch}", [BranchController::class, "update"])->name("branches.update");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("branches/{branch}", [BranchController::class, "destroy"])->name("branches.destroy");
    });

    // --- Warehouses ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("warehouses", [WarehouseController::class, "index"])->name("warehouses.index");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("warehouses/create", [WarehouseController::class, "create"])->name("warehouses.create");
        Route::post("warehouses", [WarehouseController::class, "store"])->name("warehouses.store");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("warehouses/{warehouse}", [WarehouseController::class, "show"])->name("warehouses.show");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("warehouses/{warehouse}/edit", [WarehouseController::class, "edit"])->name("warehouses.edit");
        Route::patch("warehouses/{warehouse}", [WarehouseController::class, "update"])->name("warehouses.update");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("warehouses/{warehouse}", [WarehouseController::class, "destroy"])->name("warehouses.destroy");
    });

    // --- Chart of Accounts ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("accounts", [AccountController::class, "index"])->name("accounts.index");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("accounts/create", [AccountController::class, "create"])->name("accounts.create");
        Route::post("accounts", [AccountController::class, "store"])->name("accounts.store");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("accounts/{account}", [AccountController::class, "show"])->name("accounts.show");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("accounts/{account}/edit", [AccountController::class, "edit"])->name("accounts.edit");
        Route::patch("accounts/{account}", [AccountController::class, "update"])->name("accounts.update");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("accounts/{account}", [AccountController::class, "destroy"])->name("accounts.destroy");
    });

    // --- Store Requests ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("store-requests", [StoreRequestController::class, "index"])->name("store-requests.index");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("store-requests/create", [StoreRequestController::class, "create"])->name("store-requests.create");
        Route::post("store-requests", [StoreRequestController::class, "store"])->name("store-requests.store");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("store-requests/{storeRequest}", [StoreRequestController::class, "show"])->name("store-requests.show");
    });
    Route::middleware("menu.access:can_print")->group(function () {
        Route::get("store-requests/{storeRequest}/print", [StoreRequestController::class, "print"])->name("store-requests.print");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("store-requests/{storeRequest}/edit", [StoreRequestController::class, "edit"])->name("store-requests.edit");
        Route::patch("store-requests/{storeRequest}", [StoreRequestController::class, "update"])->name("store-requests.update");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("store-requests/{storeRequest}", [StoreRequestController::class, "destroy"])->name("store-requests.destroy");
    });

    // --- Stock Transfers ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("stock-transfers", [StockTransferController::class, "index"])->name("stock-transfers.index");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("stock-transfers/create", [StockTransferController::class, "create"])->name("stock-transfers.create");
        Route::post("stock-transfers", [StockTransferController::class, "store"])->name("stock-transfers.store");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("stock-transfers/{stockTransfer}", [StockTransferController::class, "show"])->name("stock-transfers.show");
    });
    Route::middleware("menu.access:can_print")->group(function () {
        Route::get("stock-transfers/{stockTransfer}/print", [StockTransferController::class, "print"])->name("stock-transfers.print");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("stock-transfers/{stockTransfer}/edit", [StockTransferController::class, "edit"])->name("stock-transfers.edit");
        Route::patch("stock-transfers/{stockTransfer}", [StockTransferController::class, "update"])->name("stock-transfers.update");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("stock-transfers/{stockTransfer}", [StockTransferController::class, "destroy"])->name("stock-transfers.destroy");
    });

    // --- Expense Categories ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("expense-categories", [ExpenseCategoryController::class, "index"])->name("expense-categories.index");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("expense-categories/create", [ExpenseCategoryController::class, "create"])->name("expense-categories.create");
        Route::post("expense-categories", [ExpenseCategoryController::class, "store"])->name("expense-categories.store");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("expense-categories/{expenseCategory}/edit", [ExpenseCategoryController::class, "edit"])->name("expense-categories.edit");
        Route::patch("expense-categories/{expenseCategory}", [ExpenseCategoryController::class, "update"])->name("expense-categories.update");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("expense-categories/{expenseCategory}", [ExpenseCategoryController::class, "destroy"])->name("expense-categories.destroy");
    });

    // --- Expenses ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("expenses", [ExpenseController::class, "index"])->name("expenses.index");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("expenses/create", [ExpenseController::class, "create"])->name("expenses.create");
        Route::post("expenses", [ExpenseController::class, "store"])->name("expenses.store");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("expenses/{expense}", [ExpenseController::class, "show"])->name("expenses.show");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("expenses/{expense}/edit", [ExpenseController::class, "edit"])->name("expenses.edit");
        Route::patch("expenses/{expense}", [ExpenseController::class, "update"])->name("expenses.update");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("expenses/{expense}", [ExpenseController::class, "destroy"])->name("expenses.destroy");
    });

    // --- Approval Configurations ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("approval-configurations", [ApprovalConfigurationController::class, "index"])->name("approval-configurations.index");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("approval-configurations/create", [ApprovalConfigurationController::class, "create"])->name("approval-configurations.create");
        Route::post("approval-configurations", [ApprovalConfigurationController::class, "store"])->name("approval-configurations.store");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("approval-configurations/{approvalConfiguration}", [ApprovalConfigurationController::class, "show"])->name("approval-configurations.show");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("approval-configurations/{approvalConfiguration}/edit", [ApprovalConfigurationController::class, "edit"])->name("approval-configurations.edit");
        Route::patch("approval-configurations/{approvalConfiguration}", [ApprovalConfigurationController::class, "update"])->name("approval-configurations.update");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("approval-configurations/{approvalConfiguration}", [ApprovalConfigurationController::class, "destroy"])->name("approval-configurations.destroy");
    });

    // --- Journal Entries ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("journal-entries", [JournalEntryController::class, "index"])->name("journal-entries.index");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("journal-entries/create", [JournalEntryController::class, "create"])->name("journal-entries.create");
        Route::post("journal-entries", [JournalEntryController::class, "store"])->name("journal-entries.store");
    });
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("journal-entries/{journalEntry}", [JournalEntryController::class, "show"])->name("journal-entries.show");
    });
    Route::middleware("menu.access:can_print")->group(function () {
        Route::get("journal-entries/{journalEntry}/print", [JournalEntryController::class, "print"])->name("journal-entries.print");
    });
    Route::middleware("menu.access:can_approve")->group(function () {
        Route::post("journal-entries/{journalEntry}/approve", [JournalEntryController::class, "approve"])->name("journal-entries.approve");
        Route::post("journal-entries/{journalEntry}/reverse", [JournalEntryController::class, "reverse"])->name("journal-entries.reverse");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("journal-entries/{journalEntry}", [JournalEntryController::class, "destroy"])->name("journal-entries.destroy");
    });

    // --- Banking (Phase 4.4) ---
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("bank-accounts", [BankAccountController::class, "index"])->name("bank-accounts.index");
        Route::get("bank-accounts/{bankAccount}", [BankAccountController::class, "show"])->name("bank-accounts.show");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("bank-accounts/create", [BankAccountController::class, "create"])->name("bank-accounts.create");
        Route::post("bank-accounts", [BankAccountController::class, "store"])->name("bank-accounts.store");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::get("bank-accounts/{bankAccount}/edit", [BankAccountController::class, "edit"])->name("bank-accounts.edit");
        Route::patch("bank-accounts/{bankAccount}", [BankAccountController::class, "update"])->name("bank-accounts.update");
    });
    Route::middleware("menu.access:can_delete")->group(function () {
        Route::delete("bank-accounts/{bankAccount}", [BankAccountController::class, "destroy"])->name("bank-accounts.destroy");
    });

    // Banking - Transactions
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("bank-accounts/{bankAccount}/transactions", [BankTransactionController::class, "index"])->name("bank-transactions.index");
        Route::get("bank-transactions/{bankTransaction}", [BankTransactionController::class, "show"])->name("bank-transactions.show");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("bank-accounts/{bankAccount}/transactions/create", [BankTransactionController::class, "create"])->name("bank-transactions.create");
        Route::post("bank-accounts/{bankAccount}/transactions", [BankTransactionController::class, "store"])->name("bank-transactions.store");
    });

    // Banking - Reconciliations
    Route::middleware("menu.access:can_view")->group(function () {
        Route::get("bank-reconciliations", [BankReconciliationController::class, "index"])->name("bank-reconciliations.index");
        Route::get("bank-reconciliations/{bankReconciliation}", [BankReconciliationController::class, "show"])->name("bank-reconciliations.show");
    });
    Route::middleware("menu.access:can_create")->group(function () {
        Route::get("bank-reconciliations/create", [BankReconciliationController::class, "create"])->name("bank-reconciliations.create");
        Route::post("bank-reconciliations", [BankReconciliationController::class, "store"])->name("bank-reconciliations.store");
    });
    Route::middleware("menu.access:can_edit")->group(function () {
        Route::post("bank-reconciliations/{bankReconciliation}/match", [BankReconciliationController::class, "match"])->name("bank-reconciliations.match");
        Route::post("bank-reconciliations/{bankReconciliation}/complete", [BankReconciliationController::class, "complete"])->name("bank-reconciliations.complete");
        Route::post("bank-reconciliations/{bankReconciliation}/cancel", [BankReconciliationController::class, "cancel"])->name("bank-reconciliations.cancel");
    });
});

Route::middleware("auth")->group(function () {
    Route::get("/profile", [ProfileController::class, "edit"])->name("profile.edit");
    Route::patch("/profile", [ProfileController::class, "update"])->name("profile.update");
    Route::delete("/profile", [ProfileController::class, "destroy"])->name("profile.destroy");
});

require __DIR__ . "/reports.php";
require __DIR__ . "/auth.php";