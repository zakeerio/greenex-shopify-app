<?php

use App\Http\Controllers\ShopifyController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ShopifyAuthController;
use App\Http\Controllers\ShopifyWebhookController;
use App\Http\Controllers\ShopSettingsController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\OrderController;

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

    // Public inside Shop Context (Settings)
    Route::get('/authentication', [ShopSettingsController::class, 'showAuthenticationForm'])->name('authentication');
    Route::get('/settings', [ShopSettingsController::class, 'index'])->name('settings');
    Route::post('/settings/authenticate', [ShopSettingsController::class, 'authenticateAndSave'])->name('authenticateAndSave');
    Route::post('/settings/save', [ShopSettingsController::class, 'store'])->name('savesettings');
    Route::post('/settings/update', [ShopSettingsController::class, 'updatesetting'])->name('updatesetting');

    Route::get('/', [ShopifyController::class, 'dashboard'])->name('home');
    Route::get('/dashboard', [ShopifyController::class, 'dashboard'])->name('dashboard');

    // Instructions Page
    Route::view('/instructions', 'instructions')->name('instructions');

    // Protected Routes (Require GreenEx Token)
    Route::middleware(['greenex.auth'])->group(function () {

        // Shipment Routes
        Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments');
        Route::match(['get', 'post'], '/shipments/print', [ShipmentController::class, 'bulkPrint'])
            ->name('shipments.print');
        Route::get('parcel/details/{id}', [ShipmentController::class, 'details'])->name('parcel.details');
        Route::get('parcel/edit/{id}', [ShipmentController::class, 'edit'])->name('parcel.edit');

        // Orders routes
        Route::get('/orders', [OrderController::class, 'index'])->name('orders');
        Route::get('/orders/fetch', [OrderController::class, 'fetchOrders'])->name('orders.fetch');
        Route::post('/orders/send', [OrderController::class, 'sendOrders'])->name('orders.send');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::post('/orders/process-selected', [OrderController::class, 'processSelectedOrders'])->name('orders.process-selected');
        Route::get('/orders/sent', [OrderController::class, 'sentOrders'])->name('orders.sent');
    });
});
