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

    Route::get('/', [ShopifyController::class, 'dashboard'])->name('home');
    Route::get('/dashboard', [ShopifyController::class, 'dashboard'])->name('dashboard');


    //Route::get('/orders', [ShopifyController::class, 'fetchOrders'])->name('orders');

    // Shop Settings Routes
    Route::get('/settings', [ShopSettingsController::class, 'index'])->name('settings');
    Route::post('/settings/authenticate', [ShopSettingsController::class, 'authenticateAndSave'])->name('authenticateAndSave');
    Route::post('/settings/save', [ShopSettingsController::class, 'store'])->name('savesettings');
    Route::post('/settings/update', [ShopSettingsController::class, 'updatesetting'])->name('updatesetting');

    // Shipment Routes
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments');
    // Route::match(['get', 'post'], '/shipments/print', [ShipmentController::class, 'bulkPrint'])->name('shipments.print');
    // Route::post('/shipments/print', [ShipmentController::class, 'bulkPrint'])->name('shipments.print');
    Route::match(['get', 'post'], '/shipments/print', [ShipmentController::class, 'bulkPrint'])
        ->name('shipments.print');


    // Route::get('/parcel/create', [ShipmentController::class, 'create'])->name('parcel.create');
    // Route::post('parcel/store', [ShipmentController::class, 'store'])->name('parcel.store');
    Route::get('parcel/details/{id}', [ShipmentController::class, 'details'])->name('parcel.details');
    Route::get('parcel/edit/{id}', [ShipmentController::class, 'edit'])->name('parcel.edit');
    // Route::put('parcel/update/{id}', [ShipmentController::class, 'update'])->name('parcel.update');
    // Route::get('parcel/logs/{id}', [ShipmentController::class, 'logs'])->name('parcel.logs');
    // Route::get('parcel/filter',   [ShipmentController::class, 'filter'])->name('parcel.filter');
    // Route::get('parcel/{id}/status/{statusId}', [ShipmentController::class, 'updateStatus'])->name('parcel.updateStatus');
    // Route::delete('parcel/delete/{id}',  [ShipmentController::class, 'destroy'])->name('parcel.delete');



    // Orders routes

    // Main orders page
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');

    // Fetch orders from Shopify (can be used for AJAX refresh)
    Route::get('/orders/fetch', [OrderController::class, 'fetchOrders'])->name('orders.fetch');

    // Send orders to external API
    Route::post('/orders/send', [OrderController::class, 'sendOrders'])->name('orders.send');

    // Individual order routes
    // Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    // Route::put('/orders/{id}', [OrderController::class, 'update'])->name('orders.update');
    // Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');


    // Process and send selected orders with Guzzle
    Route::post('/orders/process-selected', [OrderController::class, 'processSelectedOrders'])->name('orders.process-selected');

    // Route::get('/orders/updateshopifyorder', [OrderController::class, 'updateShopifyOrder'])
    //     ->name('updateShopifyOrder');


    // Route::get('/orders/process-selected', [OrderController::class, 'processSelectedOrders'])->name('orders.process-selected');

    // View sent orders history
    Route::get('/orders/sent', [OrderController::class, 'sentOrders'])->name('orders.sent');
});
