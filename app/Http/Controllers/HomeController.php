<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kyon147\LaravelShopify\Facades\Shopify;
use Kyon147\LaravelShopify\Models\Shop; // ✅ Add this line


class HomeController extends Controller
{
    public function index(Request $request)
    {
        $shop = Shop::first();
        return view('home', compact('shop'));
    }

    // public function products()
    // {
    //     $shop = Shop::first();
    //     $response = Shopify::setShop($shop)->get('/products.json');
    //     $products = $response['products'] ?? [];

    //     return view('products', compact('products'));
    // }
}
