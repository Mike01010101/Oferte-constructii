<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        // Folosim un View Composer pentru a partaja culoarea de accent cu layout-ul principal
        View::composer('layouts.dashboard', function ($view) {
            if (Auth::check()) {
                $companyId = Auth::user()->company_id;

                // Folosim cache pentru a nu interoga baza de date la fiecare încărcare de pagină
                $accentColor = Cache::remember('accent_color_company_' . $companyId, 60, function () {
                    // Culoarea de fallback, dacă nu e setată
                    $defaultColor = '#007aff'; 
                    return Auth::user()->company->templateSetting->accent_color ?? $defaultColor;
                });

                $view->with('accentColor', $accentColor);
            }
        });
    }
}
