<?php

namespace App\Actions;

use App\Models\SentOrder;
use App\Models\ShopSetting;
use App\Services\ShopifyOrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\DTOs\PortalOrderDTO;

class ProcessOrderAction
{
    protected $shopifyService;

    public function __construct(ShopifyOrderService $shopifyService)
    {
        $this->shopifyService = $shopifyService;
    }

    public function execute($user, array $selectedOrders)
    {
        $processed = [];
        $failed = [];

        $shopSetting = ShopSetting::where('user_id', $user->id)->first();

        if (!$shopSetting || !$shopSetting->api_token) {
            throw new \Exception('Shop settings or API token missing.');
        }

        foreach ($selectedOrders as $orderData) {
            try {
                // 1. Check for duplicates
                if (SentOrder::where('order_id', $orderData['order_id'])->exists()) {
                    $failed[] = ['order_number' => $orderData['order_number'], 'reason' => 'Duplicate'];
                    continue;
                }

                // 2. Fetch fresh data from Shopify to verify status
                $shopifyOrder = $this->shopifyService->getOrder($user, $orderData['order_id']);

                if (!$shopifyOrder || $shopifyOrder['fulfillment_status'] === 'fulfilled') {
                    $failed[] = ['order_number' => $orderData['order_number'], 'reason' => 'Already fulfilled or not found'];
                    continue;
                }

                // 3. Transform Data using DTO
                // Prepare shop settings for DTO
                $shopSettingsArray = $shopSetting->toArray();
                $shopSettingsArray['name'] = $shopSetting->shop_domain ?? $shopSetting->user_name ?? 'Unknown Shop';

                // Ensure strict phone fallback from input if needed, though DTO usually takes from order
                if (isset($orderData['phone']) && !empty($orderData['phone'])) {
                    $shopifyOrder['customer']['phone'] = $orderData['phone'];
                }

                // Explicitly cast to array as API might return ResponseAccess object
                if (is_object($shopifyOrder) && method_exists($shopifyOrder, 'toArray')) {
                    $shopifyOrder = $shopifyOrder->toArray();
                } else {
                    $shopifyOrder = (array) $shopifyOrder;
                }

                $dto = new PortalOrderDTO($shopifyOrder, $shopSettingsArray);
                $payload = $dto->toArray();

                // 4. Send to Portal
                $response = Http::retry(3, 100)
                    ->withToken($shopSetting->api_token)
                    ->withHeaders([
                        'apiKey' => config('app.backend_api_key')
                    ])
                    ->post(config('app.backend_api_url') . '/order/save', $payload);

                if ($response->successful()) {
                    $responseData = $response->json();
                    Log::info('Portal Order Save Response:', $responseData);

                    // 5. Save locally and update Shopify
                    DB::transaction(function () use ($user, $shopSetting, $orderData, $responseData, $payload) {
                        $trackingId = $responseData['data']['tracking_id'] ?? null;
                        $portalOrderId = $responseData['data']['id'] ?? null;

                        SentOrder::create([
                            'user_id' => $user->id,
                            'shop_setting_id' => $shopSetting->id,
                            'order_id' => $orderData['order_id'],
                            'order_number' => $orderData['order_number'],
                            'tracking_id' => $trackingId,
                            'portal_order_id' => $portalOrderId,
                            'status' => 'sent',
                            'sent_at' => now(),
                        ]);

                        // Fulfill in Shopify
                        $this->shopifyService->fulfillOrderWithTracking(
                            $user,
                            $orderData['order_id'],
                            $trackingId,
                            $portalOrderId
                        );
                    });

                    $processed[] = [
                        'order_number' => $orderData['order_number'],
                        'tracking_id' => $responseData['data']['tracking_id'] ?? '',
                        'portal_order_id' => $responseData['data']['id'] ?? ''
                    ];
                } else {
                    $failed[] = ['order_number' => $orderData['order_number'], 'reason' => 'Portal Error: ' . $response->body()];
                }
            } catch (\Exception $e) {
                Log::error("Order Processing Error: " . $e->getMessage());
                $failed[] = ['order_number' => $orderData['order_number'], 'reason' => $e->getMessage()];
            }
        }

        return ['processed' => $processed, 'failed' => $failed];
    }
}
