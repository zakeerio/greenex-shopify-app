<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ShopifyAuthController extends Controller
{
    public function callback(Request $request)
    {
        $shop = $request->get('shop');
        $code = $request->get('code');

        // Exchange code for access token
        $response = Http::asForm()->post("https://{$shop}/admin/oauth/access_token", [
            'client_id' => config('shopify-app.api_key'),
            'client_secret' => config('shopify-app.api_secret'),
            'code' => $code,
        ]);
        $accessToken = $response->json()['access_token'] ?? null;

        // Save/Update user
        $user = User::updateOrCreate(
            ['name' => $shop], // 'name' stores shop domain in this app based on other files
            [
                'email' => "admin@{$shop}",
                'password' => $user->password ?? \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)), // Preserve password if exists
                'shop_domain' => $shop,
                'shopify_access_token' => $accessToken,
                'shopify_api_version' => config('shopify-app.api_version'),
            ]
        );

        // Ensure user is logged in
        if (!Auth::check()) {
            Auth::login($user);
        }

        // Redirect to AppBridge token page inside Shopify
        $target = route('home', ['shop' => $shop, 'host' => base64_encode("$shop/admin")]);

        return view('shopify-app::auth.token', [
            'shopDomain' => $shop,
            'target' => $target,
        ]);
    }
}
