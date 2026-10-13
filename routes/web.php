<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\ShippingOfficeController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WarehouseController;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard', [
        'title' => 'لوحة التحكم',
        'metrics' => [
            'products_count' => rescue(fn () => Product::count(), 0, false),
            'customers_count' => rescue(fn () => Customer::count(), 0, false),
            'suppliers_count' => rescue(fn () => Supplier::count(), 0, false),
        ],
    ]);
})->name('dashboard');

Route::get('/settings', [CompanySettingController::class, 'index'])->name('settings.index');
Route::put('/settings', [CompanySettingController::class, 'update'])->name('settings.update');
Route::put('/settings/barcode', [CompanySettingController::class, 'updateBarcode'])->name('settings.barcode.update');

// Product & Category Management
Route::get('/products/import/template', [ProductImportController::class, 'downloadTemplate'])->name('products.import.template');
Route::get('/products/import', [ProductImportController::class, 'showForm'])->name('products.import.form');
Route::post('/products/import/review', [ProductImportController::class, 'review'])->name('products.import.review');
Route::post('/products/import/confirm', [ProductImportController::class, 'confirm'])->name('products.import.confirm');
Route::get('/products/import/cancel', [ProductImportController::class, 'cancel'])->name('products.import.cancel');

Route::get('/products/generate-barcode', [ProductController::class, 'generateBarcode'])->name('products.generate-barcode');
Route::get('/products/lookup-barcode', [ProductController::class, 'lookupBarcode'])->name('products.lookup-barcode');
Route::match(['get', 'post'], '/products/print-labels/batch', [ProductController::class, 'printBatchLabels'])->name('products.print-batch');
Route::get('/products/{product}/print-label', [ProductController::class, 'printLabel'])->name('products.print-label');
Route::get('/products/{product}/barcode-svg', [ProductController::class, 'barcodeSvg'])->name('products.barcode-svg');
Route::resource('products', ProductController::class);
Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);

// Warehouse & Inventory Management (Phase 5)
Route::resource('warehouses', WarehouseController::class)->except(['create', 'show', 'edit']);
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::get('/inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
Route::post('/inventory/movements', [InventoryController::class, 'storeMovement'])->name('inventory.movements.store');

// Directory of Customers, Suppliers & Shipping Offices (Phase 6)
Route::get('/customers/lookup', [CustomerController::class, 'lookup'])->name('customers.lookup');
Route::resource('customers', CustomerController::class);

Route::get('/suppliers/lookup', [SupplierController::class, 'lookup'])->name('suppliers.lookup');
Route::resource('suppliers', SupplierController::class);

Route::resource('shipping-offices', ShippingOfficeController::class)->except(['create', 'show', 'edit']);
