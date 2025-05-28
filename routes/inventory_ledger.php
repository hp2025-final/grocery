<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryLedgerController;

Route::get('/inventory/{id}/ledger', [InventoryLedgerController::class, 'show'])->name('inventory.ledger');
Route::get('/inventory/{id}/ledger/export', [InventoryLedgerController::class, 'exportPdf'])->name('inventory.ledger.export');
Route::get('/inventory/{id}/ledger-without-rate', [InventoryLedgerController::class, 'showWithoutRate'])->name('inventory.ledger.without_rate');
Route::get('/inventory/{id}/ledger-without-rate/export', [InventoryLedgerController::class, 'exportPdfWithoutRate'])->name('inventory.ledger.without_rate.export');
