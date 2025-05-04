<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerLedgerController;

Route::get('/customers/{id}/ledger', [CustomerLedgerController::class, 'show'])->name('customers.ledger');
Route::get('/customers/{id}/ledger/export', [CustomerLedgerController::class, 'exportPdf'])->name('customers.ledger.export');
