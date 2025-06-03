<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        Route::middleware('web')
            ->group(function () {
                require base_path('routes/web.php');
                require base_path('routes/auth.php');
                require base_path('routes/inventory_report.php');
                require base_path('routes/sales.php');
            });
            
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));
    }
}
