<?php

namespace App\Listeners;

use Osiset\ShopifyApp\Messaging\Events\AppInstalledEvent;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class AppInstalledListener
{
    public function handle(AppInstalledEvent $event, Request $request)
    {
        // return;
        dd($event);
        // dd($event->shopId->toNative());
        $shop = $event->shopId->toShop();

        if (! $shop) {
            Log::error("Shop not found for installation", [
                'shop_id' => $event->shopId->toNative(),
            ]);
            return;
        }

        $shopDomain  = $shop->getDomain()->toNative();
        $accessToken = $shop->getAccessToken();
        $email       = $shop->email ?? "owner@$shopDomain";
        $name        = $shop->name ?? $shopDomain;
        $scopes      = implode(',', $shop->getScopes()->toArray());

        User::updateOrCreate(
            ['shop_domain' => $shopDomain],
            [
                'email'                 => $email,
                'name'                  => $name,
                'password'              => bcrypt(str()->random(32)),
                'shopify_access_token'  => $accessToken,
                'shopify_scopes'        => $scopes,
            ]
        );

        Log::info("App installed successfully", [
            'shop'  => $shopDomain,
            'token' => $accessToken,
        ]);
    }

    //     public function handle(\Osiset\ShopifyApp\Messaging\Events\AppInstalledEvent $event): void
    // {
    //     \Log::info('🔥 AppInstalledListener started');

    //     $shop = $event->shop;

    //     if (!$shop) {
    //         \Log::error('❌ Shop object missing in event');
    //         return;
    //     }

    //     $shopDomain  = $shop->getDomain()->toNative();
    //     $accessToken = $shop->getAccessToken();
    //     $email       = $shop->getEmail() ?? "owner@$shopDomain";
    //     $name        = $shop->getName() ?? $shopDomain;
    //     $scopes      = implode(',', $shop->getScopes()->toArray());

    //     \App\Models\User::updateOrCreate(
    //         ['shop_domain' => $shopDomain],
    //         [
    //             'email'                => $email,
    //             'name'                 => $name,
    //             'password'             => bcrypt(str()->random(32)),
    //             'shopify_access_token' => $accessToken,
    //             'shopify_scopes'       => $scopes,
    //         ]
    //     );

    //     \Log::info('✅ Shop stored successfully: ' . $shopDomain);
    // }

}
