<?php

namespace App\Listeners;

use Osiset\ShopifyApp\Messaging\Events\ShopAuthenticatedEvent;
use Osiset\ShopifyApp\Contracts\Queries\Shop as IShopQuery;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SaveShopifyCredentials
{
    public function handle(ShopAuthenticatedEvent $event)
    {
        $shop = app(IShopQuery::class)->getById($event->shopId);

        if (!$shop) {
            Log::error("ShopAuthenticatedEvent: Shop not found for ID {$event->shopId->toNative()}");
            return;
        }

        $shopDomain = $shop->getDomain()->toNative();
        $accessToken = $shop->getAccessToken()->toNative();
        $apiVersion = config('shopify-app.api_version');
        $email = "shop@{$shopDomain}";


        // dd('ShopAuthenticatedEvent triggered', [
        //     'shop_domain' => $shopDomain,
        //     'access_token' => $accessToken,
        //     'api_version' => $apiVersion,
        //     'email' => $email,
        // ]);

        // Multiple lookup strategies
        $user = $this->findUser($shopDomain, $email);

        if ($user) {
            $this->updateUser($user, $shopDomain, $accessToken, $apiVersion);
        } else {
            $this->createUser($shopDomain, $email, $accessToken, $apiVersion);
        }
    }

    protected function findUser($shopDomain, $email)
    {
        // Strategy 1: Find by shop domain (most reliable)
        $user = User::where('shop_domain', $shopDomain)->first();
        if ($user) return $user;

        // Strategy 2: Find by exact email
        $user = User::where('email', $email)->first();
        if ($user) return $user;

        // Strategy 3: Find by shopify domain
        $user = User::where('shopify_domain', $shopDomain)->first();
        if ($user) return $user;

        // Strategy 4: Find by name (shop domain)
        $user = User::where('name', $shopDomain)->first();

        return $user;
    }

    protected function updateUser($user, $shopDomain, $accessToken, $apiVersion)
    {
        $user->update([
            'shopify_domain' => $shopDomain,
            'shop_domain' => $shopDomain,
            'shopify_access_token' => $accessToken,
            'shopify_api_version' => $apiVersion,
            // Note: We don't update email here to avoid unique constraints
        ]);

        Log::info("Shop authenticated and user updated", [
            'shop' => $shopDomain,
            'user_id' => $user->id,
        ]);
    }

    protected function createUser($shopDomain, $email, $accessToken, $apiVersion)
    {
        try {
            $user = User::create([
                'name' => $shopDomain,
                'email' => $email,
                'password' => bcrypt(Str::random(16)),
                'shopify_domain' => $shopDomain,
                'shop_domain' => $shopDomain,
                'shopify_access_token' => $accessToken,
                'shopify_api_version' => $apiVersion,
            ]);

            Log::info("Shop authenticated and new user created", [
                'shop' => $shopDomain,
                'user_id' => $user->id,
            ]);

            return $user;
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                // Race condition: user was created by another process
                Log::warning("User creation failed, finding existing user", [
                    'shop' => $shopDomain,
                    'email' => $email,
                ]);

                $existingUser = User::where('email', $email)->first();
                if ($existingUser) {
                    $this->updateUser($existingUser, $shopDomain, $accessToken, $apiVersion);
                    return $existingUser;
                }
            }
            throw $e;
        }
    }
}
