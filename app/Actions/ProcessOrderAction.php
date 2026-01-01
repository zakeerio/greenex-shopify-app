<?php

namespace App\Actions;

use App\Models\SentOrder;
use App\Models\ShopSetting;
use App\Services\ShopifyOrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

                // 3. Transform Data
                $payload = $this->transformForPortal($shopifyOrder, $shopSetting, $orderData);

                // 4. Send to Portal
                $response = Http::retry(3, 100)
                    ->withToken($shopSetting->api_token)
                    ->withHeaders([
                        'apiKey' => config('app.backend_api_key')
                    ])
                    ->post(config('app.backend_api_url') . '/order/save', $payload);

                if ($response->successful()) {
                    $responseData = $response->json();

                    // 5. Save locally and update Shopify
                    DB::transaction(function () use ($user, $shopSetting, $orderData, $responseData, $payload) {
                        SentOrder::create([
                            'user_id' => $user->id,
                            'shop_setting_id' => $shopSetting->id,
                            'order_id' => $orderData['order_id'],
                            'order_number' => $orderData['order_number'],
                            'tracking_id' => $responseData['tracking_id'] ?? null,
                            'portal_order_id' => $responseData['portal_order_id'] ?? null,
                            'status' => 'sent',
                            'sent_at' => now(),
                        ]);

                        // Fulfill in Shopify
                        $this->shopifyService->fulfillOrderWithTracking(
                            $user,
                            $orderData['order_id'],
                            $responseData['tracking_id'] ?? null,
                            $responseData['portal_order_id'] ?? null
                        );
                    });

                    $processed[] = ['order_number' => $orderData['order_number'], 'tracking_id' => $responseData['tracking_id'] ?? ''];
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

    private function transformForPortal($shopifyOrder, $shopSetting, $inputData)
    {
        $shopName = $shopSetting->shop_domain ?? $shopSetting->user_name ?? 'Unknown Shop';

        // Helper to format phone (simplified for now)
        $phone = $inputData['phone'] ?? $shopifyOrder['customer']['phone'] ?? '';

        return [
            'merchant_order_id' => $inputData['order_id'],
            'order_number' => $inputData['order_number'],
            'shop_id' => $shopSetting->id,
            'shop_name' => $shopName,
            'customer' => [
                'name' => ($shopifyOrder['customer']['first_name'] ?? '') . ' ' . ($shopifyOrder['customer']['last_name'] ?? ''),
                'email' => $shopifyOrder['customer']['email'] ?? '',
                'phone' => $phone,
            ],
            'shipping_address' => $shopifyOrder['shipping_address'] ?? [],
            'order_details' => [
                'total_amount' => $shopifyOrder['total_price'],
                'financial_status' => $shopifyOrder['financial_status'] ?? 'pending',
            ],
            'line_items' => $shopifyOrder['line_items'] ?? [],
        ];
    }
}
