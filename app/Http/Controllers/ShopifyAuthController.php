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

        // Save to users table
        $user = Auth::user(); // Or your admin user
        $user->update([
            'shop_domain' => $shop,
            'shopify_access_token' => $accessToken,
            'shopify_api_version' => config('shopify-app.api_version'),
        ]);

        // Redirect to AppBridge token page inside Shopify
        $target = route('home', ['shop' => $shop, 'host' => base64_encode("$shop/admin")]);

        return view('shopify-app::auth.token', [
            'shopDomain' => $shop,
            'target' => $target,
        ]);
    }

}
