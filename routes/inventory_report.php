<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryReportController;

Route::get('/reports/inventory/by-category', [InventoryReportController::class, 'byCategory'])
    ->name('reports.inventory.by-category');

Route::get('/reports/inventory/product-list', [InventoryReportController::class, 'productList'])
    ->name('reports.inventory.product-list');
    
Route::get('/reports/inventory/product-list/export', [InventoryReportController::class, 'exportProductListPdf'])
    ->name('reports.inventory.product-list.export');
