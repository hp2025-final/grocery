<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\ProductObserver;
use App\Observers\PurchaseObserver;
use App\Observers\SaleObserver;
use App\Observers\CustomerReceiptObserver;
use App\Observers\VendorPaymentObserver;
use App\Observers\ExpenseObserver;
use App\Observers\StockAdjustmentObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\Product::observe(\App\Observers\ProductObserver::class);
        \App\Models\Purchase::observe(\App\Observers\PurchaseObserver::class);

        \App\Models\CustomerReceipt::observe(\App\Observers\CustomerReceiptObserver::class);
        \App\Models\VendorPayment::observe(\App\Observers\VendorPaymentObserver::class);
        \App\Models\Expense::observe(\App\Observers\ExpenseObserver::class);
        \App\Models\StockAdjustment::observe(\App\Observers\StockAdjustmentObserver::class);
    }
}
