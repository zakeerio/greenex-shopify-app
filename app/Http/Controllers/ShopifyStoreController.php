<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShopifyStore;
use Illuminate\Support\Facades\Http;

class ShopifyStoreController extends Controller
{
    //

    public function callback(Request $request)
    {

        dd($request->all());
        $shop = $request->get('shop');
        $code = $request->get('code');

        $response = Http::asForm()->post("https://{$shop}/admin/oauth/access_token", [
            'client_id' => config('shopify-app.api_key'),
            'client_secret' => config('shopify-app.api_secret'),
            'code' => $code,
        ]);

        if ($response->failed()) {
            return response('Failed to get access token', 500);
        }

        $token = $response->json('access_token');

        ShopifyStore::updateOrCreate(
            ['shop_domain' => $shop],
            [
                'access_token' => $token,
                'api_version' => config('shopify-app.api_version', '2025-01'),
            ]
        );

        return redirect('/')->with('status', 'Shopify store connected successfully!');
    }

}
