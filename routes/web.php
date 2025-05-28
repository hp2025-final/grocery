<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerBalancesController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VendorBalancesController;
use App\Http\Controllers\BankBalancesController;
use App\Http\Controllers\InventoryBalancesController;
use App\Http\Controllers\InventoryValuesController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/dashboard/kpis', [App\Http\Controllers\DashboardController::class, 'kpis'])->name('dashboard.kpis');
    Route::get('/dashboard/journal-entries', [App\Http\Controllers\DashboardController::class, 'journalEntries'])->name('dashboard.journal-entries');
    Route::get('/dashboard/sale-chart', [App\Http\Controllers\DashboardController::class, 'saleChart'])->name('dashboard.sale-chart');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/expenses/table', [App\Http\Controllers\ExpensesController::class, 'tableAjax'])->name('expenses.tableAjax');

    // Placeholder module routes
    Route::get('/sales/create', [App\Http\Controllers\SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [App\Http\Controllers\SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{id}', [App\Http\Controllers\SaleController::class, 'show'])->name('sales.show');

    // Expense Accounts
    Route::get('/expense-accounts', [App\Http\Controllers\ExpenseAccountController::class, 'index'])->name('expense_accounts.index');
    Route::get('/expense-accounts/create', [App\Http\Controllers\ExpenseAccountController::class, 'create'])->name('expense-accounts.create');
    Route::post('/expense-accounts', [App\Http\Controllers\ExpenseAccountController::class, 'store'])->name('expense-accounts.store');
    Route::get('/expense-accounts/{id}/ledger', [App\Http\Controllers\ExpenseAccountController::class, 'ledger'])->name('accounts.ledger');
    Route::get('/expense-accounts/{id}/ledger/filter', [App\Http\Controllers\ExpenseAccountController::class, 'ledgerFilter'])->name('expense-accounts.ledger.filter');
    Route::get('/sales/{id}/edit', [App\Http\Controllers\SaleController::class, 'edit'])->name('sales.edit');
    Route::put('/sales/{id}', [App\Http\Controllers\SaleController::class, 'update'])->name('sales.update');
    Route::delete('/sales/{id}', [App\Http\Controllers\SaleController::class, 'destroy'])->name('sales.destroy');
    Route::get('/sales', [App\Http\Controllers\SalesController::class, 'index'])->name('sales.index');
    Route::get('/sales/{id}/pdf', [App\Http\Controllers\SalesController::class, 'exportPdf'])->name('sales.pdf');

    // Customers
    Route::get('/customers/create', [App\Http\Controllers\CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [App\Http\Controllers\CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{id}', [App\Http\Controllers\CustomerController::class, 'update'])->name('customers.update');
    Route::get('/customers/{id}/ledger', [App\Http\Controllers\CustomerLedgerController::class, 'show'])->name('customers.ledger');
    // Vendors
    Route::get('/vendors/create', [App\Http\Controllers\VendorController::class, 'create'])->name('vendors.create');
    Route::post('/vendors', [App\Http\Controllers\VendorController::class, 'store'])->name('vendors.store');
    // Vendors Edit
    Route::get('/vendors/{id}/edit', [App\Http\Controllers\VendorController::class, 'edit'])->name('vendors.edit');
    Route::put('/vendors/{id}', [App\Http\Controllers\VendorController::class, 'update'])->name('vendors.update');
    Route::get('/vendors/{vendor}/ledger', [App\Http\Controllers\VendorLedgerController::class, 'show'])->name('vendors.ledger');
    // Banks
    Route::get('/banks', [App\Http\Controllers\BankController::class, 'index'])->name('banks.index');
    Route::get('/banks/create', [App\Http\Controllers\BankController::class, 'create'])->name('banks.create');
    // Internal Bank Transfer
    Route::get('/bank-transfers/create', [App\Http\Controllers\BankTransferController::class, 'create'])->name('bank_transfers.create');
    Route::post('/bank-transfers', [App\Http\Controllers\BankTransferController::class, 'store'])->name('bank_transfers.store');
    Route::delete('/bank-transfers/{bank_transfer}', [App\Http\Controllers\BankTransferController::class, 'destroy'])->name('bank_transfers.destroy');
    Route::get('/bank-transfers/live-search', [App\Http\Controllers\BankTransferController::class, 'liveSearch'])->name('bank_transfers.live_search');
    Route::post('/banks', [App\Http\Controllers\BankController::class, 'store'])->name('banks.store');
    Route::get('/banks/{id}/edit', [App\Http\Controllers\BankController::class, 'edit'])->name('banks.edit');
    Route::put('/banks/{id}', [App\Http\Controllers\BankController::class, 'update'])->name('banks.update');
    Route::delete('/banks/{id}', [App\Http\Controllers\BankController::class, 'destroy'])->name('banks.destroy');
    Route::get('/banks/{bank}/ledger', [App\Http\Controllers\BankLedgerController::class, 'show'])->name('banks.ledger');
    Route::get('/banks/{bank}/ledger/export', [App\Http\Controllers\BankLedgerController::class, 'exportPdf'])->name('banks.ledger.export');

    Route::get('/purchases/create', [App\Http\Controllers\PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [App\Http\Controllers\PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/purchases/{id}', [App\Http\Controllers\PurchaseController::class, 'show'])->name('purchases.show');
    Route::get('/purchases/{id}/edit', [App\Http\Controllers\PurchaseController::class, 'edit'])->name('purchases.edit');
    Route::put('/purchases/{id}', [App\Http\Controllers\PurchaseController::class, 'update'])->name('purchases.update');
    Route::delete('/purchases/{id}', [App\Http\Controllers\PurchaseController::class, 'destroy'])->name('purchases.destroy');
    Route::get('/purchases', [App\Http\Controllers\PurchasesController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/{id}/pdf', [App\Http\Controllers\PurchasesController::class, 'exportPdf'])->name('purchases.pdf');
    Route::get('/inventory', [App\Http\Controllers\InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [App\Http\Controllers\InventoryController::class, 'create'])->name('inventory.create');
    Route::put('/inventory/{id}', [App\Http\Controllers\InventoryController::class, 'update'])->name('inventory.update');
    Route::post('/inventory', [App\Http\Controllers\InventoryController::class, 'store'])->name('inventory.store');
    Route::delete('/inventory/{id}', [App\Http\Controllers\InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::get('/customer-receipts/live-search', [App\Http\Controllers\CustomerReceiptController::class, 'liveSearch'])->name('customer-receipts.live-search');
    Route::get('/customer-receipts/create/{id?}', [App\Http\Controllers\CustomerReceiptController::class, 'create'])->name('customer-receipts.create');
    Route::post('/customer-receipts', [App\Http\Controllers\CustomerReceiptController::class, 'store'])->name('customer-receipts.store');
    Route::get('/customer-receipts/{id}/edit', [App\Http\Controllers\CustomerReceiptController::class, 'edit'])->name('customer-receipts.edit');
    Route::put('/customer-receipts/{id}', [App\Http\Controllers\CustomerReceiptController::class, 'update'])->name('customer-receipts.update');
    Route::delete('/customer-receipts/{id}', [App\Http\Controllers\CustomerReceiptController::class, 'destroy'])->name('customer-receipts.destroy');
    Route::get('/receipts', [App\Http\Controllers\ReceiptsController::class, 'index'])->name('receipts.index');
    Route::get('/vendor-payments/live-search', [App\Http\Controllers\VendorPaymentController::class, 'liveSearch'])->name('vendor-payments.live-search');
    Route::get('/vendor-payments/create', [App\Http\Controllers\VendorPaymentController::class, 'create'])->name('vendor-payments.create');
    Route::post('/vendor-payments', [App\Http\Controllers\VendorPaymentController::class, 'store'])->name('vendor-payments.store');
    Route::get('/vendor-payments/{id}', [App\Http\Controllers\VendorPaymentController::class, 'show'])->name('vendor-payments.show');
    Route::delete('/vendor-payments/{id}', [App\Http\Controllers\VendorPaymentController::class, 'destroy'])->name('vendor-payments.destroy');
    Route::get('/stock-adjustments/create', [App\Http\Controllers\StockAdjustmentController::class, 'create'])->name('stock-adjustments.create');
    Route::post('/stock-adjustments', [App\Http\Controllers\StockAdjustmentController::class, 'store'])->name('stock-adjustments.store');
    Route::get('/stock-adjustments', [App\Http\Controllers\StockAdjustmentsController::class, 'index'])->name('stock-adjustments.index');
    // Journal Report Route
    Route::get('/reports/journal', [\App\Http\Controllers\JournalReportController::class, 'index'])->name('reports.journal');
    // Expense routes
    Route::get('/expenses/create', [App\Http\Controllers\ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [App\Http\Controllers\ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{expense}/edit', [App\Http\Controllers\ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::delete('/expenses/{expense}', [App\Http\Controllers\ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::get('/expenses', [App\Http\Controllers\ExpensesController::class, 'index'])->name('expenses.index');
    Route::get('/reports/trial-balance', [App\Http\Controllers\ReportsController::class, 'trialBalance'])->name('reports.trial_balance');
    Route::get('/reports/general-ledger', [App\Http\Controllers\ReportsController::class, 'generalLedger'])->name('reports.general_ledger');
    Route::get('/reports/journal', [App\Http\Controllers\ReportsController::class, 'journal'])->name('reports.journal');
    Route::get('/reports/income-statement', [App\Http\Controllers\ReportsController::class, 'incomeStatement'])->name('reports.income_statement');
    Route::get('/reports/balance-sheet', [App\Http\Controllers\ReportsController::class, 'balanceSheet'])->name('reports.balance_sheet');
    Route::get('/reports/customer-list', [App\Http\Controllers\CustomerListController::class, 'index'])->name('reports.customer_list');
    Route::get('/customer-balances', [CustomerBalancesController::class, 'index'])->name('customer-balances.index');
    Route::get('/customer-balances/export', [CustomerBalancesController::class, 'exportPdf'])->name('customer-balances.export');

    // Bank Balances Routes
    Route::get('/bank-balances', [BankBalancesController::class, 'index'])->name('bank-balances.index');
    Route::get('/bank-balances/export', [BankBalancesController::class, 'exportPdf'])->name('bank-balances.export');

    // Inventory Balances Routes
    Route::get('/inventory-balances', [InventoryBalancesController::class, 'index'])->name('inventory-balances.index');
    Route::get('/inventory-balances/export', [InventoryBalancesController::class, 'exportPdf'])->name('inventory-balances.export');

    // Vendor Balances Routes
    Route::get('/vendor-balances', [VendorBalancesController::class, 'index'])->name('vendor-balances.index');
    Route::get('/vendor-balances/export', [VendorBalancesController::class, 'exportPdf'])->name('vendor-balances.export');

    // Inventory Values Routes
    Route::get('/inventory-values', [InventoryValuesController::class, 'index'])->name('inventory-values.index');
    Route::get('/inventory-values/export-pdf', [InventoryValuesController::class, 'exportPdf'])->name('inventory-values.export-pdf');
    Route::get('/inventory-values/info', [InventoryValuesController::class, 'downloadInfo'])->name('inventory-values.info');

});

// Reports UI
Route::middleware(['auth', 'verified'])->prefix('reports')->group(function () {
    Route::get('/trial-balance', [App\Http\Controllers\ReportsController::class, 'trialBalance'])->name('reports.trial_balance');
    Route::get('/ledger', [App\Http\Controllers\ReportsController::class, 'generalLedger'])->name('reports.general_ledger');
    Route::get('/journal', [App\Http\Controllers\ReportsController::class, 'journal'])->name('reports.journal');
    Route::get('/income-statement', [App\Http\Controllers\ReportsController::class, 'incomeStatement'])->name('reports.income_statement');
    Route::get('/balance-sheet', [App\Http\Controllers\ReportsController::class, 'balanceSheet'])->name('reports.balance_sheet');
});

// Include inventory categories routes
require __DIR__.'/inventory_categories.php';
// Include inventory ledger route
require __DIR__.'/inventory_ledger.php';
// Include customer ledger route
require __DIR__.'/customer_ledger.php';
// Include vendor ledger route
require __DIR__.'/vendor_ledger.php';

// Include auth routes
require __DIR__.'/auth.php';
