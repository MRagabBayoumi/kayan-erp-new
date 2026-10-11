<?php

namespace App\Providers;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Schema;
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
            $settings = null;
            try {
                if (Schema::hasTable('company_settings')) {
                    $settings = CompanySetting::getSettings();
                }
            } catch (\Throwable $e) {
                // Silently fallback if database is not reachable during early boot
            }

            $view->with('globalCompanySetting', $settings);
        });

    }
}
