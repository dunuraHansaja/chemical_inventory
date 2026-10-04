<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer('*', function ($view) {
            $settings = DB::table('inventory_settings')->pluck('value', 'key');
            $view->with('inventorySettings', [
                'currency_code' => $settings['currency_code'] ?? 'USD',
                'currency_symbol' => $settings['currency_symbol'] ?? '$',
                'vat_rate' => $settings['vat_rate'] ?? '0',
            ]);
        });
    }
}
