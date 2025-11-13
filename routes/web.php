<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Osiset\ShopifyApp\Http\Controllers\AuthController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShopifyStoreController;
use Osiset\ShopifyApp\Util;

/*
|--------------------------------------------------------------------------
| Shopify App Routes
|--------------------------------------------------------------------------
*/

// Start OAuth
Route::get('/auth', [AuthController::class, 'authenticate'])->name('shopify.auth');

// // OAuth callback
// Route::get('/auth/callback', [AuthController::class, 'authenticateCallback'])->name('shopify.callback');

Route::get('/auth/callback', [ShopifyStoreController::class, 'callback'])->name('shopify.callback');

Route::get('/login', function(Request $request) {
    dd($request->all());
    // $authController = app()->make(\Osiset\ShopifyApp\Http\Controllers\AuthController::class);
    // return $authController->authenticate($request);

})->name('shopify.login');


// Route::get('/login', [\Osiset\ShopifyApp\Http\Controllers\AuthController::class, 'authenticate'])
//     ->name('shopify.login');
// 🧭 Shopify OAuth: Start installation or login
// Route::get('/login', [AuthController::class, 'authenticate'])->name('shopify.login');

// 🔁 Shopify OAuth: Callback after install
Route::get('/authenticate', [AuthController::class, 'authenticate'])->name('shopify.authenticate');

// 🛡️ Protected routes (only accessible after authentication)
Route::middleware(['auth.shopify'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('shopify.home');
    Route::get('/products', [HomeController::class, 'products'])->name('shopify.products');
});

// 📬 Webhooks

Route::prefix('webhook')->group(function () {
    Route::post('orders-create', [WebhookController::class, 'ordersCreate'])->middleware('auth.webhook');
    Route::post('orders-fulfilled', [WebhookController::class, 'ordersFulfilled'])->middleware('auth.webhook');
    Route::post('fulfillments-create', [WebhookController::class, 'fulfillmentsCreate'])->middleware('auth.webhook');
    Route::post('fulfillments-update', [WebhookController::class, 'fulfillmentsUpdate'])->middleware('auth.webhook');
    Route::post('shop-update', [WebhookController::class, 'shopUpdate'])->middleware('auth.webhook');
    Route::post('app-uninstalled', [WebhookController::class, 'appUninstalled'])->middleware('auth.webhook');
});

// ⚙️ App Proxy Example
// Route::get('/proxy/data', function () {
//     return response()->json(['status' => 'Proxy verified']);
// })->middleware('auth.proxy');

// Redirect root to the Shopify login/install
// Route::get('/', function () {
//     // dd(Util::getShopifyConfig('domain'), request()->all());
//     // return redirect()->route('dashboard');
//         return view('dashboard');

// });

// Protected routes for authenticated shops

// Route::group([
    // 'domain' => Util::getShopifyConfig('domain'),
    // 'prefix' => Util::getShopifyConfig('prefix'),
//     'middleware' => ['web', 'verify.shopify'], // ensure shop is logged in
// ], function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/orders', [\App\Http\Controllers\ShopifyController::class, 'fetchOrders'])->name('orders');

    // Route::get('/orders', function () {
    //     return view('orders');
    // })->name('orders');

    Route::get('/settings', function () {
        return view('settings');
    })->name('settings');

    Route::get('/shipments', function () {
        return view('shipments');
    })->name('shipments');
// });


// api calls

// Route::post('/settings/save', [SettingsController::class, 'save'])->name('settings.save');


// Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments');
