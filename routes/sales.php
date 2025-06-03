<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesController;

Route::middleware(['web', 'auth', 'verified', 'permission'])->group(function () {
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('/sales/export-pdf', [SalesController::class, 'exportAllPdf'])->name('sales.export_pdf');
    Route::get('/sales/{id}/pdf', [SalesController::class, 'exportPdf'])->name('sales.pdf');
});
