<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->validateCsrfTokens(except: [ '*']);


        // Register Shopify middleware
        $middleware->alias([
            // 'auth.shopify' => \Osiset\ShopifyApp\Http\Middleware\AuthShop::class,
            'verify.shopify' => \Osiset\ShopifyApp\Http\Middleware\VerifyShopify::class,
            'auth.webhook' => \Osiset\ShopifyApp\Http\Middleware\AuthWebhook::class,
            'auth.proxy'   => \Osiset\ShopifyApp\Http\Middleware\AuthProxy::class,
            'billable'     => \Osiset\ShopifyApp\Http\Middleware\Billable::class,
            // 'sanitize.shopify' => \Osiset\ShopifyApp\Http\Middleware\VerifyShopify::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

    })

    ->withProviders([
        Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class,
    ])

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
