<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
}
