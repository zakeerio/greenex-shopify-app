<?php

namespace App\Http\Controllers;

use App\Models\ShopifyStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Kyon147\LaravelShopify\Models\Shop;

class ShopifyController extends Controller
{
    public function index()
    {
        $shop = Shop::first(); // Use the model from the package
        return view('welcome', compact('shop'));
    }

    public function callback(Request $request)
    {
        // handle OAuth callback logic here if needed
    }

    public function fetchOrders(Request $request)
    {
        $shop = $request->get('shop');
        $store = ShopifyStore::where('shop_domain', $shop)->first();

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $store->access_token,
        ])->get("https://{$store->shop_domain}/admin/api/2025-01/orders.json");

        $orders = $response->json();

        dd($orders);
        return view('orders', compact('orders'));
    }
}
