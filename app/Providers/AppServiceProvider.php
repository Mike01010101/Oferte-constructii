<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
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
        // NOU: Setăm proxy-urile de încredere pentru a funcționa corect în spatele Reverse Proxy
        Request::setTrustedProxies(
            ['*'], // Ai încredere în orice proxy
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO |
            Request::HEADER_X_FORWARDED_AWS_ELB
        );

        // Folosim un View Composer pentru a partaja culoarea de accent cu layout-ul principal
        View::composer('layouts.dashboard', function ($view) {
            if (Auth::check()) {
                $companyId = Auth::user()->company_id;

                $accentColor = Cache::remember('accent_color_company_' . $companyId, 60, function () {
                    $defaultColor = '#007aff'; 
                    return Auth::user()->company->templateSetting->accent_color ?? $defaultColor;
                });

                $view->with('accentColor', $accentColor);
            }
        });
    }
}
