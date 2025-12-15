<?php

namespace App\Providers;

use App\Listeners\AppInstalledListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Osiset\ShopifyApp\Messaging\Events\AppInstalledEvent;
// use Osiset\ShopifyApp\Messaging\Events\AppInstalledEvent;
use Osiset\ShopifyApp\Messaging\Events\ShopAuthenticatedEvent;
// use App\Listeners\ShopAuthenticatedListener;


class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // AppInstalledEvent::class => [
        //     AppInstalledListener::class,
        // ],
        ShopAuthenticatedEvent::class => [
            \App\Listeners\SaveShopifyCredentials::class,
        ],
    ];
}
