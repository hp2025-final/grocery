<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryCategoryController;

Route::get('/inventory-categories', [InventoryCategoryController::class, 'index'])->name('inventory-categories.index');
Route::get('/inventory-categories/create', [InventoryCategoryController::class, 'create'])->name('inventory-categories.create');
Route::post('/inventory-categories', [InventoryCategoryController::class, 'store'])->name('inventory-categories.store');
Route::get('/inventory-categories/{id}/edit', [InventoryCategoryController::class, 'edit'])->name('inventory-categories.edit');
Route::put('/inventory-categories/{id}', [InventoryCategoryController::class, 'update'])->name('inventory-categories.update');
Route::delete('/inventory-categories/{id}', [InventoryCategoryController::class, 'destroy'])->name('inventory-categories.destroy');
