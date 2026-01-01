<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ShopifyOrderService
{
    protected $api_version;

    public function __construct()
    {
        $this->api_version = config('shopify-app.api_version');
    }

    /**
     * Fetch orders from Shopify
     */
    public function fetchOrders($shop, array $params = [])
    {
        try {
            $response = $shop->api()->rest('GET', "/admin/api/{$this->api_version}/orders.json", $params);
           

            if ($response['errors'] ?? false) {
                Log::error('Shopify API Error', $response);
                throw new \Exception('Failed to fetch orders from Shopify.', $response['status'] ?? 500);
            }

            $orders = $response['body']['orders'] ?? [];

            // Convert ResponseAccess to array if needed (Top level)
            if ($orders instanceof \Gnikyt\BasicShopifyAPI\ResponseAccess) {
                $orders = $orders->toArray();
            }

            return $this->processOrders($shop, $orders);
        } catch (\Exception $e) {
            Log::error('Shopify Service Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process and format raw Shopify orders
     */
    private function processOrders($user, array $orders): array
    {
        $processedOrders = [];

        foreach ($orders as $order) {
            // Convert ResponseAccess to array if needed
            if ($order instanceof \Gnikyt\BasicShopifyAPI\ResponseAccess) {
                $order = $order->toArray();
            }

            // Helper function to convert nested ResponseAccess objects
            $convertToArray = function ($item) {
                if ($item instanceof \Gnikyt\BasicShopifyAPI\ResponseAccess) {
                    return $item->toArray();
                }
                return (array)$item;
            };

            // Determine Payment Type (COD vs PREPAID)
            $gateways = $order['payment_gateway_names'] ?? [];
            $isCod = false;
            foreach ($gateways as $gateway) {
                if (stripos($gateway, 'cash') !== false || stripos($gateway, 'cod') !== false) {
                    $isCod = true;
                    break;
                }
            }
            $paymentType = $isCod ? 'COD' : 'PREPAID';

            // Calculate total pieces and weight
            $totalPieces = 0;
            $totalWeight = 0;

            // Ensure line_items is an array
            $lineItems = isset($order['line_items']) ? $order['line_items'] : [];
            if ($lineItems instanceof \Gnikyt\BasicShopifyAPI\ResponseAccess) {
                $lineItems = $lineItems->toArray();
            }

            $processedLineItems = [];
            foreach ($lineItems as $item) {
                $item = $convertToArray($item);
                $processedLineItems[] = $item;
                $totalPieces += $item['quantity'] ?? 0;
                $totalWeight += ($item['grams'] ?? 0) * ($item['quantity'] ?? 0);
            }

            // Convert weight to KG (Shopify returns grams)
            $totalWeight = $totalWeight / 1000;
            $totalWeight = max(0.1, $totalWeight);

            // Check if order already sent
            $alreadySent = null;
            if ($user) {
                $alreadySent = \App\Models\SentOrder::where('user_id', $user->id)
                    ->where('order_id', $order['id'])
                    ->first();
            }

            // Check fulfillment status
            $fulfillmentStatus = $order['fulfillment_status'] ?? null;
            $isAlreadyFulfilled = $fulfillmentStatus === 'fulfilled';

            // Check if order can be processed
            $canBeProcessed = !$alreadySent && !$isAlreadyFulfilled;

            $processedOrder = [
                'id' => $order['id'] ?? null,
                'order_number' => $order['order_number'] ?? null,
                'name' => $order['name'] ?? null,
                'contact_email' => $order['contact_email'] ?? null,
                'phone' => $order['phone'] ?? null,
                'created_at' => $order['created_at'] ?? null,
                'updated_at' => $order['updated_at'] ?? null,
                'processed_at' => $order['processed_at'] ?? null,
                'current_total_price' => $order['current_total_price'] ?? 0,
                'total_price' => $order['total_price'] ?? 0,
                'subtotal_price' => $order['subtotal_price'] ?? 0,
                'total_tax' => $order['total_tax'] ?? 0,
                'currency' => $order['currency'] ?? 'USD',
                'financial_status' => $order['financial_status'] ?? 'pending',
                'fulfillment_status' => $fulfillmentStatus,
                'cancelled_at' => $order['cancelled_at'] ?? null,
                'cancel_reason' => $order['cancel_reason'] ?? null,
                'note' => $order['note'] ?? null,
                'note_attributes' => array_map($convertToArray, isset($order['note_attributes']) ? (is_array($order['note_attributes']) ? $order['note_attributes'] : $order['note_attributes']->toArray()) : []),
                'tags' => $order['tags'] ?? '',
                'merchant_id' => $order['id'] ?? null,
                'payment_type' => $paymentType,

                // Calculated fields
                'pieces' => $totalPieces,
                'weight' => round($totalWeight, 2),

                // Status tracking
                'already_sent' => $alreadySent ? true : false,
                'already_fulfilled' => $isAlreadyFulfilled,
                'can_be_processed' => $canBeProcessed,
                'sent_order_data' => $alreadySent ? [
                    'tracking_id' => $alreadySent->tracking_id,
                    'portal_order_id' => $alreadySent->portal_order_id,
                    'status' => $alreadySent->status,
                    'sent_at' => $alreadySent->sent_at
                ] : null,

                // Line items
                'line_items' => $processedLineItems,

                // Shipping address
                'shipping_address' => $convertToArray($order['shipping_address'] ?? []),

                // Billing address
                'billing_address' => $convertToArray($order['billing_address'] ?? []),

                // Customer info
                'customer' => $convertToArray($order['customer'] ?? []),

                // Shipping lines
                'shipping_lines' => array_map($convertToArray, isset($order['shipping_lines']) ? (is_iterable($order['shipping_lines']) ? $order['shipping_lines'] : []) : []),

                // Discount codes
                'discount_codes' => array_map($convertToArray, isset($order['discount_codes']) ? (is_iterable($order['discount_codes']) ? $order['discount_codes'] : []) : []),
            ];

            $processedOrders[] = $processedOrder;
        }

        return $processedOrders;
    }

    /**
     * Get single order
     */
    public function getOrder($shop, $orderId)
    {
        $response = $shop->api()->rest('GET', "/admin/api/{$this->api_version}/orders/{$orderId}.json");
        return $response['body']['order'] ?? null;
    }

    /**
     * Update order tags or attributes
     */
    public function updateOrder($shop, $orderId, array $data)
    {
        return $shop->api()->rest('PUT', "/admin/api/{$this->api_version}/orders/{$orderId}.json", ['order' => $data]);
    }
    /**
     * Fulfill order with tracking information
     */
    public function fulfillOrderWithTracking($shop, $orderId, $trackingId, $portalOrderId = null)
    {
        try {
            // 1. Update Note and Attributes
            $this->updateOrderNote($shop, $orderId, $trackingId, $portalOrderId);

            // 2. Get Fulfillment Orders
            $fulfillmentOrders = $this->getFulfillmentOrders($shop, $orderId);

            if (empty($fulfillmentOrders)) {
                // Fallback to legacy
                return $this->createLegacyFulfillment($shop, $orderId, $trackingId);
            }

            // 3. Create Fulfillment
            $fulfillmentOrder = $fulfillmentOrders[0];
            $payload = [
                'fulfillment' => [
                    'message' => 'Shipped via GreenEx',
                    'notify_customer' => true,
                    'tracking_info' => [
                        'number' => $trackingId,
                        'company' => 'GreenEx',
                        'url' => $this->generateTrackingUrl($trackingId)
                    ],
                    'line_items_by_fulfillment_order' => [[
                        'fulfillment_order_id' => $fulfillmentOrder['id'],
                        'fulfillment_order_line_items' => array_map(function ($item) {
                            return ['id' => $item['id'], 'quantity' => $item['quantity']];
                        }, $fulfillmentOrder['line_items'] ?? [])
                    ]]
                ]
            ];

            $shop->api()->rest('POST', "/admin/api/{$this->api_version}/fulfillments.json", $payload);
            return true;
        } catch (\Exception $e) {
            Log::error("Fulfillment Error: {$e->getMessage()}");
            return false;
        }
    }

    private function updateOrderNote($shop, $orderId, $trackingId, $portalOrderId)
    {
        $order = $this->getOrder($shop, $orderId);
        $existingNote = $order['note'] ?? '';
        $newNote = trim($existingNote . "\n\n--- Shipping Info ---\n" .
            "Tracking ID: {$trackingId}\n" .
            "Portal Order ID: {$portalOrderId}\n" .
            "Processed: " . now()->format('Y-m-d H:i:s'));

        $this->updateOrder($shop, $orderId, [
            'note' => $newNote,
            'note_attributes' => [
                ['name' => 'tracking_id', 'value' => $trackingId],
                ['name' => 'portal_order_id', 'value' => $portalOrderId ?? ''],
                ['name' => 'processed_by_portal', 'value' => 'yes']
            ]
        ]);
    }

    private function getFulfillmentOrders($shop, $orderId)
    {
        $response = $shop->api()->rest('GET', "/admin/api/{$this->api_version}/orders/{$orderId}/fulfillment_orders.json");
        $data = $response['body']['fulfillment_orders'] ?? [];

        if ($data instanceof \Gnikyt\BasicShopifyAPI\ResponseAccess) {
            return $data->toArray();
        }

        return $data;
    }

    private function createLegacyFulfillment($shop, $orderId, $trackingId)
    {
        $locationId = $this->getDefaultLocationId($shop);
        if (!$locationId) return false;

        $payload = [
            'fulfillment' => [
                'location_id' => $locationId,
                'tracking_number' => $trackingId,
                'tracking_company' => 'GreenEx',
                'tracking_url' => $this->generateTrackingUrl($trackingId),
                'notify_customer' => true,
                'status' => 'success'
            ]
        ];

        $shop->api()->rest('POST', "/admin/api/{$this->api_version}/orders/{$orderId}/fulfillments.json", $payload);
        return true;
    }

    private function getDefaultLocationId($shop)
    {
        try {
            $response = $shop->api()->rest('GET', "/admin/api/{$this->api_version}/locations.json");
            $locations = $response['body']['locations'] ?? [];

            if ($locations instanceof \Gnikyt\BasicShopifyAPI\ResponseAccess) {
                $locations = $locations->toArray();
            }

            return $locations[0]['id'] ?? env('SHOPIFY_DEFAULT_LOCATION_ID');
        } catch (\Exception $e) {
            return env('SHOPIFY_DEFAULT_LOCATION_ID');
        }
    }

    private function generateTrackingUrl($trackingId)
    {
        return "https://greenex.pk/tracking?tracking_id={$trackingId}";
    }
}
