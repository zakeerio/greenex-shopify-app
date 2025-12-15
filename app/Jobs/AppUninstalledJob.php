<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Osiset\ShopifyApp\Contracts\Queries\Shop as IShopQuery;
use App\Models\User;

class AppUninstalledJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $domain;
    protected $webhookId;

    /**
     * Create a new job instance.
     */
    public function __construct($domain, $webhookId = null)
    {
        $this->domain = $domain;
        $this->webhookId = $webhookId;
    }

    /**
     * Execute the job.
     */
    public function handle(IShopQuery $shopQuery): void
    {
        // Get the shop domain from the webhook
        $shopDomain = $this->domain->toNative();

        Log::info('App uninstalled webhook received', [
            'shop_domain' => $shopDomain,
            'webhook_id' => $this->webhookId?->toNative(),
        ]);

        // Find the shop using the query interface
        $shop = $shopQuery->getByDomain($this->domain);

        if (!$shop) {
            Log::warning('AppUninstalledJob: Shop not found for domain', [
                'domain' => $shopDomain
            ]);
            return;
        }

        try {
            // Find and handle the user
            $user = User::where('shop_domain', $shopDomain)
                        ->orWhere('shopify_domain', $shopDomain)
                        ->first();

            if ($user) {
                // Option 1: Soft delete (if using SoftDeletes trait)
                if (method_exists($user, 'delete')) {
                    $user->delete();
                    Log::info('User soft deleted due to app uninstall', [
                        'shop_domain' => $shopDomain,
                        'user_id' => $user->id
                    ]);
                } else {
                    // Option 2: Revoke access tokens and mark as inactive
                    $user->update([
                        'shopify_access_token' => null,
                        'access_token' => null,
                        // Add any other fields you want to clear
                    ]);
                    Log::info('User access tokens revoked due to app uninstall', [
                        'shop_domain' => $shopDomain,
                        'user_id' => $user->id
                    ]);
                }
            }

            // Clean up any additional shop data
            $this->cleanupShopData($shopDomain);

            Log::info('App uninstalled processing completed', [
                'shop_domain' => $shopDomain,
                'shop_id' => $shop->getId()->toNative()
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing app uninstall', [
                'shop_domain' => $shopDomain,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Clean up shop-specific data from your database
     */
    protected function cleanupShopData(string $shopDomain): void
    {
        try {
            // Clean up any related data here
            // Example:
            // Order::where('shop_domain', $shopDomain)->delete();
            // Product::where('shop_domain', $shopDomain)->delete();
            // Setting::where('shop_domain', $shopDomain)->delete();

            Log::info('Shop data cleanup completed', [
                'shop_domain' => $shopDomain
            ]);

        } catch (\Exception $e) {
            Log::error('Error during shop data cleanup', [
                'shop_domain' => $shopDomain,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AppUninstalledJob failed', [
            'shop_domain' => $this->domain->toNative(),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
