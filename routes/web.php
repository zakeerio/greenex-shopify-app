<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Osiset\ShopifyApp\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Shopify App Routes
|--------------------------------------------------------------------------
*/

// Redirect root to the Shopify login/install
// Route::get('/', function () {
//     return redirect()->route('shopify.login');
// });

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
Route::middleware(['verify.shopify'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('shopify.home');
    Route::get('/products', [HomeController::class, 'products'])->name('shopify.products');
});

// 📬 Webhooks
Route::post('/webhook/orders/create', [WebhookController::class, 'handle'])
    ->middleware('auth.webhook');

Route::post('/webhook/app-uninstalled', [WebhookController::class, 'appUninstalled'])
    ->middleware('auth.webhook');

// ⚙️ App Proxy Example
Route::get('/proxy/data', function () {
    return response()->json(['status' => 'Proxy verified']);
})->middleware('auth.proxy');
