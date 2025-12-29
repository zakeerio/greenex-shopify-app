<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ShopSetting;
use Illuminate\Support\Facades\Log;

class CheckGreenExAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            // Should properly be handled by auth middleware, but safety check
            return redirect()->route('login');
        }

        $shopSetting = ShopSetting::where('user_id', $user->id)->first();
        // dd($shopSetting);

        if ($shopSetting) {
            Log::info($shopSetting);
            // Log::info('Fetching settings from Shopify', [
            //     'shopSetting' => $shopSetting->id,
            //     'shop_name' => $shopSetting->shop_domain ?? 'unknown'
            // ]);
        }

        // Check if token exists and is not empty
        if (!$shopSetting || empty($shopSetting->api_token)) {
            // Redirect to settings page with a message (optional, usually handled by frontend flashing)
            // We need to make sure we don't redirect loop if we are already on settings.
            // But the route group exclusion handles that.
            // Adding a query param or session flash could be useful.
            return redirect()->route('authentication');
        }

        return $next($request);
    }
}
