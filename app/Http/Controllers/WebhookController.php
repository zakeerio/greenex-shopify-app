<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Osiset\ShopifyApp\Http\Middleware\AuthWebhook;

class WebhookController extends Controller
{
    public function __construct()
    {
        // Apply the AuthWebhook middleware to verify webhook requests
        $this->middleware(AuthWebhook::class);
    }

    /**
     * Handle app/uninstalled webhook
     */
    public function handleAppUninstalled(Request $request)
    {
        // The package should automatically handle the webhook and dispatch the job
        // But you can add additional logic here if needed

        Log::info('App uninstalled webhook received in controller', [
            'shop_domain' => $request->get('shop_domain'),
            'webhook_data' => $request->all()
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Handle other webhooks if needed
     */
    public function handleOtherWebhook(Request $request)
    {
        // Handle other webhook types
        return response()->json(['success' => true]);
    }
}
