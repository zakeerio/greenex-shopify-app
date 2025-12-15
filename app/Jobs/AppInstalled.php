<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Osiset\ShopifyApp\Contracts\Queries\Shop as IShopQuery;
use Osiset\ShopifyApp\Contracts\ShopModel;
use App\Models\User;

class AppInstalledJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $domain;
    protected $token;
    protected $webhookId;

    /**
     * Create a new job instance.
     */
    public function __construct($domain, $token, $webhookId = null)
    {
        $this->domain = $domain;
        $this->token = $token;
        $this->webhookId = $webhookId;
    }

    /**
     * Execute the job.
     */
    public function handle(IShopQuery $shopQuery): void
    {
        $shopDomain = $this->domain->toNative();
        $accessToken = $this->token->toNative();

        Log::info('App installed webhook received', [
            'shop_domain' => $shopDomain,
            'webhook_id'  => $this->webhookId?->toNative(),
        ]);

        $shop = $shopQuery->getByDomain($this->domain);

        if (!$shop) {
            Log::warning('AppInstalledJob: Shop not found', [
                'domain' => $shopDomain
            ]);
            return;
        }

        try {

            // ✅ CREATE OR UPDATE USER
            $user = User::updateOrCreate(
                [
                    'shop_domain' => $shopDomain
                ],
                [
                    'shopify_domain'       => $shopDomain,
                    'shopify_access_token'=> $accessToken,
                    'access_token'        => $accessToken,
                    'name'                => $shopDomain,
                    'email'               => 'store@' . str_replace('.myshopify.com', '', $shopDomain) . '.com',
                    'password'            => bcrypt(uniqid()),
                ]
            );

            Log::info('User saved on app install', [
                'user_id' => $user->id,
                'shop_domain' => $shopDomain
            ]);

            // ✅ REGISTER WEBHOOKS AUTOMATICALLY
            $this->registerWebhooks($shop);

            Log::info('App install process completed successfully', [
                'shop_domain' => $shopDomain,
                'shop_id' => $shop->getId()->toNative()
            ]);

        } catch (\Exception $e) {

            Log::error('Error during app installation process', [
                'shop_domain' => $shopDomain,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * ✅ Register all required webhooks
     */
    protected function registerWebhooks(ShopModel $shop): void
    {
        $endpoints = [
            'app/uninstalled'     => '/webhooks/app-uninstalled',
            'orders/create'      => '/webhooks/order-create',
            'orders/updated'     => '/webhooks/order-update',
            'products/update'    => '/webhooks/product-update',
            'customers/create'  => '/webhooks/customer-create',
            'fulfillments/create'=> '/webhooks/fulfillment-create',
        ];

        foreach ($endpoints as $topic => $route) {

            $response = $shop->api()->rest('POST', '/admin/api/2024-01/webhooks.json', [
                'webhook' => [
                    'topic'   => $topic,
                    'address'=> config('app.url') . $route,
                    'format' => 'json',
                ]
            ]);

            Log::info('Webhook registered', [
                'topic' => $topic,
                'response' => $response
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AppInstalledJob failed', [
            'shop_domain' => $this->domain->toNative(),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
