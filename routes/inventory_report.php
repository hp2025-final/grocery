<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryReportController;

Route::get('/reports/inventory/by-category', [InventoryReportController::class, 'byCategory'])
    ->name('reports.inventory.by-category');
