<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryLedgerController;

Route::get('/inventory/{id}/ledger', [InventoryLedgerController::class, 'show'])->name('inventory.ledger');
