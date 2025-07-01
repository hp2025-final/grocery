<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesController;

Route::middleware(['web', 'auth', 'verified', 'permission'])->group(function () {
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    // Redirect /sales/create to admin sales form
    Route::get('/sales/create', function () {
        return redirect()->route('admin.sales-form-copy');
    })->name('sales.create');
    Route::post('/sales', [App\Http\Controllers\SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/export-pdf', [SalesController::class, 'exportAllPdf'])->name('sales.export_pdf');
    Route::get('/sales/{id}', [App\Http\Controllers\SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{id}/edit', [App\Http\Controllers\SaleController::class, 'edit'])->name('sales.edit');
    Route::put('/sales/{id}', [App\Http\Controllers\SaleController::class, 'update'])->name('sales.update');
    Route::get('/sales/{id}/pdf', [SalesController::class, 'exportPdf'])->name('sales.pdf');
    Route::delete('/sales/{id}', [SalesController::class, 'destroy'])->name('sales.destroy');
});
