<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExpenseLedgerController;

Route::get('/expense-accounts/{id}/ledger', [ExpenseLedgerController::class, 'show'])->name('accounts.ledger');
