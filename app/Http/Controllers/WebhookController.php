<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Webhook received:', $request->all());
        return response()->json(['status' => 'success']);
    }

    /**
     * Handle app uninstall webhook
     */
    public function appUninstalled(Request $request)
    {
        Log::info('App Uninstalled Webhook received:', $request->all());
        $shopDomain = $request->header('X-Shopify-Shop-Domain');

        // Delete the user/shop
        $user = User::where('shopify_domain', $shopDomain)->first();
        if ($user) {
            $user->delete();
        }

        return response()->json(['status' => 'success']);
    }
}
