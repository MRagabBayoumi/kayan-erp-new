<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashierShiftController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PrintTemplateController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\ShippingOfficeController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Application Routes (Protected by auth middleware)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // BayoSoft ERP Home Applications Hub
    Route::get('/', [HomeController::class, 'home'])->name('home');

    // General Administrative Dashboard (Hidden from cashiers)
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    // Company Settings & Print Templates (Admin Only)
    Route::middleware('can:manage-company-settings')->group(function () {
        Route::get('/settings', [CompanySettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [CompanySettingController::class, 'update'])->name('settings.update');
        Route::put('/settings/barcode', [CompanySettingController::class, 'updateBarcode'])->name('settings.barcode.update');

        Route::prefix('settings/print-templates')->name('settings.print-templates.')->group(function () {
            Route::get('/data', [PrintTemplateController::class, 'data'])->name('data');
            Route::put('/{template}', [PrintTemplateController::class, 'update'])->name('update');
            Route::post('/{template}/reset', [PrintTemplateController::class, 'reset'])->name('reset');
            Route::post('/{template}/duplicate', [PrintTemplateController::class, 'duplicate'])->name('duplicate');
        });
    });

    // Users & Roles Management
    Route::middleware('can:manage-users')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });

    Route::middleware('can:manage-roles')->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
    });

    // Dynamic Units of Measurement Management
    Route::middleware('can:manage-units')->group(function () {
        Route::resource('units', UnitController::class)->except(['show']);
    });

    // Product & Category Management
    // Read-only product routes accessible to admin, warehouse, and cashier
    Route::middleware('can:view-products')->group(function () {
        Route::get('/products/lookup-barcode', [ProductController::class, 'lookupBarcode'])->name('products.lookup-barcode');
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{product}', [ProductController::class, 'show'])->whereNumber('product')->name('products.show');
        Route::get('/products/{product}/barcode-svg', [ProductController::class, 'barcodeSvg'])->whereNumber('product')->name('products.barcode-svg');
        Route::get('/products/{product}/ledger/print', [ProductController::class, 'printLedger'])->whereNumber('product')->name('products.ledger.print');
        Route::post('/products/{product}/ledger/direct-print', [ProductController::class, 'directPrintLedger'])->whereNumber('product')->name('products.ledger.direct-print');
        Route::get('/products/{product}/ledger', [ProductController::class, 'ledger'])->whereNumber('product')->name('products.ledger');
        Route::get('/products/{product}/print-label', [ProductController::class, 'printLabel'])->whereNumber('product')->name('products.print-label');
        Route::match(['get', 'post'], '/products/print-labels/batch', [ProductController::class, 'printBatchLabels'])->name('products.print-batch');
        Route::post('/products/{product}/direct-print', [ProductController::class, 'directPrint'])->whereNumber('product')->name('products.direct-print');
        Route::post('/products/direct-print/batch', [ProductController::class, 'directPrintBatch'])->name('products.direct-print-batch');
    });

    // Product modifications & categories (Admin & Warehouse)
    Route::middleware('can:manage-products')->group(function () {
        Route::get('/products/import/template', [ProductImportController::class, 'downloadTemplate'])->name('products.import.template');
        Route::get('/products/import', [ProductImportController::class, 'showForm'])->name('products.import.form');
        Route::post('/products/import/review', [ProductImportController::class, 'review'])->name('products.import.review');
        Route::post('/products/import/confirm', [ProductImportController::class, 'confirm'])->name('products.import.confirm');
        Route::get('/products/import/cancel', [ProductImportController::class, 'cancel'])->name('products.import.cancel');

        Route::get('/products/generate-barcode', [ProductController::class, 'generateBarcode'])->name('products.generate-barcode');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    });

    // Purchasing & Suppliers Invoices (FIFO Receipt)
    Route::middleware('can:access-purchases')->group(function () {
        // Admin-only operations on purchase invoices
        Route::post('/purchases/{purchase}/post', [PurchaseInvoiceController::class, 'post'])
            ->middleware('can:post-purchase-invoice')
            ->name('purchases.post');

        Route::post('/purchases/{purchase}/cancel', [PurchaseInvoiceController::class, 'cancel'])
            ->middleware('can:cancel-purchase-invoice')
            ->name('purchases.cancel');

        Route::post('/purchases/{purchase}/direct-print', [PurchaseInvoiceController::class, 'directPrint'])->name('purchases.direct-print');
        Route::get('/purchases/{purchase}/print', [PurchaseInvoiceController::class, 'print'])->name('purchases.print');
        Route::resource('purchases', PurchaseInvoiceController::class)->except(['edit', 'update', 'destroy']);
    });

    // Sales & Outgoing Invoices (FIFO Costing & Dynamic Pricing)
    Route::middleware('can:access-sales')->group(function () {
        Route::post('/sales/{sale}/post', [SalesInvoiceController::class, 'post'])
            ->middleware('can:post-sales-invoice')
            ->name('sales.post');

        Route::post('/sales/{sale}/void', [SalesInvoiceController::class, 'void'])
            ->middleware('can:void-sales-invoice')
            ->name('sales.void');

        Route::post('/sales/{sale}/unpost-and-edit', [SalesInvoiceController::class, 'unpostAndEdit'])
            ->middleware('can:manage-sales')
            ->name('sales.unpost-and-edit');

        Route::get('/sales/{sale}/edit', [SalesInvoiceController::class, 'edit'])
            ->middleware('can:manage-sales')
            ->name('sales.edit');

        Route::put('/sales/{sale}', [SalesInvoiceController::class, 'update'])
            ->middleware('can:manage-sales')
            ->name('sales.update');

        Route::get('/sales/{sale}/print', [SalesInvoiceController::class, 'print'])->name('sales.print');
        Route::post('/sales/{sale}/direct-print', [SalesInvoiceController::class, 'directPrint'])->name('sales.direct-print');
        Route::resource('sales', SalesInvoiceController::class)->except(['edit', 'update', 'destroy']);

        // Cashier Shifts & Cash Drawer
        Route::get('/shifts', [CashierShiftController::class, 'index'])->name('shifts.index');
        Route::post('/shifts', [CashierShiftController::class, 'store'])->name('shifts.store');
        Route::get('/shifts/{shift}', [CashierShiftController::class, 'show'])->name('shifts.show');
        Route::get('/shifts/{shift}/print', [CashierShiftController::class, 'print'])->name('shifts.print');
        Route::post('/shifts/{shift}/direct-print', [CashierShiftController::class, 'directPrint'])->name('shifts.direct-print');
        Route::post('/shifts/{shift}/movement', [CashierShiftController::class, 'movement'])->name('shifts.movement');
        Route::post('/shifts/{shift}/close', [CashierShiftController::class, 'close'])->name('shifts.close');
    });

    // Warehouse & Inventory Management (Admin & Warehouse)
    Route::middleware('can:access-inventory')->group(function () {
        Route::resource('warehouses', WarehouseController::class)->except(['create', 'show', 'edit']);
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
        Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
        Route::get('/inventory/low-stock/print', [InventoryController::class, 'printLowStock'])->name('inventory.low-stock.print');
        Route::post('/inventory/low-stock/direct-print', [InventoryController::class, 'directPrintLowStock'])->name('inventory.low-stock.direct-print');
        Route::post('/inventory/movements', [InventoryController::class, 'storeMovement'])->name('inventory.movements.store');
    });

    // Directory of Customers & POS Customer Creation
    Route::get('/customers/lookup', [CustomerController::class, 'lookup'])
        ->middleware('can:access-customers')
        ->name('customers.lookup');

    Route::post('/customers', [CustomerController::class, 'store'])
        ->middleware('can:create-customers-pos')
        ->name('customers.store');

    Route::middleware('can:manage-customers')->group(function () {
        Route::resource('customers', CustomerController::class)->except(['store']);
    });

    // Directory of Suppliers & Shipping Offices (Purchases & Inventory)
    Route::middleware('can:manage-purchases')->group(function () {
        Route::get('/suppliers/lookup', [SupplierController::class, 'lookup'])->name('suppliers.lookup');
        Route::resource('suppliers', SupplierController::class);

        Route::resource('shipping-offices', ShippingOfficeController::class)->except(['create', 'show', 'edit']);
    });
});
