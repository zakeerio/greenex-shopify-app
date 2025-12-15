<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

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
        //
        Response::macro('shopifyFrameHeaders', function ($response) {
            $response->headers->set('X-Frame-Options', 'ALLOWALL');
            $response->headers->set(
                'Content-Security-Policy',
                "frame-ancestors https://*.myshopify.com https://admin.shopify.com;"
            );
            return $response;
        });
    }
}
