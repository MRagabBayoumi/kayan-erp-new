<?php

use App\Http\Controllers\CompanySettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard', [
        'title' => 'لوحة التحكم',
    ]);
})->name('dashboard');

Route::get('/settings', [CompanySettingController::class, 'index'])->name('settings.index');
Route::put('/settings', [CompanySettingController::class, 'update'])->name('settings.update');
Route::put('/settings/barcode', [CompanySettingController::class, 'updateBarcode'])->name('settings.barcode.update');

// Product & Category Management
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImportController;

// Excel / CSV Import Routes
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
