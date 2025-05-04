<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VendorLedgerController;

// Vendor Ledger Route
Route::get('/vendors/{vendor}/ledger', [VendorLedgerController::class, 'show'])->name('vendors.ledger');
Route::get('/vendors/{vendor}/ledger/export', [VendorLedgerController::class, 'exportPdf'])->name('vendors.ledger.export');
