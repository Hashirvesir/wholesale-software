<?php

namespace App\Providers;

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
        // Share settings with all views
        if (!app()->runningInConsole() && \Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $settings = \App\Models\Setting::first();
            
            // If no settings found in DB, create a default anonymous object
            if (!$settings) {
                $settings = (object)[
                    'company_name' => 'Wholesaler Software',
                    'currency_symbol' => 'Rs.',
                    'timezone' => 'Asia/Karachi',
                    'invoice_prefix' => 'INV-',
                    'low_stock_threshold' => 10,
                    'show_sale_time' => false,
                ];
            }

            view()->share('settings', $settings);
            
            if (isset($settings->timezone)) {
                config(['app.timezone' => $settings->timezone]);
                date_default_timezone_set($settings->timezone);
            }
        }
    }
}
