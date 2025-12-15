<?php

use App\Http\Controllers\ShopifyController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ShopifyAuthController;

use App\Http\Controllers\ShopifyWebhookController;
use App\Http\Controllers\ShopSettingsController;

Route::post('/webhooks/app-uninstalled', [ShopifyWebhookController::class, 'appUninstalled']);
Route::post('/webhooks/order-create', [ShopifyWebhookController::class, 'orderCreate']);
Route::post('/webhooks/order-update', [ShopifyWebhookController::class, 'orderUpdate']);
Route::post('/webhooks/product-update', [ShopifyWebhookController::class, 'productUpdate']);
Route::post('/webhooks/customer-create', [ShopifyWebhookController::class, 'customerCreate']);
Route::post('/webhooks/fulfillment-create', [ShopifyWebhookController::class, 'fulfillmentCreate']);


// Route::get('/authenticate', [ShopifyAuthController::class, 'authenticate'])->name('shopify.auth');
// Route::get('/authenticate/callback', [ShopifyAuthController::class, 'callback'])->name('shopify.callback');
// Route::get('/authenticate/token', [ShopifyAuthController::class, 'embedded'])->name('authenticate.token');

// Your app home page
// Route::get('/', function () {
//     return "App Loaded Successfully!";
// })->name('app.home');

// Route::get('/dashboard', [ShopifyController::class, 'dashboard'])->name('home');




// Embedded app home
Route::middleware(['verify.shopify'])->group(function () {
    // Route::get('/authenticate/token', [AuthController::class, 'token'])->name('authenticate.token');

    // Route::get('/dashboard', function () {
    //     return view('dashboard');
    // })->name('dashboard');

    Route::get('/dashboard', [ShopifyController::class, 'dashboard'])->name('dashboard');
    Route::get('/', [ShopifyController::class, 'dashboard'])->name('home');


    Route::get('/orders', [ShopifyController::class, 'fetchOrders'])->name('orders');

    // Route::get('/orders', function () {
    //     return view('orders');
    // })->name('orders');

    Route::get('/settings', function () {
        return view('settings');
    })->name('settings');

    Route::get('/shipments', function () {
        return view('shipments');
    })->name('shipments');



    Route::post('/settings/authenticate', [ShopSettingsController::class, 'authenticate']);
    Route::post('/settings/save', [ShopSettingsController::class, 'store']);


    // Route::get('/', function () {
    //      $shopDomain = Auth::user() ?? null;
    //      dd($shopDomain, \Request::all());
    //     return view('dashboard');
    // })->name('home');
});
