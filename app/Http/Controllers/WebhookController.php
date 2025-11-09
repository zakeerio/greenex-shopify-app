<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class WebhookController extends Controller
{
    public function ordersCreate(Request $request)
    {
        Log::info('Order created webhook', $request->all());
    }

    public function ordersFulfilled(Request $request)
    {
        Log::info('Order fulfilled webhook', $request->all());
    }

    public function fulfillmentsCreate(Request $request)
    {
        Log::info('Fulfillment created webhook', $request->all());
    }

    public function fulfillmentsUpdate(Request $request)
    {
        Log::info('Fulfillment updated webhook', $request->all());
    }

    public function shopUpdate(Request $request)
    {
        Log::info('Shop updated webhook', $request->all());
    }

    public function appUninstalled(Request $request)
    {
        Log::info('App uninstalled webhook', $request->all());

        $shopDomain = $request->input('domain');
        if ($shopDomain) {
            User::where('shopify_domain', $shopDomain)->delete();
        }
    }
}
